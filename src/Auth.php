<?php

declare(strict_types = 1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Container\Interfaces\RudraInterface;
use Rudra\Container\Traits\SetRudraContainersTrait;
use Rudra\Redirect\Redirect;

class Auth implements AuthInterface
{
    use SetRudraContainersTrait {
        SetRudraContainersTrait::__construct as protected __setRudraContainersTrait;
    }

    protected int    $expireTime;
    protected string $sessionHash;

    /**
     * Auth constructor.
     * @param RudraInterface $rudra
     *
     * Sets cookie lifetime, session hash
     * ----------------------------------
     * Устанавливает время жизни cookie, хеш сеанса
     */
    public function __construct(RudraInterface $rudra)
    {
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5($rudra->request()->server()->get("REMOTE_ADDR") . $rudra->request()->server()->get("HTTP_USER_AGENT"));
        $this->__setRudraContainersTrait($rudra);
    }

    /**
     * @param \stdClass $user
     * @param string $password
     * @param string $redirect
     * @param string $notice
     * @return callable
     *
     * Authentication
     * --------------
     * Аутентификация
     */
    public function authentication(\stdClass $user, string $password, string $redirect = "", string $notice = "")
    {
        if (!isset($user->password)) {
            throw new \InvalidArgumentException("Invalid user object received");
        }

        if (password_verify($password, $user->password)) {
            $token = md5($user->password . $user->email);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthenticationSession($user, $token);

            return $this->handleRedirect($redirect, ["status" => "Authorized"]);
        }

        return $this->handleRedirect($redirect, ["status" => "Wrong access data"], $this->loginRedirectWithNotice($notice));
    }

    /**
     * @param \stdClass $user
     * @param string $token
     *
     * Sets cookies if present $_POST["remember_me"]
     * ---------------------------------------------
     * Устанавливает cookies если есть $_POST["remember_me"]
     */
    private function setCookiesIfSetRememberMe(\stdClass $user, string $token): void
    {
        if ($this->rudra()->request()->post()->has("remember_me")) {
            $this->rudra()->cookie()->set(["RudraPermit", [$this->sessionHash, $this->expireTime]]); // @codeCoverageIgnore
            $this->rudra()->cookie()->set(["RudraToken", [$token, $this->expireTime]]);   // @codeCoverageIgnore
            $this->rudra()->cookie()->set(["RudraUser", [json_encode($user), $this->expireTime]]);   // @codeCoverageIgnore
        }
    }

    /**
     * @param object $user
     * @param string $token
     *
     * Sets session data on successful authentication
     * ----------------------------------------------
     * Устанавливает данные сессии при успешной аутентификации
     */
    private function setAuthenticationSession(object $user, string $token): void
    {
        $this->rudra()->session()->set(["token", $token]);
        $this->rudra()->session()->set(["user", $user]);
    }

    /**
     * @param string $redirect
     *
     * Exit authentication session
     * ---------------------------
     * Выйти из сеанса аутентификации
     */
    public function exitAuthenticationSession(string $redirect = ""): void
    {
        $this->rudra()->session()->unset("token");
        $this->rudra()->session()->unset("user");
        $this->unsetRememberMeCookie();
        $this->handleRedirect($redirect, ["status" => "Logout"]);
    }

    /**
     * Removes the $_POST["remember_me"] cookie
     * ----------------------------------------
     * Удаляет $_POST["remember_me"] cookie
     */
    protected function unsetRememberMeCookie(): void
    {
        if ("test" !== $this->rudra()->config()->get("environment")) {
            // @codeCoverageIgnoreStart
            if ($this->rudra()->cookie()->has("RudraPermit")) {
                $this->rudra()->cookie()->unset("RudraPermit"); // @codeCoverageIgnore
                $this->rudra()->cookie()->unset("RudraToken"); // @codeCoverageIgnore
                $this->rudra()->cookie()->unset("RudraUser"); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * @param string|null $token
     * @param string|null $redirect
     * @return bool|callable
     *
     * Authorization
     * Providing access to shared or personal resources
     * ------------------------------------------------
     * Авторизация
     * Предоставление доступа к общим или личным ресурсам
     */
    public function authorization(string $token = null, string $redirect = null)
    {
        // If authorized / Если авторизован
        if ($this->rudra()->session()->has("token")) {
            // Providing access to shared resources / Предоставление доступа к общим ресурсам
            if (!isset($token)) {
                return true;
            }

            // Providing access to the user's personal resources / Предоставление доступа к личным ресурсам пользователя
            if ($token === $this->rudra()->session()->get("token")) {
                return true;
            }
        }

        // If not logged in / Если не авторизован
        if (isset($redirect)) {
            return $this->handleRedirect($redirect, ["status" => "Access denied"]);
        }

        return false;
    }

    /**
     * @param string $role
     * @param string $privilege
     * @param string|null $redirect
     * @return bool
     *
     * Role based access
     * -----------------
     * Доступ на основе ролей
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null)
    {
        $roles = $this->rudra()->config()->get("roles");

        if ($roles[$role] <= $roles[$privilege]) {
            return true;
        }

        if (isset($redirect)) {
            $this->handleRedirect($redirect, ["status" => "Permissions denied"]);
        }

        return false;
    }

    /**
     * @param string $redirect
     *
     * Restore session data if $_POST["remember_me"] was set
     * Восствнавливает данные сессии если был установлен $_POST["remember_me"]
     */
    public function restoreSessionIfSetRememberMe($redirect = "login"): void
    {
        // If the user is logged in using the remember_me flag
        if ($this->rudra()->cookie()->has("RudraPermit")) {
            if ($this->sessionHash === $this->rudra()->cookie()->get("RudraPermit")) {
                $this->setAuthenticationSession(
                    json_decode($this->rudra()->cookie()->get("RudraUser")),
                    $this->rudra()->cookie()->get("RudraToken")
                );
                return; // @codeCoverageIgnore
            }

            $this->unsetRememberMeCookie();
            $this->handleRedirect($redirect, ["status" => "Authorization data expired"]);
        }
    }

    /**
     * @param string $password
     * @param int $cost
     * @return string
     *
     * Creates a password hash
     * -----------------------
     * Создаёт хеш пароля
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
    }

    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ("API" !== $redirect) ?: $this->rudra()->response()->json($jsonResponse);

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        $this->rudra()->get(Redirect::class)->run($redirect);
    }

    /**
     * @param string $notice
     * @codeCoverageIgnore
     *
     * Redirect by setting a notification
     * ----------------------------------
     * Перенаправить установив уведомление
     */
    protected function loginRedirectWithNotice(string $notice): void
    {
        $this->rudra()->session()->set(["alert", ["error" => $notice]]);
        $this->rudra()->get(Redirect::class)->run("login");
    }
}
