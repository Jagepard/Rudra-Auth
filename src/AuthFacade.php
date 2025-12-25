<?php

declare(strict_types = 1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Auth;

use Rudra\Container\Traits\FacadeTrait;

/**
 * @method static authentication(\stdClass $user, string $password, string $redirect = "", string $notice = "")
 * @method static void logout(string $redirect = "")
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
