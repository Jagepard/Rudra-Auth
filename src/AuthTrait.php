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
     * @param iterable $user
     * @param array    $res
     * @param string   $message
     */
    public function login(iterable $user, array $res, string $message = 'Укажите верные данные'): void
    {
        $this->container()->get('auth')->login($user, $res, $message);
    }

    public function logout(): void
    {
        $this->container()->get('auth')->logout();
    }

    public function check(): void
    {
        $this->container()->get('auth')->check();
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