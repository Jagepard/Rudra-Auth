<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

class Auth extends AuthBase implements AuthInterface
{
    public function login(string $password, \stdClass $user, string $redirect = "admin", string $notice = "Please enter correct information")
    {
        if (password_verify($password, $user->password)) {
            $token = md5($user->password . $user->email);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthSession($user, $token);

            return $this->handleRedirect($redirect, ["status" => "Authorized"]);
        }

        return $this->handleRedirect($redirect, ["status" => "Wrong access data"], $this->loginRedirectWithFlash($notice));
    }

    public function logout(string $redirect = ""): void
    {
        $this->rudra()->session()->unset("token");
        $this->unsetCookie();
        $this->handleRedirect($redirect, ["status" => "Logout"]);
    }

    public function access(string $token = null, string $redirect = null)
    {
        // If authorized
        if ($this->rudra()->session()->has("token")) {
            // Providing access to shared resources
            if (!isset($token)) {
                return true;
            }

            // Providing access to the user's personal resources
            if ($token === $this->rudra()->session()->get("token")) {
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
        $roles = $this->rudra()->config()->get("roles");

        if (in_array($privilege, $roles[$role])) {
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
        if ($this->rudra()->cookie()->has("RudraPermit")) {
            if ($this->sessionHash === $this->rudra()->cookie()->get("RudraPermit")) {
                $this->setAuthSession(
                    json_decode($this->rudra()->cookie()->get("RudraUser")),
                    $this->rudra()->cookie()->get("RudraToken")
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

    private function setAuthSession(object $user, string $token): void
    {
        $this->rudra()->session()->set(["token", $token]);
        $this->rudra()->session()->set(["user", $user]);
    }

    private function setCookiesIfSetRememberMe(\stdClass $user, string $token): void
    {
        if ($this->rudra()->request()->post()->has("remember_me")) {
            $this->rudra()->cookie()->set(["RudraPermit", [$this->sessionHash, $this->expireTime]]); // @codeCoverageIgnore
            $this->rudra()->cookie()->set(["RudraToken", [$token, $this->expireTime]]);   // @codeCoverageIgnore
            $this->rudra()->cookie()->set(["RudraUser", [json_encode($user), $this->expireTime]]);   // @codeCoverageIgnore
        }
    }
}
