<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Interfaces;

interface AuthInterface
{
    /**
     * @param string $password
     * @param array $user
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function login(string $password, array $user, string $redirect, string $notice);

    /**
     * @param string $redirect
     */
    public function checkCookie($redirect): void;

    /**
     * @param string|null $token
     * @param string|null $redirect
     * @return mixed
     */
    public function access(string $token = null, string $redirect = null);

    /**
     * @param string $redirect
     */
    public function logout(string $redirect): void;

    /**
     * @param string      $role
     * @param string      $privilege
     * @param string|null $redirect
     * @return bool
     */
    public function role(string $role, string $privilege, string $redirect = null);

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
