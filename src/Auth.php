<?php

/**
 * Date: 14.09.16
 * Time: 11:16
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class Auth
 *
 * @package Rudra
 */
class Auth
{

    /**
     * @var IContainer
     */
    protected $container;

    /**
     * @var
     */
    protected $userToken;

    /**
     * @var bool
     * Параметр необходимый для авторизации
     */
    protected $token = false;

    /**
     * @var
     */
    protected $role;

    /**
     * Auth constructor.
     *
     * @param IContainer $container
     * @param array      $roles
     */
    public function __construct(IContainer $container, array $roles = [])
    {
        $this->container = $container;
        $this->role      = $roles;
    }

    /**
     * @param null  $userToken
     * @param bool  $accessOrRedirect
     * @param array $redirect
     *
     * @return bool
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на страницу регистрации
     */
    public function auth($accessOrRedirect = false, $userToken = null, $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            return $this->access($accessOrRedirect, null, $redirect[0]);
        }

        return $this->access($accessOrRedirect, $userToken, $redirect[1]);
    }

    /**
     * @param bool   $accessOrRedirect
     * @param null   $userToken
     * @param string $redirect
     *
     * @return bool
     */
    public function access($accessOrRedirect = false, $userToken = null, $redirect = '')
    {
        /* Если авторизован $this->container()->getSession('token') == true */
        if ($this->container()->hasSession('token')) {

            if (isset($userToken)) {
                if ($userToken === $this->getToken()) {
                    return true;
                }
            } else {
                if ($this->getToken() == $this->container()->getSession('token')) {
                    return true;
                }
            }
        }

        /* Если не авторизован $this->container()->getSession('token') == 'undefined' */
        if ($accessOrRedirect) {
            return false;
        }

        /* Переадресация, если $accessOrRedirect не установлен*/
        $this->container()->get('redirect')->run($redirect, 'https');
    }

    /**
     * Проверка авторизации
     */
    public function check()
    {
        if ($this->container()->hasCookie('RUDRA')) {

            if (md5($this->container()->getServer('REMOTE_ADDR') . $this->container()->getServer('HTTP_USER_AGENT'))
                == $this->container()->getCookie('RUDRA')
            ) {
                $this->container()->setSession('token', $this->container()->getCookie('RUDRA_INVOICE'));
                $this->setToken($this->container()->getSession('token'));
            } else {
                $this->unsetCookie();
                $this->container()->get('redirect')->run('stargate');
            }

        } else {

            if ($this->container()->hasSession('token')) {
                $this->setToken($this->container()->getSession('token'));
            } else {
                $this->setToken(false);
            }
        }
    }

    /**
     * Завершить сессию
     */
    public function logout()
    {
        $this->container()->unsetSession('token');
        $this->unsetCookie();
        $this->container()->get('redirect')->run('');
    }

    /**
     * @param        $usersFromDb
     * @param array  $inputData
     * @param string $notice
     */
    public function login($usersFromDb, array $inputData, string $notice)
    {
        if (count($usersFromDb) > 0) {

            foreach ($usersFromDb as $user) {

                if ($user['pass'] == $inputData['pass']) {
                    $this->container()->setSession('token', md5($user['name'], $user['pass']));

                    if ($this->container()->hasPost('remember_me')) {
                        setcookie("RUDRA", md5($this->container()->getServer('REMOTE_ADDR')                    // @codeCoverageIgnore
                            . $this->container()->getServer('HTTP_USER_AGENT')), time() + 3600 * 24 * 7);      // @codeCoverageIgnore
                        setcookie("RUDRA_INVOICE", md5($user['name'], $user['pass']), time() + 3600 * 24 * 7); // @codeCoverageIgnore
                    }

                    return $this->container()->get('redirect')->run('admin');
                }

                return $this->loginRedirect($notice);
            }
        }

        return $this->loginRedirect($notice);
    }

    /**
     * @param        $role
     * @param        $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     *
     * @return bool
     */
    public function role($role, $privilege, $redirectOrAccess = false, $redirect = '')
    {
        if ($this->getRole($role) <= $this->getRole($privilege)) {
            return true;
        }

        /* Если не авторизован $this->container()->getSession('token') == 'undefined' */
        if (!$redirectOrAccess) {
            return false;
        }

        $this->container()->get('redirect')->run($redirect);
    }

    /**
     * @return IContainer
     */
    public function container(): IContainer
    {
        return $this->container;
    }

    /**
     * @return boolean
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param boolean $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getRole($key)
    {
        return $this->role[$key];
    }

    protected function loginRedirect($notice)
    {
        $this->container()->setSession('alert', 'main', $notice);
        $this->container()->get('redirect')->run('stargate');
    }

    protected function unsetCookie()
    {
        if (DEV !== 'test') {
            // @codeCoverageIgnoreStart
            if ($this->container()->hasCookie('RUDRA')) {
                $this->container()->unsetCookie('RUDRA'); // @codeCoverageIgnore
                $this->container()->unsetCookie('RUDRA_INVOICE'); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
