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
     * Проверка авторизации
     *
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
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     *
     * @param bool        $access
     * @param string|null $token
     * @param string      $redirect
     * @return mixed
     */
    public function access(bool $access = false, string $token = null, string $redirect = '')
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
        if (!$access) {
            return $this->handleRedirect($redirect, ['status' => 'Access denied']);
        }

        return false;
    }

    /**
     * Завершить сессию
     *
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

        if (!$redirect) {
            $this->handleRedirect($redirect, ['status' => 'Permissions denied']);
        }

        return false;
    }

    /**
     * Получить хеш пароля
     *
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
