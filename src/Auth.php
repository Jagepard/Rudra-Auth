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
 * @package Rudra
 */
class Auth
{

    /**
     * @var iContainer
     */
    protected $di;

    /**
     * @var
     */
    protected $email;

    /**
     * @var
     */
    protected $password;

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
     * @param iContainer $di
     * @param $data
     */
    public function __construct(iContainer $di, $data)
    {
        $this->di       = $di;
        $this->email    = $data['email'];
        $this->password = $data['password'];
        $this->role     = $data['role'];
    }

    /**
     * @param null $userToken
     * @param bool $false
     * @param array $redirect
     * @return bool
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на страницу регистрации
     */
    public function auth($userToken = null, $false = false, $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            if ($this->isToken() === $this->getDi()->getSession('token')) {
                return true;
            } else {
                (!$false) ? $this->getDi()->get('redirect')->run($redirect[0], 'https') : false;
            }
        } else {
            if ($userToken === $this->isToken()) {
                return true;
            } else {
                (!$false) ? $this->getDi()->get('redirect')->run($redirect[1], 'https') : false;
            }
        }
    }

    public function check()
    {
        if ($this->getDi()->hasSession('auth', true)) {
            $this->setToken($this->getDi()->getSession('token'));
        } else {
            $this->setToken(false);
            $this->getDi()->setSession('token', 'undefined');
        }
    }

    public function logout()
    {
        $this->getDi()->unsetSession('auth');
        $this->getDi()->unsetSession('token');
        $this->getDi()->get('redirect')->run('');
    }

    /**
     * @param $data
     * @param $notice
     */
    public function login($data, $notice)
    {
        /**
         * Если данные введенные в форму авторизации совпадают
         * с данными в БД, то устанавливаем следующие параметры
         * $_SESSION['auth']                                              boolean
         *      - параметр подтверждающий авторизацию
         */
        if ($this->getEmail() == $data['name'] and $this->getPassword() == $data['pass']) {

            $this->getDi()->setSession('auth', true);
            $this->getDi()->setSession('token', $this->getUserToken()[0]);

            $this->getDi()->get('redirect')->run('admin');
            /**
             * Если при авторизации пользователь поставил галочку "Запомнить меня",
             * то записываем его данные в cookie
             *
             * $_COOKIE['HELPIO_WELCOME'] string
             *      хеш склейки ip пользователя и заголовка User-Agent:
             *      md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
             */
            if (isset($this->getDi()->getPost('remember_me'))) {
                setcookie("WELCOME", md5($this->getDi()->getServer('REMOTE_ADDR') . $this->getDi()->getServer('HTTP_USER_AGENT')), time() + 3600 * 24 * 7);
            }
        } else {
            $this->getDi()->setSubSession('alert', 'main', $notice);
            $this->getDi()->get('redirect')->run('login');
        }
    }

    /**
     * @return array
     */
    public function getUserToken()
    {
        return [md5($this->getEmail() . $this->getPassword()), null];
    }

    /**
     * @return iContainer
     */
    public function getDi(): iContainer
    {
        return $this->di;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
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
