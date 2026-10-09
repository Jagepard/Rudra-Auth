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

use Rudra\Container\Cookie;
use Rudra\Container\Session;
use Rudra\Container\Request;
use Rudra\Container\Response;
use Rudra\Redirect\Redirect;
use Rudra\Exceptions\LogicException;
use Rudra\Container\Interfaces\RudraInterface;

class Auth implements AuthInterface
{
    private const CIPHER = 'AES-128-CTR';

    private readonly Session $session;
    private readonly Request $request;
    private readonly Cookie  $cookie;
    private readonly Response $response;
    private readonly Redirect $redirect;
    private readonly string $secret;
    private readonly string $environment;

    private int    $expireTime;
    private string $sessionHash;

    /**
     * @param  RudraInterface $rudra
     * @return void
     * @throws \RuntimeException
     */
    public function __construct(private readonly RudraInterface $rudra)
    {
        $this->cookie   = $this->rudra->cookie();
        $this->session  = $this->rudra->session();
        $this->request  = $this->rudra->request();
        $this->response = $this->rudra->response();
        $this->redirect = $this->rudra->get(Redirect::class);
        
        $config            = $this->rudra->config();
        $this->secret      = $config->get('secret') ?? throw new \RuntimeException('Auth secret is missing');
        $this->environment = $config->get('environment') ?? 'production';

        $remoteAddr = $this->request->server()?->get('REMOTE_ADDR') ?? '';
        $userAgent  = $this->request->server()?->get('HTTP_USER_AGENT') ?? '';

        $this->expireTime  = strtotime('+1 week');
        $this->sessionHash = hash_hmac('sha256', $remoteAddr . $userAgent, $this->secret);
    }

    /**
     * @param  array{email: string, password: string} $user
     * @param  string $password
     * @param  array{0: string, 1: string} $redirect // [0]: 'admin' (success), [1]: 'login' (error)
     * @param  array{error: string} $notice
     * @return void
     * @throws LogicException
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
            throw new LogicException('Redirect array must contain exactly two elements');
        }

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            
            $token = hash('sha256', $user['password'] . $user['email'] . $this->sessionHash);
            $this->setCookiesIfSetRememberMe($user, $token);
            $this->setAuthenticationSession($user, $token);

            $this->handleRedirect($redirect[0], ['status' => 'Authorized']);
            return;
        }

        $this->session->set('alert', $notice);
        $this->handleRedirect($redirect[1], ['status' => 'Wrong access data']);
    }

    /**
     * @codeCoverageIgnore
     */
    private function setCookiesIfSetRememberMe(array $user, string $token): void
    {
        if (!$this->request->post()->has('remember_me')) {
            return;
        }
        
        $payload = json_encode([
            'user'    => $user,
            'token'   => $token,
            'expires' => $this->expireTime
        ], JSON_THROW_ON_ERROR);

        $this->cookie->set(
            'rudra_remember_me',
            $this->encrypt($payload, $this->secret),
            $this->expireTime
        );
    }

    private function setAuthenticationSession(array $user, string $token): void
    {
        $this->session->set('token', $token);
        $this->session->set('user', $user);
    }

    #[\Override]
    public function logout(string $redirect = ''): void
    {
        $this->session->remove('token');
        $this->session->remove('user');
        $this->unsetRememberMeCookie();
        session_regenerate_id(true);
        $this->handleRedirect($redirect, ['status' => 'Logout']);
    }

    /**
     * @codeCoverageIgnore
     */
    private function unsetRememberMeCookie(): void
    {
        if ($this->environment === 'test') {
            return;
        }

        if ($this->cookie->has('rudra_remember_me')) {
            $this->cookie->remove('rudra_remember_me');
        }
    }

    #[\Override]
    public function authorization(?string $token = null, ?string $redirect = null): bool
    {
        if (!$this->session->has('token')) {
            return false;
        }

        // Providing access to shared resources
        if ($token === null) {
            return true;
        }

        // Providing access to the user's personal resources
        if (hash_equals($token, $this->session->get('token'))) {
            return true;
        }

        // If not logged in
        if ($redirect !== null) {
            $this->handleRedirect($redirect, ['status' => 'Access denied']);
            return false;
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     */
    #[\Override]
    public function roleBasedAccess(string $role, string $privilege, ?string $redirect = null): bool
    {
        $roles = $this->rudra->config()->get('roles');

        if (!isset($roles[$role], $roles[$privilege])) {
            throw new \InvalidArgumentException("Role $role or $privilege not found in config");
        }

        // Roles: the smaller the number, the higher the privilege (1 > 2 > 3)
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
     * @codeCoverageIgnore
     */
    public function restoreSessionIfSetRememberMe(string $redirect = 'login'): void
    {
        if (!$this->cookie->has('rudra_remember_me')) {
            return;
        }

        try {
            $decryptedJson = $this->decrypt(
                $this->cookie->get('rudra_remember_me'),
                $this->secret
            );
            $data = json_decode($decryptedJson, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($data['expires']) || $data['expires'] < time()) {
                $this->unsetRememberMeCookie();
                $this->handleRedirect($redirect, ['status' => 'Authorization data expired']);
                return;
            }

            if (isset($data['user'], $data['token'])) {
                $this->setAuthenticationSession($data['user'], $data['token']);
                return;
            }
        } catch (\Exception $e) {
            $this->unsetRememberMeCookie();
            $this->handleRedirect($redirect, ['status' => 'Invalid authorization data']);
            return;
        }
    }


    #[\Override]
    public function bcrypt(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    private function handleRedirect(string $redirect, array $jsonResponse): void
    {
        if ($redirect === 'API') {
            $this->response->json($jsonResponse);
            return;
        }

        $this->redirect->run($redirect);
    }

    public function getSessionHash(): string
    {
        return $this->sessionHash;
    }

    private function encrypt(string $data, string $secret): string
    {
        $ivLength  = openssl_cipher_iv_length(self::CIPHER);
        $iv        = random_bytes($ivLength);
        
        $ciphertext = openssl_encrypt($data, self::CIPHER, $secret, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv . $ciphertext);
    }

    /**
     * @throws \RuntimeException
     */
    private function decrypt(string $data, string $secret): string
    {
        $binary = base64_decode($data, true);
        if ($binary === false) {
            throw new \RuntimeException('Invalid encrypted data (base64 decode failed)');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        
        if (strlen($binary) < $ivLength) {
            throw new \RuntimeException('Encrypted data too short');
        }

        $iv         = substr($binary, 0, $ivLength);
        $ciphertext = substr($binary, $ivLength);
        $result     = openssl_decrypt($ciphertext, self::CIPHER, $secret, OPENSSL_RAW_DATA, $iv);

        if ($result === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $result;
    }
}
