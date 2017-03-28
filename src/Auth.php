<?php

declare(strict_types = 1);

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
 *
 * Класс работающий с аутентификацией и авторизацией пользователей
 */
class Auth
{

    /**
     * @var IContainer
     */
    protected $container;

    /**
     * @var string
     */
    protected $userToken;

    /**
     * @var string
     */
    protected $token = false;

    /**
     * @var array
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
     * @param bool        $accessOrRedirect
     * @param string|null $userToken
     * @param array       $redirect
     *
     * @return bool
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на необходимую страницу
     */
    public function auth(bool $accessOrRedirect = false, string $userToken = null, array $redirect = ['', 'login'])
    {
        if (!isset($userToken)) {
            return $this->access($accessOrRedirect, null, $redirect[0]);
        }

        return $this->access($accessOrRedirect, $userToken, $redirect[1]);
    }

    /**
     * @param bool        $accessOrRedirect
     * @param string|null $userToken
     * @param string      $redirect
     *
     * @return bool
     *
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     */
    public function access(bool $accessOrRedirect = false, string $userToken = null, string $redirect = '')
    {
        /* Если авторизован $this->container()->getSession('token') == true */
        if ($this->container()->hasSession('token')) {

            if (isset($userToken)) {
                /* Предоставление доступа к личным ресурсам пользователя */
                if ($userToken === $this->getToken()) {
                    return true;
                }
            } else {
                /* Предоставление доступа, к общим ресурсам */
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
    public function check(): void
    {
        /* Если пользователь зашел используя флаг remember_me */
        if ($this->container()->hasCookie('RUDRA')) {

            /* Если REMOTE_ADDR . HTTP_USER_AGENT совпадают с cookie RUDRA */
            if (md5($this->container()->getServer('REMOTE_ADDR') . $this->container()->getServer('HTTP_USER_AGENT'))
                == $this->container()->getCookie('RUDRA')
            ) {
                /* Восстанавливаем сессию */
                $this->container()->setSession('token', $this->container()->getCookie('RUDRA_INVOICE'));
                $this->setToken($this->container()->getSession('token'));
            } else {
                /* Уничтожаем устаревшие данные cookie, переадресуем на страницу авторизации */
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
    public function logout(): void
    {
        $this->container()->unsetSession('token');
        $this->unsetCookie();
        $this->container()->get('redirect')->run('');
    }

    /**
     * @param iterable $usersFromDb
     * @param array    $inputData
     * @param string   $notice
     *
     * Аутентификация, Авторизация
     */
    public function login(iterable $usersFromDb, array $inputData, string $notice)
    {
        if (count($usersFromDb) > 0) {
            foreach ($usersFromDb as $user) {

                if ($user['pass'] == $inputData['pass']) {
                    $this->container()->setSession('token', md5($user['name'] . $user['pass']));

                    /* Если установлен флаг remember_me */
                    if ($this->container()->hasPost('remember_me')) {
                        setcookie("RUDRA", md5($this->container()->getServer('REMOTE_ADDR')                    // @codeCoverageIgnore
                            . $this->container()->getServer('HTTP_USER_AGENT')), time() + 3600 * 24 * 7);      // @codeCoverageIgnore
                        setcookie("RUDRA_INVOICE", md5($user['name'] . $user['pass']), time() + 3600 * 24 * 7); // @codeCoverageIgnore
                    }

                    return $this->container()->get('redirect')->run('admin');
                }

                return $this->loginRedirect($notice);
            }
        }

        return $this->loginRedirect($notice);
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     *
     * @return bool
     *
     * Проверка прав доступа
     */
    public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '')
    {
        if ($this->getRole($role) <= $this->getRole($privilege)) {
            return true;
        }

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
     * @return bool|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param boolean|string $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function getRole(string $key): int
    {
        return $this->role[$key];
    }

    /**
     * @param string $notice
     *
     * Переадресация с добавлением уведомления в 'alert'
     */
    protected function loginRedirect(string $notice): void
    {
        $this->container()->setSession('alert', 'main', $notice);
        $this->container()->get('redirect')->run('stargate');
    }

    protected function unsetCookie(): void
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
