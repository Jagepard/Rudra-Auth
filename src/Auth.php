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
class Auth extends AuthBase implements AuthInterface
{

    use AuthHelperTrait;

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

            $sessionToken = md5($password . $hash);

            /* Если установлен флаг remember_me */
            if ($this->container()->hasPost('remember_me')) {
                $this->setCookie('RUDRA', $this->getSessionHash(), $this->getExpireTime()); // @codeCoverageIgnore
                $this->setCookie('RUDRA_INVOICE', $sessionToken, $this->getExpireTime());   // @codeCoverageIgnore
            }

            $this->container()->setSession('token', $sessionToken);

            return $this->handleRedirect($redirect, ['status' => 'Authorized']);
        }

        return $this->handleRedirect($redirect, ['status' => 'Wrong access data'], function ($notice) {
            $this->loginRedirectWithFlash($notice); // @codeCoverageIgnore
        });
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
            if ($this->getSessionHash() == $this->container()->getCookie('RUDRA')) {
                /* Восстанавливаем сессию */
                $this->container()->setSession('token', $this->container()->getCookie('RUDRA_INVOICE'));
                $this->setToken($this->container()->getSession('token'));
                return;
            }

            /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
            $this->unsetCookie();
            $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
            return;
        }

        if ($this->container()->hasSession('token')) {
            $this->setToken($this->container()->getSession('token'));
            return;
        }

        $this->setToken(false);
    }

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param array       $redirect
     * @return mixed
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
     * @return mixed
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
            return $this->handleRedirect($redirect, ['status' => 'Access denied']);
        }

        return false;
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
        $this->handleRedirect($redirect, ['status' => 'Logout']);
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

        $this->handleRedirect($redirect, ['status' => 'Permissions denied']);
    }

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public static function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
