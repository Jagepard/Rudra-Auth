<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
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
     * @param bool        $access
     * @param string|null $userToken
     * @param array       $redirect
     * @return bool|callable
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на необходимую страницу
     */
    public function authenticate(bool $access = false, string $userToken = null, array $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            return $this->access($access, null, $redirect[0]);
        }

        return $this->access($access, $userToken, $redirect[1]);
    }

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param string      $redirect
     * @return bool|callable
     *
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     */
    public function access(bool $access = false, string $userToken = null, string $redirect = '')
    {
        /* Если авторизован */
        if ($this->container()->hasSession('token')) {

            /* Предоставление доступа к личным ресурсам пользователя */
            if (isset($userToken) && ($userToken === $this->getToken())) {
                return true;
            }

            /* Предоставление доступа, к общим ресурсам */
            if ($this->getToken() == $this->container()->getSession('token')) {
                return true;
            }
        }

        /* Если не авторизован */
        if (!$access) {
            return $this->handleResult($redirect, ['status' => 'Access denied']);
        }

        return false;
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
                return;
            }
            /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
            $this->unsetCookie();
            $this->handleResult($redirect, ['status' => 'Authorization data expired']);
            return;
        }

        if ($this->container()->hasSession('token')) {
            $this->setToken($this->container()->getSession('token'));
            return;
        }

        $this->setToken(false);
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
            $this->loginRedirectWithFlash($notice); // @codeCoverageIgnore
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

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public static function bcrypt(string $password, int $cost = 10)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
