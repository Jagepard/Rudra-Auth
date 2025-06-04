<?php

declare(strict_types = 1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Redirect\Redirect;
use Rudra\Container\Interfaces\RudraInterface;

class Auth implements AuthInterface
{
    private int $expireTime;
    private string $sessionHash;
    private RudraInterface $rudra;

    /**
     * Sets cookie lifetime, session hash
     */
    public function __construct(RudraInterface $rudra)
    {
        $this->rudra       = $rudra;
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5(
            $rudra->request()->server()->get("REMOTE_ADDR") . 
            $rudra->request()->server()->get("HTTP_USER_AGENT")
        );
    }

    /**
     * @param  array  $user
     * @param  string $password
     * @param  array  $redirect
     * @param  array  $notice
     * @return void
     */
    public function authentication(
        array $user, string $password, array $redirect = ['admin', 'login'], array $notice = ["error" => "Wrong access data"]
    )
    {
        if (!isset($user['password'], $user['email'])) {
            throw new \InvalidArgumentException("User's array must contain 'password' and 'email'");
        }

        if (count($redirect) !== 2) {
            throw new \InvalidArgumentException("Redirect array must contain exactly two elements");
        }

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $token = hash('sha256', $user['password'] . $user['email'] . $this->sessionHash);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthenticationSession($user, $token);

            return $this->handleRedirect($redirect[0], ["status" => "Authorized"]);
        }

        $this->rudra->session()->set(["alert", $notice]);

        return $this->handleRedirect($redirect[1], ["status" => "Wrong access data"]);
    }

    /**
     * @param  array  $user
     * @param  string $token
     * @return void
     */
    private function setCookiesIfSetRememberMe(array $user, string $token): void
    {
        if ($this->rudra->request()->post()->has("remember_me")) {
            $this->rudra->cookie()->set([md5("RudraPermit" . $this->sessionHash), [$this->sessionHash, $this->expireTime]]); // @codeCoverageIgnore
            $this->rudra->cookie()->set([md5("RudraToken" . $this->sessionHash), [$token, $this->expireTime]]);   // @codeCoverageIgnore
            $this->rudra->cookie()->set([md5("RudraUser" . $this->sessionHash), [$this->encrypt(json_encode($user), $this->rudra->config()->get('secret')), $this->expireTime]]); // @codeCoverageIgnore
        }
    }

    /**
     * @param  array  $user
     * @param  string $token
     * @return void
     */
    private function setAuthenticationSession(array $user, string $token): void
    {
        $this->rudra->session()->set(["token", $token]);
        $this->rudra->session()->set(["user", $user]);
    }

    /**
     * @param  string $redirect
     * @return void
     */
    public function exitAuthenticationSession(string $redirect = ""): void
    {
        $this->rudra->session()->unset("token");
        $this->rudra->session()->unset("user");
        $this->unsetRememberMeCookie();
        session_regenerate_id(true);
        $this->handleRedirect($redirect, ["status" => "Logout"]);
    }

    /**
     * Removes the $_POST["remember_me"] cookie
     */
    protected function unsetRememberMeCookie(): void
    {
        if ("test" !== $this->rudra->config()->get("environment")) {
            // @codeCoverageIgnoreStart
            if ($this->rudra->cookie()->has(md5("RudraPermit" . $this->sessionHash))) {
                $this->rudra->cookie()->unset(md5("RudraPermit" . $this->sessionHash)); // @codeCoverageIgnore
                $this->rudra->cookie()->unset(md5("RudraToken" . $this->sessionHash)); // @codeCoverageIgnore
                $this->rudra->cookie()->unset(md5("RudraUser" . $this->sessionHash)); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * @param  string|null $token
     * @param  string|null $redirect
     * @return bool
     */
    public function authorization(string $token = null, string $redirect = null): bool
    {
        // If authorized / Если авторизован
        if ($this->rudra->session()->has("token")) {
            // Providing access to shared resources / Предоставление доступа к общим ресурсам
            if (!isset($token)) {
                return true;
            }

            // Providing access to the user's personal resources / Предоставление доступа к личным ресурсам пользователя
            if ($token === $this->rudra->session()->get("token")) {
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
     * @param  string      $role
     * @param  string      $privilege
     * @param  string|null $redirect
     * @return void
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null)
    {
        $roles = $this->rudra->config()->get("roles");

        if ($roles[$role] <= $roles[$privilege]) {
            return true;
        }

        if (isset($redirect)) {
            $this->handleRedirect($redirect, ["status" => "Permissions denied"]);
        }

        return false;
    }

    /**
     * @param  string $redirect
     * @return void
     */
    public function restoreSessionIfSetRememberMe($redirect = "login"): void
    {
        // If the user is logged in using the remember_me flag
        if ($this->rudra->cookie()->has(md5("RudraPermit" . $this->sessionHash))) {

            if ($this->sessionHash === $this->rudra->cookie()->get(md5("RudraPermit" . $this->sessionHash))) {
                $this->setAuthenticationSession(
                    json_decode(
                        $this->decrypt($this->rudra->cookie()->get(md5("RudraUser" . $this->sessionHash)), 
                        $this->rudra->config()->get('secret')), 
                        true
                    ),
                    $this->rudra->cookie()->get(md5("RudraToken" . $this->sessionHash))
                );
                return; // @codeCoverageIgnore
            }

            $this->unsetRememberMeCookie();
            $this->handleRedirect($redirect, ["status" => "Authorization data expired"]);
        }
    }

    /**
     * @param  string  $password
     * @param  integer $cost
     * @return string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
    }


    /**
     * @param  string $redirect
     * @param  array  $jsonResponse
     * @return void
     */
    private function handleRedirect(string $redirect, array $jsonResponse): void
    {
        if ("API" === $redirect) { 
            $this->rudra->response()->json($jsonResponse);
        };

        $this->rudra->get(Redirect::class)->run($redirect);
    }

    /**
     * @return string
     */
    public function getSessionHash(): string
    {
        return $this->sessionHash;
    }

    /**
     * @param  string $data
     * @param  string $secret
     * @return void
     */
    public function encrypt(string $data, string $secret)
    {
        $ciphering     = "AES-128-CTR";
        $iv_length     = openssl_cipher_iv_length($ciphering);
        $options       = 0;
        $encryption_iv = $this->rudra->config()->get('secret') ?? '1234567891011121';

        return openssl_encrypt($data, $ciphering, $secret, $options, $encryption_iv);
    }

    /**
     * @param  string $data
     * @param  string $secret
     * @return void
     */
    public function decrypt(string $data, string $secret)
    {
        $ciphering     = "AES-128-CTR";
        $options       = 0;
        $decryption_iv = $this->rudra->config()->get('secret') ?? '1234567891011121';
        
        return openssl_decrypt($data, $ciphering, $secret, $options, $decryption_iv);
    }
}
