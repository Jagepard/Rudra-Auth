<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra;

use Rudra\Interfaces\ContainerInterface;
use Rudra\ExternalTraits\SetContainerTrait;

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
     * Устанавливает роли, окружение, время жизни куки, хеш сессии
     *
     * @param ContainerInterface $container
     * @param string             $env
     * @param array              $roles
     */
    public function __construct(ContainerInterface $container, string $env, array $roles = [])
    {
        $this->env         = $env;
        $this->roles       = $roles;
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

    protected function unsetCookie(): void
    {
        if ('test' !== $this->env) {
            // @codeCoverageIgnoreStart
            if ($this->container()->hasCookie('RudraPermit')) {
                $this->container()->unsetCookie('RudraPermit'); // @codeCoverageIgnore
                $this->container()->unsetCookie('RudraToken'); // @codeCoverageIgnore
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
    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ('API' !== $redirect) ?: $this->container()->jsonResponse($jsonResponse);

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->container()->get('redirect')->run($redirect);
    }

    /**
     * @codeCoverageIgnore
     * @param string $notice
     */
    protected function loginRedirectWithFlash(string $notice)
    {
        $this->container()->setSession('alert',  $notice, 'error');
        $this->container()->get('redirect')->run('stargate');
    }
}
