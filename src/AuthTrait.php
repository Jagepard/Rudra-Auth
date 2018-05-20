<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

use Rudra\Interfaces\ContainerInterface;

/**
 * Trait AuthTrait
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
    public function checkCookie(string $redirect = ''): void
    {
        $this->container()->get('auth')->checkCookie($redirect);
    }

    /**
     * @param string|null $userToken
     * @param string      $redirect
     *
     * @return mixed
     */
    public function auth(string $userToken = null, string $redirect = '')
    {
        return $this->container()->get('auth')->access(false, $userToken, $redirect);
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
     * @return string
     */
    public function userToken(): string
    {
        return $this->container()->getSession('token');
    }

    /**
     * @return ContainerInterface
     */
    abstract public function container(): ContainerInterface;
}
