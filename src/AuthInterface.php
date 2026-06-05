<?php

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Auth;

interface AuthInterface
{
    public function authentication(array $user, string $password, array $redirect = ['admin', 'login'], array $notice = ["error" => "Wrong access data"]): void;
    public function logout(string $redirect = ""): void;
    public function authorization(?string $token = null, ?string $redirect = null): bool;
    public function roleBasedAccess(string $role, string $privilege, ?string $redirect = null): bool;
    public function bcrypt(string $password, int $cost = 10): string;
}
