<?php
/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra;


/**
 * Trait AuthHelperTrait
 * @package Rudra
 */
trait AuthHelperTrait
{

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
        ('API' !== $redirect) ?: exit($this->container()->jsonResponse($jsonResponse));

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->container()->get('redirect')->run($redirect);
    }

    /**
     * @codeCoverageIgnore
     * @param string $notice
     *
     * Переадресация с добавлением уведомления в 'alert'
     */
    protected function loginRedirectWithFlash(string $notice): void
    {
        $this->container()->setSession('alert', 'main', $notice);
        $this->container()->get('redirect')->run('stargate');
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
     * @return ContainerInterface
     */
    public abstract function container(): ContainerInterface;
}