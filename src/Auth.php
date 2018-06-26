<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

use Rudra\Interfaces\AuthInterface;

/**
 * Class Auth
 * @package Rudra
 *
 * Класс работающий с аутентификацией и авторизацией пользователей
 */
class Auth extends AuthBase implements AuthInterface
{

    /**
     * @param string $password
     * @param array  $user
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, array $user, string $redirect = 'admin', string $notice)
    {
        if (password_verify($password, $user['password'])) {

            $sessionToken = md5($password . $user['password']);

            /* Если установлен флаг remember_me */
            if ($this->container->hasPost('remember_me')) {
                $this->setCookie('RudraPermit', $this->sessionHash, $this->expireTime); // @codeCoverageIgnore
                $this->setCookie('RudraToken', $sessionToken, $this->expireTime);   // @codeCoverageIgnore
                $this->setCookie('RudraUserId', $user['id'], $this->expireTime);   // @codeCoverageIgnore
            }

            $this->container->setSession('id', $user['id']);
            $this->container->setSession('token', $sessionToken);

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
    public function checkCookie($redirect = 'login'): void
    {
        /* Если пользователь зашел используя флаг remember_me */
        if ($this->container->hasCookie('RudraPermit')) {
            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie Rudra */
            if ($this->sessionHash == $this->container->getCookie('RudraPermit')) {
                /* Восстанавливаем сессию */
                $this->container->setSession('id', $this->container->getCookie('RudraUserId')); // @codeCoverageIgnore
                $this->container->setSession('token', $this->container->getCookie('RudraToken')); // @codeCoverageIgnore
                return; // @codeCoverageIgnore
            }

            /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
            $this->unsetCookie();
            $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
            return;
        }
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
            /* Предоставление доступа к общим ресурсам пользователя */
            if (!isset($userToken)) {
                return true;
            }

            /* Предоставление доступа к личным ресурсам пользователя */
            if ($userToken === $this->container()->getSession('token')) {
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
        $this->container->unsetSession('token');
        $this->unsetCookie();
        $this->handleRedirect($redirect, ['status' => 'Logout']);
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
            $this->handleRedirect($redirect, ['status' => 'Permissions denied']);
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
