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
abstract class AuthBase
{

    use SetContainerTrait;

    /**
     * @var string
     */
    protected $token = false;
    /**
     * @var string
     */
    protected $env;
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var string
     */
    protected $userToken;
    /**
     * @var integer
     */
    protected $expireTime;
    /**
     * @var string
     */
    protected $sessionHash;

    /**
     * AuthBase constructor.
     * @param ContainerInterface $container
     * @param string             $env
     * @param array              $roles
     */
    public function __construct(ContainerInterface $container, string $env, array $roles = [])
    {
        $this->env           = $env;
        $this->roles         = $roles;
        $this->container     = $container;
        $this->expireTime    = time() + 3600 * 24 * 7;
        $this->sessionHash   = md5($container->getServer('REMOTE_ADDR') .
            $container->getServer('HTTP_USER_AGENT'));
    }

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

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @return string
     */
    public function getSessionHash(): string
    {
        return $this->sessionHash;
    }

    /**
     * @return int
     */
    public function getExpireTime(): int
    {
        return $this->expireTime;
    }
}
