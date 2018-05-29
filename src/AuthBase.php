<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;

use Rudra\ExternalTraits\SetContainerTrait;

/**
 * Class AuthBase
 * @package Rudra
 *
 * Класс работающий с аутентификацией и авторизацией пользователей
 */
class AuthBase
{

    use SetContainerTrait {
        SetContainerTrait::__construct as protected __setContainerTraitConstruct;
    }

    /**
     * @var string
     */
    protected $env;
    /**
     * @var array
     */
    protected $roles;
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
        $this->env         = $env;
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5($container->getServer('REMOTE_ADDR') . $container->getServer('HTTP_USER_AGENT'));
        $this->__setContainerTraitConstruct($container);
    }

    /**
     * @codeCoverageIgnore
     * @param string $name
     * @param string $value
     * @param int    $expire
     */
    protected function setCookie(string $name, string $value, int $expire): void
    {
        setcookie($name, $value, $expire);
    }

    /**
     * @param string   $redirect
     * @param array    $jsonResponse
     * @param callable $redirectCallable
     * @return callable
     */
    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ('API' !== $redirect) ?: exit($this->container->jsonResponse($jsonResponse));

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->container->get('redirect')->run($redirect);
    }

    /**
     * @codeCoverageIgnore
     * @param string $notice
     *
     * Переадресация с добавлением уведомления в 'alert'
     */
    protected function loginRedirectWithFlash(string $notice): void
    {
        $this->container->setSession('alert', 'main', $notice);
        $this->container->get('redirect')->run('stargate');
    }

    protected function unsetCookie(): void
    {
        if ('test' !== $this->env) {
            // @codeCoverageIgnoreStart
            if ($this->container->hasCookie('RudraPermit')) {
                $this->container->unsetCookie('RudraPermit'); // @codeCoverageIgnore
                $this->container->unsetCookie('RudraToken'); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
