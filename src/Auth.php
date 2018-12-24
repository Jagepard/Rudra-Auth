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
     * Аутентификация, Авторизация
     *
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, string $hash, string $redirect = 'admin', string $notice)
    {
        if (password_verify($password, $hash)) {
            /* Если установлен флаг remember_me */
            if ($this->container->hasPost('remember_me')) {
                $this->setCookie('RudraPermit', $this->sessionHash, $this->expireTime); // @codeCoverageIgnore
            }

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
        if ($this->container->hasCookie('RudraPermit')) {
            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie Rudra */
            if ($this->sessionHash == $this->container->getCookie('RudraPermit')) {
                /* Восстанавливаем сессию */
                return; // @codeCoverageIgnore
            }

            /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
            $this->unsetCookie();
            $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
            return;
        }
    }

    /**
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     *
     * @param bool        $access
     * @param string|null $userToken
     * @param string      $redirect
     * @return mixed
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
     * Завершить сессию
     *
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void
    {
        $this->container->unsetSession('token');
        $this->unsetCookie();
        $this->handleRedirect($redirect, ['status' => 'Logout']);
    }

    /**
     * Проверка прав доступа
     *
     * @param string $role
     * @param string $privilege
     * @param bool   $access
     * @param string $redirect
     * @return bool
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
