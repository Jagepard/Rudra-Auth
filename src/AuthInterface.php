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

interface AuthInterface
{
    /**
     * @param  array  $user
     * @param  string $password
     * @param  array  $redirect
     * @param  array  $notice
     * @return void
     */
    public function authentication(array $user, string $password, array $redirect = ['admin', 'login'], array $notice = ["error" => "Wrong access data"]);

    /**
     * @param  string $redirect
     * @return void
     */
    public function logout(string $redirect = ""): void;

    /**
     * @param  string|null $token
     * @param  string|null $redirect
     * @return void
     */
    public function authorization(string $token = null, string $redirect = null);

    /**
     * @param  string      $role
     * @param  string      $privilege
     * @param  string|null $redirect
     * @return void
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null);

    /**
     * @param  string  $password
     * @param  integer $cost
     * @return string
     */
    public function bcrypt(string $password, int $cost = 10): string;
}
