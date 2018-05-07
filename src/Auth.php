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
class Auth extends AbstractAuth implements AuthInterface
{

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
            if ($this->container->hasPost('remember_me')) {
                $this->support->setCookie('RudraPermit', $this->sessionHash, $this->expireTime); // @codeCoverageIgnore
                $this->support->setCookie('RudraToken', $sessionToken, $this->expireTime);   // @codeCoverageIgnore
            }

            $this->container->setSession('token', $sessionToken);

            return $this->support->handleRedirect($redirect, ['status' => 'Authorized']);
        }

        return $this->support->handleRedirect($redirect, ['status' => 'Wrong access data'], function ($notice) {
            $this->support->loginRedirectWithFlash($notice); // @codeCoverageIgnore
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
        if ($this->container->hasCookie('RudraPermit')) {

            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie Rudra */
            if ($this->sessionHash == $this->container->getCookie('RudraPermit')) {
                /* Восстанавливаем сессию */
                $this->container->setSession('token', $this->container->getCookie('RudraToken')); // @codeCoverageIgnore
                $this->token = $this->container->getSession('token'); // @codeCoverageIgnore
                return; // @codeCoverageIgnore
            }

            /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
            $this->support->unsetCookie();
            $this->support->handleRedirect($redirect, ['status' => 'Authorization data expired']);
            return;
        }

        if ($this->container->hasSession('token')) {
            if (is_bool($this->container->getSession('token'))) {
                $this->token = $this->container->getSession('token');
                return;
            }

            $this->userToken = $this->container->getSession('token');
            $this->token     = true;
            return;
        }

        $this->token = false;
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
        if ($this->container->hasSession('token')) {

            /* Предоставление доступа к личным ресурсам пользователя */
            if (isset($userToken) && ($userToken === $this->userToken)) {
                return true;
            }

            /* Предоставление доступа, к общим ресурсам */
            if ($this->token == $this->container->getSession('token')) {
                return true;
            }
        }

        /* Если не авторизован */
        if (!$access) {
            return $this->support->handleRedirect($redirect, ['status' => 'Access denied']);
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
        $this->container->unsetSession('token');
        $this->support->unsetCookie();
        $this->support->handleRedirect($redirect, ['status' => 'Logout']);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $access
     * @param string $redirect
     * @return bool
     *
     * Проверка прав доступа
     */
    public function role(string $role, string $privilege, bool $access = false, string $redirect = '')
    {
        if ($this->roles[$role] <= $this->roles[$privilege]) {
            return true;
        }

        if (!$access) {
            $this->support->handleRedirect($redirect, ['status' => 'Permissions denied']);
        }

        return false;
    }

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
