<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

interface AuthInterface
{
    public function login(string $password, \stdClass $user, string $redirect = "admin", string $notice = "");
    public function updateSessionIfSetRememberMe($redirect): void;
    public function access(string $token = null, string $redirect = null);
    public function logout(string $redirect = ""): void;
    public function role(string $role, string $privilege, string $redirect = null);
    public function bcrypt(string $password, int $cost = 10): string;
}
