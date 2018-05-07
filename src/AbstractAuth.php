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

    use SetContainerTrait {
        SetContainerTrait::__construct as protected __setContainerTraitConstruct;
    }

    /**
     * @var bool
     */
    protected $token = false;
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var AuthSupport
     */
    protected $support;
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
     * AbstractAuth constructor.
     * @param ContainerInterface $container
     * @param string             $env
     * @param array              $roles
     */
    public function __construct(ContainerInterface $container, string $env, array $roles = [])
    {
        $this->roles       = $roles;
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5($container->getServer('REMOTE_ADDR') . $container->getServer('HTTP_USER_AGENT'));
        $this->support     = new AuthSupport($container, $env);
        $this->__setContainerTraitConstruct($container);
    }

    /**
     * @param string $userToken
     */
    public function setUserToken(string $userToken): void
    {
        $this->userToken = $userToken;
    }

    /**
     * @return bool
     */
    public function getToken(): bool
    {
        return $this->token;
    }

    /**
     * @param bool $token
     */
    public function setToken(bool $token): void
    {
        $this->token = $token;
    }
}
