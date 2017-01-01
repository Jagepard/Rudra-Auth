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

use App\Config\Config;

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
    protected $di;

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
     * @param IContainer $di
     */
    public function __construct(IContainer $di)
    {
        $this->di = $di;
    }

    /**
     * @param null  $userToken
     * @param bool  $false
     * @param array $redirect
     *
     * @return bool
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на страницу регистрации
     */
    public function auth($userToken = null, $false = false, $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            $this->regularAccess($false, $redirect);
        } else {
            $this->userAcces($userToken, $false, $redirect);
        }
    }

    /**
     * @param $false
     * @param $redirect
     *
     * @return boolean
     * Доступ для всех авторизованных
     */
    public function regularAccess($false = true, $redirect = ['', 'login'])
    {
        if ($this->isToken() === $this->getDi()->getSession('token')) {
            return true;
        } else {
            (!$false) ? $this->getDi()->get('redirect')->run($redirect[0], 'https') : false;
        }
    }

    /**
     * @param $userToken
     * @param $false
     * @param $redirect
     *
     * @return boolean
     * Для предоставления доступа определенному пользователю
     */
    public function userAcces($userToken, $false, $redirect)
    {
        if ($userToken === $this->isToken()) {
            return true;
        } else {
            (!$false) ? $this->getDi()->get('redirect')->run($redirect[1], 'https') : false;
        }
    }

    /**
     * Проверка авторизации
     */
    public function check()
    {
        if ($this->getDi()->hasCoockie('RUDRA')) {

            if (md5($this->getDi()->getServer('REMOTE_ADDR') . $this->getDi()->getServer('HTTP_USER_AGENT')) == $this->getDi()->getCoockie('RUDRA')) {

                $this->getDi()->setSession('token', $this->getDi()->getCoockie('RUDRA_INVOICE'));
                $this->getDi()->setSession('auth', true);
                $this->setToken($this->getDi()->getCoockie('RUDRA_INVOICE'));

            } else {
                $this->getDi()->unsetCoockie('RUDRA');
                $this->getDi()->unsetCoockie('RUDRA_INVOICE');

                $this->di->get('redirect')->run('login');
            }

        } else {

            if ($this->getDi()->hasSession('auth')) {
                $this->setToken($this->getDi()->getSession('token'));
            } else {
                $this->setToken(false);
                $this->getDi()->setSession('token', 'undefined');
            }
        }

    }

    /**
     * Завершить сессию
     */
    public function logout()
    {
        $this->getDi()->unsetSession('auth');
        $this->getDi()->unsetSession('token');

        /**
         * Если установлены cookie, то удаляем их
         */
        if ($this->getDi()->hasCoockie('RUDRA')) {
            $this->getDi()->unsetCoockie('RUDRA');
            $this->getDi()->unsetCoockie('RUDRA_INVOICE');
        }

        $this->getDi()->get('redirect')->run('');
    }

    /**
     * @param $user
     * @param $res
     * @param $notice
     */
    public function login($user, array $res, string $notice)
    {
        if (count($user) > 0) {
            foreach ($user as $value) {
                if ($value['pass'] == $res['pass']) {

                    $this->getDi()->setSession('auth', true);
                    $this->getDi()->setSession('token', $this->getUserToken($value['name'], $value['pass']));

                    if ($this->getDi()->hasPost('remember_me')) {
                        setcookie("RUDRA",
                            md5($this->getDi()->getServer('REMOTE_ADDR') . $this->getDi()->getServer('HTTP_USER_AGENT')),
                            time() + 3600 * 24 * 7);
                        setcookie("RUDRA_INVOICE", $this->getUserToken($value['name'], $value['pass']), time() + 3600 * 24 * 7);
                    }

                    $this->getDi()->get('redirect')->run('admin');

                } else {
                    $this->loginRedirect($notice);
                }
            }
        } else {
            $this->loginRedirect($notice);
        }

    }

    protected function loginRedirect($notice)
    {
        $this->getDi()->setSubSession('alert', 'main', $notice);
        $this->getDi()->get('redirect')->run('login');
    }

    /**
     * @param $name
     * @param $pass
     *
     * @return string Возвращает токен пользователя
     */
    public function getUserToken($name, $pass)
    {
        return md5($name . $pass);
    }

    /**
     * @return IContainer
     */
    public function getDi(): IContainer
    {
        return $this->di;
    }

    /**
     * @return boolean
     */
    public function isToken()
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
            $this->getDi()->setSubSession('alert', 'main', $this->getDi()->get('notice')->noticeErrorMessage($notice));
            $this->getDi()->get('redirect')->run($redirect);
        }
    }

}
