<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Interfaces\ApplicationInterface;
use Rudra\Container\Traits\SetApplicationContainersTrait;

class AuthBase
{
    use SetApplicationContainersTrait {
        SetApplicationContainersTrait::__construct as protected __setContainerTraitConstruct;
    }

    protected string $environment;
    protected array $roles;
    protected int $expireTime;
    protected string $sessionHash;

    /**
     * Sets roles, environment, cookie lifetime, session hash
     */
    public function __construct(ApplicationInterface $application, string $environment, array $roles = [])
    {
        $this->environment = $environment;
        $this->roles       = $roles;
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5(
            $application->request()->server()->get("REMOTE_ADDR") .
            $application->request()->server()->get("HTTP_USER_AGENT")
        );
        $this->__setContainerTraitConstruct($application);
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
            if ($this->application()->cookie()->has("RudraPermit")) {
                $this->application()->cookie()->unset("RudraPermit"); // @codeCoverageIgnore
                $this->application()->cookie()->unset("RudraToken"); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }

    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ("API" !== $redirect) ?: $this->application()->response()->json($jsonResponse);

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->application()->objects()->get('redirect')->run($redirect);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loginRedirectWithFlash(string $notice)
    {
        $this->application()->session()->set(["alert",  [$notice, "error"]]);
        $this->application()->objects()->get("redirect")->run("stargate");
    }
}
