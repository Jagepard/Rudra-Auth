<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Interfaces;

/**
 * Interface AuthInterface
 * @package Rudra
 */
interface AuthInterface
{

    /**
     * @param string $password
     * @param array  $user
     * @param string $redirect
     * @param string $notice
     * @return mixed
     *
     * Аутентификация, Авторизация
     */
    public function login(string $password, array $user, string $redirect = 'admin', string $notice);

    /**
     * @param string $redirect
     *
     * Проверка авторизации
     */
    public function checkCookie($redirect = 'login'): void;

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param string      $redirect
     * @return callable
     *
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     */
    public function access(bool $access = false, string $userToken = null, string $redirect = '');

    /**
     * Завершить сессию
     *
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void;

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     * @return bool
     *
     * Проверка прав доступа
     */
    public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '');

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
