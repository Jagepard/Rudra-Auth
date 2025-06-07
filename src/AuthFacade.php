<?php

declare(strict_types = 1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static authentication(\stdClass $user, string $password, string $redirect = "", string $notice = "")
 * @method static void exitAuthenticationSession(string $redirect = "")
 * @method static authorization(string $token = null, string $redirect = null)
 * @method static roleBasedAccess(string $role, string $privilege, string $redirect = null)
 * @method static void restoreSessionIfSetRememberMe($redirect = "login")
 * @method static string bcrypt(string $password, int $cost = 10)
 * @method static string getSessionHash()
 *
 * @see Auth
 */
final class AuthFacade
{
    use FacadeTrait;
}
