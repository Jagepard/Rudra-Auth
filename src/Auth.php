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

use App\Config;

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
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
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
            return $this->regularAccess($accessOrRedirect, $redirect);
        }

        return $this->userAccess($userToken, $accessOrRedirect, $redirect);
    }

    /**
     * @param $accessOrRedirect
     * @param $redirect
     *
     * @return boolean
     * Доступ для всех авторизованных
     */
    public function regularAccess($accessOrRedirect = false, $redirect = ['', 'login'])
    {
        /* Если авторизован $this->container()->getSession('token') == true */
        if ($this->container()->hasSession('token')) {
            if ($this->getToken() == $this->container()->getSession('token')) {
                return true;
            }
        }

        /* Если не авторизован $this->container()->getSession('token') == 'undefined' */
        if ($accessOrRedirect) {
            return false;
        }

        /* Переадресация, если $accessOrRedirect не установлен*/
        $this->container()->get('redirect')->run($redirect[0], 'https');
    }

    /**
     * @param $userToken
     * @param $accessOrRedirect
     * @param $redirect
     *
     * @return boolean
     * Для предоставления доступа определенному пользователю
     */
    public function userAccess($userToken, $accessOrRedirect = false, $redirect = ['', 'login'])
    {
        /* Если авторизован $this->container()->getSession('token') == true */
        if ($this->container()->hasSession('token')) {
            if ($userToken === $this->getToken()) {
                return true;
            }
        }

        /* Если не авторизован $this->container()->getSession('token') == 'undefined' */
        if ($accessOrRedirect) {
            return false;
        }

        /* Переадресация, если $accessOrRedirect не установлен*/
        $this->container()->get('redirect')->run($redirect[1]);
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
                // @codeCoverageIgnoreStart
                $this->container()->unsetCookie('RUDRA');         // @codeCoverageIgnore
                $this->container()->unsetCookie('RUDRA_INVOICE'); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd

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

        if ($this->container()->hasCookie('RUDRA')) {
            // @codeCoverageIgnoreStart
            $this->container()->unsetCookie('RUDRA'); // @codeCoverageIgnore
            $this->container()->unsetCookie('RUDRA_INVOICE'); // @codeCoverageIgnore
            // @codeCoverageIgnoreEnd
        }

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

    protected function loginRedirect($notice)
    {
        $this->container()->setSession('alert', 'main', $notice);
        $this->container()->get('redirect')->run('stargate');
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

    public function getRole()
    {
        return Config::ROLE[$this->role];
    }

    /**
     * @param $role
     * @param $redirect
     * @param $notice
     * Проверка на соответсвие прав доступа
     */
    public function role($role, $redirect, $notice = 'Недостаточно прав')
    {
        if ($this->getRole() <= Config::ROLE[$role]) {
            return;
        } else {
            $this->container()->setSession(
                'alert', 'main', $this->container()->get('notice')->noticeErrorMessage($notice)
            );
            $this->container()->get('redirect')->run($redirect);
        }
    }
}
