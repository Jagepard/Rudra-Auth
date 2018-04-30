<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

/**
 * Class AbstractAuth
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
    protected $roles;
    /**
     * @var string
     */
    protected $env;

    /**
     * AbstractAuth constructor.
     * @param ContainerInterface $container
     * @param string             $env
     * @param array              $roles
     * @param string             $redirect
     */
    public function __construct(ContainerInterface $container, string $env, array $roles = [], $redirect = 'login')
    {
        $this->env       = $env;
        $this->roles     = $roles;
        $this->container = $container;
    }

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param array       $redirect
     * @return callable
     *
     * Проверяет авторизован ли пользователь
     * Если да, то пропускаем выполнение скрипта дальше,
     * Если нет, то редиректим на необходимую страницу
     */
    abstract public function authenticate(bool $access = false, string $userToken = null, array $redirect = ['', 'login']);

    /**
     * @param bool        $access
     * @param string|null $userToken
     * @param string      $redirect
     * @return callable
     *
     * Предоставление доступа к общим ресурсам,
     * либо личным ресурсам пользователя
     */
    abstract public function access(bool $access = false, string $userToken = null, string $redirect = '');

    /**
     * @param string $redirect
     *
     * Проверка авторизации
     */
    abstract public function check($redirect = 'stargate'): void;

    /**
     * @param string $password
     * @param string $hash
     * @param string $redirect
     * @param string $notice
     * @return mixed
     *
     * Аутентификация, Авторизация
     */
    abstract public function login(string $password, string $hash, string $redirect = 'admin', string $notice);

    /**
     * Завершить сессию
     *
     * @param string $redirect
     */
    abstract public function logout(string $redirect = ''): void;

    /**
     * @param string $role
     * @param string $privilege
     * @param bool   $redirectOrAccess
     * @param string $redirect
     * @return bool
     *
     * Проверка прав доступа
     */
    abstract public function role(string $role, string $privilege, bool $redirectOrAccess = false, string $redirect = '');

    /**
     * @return bool|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getRole(string $key)
    {
        return $this->roles[$key];
    }

    protected function unsetCookie(): void
    {
        if ('test' !== $this->getEnv()) {
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
     * @return callable
     */
    public function handleResult(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ('API' !== $redirect) ?: exit($this->container()->jsonResponse($jsonResponse));

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->container()->get('redirect')->run($redirect);
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }
}
