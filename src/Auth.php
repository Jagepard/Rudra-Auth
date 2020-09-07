<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

class Auth extends AuthBase implements AuthInterface
{
    public function login(string $password, array $userData, string $redirect = "admin", string $notice = "Please enter correct information")
    {
        $userData["uniq_name"] ??= "not set";

        if (password_verify($password, $userData["password"])) {
            $token = md5($userData["password"] . $userData["uniq_name"]);
            $this->setCookiesIfSetRememberMe($userData, $token);
            $this->setAuthSession($userData["uniq_name"], $token);

            return $this->handleRedirect($redirect, ["status" => "Authorized"]);
        }

        return $this->handleRedirect($redirect, ["status" => "Wrong access data"], $this->loginRedirectWithFlash($notice));
    }

    public function logout(string $redirect = ""): void
    {
        $this->application()->session()->unset("token");
        $this->unsetCookie();
        $this->handleRedirect($redirect, ["status" => "Logout"]);
    }

    public function access(string $token = null, string $redirect = null)
    {
        // If authorized
        if ($this->application()->session()->has("token")) {
            // Providing access to shared resources
            if (!isset($token)) {
                return true;
            }

            // Providing access to the user's personal resources
            if ($token === $this->application()->session()->get("token")) {
                return true;
            }
        }

        // If not logged in
        if (isset($redirect)) {
            return $this->handleRedirect($redirect, ["status" => "Access denied"]);
        }

        return false;
    }

    public function role(string $role, string $privilege, string $redirect = null)
    {
        if (in_array($privilege, $this->roles[$role])) {
            return true;
        }

        if (isset($redirect)) {
            $this->handleRedirect($redirect, ["status" => "Permissions denied"]);
        }

        return false;
    }

    public function updateSessionIfSetRememberMe($redirect = "login"): void
    {
        // If the user is logged in using the remember_me flag
        if ($this->application()->cookie()->has("RudraPermit")) {
            if ($this->sessionHash === $this->application()->cookie()->get("RudraPermit")) {
                $this->setAuthSession(
                    $this->application()->cookie()->get("RudraUser"),
                    $this->application()->cookie()->get("RudraToken")
                );
                return; // @codeCoverageIgnore
            }

            $this->unsetCookie();
            $this->handleRedirect($redirect, ["status" => "Authorization data expired"]);
        }
    }

    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
    }

    private function setAuthSession(string $email, string $token): void
    {
        $this->application()->session()->set(["token", $token]);
        $this->application()->session()->set(["user", $email]);
    }

    private function setCookiesIfSetRememberMe(array $userData, string $token): void
    {
        if ($this->application()->request()->post()->has("remember_me")) {
            $this->application()->cookie()->set(["RudraPermit", [$this->sessionHash, $this->expireTime]]); // @codeCoverageIgnore
            $this->application()->cookie()->set(["RudraToken", [$token, $this->expireTime]]);   // @codeCoverageIgnore
            $this->application()->cookie()->set(["RudraUser", [$userData["email"], $this->expireTime]]);   // @codeCoverageIgnore
        }
    }
}
