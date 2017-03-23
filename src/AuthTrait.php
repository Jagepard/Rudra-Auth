<?php
/**
 * Date: 22.03.17
 * Time: 13:03
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


trait AuthTrait
{

    /**
     * @param        $user
     * @param        $res
     * @param string $message
     *
     * @return mixed
     */
    public function login($user, $res, $message = 'Укажите верные данные')
    {
        $this->container()->get('auth')->login($user, $res, $message);
    }

    public function logout()
    {
        $this->container()->get('auth')->logout();
    }

    public function check()
    {
        $this->container()->get('auth')->check();
    }

    /**
     * @param bool  $accessOrRedirect
     * @param null  $userToken
     * @param array $redirect
     *
     * @return mixed
     */
    public function auth($accessOrRedirect = false, $userToken = null, $redirect = ['', 'login'])
    {
        return $this->container()->get('auth')->auth($accessOrRedirect, $userToken, $redirect);
    }
}