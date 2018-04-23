<?php

declare(strict_types = 1);

/**
 * Date: 22.03.17
 * Time: 13:03
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class AuthTrait
 *
 * @package Rudra
 */
trait AuthTrait
{

    /**
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $message
     */
    public function login(string $password, string $hash, string $redirect  = 'admin', string $message = 'Укажите верные данные'): void
    {
        $this->container()->get('auth')->login($password, $hash, $redirect, $message);
    }

    /**
     * @param string $redirect
     */
    public function logout(string $redirect = ''): void
    {
        $this->container()->get('auth')->logout($redirect);
    }

    /**
     * @param string $redirect
     */
    public function check(string $redirect = ''): void
    {
        $this->container()->get('auth')->check($redirect);
    }

    /**
     * @param bool        $accessOrRedirect
     * @param string|null $userToken
     * @param array       $redirect
     *
     * @return mixed
     */
    public function auth(bool $accessOrRedirect = false, string $userToken = null, array $redirect = ['', 'login'])
    {
        return $this->container()->get('auth')->auth($accessOrRedirect, $userToken, $redirect);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     *
     * @return mixed
     */
    public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '')
    {
        return $this->container()->get('auth')->role($role, $privilege, $redirectOrAccess, $redirect);
    }
}
