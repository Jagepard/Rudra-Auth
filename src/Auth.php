<?php declare(strict_types = 1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Auth;

use Rudra\Redirect\Redirect;
use Rudra\Exceptions\LogicException;
use Rudra\Container\Interfaces\RudraInterface;

class Auth implements AuthInterface
{
    private int    $expireTime;
    private string $sessionHash;

    /**
     * @param RudraInterface $rudra
     */
    public function __construct(private readonly RudraInterface $rudra)
    {
        $remoteAddr = $rudra->request()?->server()?->get("REMOTE_ADDR") ?? '';
        $userAgent  = $rudra->request()?->server()?->get("HTTP_USER_AGENT") ?? '';
        $secret     = $rudra->config()?->get("secret") ?? throw new \RuntimeException('Auth secret is missing');

        // Sets the cookie lifetime, session hash / Устанавливает время жизни cookie, хэш сессии.
        $this->expireTime  = strtotime('+1 week');
        $this->sessionHash = hash_hmac(
            algo: 'sha256',
            data: $remoteAddr . $userAgent . session_id(),
            key: $secret
        );
    }

    /**
     * @param  array{email: string, password: string} $user
     * @param  string $password
     * @param  array{0: string, 1: string} $redirect // [0]: 'admin' (успех), [1]: 'login' (ошибка)
     * @param  array{error: string} $notice
     * @return void
     */
    #[\Override]
    public function authentication(array $user, string $password,
        array $redirect = ['admin', 'login'],
        array $notice   = ['error' => 'Wrong access data']
    ): void {
        if (!isset($user['password'], $user['email'])) {
            throw new LogicException("User's array must contain 'password' and 'email'");
        }

        if (count($redirect) !== 2) {
            throw new LogicException("Redirect array must contain exactly two elements");
        }

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            
            $token = hash('sha256', $user['password'] . $user['email'] . $this->sessionHash);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthenticationSession($user, $token);

            $this->handleRedirect($redirect[0], ['status' => 'Authorized']);
            return;
        }

        $this->rudra->session()->set('alert', $notice);
        $this->handleRedirect($redirect[1], ['status' => 'Wrong access data']);
    }

    /**
     * @param  array  $user
     * @param  string $token
     * @return void
     * @codeCoverageIgnore
     */
    private function setCookiesIfSetRememberMe(array $user, string $token): void
    {
        if (!$this->rudra->request()->post()->has("remember_me")) {
            return;
        }

        $secret = $this->rudra->config()->get('secret');
        $hash   = $this->sessionHash;
        
        $this->rudra->cookie()->set(
            md5("RudraPermit{$hash}"), $hash, $this->expireTime
        );

        $this->rudra->cookie()->set(
            md5("RudraToken{$hash}"), $token, $this->expireTime
        );

        $this->rudra->cookie()->set(
            md5("RudraUser{$hash}"),
            $this->encrypt(json_encode($user, JSON_THROW_ON_ERROR), $secret),
            $this->expireTime
        );
    }

    /**
     * @param  array  $user
     * @param  string $token
     * @return void
     */
    private function setAuthenticationSession(array $user, string $token): void
    {
        $this->rudra->session()->set("token", $token);
        $this->rudra->session()->set("user", $user);
    }

    /**
     * @param  string $redirect
     * @return void
     */
    #[\Override]
    public function logout(string $redirect = ""): void
    {
        $this->rudra->session()->remove("token");
        $this->rudra->session()->remove("user");
        $this->unsetRememberMeCookie();
        session_regenerate_id(true);
        $this->handleRedirect($redirect, ['status' => 'Logout']);
    }

    /**
     * @return void
     * @codeCoverageIgnore
     */
    private function unsetRememberMeCookie(): void
    {
        if ("test" === $this->rudra->config()->get("environment")) {
            return;
        }

        $hash = $this->sessionHash;
        
        if ($this->rudra->cookie()->has(md5("RudraPermit{$hash}"))) {
            $this->rudra->cookie()->remove(md5("RudraPermit{$hash}"));
            $this->rudra->cookie()->remove(md5("RudraToken{$hash}"));
            $this->rudra->cookie()->remove(md5("RudraUser{$hash}"));
        }
    }

    /**
     * @param  string|null $token
     * @param  string|null $redirect
     * @return bool
     */
    #[\Override]
    public function authorization(?string $token = null, ?string $redirect = null): bool
    {
        if (!$this->rudra->session()->has("token")) {
            return false;
        }

        // Providing access to shared resources / Предоставление доступа к общим ресурсам
        if ($token === null) {
            return true;
        }

        // Providing access to the user's personal resources / Предоставление доступа к личным ресурсам пользователя
        if (hash_equals($token, $this->rudra->session()->get("token"))) {
            return true;
        }

        // If not logged in / Если не авторизован
        if ($redirect !== null) {
            $this->handleRedirect($redirect, ["status" => "Access denied"]);
            return false;
        }

        return false;
    }

    /**
     * @param  string      $role
     * @param  string      $privilege
     * @param  string|null $redirect
     * @return bool
     */
    #[\Override]
    public function roleBasedAccess(string $role, string $privilege, ?string $redirect = null): bool
    {
        $roles = $this->rudra->config()->get("roles");

        // Roles: the smaller the number, the higher the privilege (1 > 2 > 3) / Роли: чем меньше число, тем выше привилегия (1 > 2 > 3)
        if ($roles[$role] <= $roles[$privilege]) {
            return true;
        }

        if ($redirect !== null) {
            $this->handleRedirect($redirect, ['status' => 'Permissions denied']); // @codeCoverageIgnore
            return false;
        }

        return false;
    }

    /**
     * @param  string $redirect
     * @return void
     * @codeCoverageIgnore
     */
    public function restoreSessionIfSetRememberMe(string $redirect = "login"): void
    {
        $hash      = $this->sessionHash;
        $permitKey = md5("RudraPermit{$hash}");

        if (!$this->rudra->cookie()->has($permitKey)) {
            return;
        }

        if ($hash === $this->rudra->cookie()->get($permitKey)) {
            $secret    = $this->rudra->config()->get('secret');
            $userKey   = md5("RudraUser{$hash}");
            $tokenKey  = md5("RudraToken{$hash}");

            $this->setAuthenticationSession(
                json_decode($this->decrypt($this->rudra->cookie()->get($userKey), $secret), true, 512, JSON_THROW_ON_ERROR),
                $this->rudra->cookie()->get($tokenKey)
            );

            return;
        }

        $this->unsetRememberMeCookie();
        $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
    }

    /**
     * @param  string $password
     * @param  int    $cost
     * @return string
     */
    #[\Override]
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }


    /**
     * @param  string $redirect
     * @param  array  $jsonResponse
     * @return void
     */
    private function handleRedirect(string $redirect, array $jsonResponse): void
    {
        if ($redirect === 'API') {
            $this->rudra->response()->json($jsonResponse);
            return;
        }

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
     * @return string
     */
    private function encrypt(string $data, string $secret): string
    {
        $ciphering = 'AES-128-CTR';
        $iv        = $this->rudra->config()->get('secret');
        $result    = openssl_encrypt($data, $ciphering, $secret, 0, $iv);

        if ($result === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return $result;
    }

    /**
     * @param  string $data
     * @param  string $secret
     * @return string
     */
    private function decrypt(string $data, string $secret): string
    {
        $ciphering = 'AES-128-CTR';
        $iv        = $this->rudra->config()->get('secret');
        $result    = openssl_decrypt($data, $ciphering, $secret, 0, $iv);

        if ($result === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $result;
    }
}
