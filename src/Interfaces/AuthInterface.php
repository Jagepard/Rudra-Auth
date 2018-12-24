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
     * Аутентификация, Авторизация
     *
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, string $hash, string $redirect = 'admin', string $notice);

    /**
     * Проверка авторизации
     *
     * @param string $redirect
     */
    public function checkCookie($redirect = 'login'): void;

    /**
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     *
     * @param bool        $access
     * @param string|null $userToken
     * @param string      $redirect
     * @return callable
     */
    public function access(bool $access = false, string $userToken = null, string $redirect = '');

    /**
     * Завершить сессию
     *
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void;

    /**
     * Проверка прав доступа
     *
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     * @return bool
     */
    public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '');

    /**
     * Получить хеш пароля
     *
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
