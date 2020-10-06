<?php

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static login(string $password, array $userData, string $redirect = "admin", string $notice = "Please enter correct information")
 * @method static void logout(string $redirect = "")
 * @method static access(string $token = null, string $redirect = null)
 * @method static role(string $role, string $privilege, string $redirect = null)
 * @method static void updateSessionIfSetRememberMe($redirect = "login")
 * @method static string bcrypt(string $password, int $cost = 10)
 *
 * @see Auth
 */
final class AuthFacade
{
    use FacadeTrait;
}
