<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Rudra;

trait AuthTrait
{
    public function login(string $password, array $user, string $redirect = "admin", string $notice = "Please enter correct information"): void
    {
        Rudra::run()->get("auth")->login($password, $user, $redirect, $notice);
    }

    public function logout(string $redirect = ''): void
    {
        Rudra::run()->get("auth")->logout($redirect);
    }

    public function updateSessionIfSetRememberMe(string $redirect = ""): void
    {
        Rudra::run()->get("auth")->updateSessionIfSetRememberMe($redirect);
    }

    /**
     * Providing access to shared resources,
     * or personal user resources
     */
    public function auth(string $userToken = null, string $redirect = "")
    {
        return Rudra::run()->get("auth")->access($userToken, $redirect);
    }

    public function role(string $role, string $privilege, string $redirect = "")
    {
        return Rudra::run()->get("auth")->role($role, $privilege, $redirect);
    }

    public function bcrypt(string $password, int $cost = 10): string
    {
        return Rudra::run()->get("auth")->bcrypt($password, $cost);
    }

    public function userToken(): string
    {
        return Rudra::run()->session()->get("token");
    }
}
