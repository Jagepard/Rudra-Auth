<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

interface AuthInterface
{
    /**
     * @param array $user
     * @param string $password
     * @param string $redirect
     * @param string $notice
     * @return callable
     *
     * Authentication
     * --------------
     * Аутентификация
     */
    public function authentication(array $user, string $password, string $redirect = "", string $notice = "");

    /**
     * @param string $redirect
     *
     * Exit authentication session
     * ---------------------------
     * Выйти из сеанса аутентификации
     */
    public function exitAuthenticationSession(string $redirect = ""): void;

    /**
     * @param string|null $token
     * @param string|null $redirect
     * @return bool|callable
     *
     * Authorization
     * Providing access to shared or personal resources
     * ------------------------------------------------
     * Авторизация
     * Предоставление доступа к общим или личным ресурсам
     */
    public function authorization(string $token = null, string $redirect = null);

    /**
     * @param string $role
     * @param string $privilege
     * @param string|null $redirect
     * @return bool
     *
     * Role based access
     * -----------------
     * Доступ на основе ролей
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null);

    /**
     * @param string $password
     * @param int $cost
     * @return string
     *
     * Creates a password hash
     * -----------------------
     * Создаёт хеш пароля
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
