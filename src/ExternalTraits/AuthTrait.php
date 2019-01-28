<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\ExternalTraits;

use Rudra\Interfaces\ContainerInterface;

/**
 * Trait AuthTrait
 * @package Rudra
 */
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
     * Завершить сессию
     *
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void
    {
        rudra()->get('auth')->logout($redirect);
    }

    /**
     * Проверка авторизации
     *
     * @param string $redirect
     */
    public function checkCookie(string $redirect = ''): void
    {
        rudra()->get('auth')->checkCookie($redirect);
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
        return rudra()->get('auth')->access(false, $userToken, $redirect);
    }

    /**
     * Проверка прав доступа
     *
     * @param string $role
     * @param string $privilege
     * @param bool   $access
     * @param string $redirect
     *
     * @return mixed
     */
    public function role(string $role, string $privilege, bool $access = false, string $redirect = '')
    {
        return rudra()->get('auth')->role($role, $privilege, $access, $redirect);
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
        return rudra()->get('auth')->bcrypt($password, $cost);
    }

    /**
     * Получить токен сессии
     *
     * @return string
     */
    public function userToken(): string
    {
        return rudra()->getSession('token');
    }

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
