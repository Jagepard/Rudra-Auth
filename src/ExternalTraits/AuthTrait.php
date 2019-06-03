<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\ExternalTraits;

trait AuthTrait
{
    /**
     * @param string $password
     * @param array $user
     * @param string $redirect
     * @param string $notice
     */
    public function login(string $password, array $user, string $redirect = 'admin', string $notice = 'Укажите верные данные'): void
    {
        rudra()->get('auth')->login($password, $user, $redirect, $notice);
    }

    /**
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void
    {
        rudra()->get('auth')->logout($redirect);
    }

    /**
     * @param string $redirect
     */
    public function updateSessionIfSetRememberMe(string $redirect = ''): void
    {
        rudra()->get('auth')->updateSessionIfSetRememberMe($redirect);
    }

    /**
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     *
     * @param string|null $userToken
     * @param string      $redirect
     *
     * @return mixed
     */
    public function auth(string $userToken = null, string $redirect = '')
    {
        return rudra()->get('auth')->access($userToken, $redirect);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param string $redirect
     * @return mixed
     */
    public function role(string $role, string $privilege, string $redirect = '')
    {
        return rudra()->get('auth')->role($role, $privilege, $redirect);
    }

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return rudra()->get('auth')->bcrypt($password, $cost);
    }

    /**
     * @return string
     */
    public function userToken(): string
    {
        return rudra()->getSession('token');
    }
}
