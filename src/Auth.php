<?php

declare(strict_types = 1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 */

namespace Rudra\Auth;

use Rudra\Redirect\Redirect;
use Rudra\Container\Facades\Rudra;
use Rudra\Container\Facades\Cookie;
use Rudra\Container\Facades\Session;
use Rudra\Container\Facades\Request;
use Rudra\Container\Interfaces\RudraInterface;

class Auth implements AuthInterface
{
    protected int    $expireTime;
    protected string $sessionHash;

    /**
     * Sets cookie lifetime, session hash
     * ----------------------------------
     * Устанавливает время жизни cookie, хеш сеанса
     * 
     * Auth constructor.
     * @param RudraInterface $rudra
     */
    public function __construct(RudraInterface $rudra)
    {
        $this->expireTime  = time() + 3600 * 24 * 7;
        $this->sessionHash = md5(Request::server()->get("REMOTE_ADDR") . Request::server()->get("HTTP_USER_AGENT"));
    }

    /**
     * Authentication
     * --------------
     * Аутентификация
     * 
     * @param \stdClass $user
     * @param string $password
     * @param string $redirect
     * @param string $notice
     * @return callable
     */
    public function authentication(array $user, string $password, string $redirect = "", string $notice = "")
    {
        if (!isset($user['password'])) {
            throw new \InvalidArgumentException("Invalid user's array received");
        }

        if (password_verify($password, $user['password'])) {
            session_regenerate_id();
            $token = md5($user['password'] . $user['email'] . $this->sessionHash);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthenticationSession($user, $token);

            return $this->handleRedirect($redirect, ["status" => "Authorized"]);
        }

        return $this->handleRedirect($redirect, ["status" => "Wrong access data"], $this->loginRedirectWithNotice($notice));
    }

    /**
     * Sets cookies if present $_POST["remember_me"]
     * ---------------------------------------------
     * Устанавливает cookies если есть $_POST["remember_me"]
     * 
     * @param array $user
     * @param string $token
     */
    private function setCookiesIfSetRememberMe(array $user, string $token): void
    {
        if (Request::post()->has("remember_me")) {
            Cookie::set([md5("RudraPermit" . $this->sessionHash), [$this->sessionHash, $this->expireTime]]); // @codeCoverageIgnore
            Cookie::set([md5("RudraToken" . $this->sessionHash), [$token, $this->expireTime]]);   // @codeCoverageIgnore
            Cookie::set([md5("RudraUser" . $this->sessionHash), [$this->encrypt(json_encode($user), Rudra::config()->get('secret')), $this->expireTime]]);   // @codeCoverageIgnore
        }
    }

    /**
     * Sets session data on successful authentication
     * ----------------------------------------------
     * Устанавливает данные сессии при успешной аутентификации
     * 
     * @param array $user
     * @param string $token
     */
    private function setAuthenticationSession(array $user, string $token): void
    {
        Session::set(["token", $token]);
        Session::set(["user", $user]);
    }

    /**
     * Exit authentication session
     * ---------------------------
     * Выйти из сеанса аутентификации
     * 
     * @param string $redirect
     */
    public function exitAuthenticationSession(string $redirect = ""): void
    {
        Session::unset("token");
        Session::unset("user");
        $this->unsetRememberMeCookie();
        session_regenerate_id();
        $this->handleRedirect($redirect, ["status" => "Logout"]);
    }

    /**
     * Removes the $_POST["remember_me"] cookie
     * ----------------------------------------
     * Удаляет $_POST["remember_me"] cookie
     */
    protected function unsetRememberMeCookie(): void
    {
        if ("test" !== Rudra::config()->get("environment")) {
            // @codeCoverageIgnoreStart
            if (Cookie::has(md5("RudraPermit" . $this->sessionHash))) {
                Cookie::unset(md5("RudraPermit" . $this->sessionHash)); // @codeCoverageIgnore
                Cookie::unset(md5("RudraToken" . $this->sessionHash)); // @codeCoverageIgnore
                Cookie::unset(md5("RudraUser" . $this->sessionHash)); // @codeCoverageIgnore
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * Authorization
     * Providing access to shared or personal resources
     * ------------------------------------------------
     * Авторизация
     * Предоставление доступа к общим или личным ресурсам
     * 
     * @param string|null $token
     * @param string|null $redirect
     * @return bool|callable
     */
    public function authorization(string $token = null, string $redirect = null)
    {
        // If authorized / Если авторизован
        if (Session::has("token")) {
            // Providing access to shared resources / Предоставление доступа к общим ресурсам
            if (!isset($token)) {
                return true;
            }

            // Providing access to the user's personal resources / Предоставление доступа к личным ресурсам пользователя
            if ($token === Session::get("token")) {
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
     * Role based access
     * -----------------
     * Доступ на основе ролей
     * 
     * @param string $role
     * @param string $privilege
     * @param string|null $redirect
     * @return bool
     */
    public function roleBasedAccess(string $role, string $privilege, string $redirect = null)
    {
        $roles = Rudra::config()->get("roles");

        if ($roles[$role] <= $roles[$privilege]) {
            return true;
        }

        if (isset($redirect)) {
            $this->handleRedirect($redirect, ["status" => "Permissions denied"]);
        }

        return false;
    }

    /**
     * Restore session data if $_POST["remember_me"] was set
     * -----------------------------------------------------
     * Восствнавливает данные сессии если был установлен $_POST["remember_me"]
     * 
     * @param string $redirect
     */
    public function restoreSessionIfSetRememberMe($redirect = "login"): void
    {
        // If the user is logged in using the remember_me flag
        if (Cookie::has(md5("RudraPermit" . $this->sessionHash))) {

            if ($this->sessionHash === Cookie::get(md5("RudraPermit" . $this->sessionHash))) {
                $this->setAuthenticationSession(
                    json_decode($this->decrypt(Cookie::get(md5("RudraUser" . $this->sessionHash)), Rudra::config()->get('secret')), true),
                    Cookie::get(md5("RudraToken" . $this->sessionHash))
                );
                return; // @codeCoverageIgnore
            }

            $this->unsetRememberMeCookie();
            $this->handleRedirect($redirect, ["status" => "Authorization data expired"]);
        }
    }

    /**
     * Creates a password hash
     * -----------------------
     * Создаёт хеш пароля
     * 
     * @param string $password
     * @param int $cost
     * @return string
     */
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
    }

    protected function handleRedirect(string $redirect, array $jsonResponse, callable $redirectCallable = null)
    {
        ("API" !== $redirect) ?: Rudra::response()->json($jsonResponse);

        if (isset($redirectCallable)) {
            return $redirectCallable;
        }

        Rudra::get(Redirect::class)->run($redirect);
    }

    /**
     * Redirect by setting a notification
     * ----------------------------------
     * Перенаправить установив уведомление
     * 
     * @param string $notice
     * @codeCoverageIgnore
     */
    protected function loginRedirectWithNotice(string $notice): void
    {
        Session::set(["alert", ["error" => $notice]]);
        Rudra::get(Redirect::class)->run("login");
    }

    /**
     * Gets the hash of the session
     * ----------------------------
     * Получает хэш сессии
     * 
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
        $encryption_iv = '1234567891011121';

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
        $decryption_iv = '1234567891011121';
        
        return openssl_decrypt($data, $ciphering, $secret, $options, $decryption_iv);
    }
}
