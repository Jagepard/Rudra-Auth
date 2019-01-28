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
     * @param array $user
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, array $user, string $redirect = 'admin', string $notice);

    /**
     * Проверка авторизации
     *
     * @param string $redirect
     */
    public function checkCookie($redirect = 'login'): void;

    /**
     * @param string|null $token
     * @param string|null $redirect
     * @return mixed
     */
    public function access(string $token = null, string $redirect = null);

    /**
     * Завершить сессию
     *
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void;

    /**
     * @param string      $role
     * @param string      $privilege
     * @param string|null $redirect
     * @return bool
     */
    public function role(string $role, string $privilege, string $redirect = null);

    /**
     * Получить хеш пароля
     *
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
