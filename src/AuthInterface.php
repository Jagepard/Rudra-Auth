<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

interface AuthInterface
{
    /**
     * Authentication
     * --------------
     * Аутентификация
     * 
     * @param array $user
     * @param string $password
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function authentication(array $user, string $password, string $redirect = "", string $notice = "");

    /**
     * Exit authentication session
     * ---------------------------
     * Выйти из сеанса аутентификации
     * 
     * @param string $redirect
     */
    public function exitAuthenticationSession(string $redirect = ""): void;

    /**
     * Authorization
     * Providing access to shared or personal resources
     * ------------------------------------------------
     * Авторизация
     * Предоставление доступа к общим или личным ресурсам
     * 
     * @param string|null $token
     * @param string|null $redirect
     * @return bool|callable
     */
    public function authorization(string $token = null, string $redirect = null);

    /**
     * Role based access
     * -----------------
     * Доступ на основе ролей
     * 
     * @param string $role
     * @param string $privilege
     * @param string|null $redirect
     * @return bool
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null);

    /**
     * Creates a password hash
     * -----------------------
     * Создаёт хеш пароля
     * 
     * @param string $password
     * @param int $cost
     * @return string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
