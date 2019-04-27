<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra;

use Rudra\Interfaces\AuthInterface;

class Auth extends AuthBase implements AuthInterface
{
    /**
     * @param string $password
     * @param array  $user
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, array $user, string $redirect = 'admin', string $notice = 'Укажите верные данные')
    {
        if (password_verify($password, $user['password'])) {
            $token = md5($user['password'] . $user['email']);
            if ($this->container()->hasPost('remember_me')) {
                $this->setCookie('RudraPermit', $this->sessionHash(), $this->expireTime()); // @codeCoverageIgnore
                $this->setCookie('RudraToken', $token, $this->expireTime());   // @codeCoverageIgnore
                $this->setCookie('RudraUser', $user['email'], $this->expireTime());   // @codeCoverageIgnore
            }

            $this->container()->setSession('token', $token);
            $this->container()->setSession('user', $user['email']);

            return $this->handleRedirect($redirect, ['status' => 'Authorized']);
        }

        return $this->handleRedirect($redirect, ['status' => 'Wrong access data'], $this->loginRedirectWithFlash($notice));
    }

    /**
     * @param string $redirect
     */
    public function checkCookie($redirect = 'login'): void
    {
        /* Если пользователь зашел используя флаг remember_me */
        if ($this->container()->hasCookie('RudraPermit')) {
            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie Rudra */
            if ($this->sessionHash() == $this->container()->getCookie('RudraPermit')) {
                $this->container()->setSession('token', $this->container()->getCookie('RudraToken')); // @codeCoverageIgnore
                $this->container()->setSession('user', $this->container()->getCookie('RudraUser')); // @codeCoverageIgnore
                return; // @codeCoverageIgnore
            }

            $this->unsetCookie();
            $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
        }
    }

    /**
     * @param string|null $token
     * @param string|null $redirect
     * @return bool|callable|mixed
     */
    public function access(string $token = null, string $redirect = null)
    {
        /* Если авторизован */
        if ($this->container()->hasSession('token')) {
            /* Предоставление доступа к общим ресурсам */
            if (!isset($token)) {
                return true;
            }

            /* Предоставление доступа к личным ресурсам пользователя */
            if ($token === $this->container()->getSession('token')) {
                return true;
            }
        }

        /* Если не авторизован */
        if (isset($redirect)) {
            return $this->handleRedirect($redirect, ['status' => 'Access denied']);
        }

        return false;
    }

    /**
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void
    {
        $this->container()->unsetSession('token');
        $this->unsetCookie();
        $this->handleRedirect($redirect, ['status' => 'Logout']);
    }

    /**
     * @param string      $role
     * @param string      $privilege
     * @param string|null $redirect
     * @return bool
     */
    public function role(string $role, string $privilege, string $redirect = null)
    {
        if (in_array($privilege, $this->roles($role))) {
            return true;
        }

        if (isset($redirect)) {
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
