<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Container\Traits\SetRudraContainersTrait;

class AuthBase
{
    use SetRudraContainersTrait {
        SetRudraContainersTrait::__construct as protected __setRudraContainersTrait;
    }

    protected string $environment;
    protected array $roles;
    protected int $expireTime;
    protected string $sessionHash;

    /**
     * Sets roles, environment, cookie lifetime, session hash
     */
    public function __construct(RudraInterface $rudra, string $environment, array $roles = [])
    {
        $this->environment = $environment;
        $this->roles       = $roles;
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5(
            $rudra->request()->server()->get("REMOTE_ADDR") .
            $rudra->request()->server()->get("HTTP_USER_AGENT")
        );
        $this->__setRudraContainersTrait($rudra);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function setCookie(string $name, string $value, int $expire): void
    {
        setcookie($name, $value, $expire);
    }

    protected function unsetCookie(): void
    {
        if ("test" !== $this->environment) {
            // @codeCoverageIgnoreStart
            if ($this->rudra()->cookie()->has("RudraPermit")) {
                $this->rudra()->cookie()->unset("RudraPermit"); // @codeCoverageIgnore
                $this->rudra()->cookie()->unset("RudraToken"); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }

    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ("API" !== $redirect) ?: $this->rudra()->response()->json($jsonResponse);

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->rudra()->get("redirect")->run($redirect);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loginRedirectWithFlash(string $notice): void
    {
        $this->rudra()->session()->set(["alert",  [$notice, "error"]]);
        $this->rudra()->get("redirect")->run("stargate");
    }
}
