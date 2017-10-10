<?php

declare(strict_types = 1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2017, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class AbstractAuth
 *
 * @package Rudra
 *
 * Класс работающий с аутентификацией и авторизацией пользователей
 */
abstract class AbstractAuth
{

    use SetContainerTrait;

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
     * @param ContainerInterface $container
     * @param array              $roles
     */
    public function __construct(ContainerInterface $container, array $roles = [])
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
    public abstract function auth(bool $accessOrRedirect = false, string $userToken = null, array $redirect = ['', 'login']);

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
    public abstract function access(bool $accessOrRedirect = false, string $userToken = null, string $redirect = '');

    /**
     * Проверка авторизации
     *
     * @param string $redirect
     */
    public abstract function check($redirect = 'stargate'): void;

    /**
     * @param iterable $usersFromDb
     * @param array    $inputData
     * @param string   $redirect
     * @param string   $notice
     *
     * @return callable|void
     * Аутентификация, Авторизация
     */
    public abstract function login(iterable $usersFromDb, array $inputData, string $redirect = 'admin', string $notice);

    /**
     * Завершить сессию
     *
     * @param string $redirect
     */
    public abstract function logout(string $redirect = ''): void;

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
    public abstract function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '');

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
    public function getRole(string $key)
    {
        return $this->role[$key];
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

    /**
     * @param string   $redirect
     * @param array    $jsonResponse
     * @param callable $redirectCallable
     *
     * @return callable
     */
    public function handleResult(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        if ($redirect == 'API') {
            exit($this->container()->jsonResponse($jsonResponse));
        } else {
            if (isset($redirectCallable)) {
                return $redirectCallable;
            } else {
                $this->container()->get('redirect')->run($redirect);
            }
        }
    }
}
