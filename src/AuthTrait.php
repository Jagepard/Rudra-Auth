<?php

declare(strict_types=1);

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
    public function login(string $password, string $hash, string $redirect = 'admin', string $message = 'Укажите верные данные'): void
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
        return $this->container()->get('auth')->authenticate($accessOrRedirect, $userToken, $redirect);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $access
     * @param string $redirect
     *
     * @return mixed
     */
    public function role(string $role, string $privilege, bool $access = false, string $redirect = '')
    {
        return $this->container()->get('auth')->role($role, $privilege, $access, $redirect);
    }

    /**
     * @param string $password
     * @param int    $cost
     * @return bool|string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return $this->container()->get('auth')->bcrypt($password, $cost);
    }

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
