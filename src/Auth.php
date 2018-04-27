<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class Auth
 * @package Rudra
 *
 * Класс работающий с аутентификацией и авторизацией пользователей
 */
class Auth extends AbstractAuth
{

    /**
     * @param bool        $accessOrRedirect
     * @param string|null $userToken
     * @param array       $redirect
     * @return bool
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на необходимую страницу
     */
    public function auth(bool $accessOrRedirect = false, string $userToken = null, array $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            return $this->access($accessOrRedirect, null, $redirect[0]);
        }

        return $this->access($accessOrRedirect, $userToken, $redirect[1]);
    }

    /**
     * @param bool        $accessOrRedirect
     * @param string|null $userToken
     * @param string      $redirect
     *
     * @return mixed
     *
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     */
    public function access(bool $accessOrRedirect = false, string $userToken = null, string $redirect = '')
    {
        /* Если авторизован $this->container()->getSession('token') == true */
        if ($this->container()->hasSession('token')) {

            if (isset($userToken)) {
                /* Предоставление доступа к личным ресурсам пользователя */
                if ($userToken === $this->getToken()) {
                    return true;
                }
            } else {
                /* Предоставление доступа, к общим ресурсам */
                if ($this->getToken() == $this->container()->getSession('token')) {
                    return true;
                }
            }
        }

        /* Если не авторизован $this->container()->getSession('token') == 'undefined' */
        if ($accessOrRedirect) {
            return false;
        }

        /* Переадресация, если $accessOrRedirect не установлен */
        return $this->handleResult($redirect, ['status' => 'Access denied']);
    }

    /**
     * Проверка авторизации
     *
     * @param string $redirect
     */
    public function check($redirect = 'stargate'): void
    {
        /* Если пользователь зашел используя флаг remember_me */
        if ($this->container()->hasCookie('RUDRA')) {

            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie RUDRA */
            if (md5($this->container()->getServer('REMOTE_ADDR') . $this->container()->getServer('HTTP_USER_AGENT'))
                == $this->container()->getCookie('RUDRA')
            ) {
                /* Восстанавливаем сессию */
                $this->container()->setSession('token', $this->container()->getCookie('RUDRA_INVOICE'));
                $this->setToken($this->container()->getSession('token'));
            } else {
                /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
                $this->unsetCookie();
                $this->handleResult($redirect, ['status' => 'Authorization data expired']);
            }

        } else {

            if ($this->container()->hasSession('token')) {
                $this->setToken($this->container()->getSession('token'));
            } else {
                $this->setToken(false);
            }
        }
    }

    /**
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, string $hash, string $redirect = 'admin', string $notice)
    {
        if (password_verify($password, $hash)) {
            $this->container()->setSession('token', md5($password . $hash));

            /* Если установлен флаг remember_me */
            if ($this->container()->hasPost('remember_me')) {
                setcookie("RUDRA", md5($this->container()->getServer('REMOTE_ADDR')                    // @codeCoverageIgnore
                    . $this->container()->getServer('HTTP_USER_AGENT')), time() + 3600 * 24 * 7);      // @codeCoverageIgnore
                setcookie("RUDRA_INVOICE", md5($password . $hash), time() + 3600 * 24 * 7); // @codeCoverageIgnore
            }

            return $this->handleResult($redirect, ['status' => 'Authorized']);
        }

        return $this->handleResult($redirect, ['status' => 'Wrong access data'], function ($notice) {
            return $this->loginRedirectWithFlash($notice); // @codeCoverageIgnore
        });

        return $this->handleResult($redirect, ['status' => 'User not found'], function ($notice) {
            return $this->loginRedirectWithFlash($notice); // @codeCoverageIgnore
        });
    }

    /**
     * @param string $redirect
     *
     * Завершить сессию
     */
    public function logout(string $redirect = ''): void
    {
        $this->container()->unsetSession('token');
        $this->unsetCookie();
        $this->handleResult($redirect, ['status' => 'Logout']);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     * @return bool
     *
     * Проверка прав доступа
     */
    public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '')
    {
        if ($this->getRole($role) <= $this->getRole($privilege)) {
            return true;
        }

        if (!$redirectOrAccess) {
            return false;
        }

        $this->handleResult($redirect, ['status' => 'Permissions denied']);
    }

    /**
     * @codeCoverageIgnore
     * @param string $notice
     *
     * Переадресация с добавлением уведомления в 'alert'
     */
    protected function loginRedirectWithFlash(string $notice): void
    {
        $this->container()->setSession('alert', 'main', $notice);
        $this->container()->get('redirect')->run('stargate');
    }
}
