<?php

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Interface AuthInterface
 * @package Rudra
 */
interface AuthInterface
{

    /**
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $notice
     * @return mixed
     *
     * Аутентификация, Авторизация
     */
    public function login(string $password, string $hash, string $redirect = 'admin', string $notice);

    /**
     * @param string $redirect
     *
     * Проверка авторизации
     */
    public function check($redirect = 'stargate'): void;

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param array       $redirect
     * @return callable
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на необходимую страницу
     */
    public function authenticate(bool $access = false, string $userToken = null, array $redirect = ['', 'login']);

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
