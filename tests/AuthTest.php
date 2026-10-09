<?php declare(strict_types = 1);

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @author  Korotkov Danila (Jagepard) <jagepard@yandex.ru>
 * @license https://mozilla.org/MPL/2.0/  MPL-2.0
 */

namespace Rudra\Auth\Tests;

use Rudra\Auth\Auth;
use Rudra\Container\Rudra;
use Rudra\Container\Session;
use Rudra\Exceptions\LogicException;
use Rudra\Container\Interfaces\RudraInterface;

class AuthTest extends \PHPUnit\Framework\TestCase
{
    private Auth $auth;
    private Session $session;
    private RudraInterface $rudra;

    protected function setUp(): void
    {
        $this->rudra = Rudra::run();
        $this->rudra->config([
            'url'         => 'http://example.com',
            'environment' => 'test',
            'roles'       => [
                'admin'  => 0,
                'editor' => 1,
                'user'   => 2
            ],
            'secret' => 'pass'
        ]);
        $this->rudra->binding([RudraInterface::class => $this->rudra]);
        $this->rudra->request()->server()->set([
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla'
        ]);

        $this->session = $this->rudra->session();
        $this->auth    = $this->rudra->get(Auth::class);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegularAccess()
    {
        session_start();
        $this->session->set('token', 'token');
        $this->assertTrue($this->auth->authorization());
        $this->session->remove('token');
        $this->assertFalse($this->auth->authorization('someToken'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserAccess(): void
    {
        /* User Access */
        session_start();
        $this->session->set('token', 'userIdToken');
        $this->assertTrue($this->auth->authorization('userIdToken'));
        $this->session->remove('token');
        $this->assertFalse($this->auth->authorization('userIdToken'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheck(): void
    {
        session_start();
        $server = $this->rudra->request()->server();

        $_COOKIE["RudraPermit{$this->auth->getSessionHash()}"] = md5(
            $server->get('REMOTE_ADDR') .
            $server->get('HTTP_USER_AGENT')
        );
        $_COOKIE["RudraPermit{$this->auth->getSessionHash()}"] = 'userIdToken';
        $_COOKIE["RudraPermit{$this->auth->getSessionHash()}"] = json_encode((object)[]);

        $this->auth->restoreSessionIfSetRememberMe();
        $this->session->set('token', 'userIdToken');
        $this->assertEquals('userIdToken', $this->rudra->session()->get('token'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin(): void
    {
        session_start();
        $this->assertNull(
            $this->auth->authentication([
                'email'    => '',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
            ], 'password'));

        $this->assertNull(
            $this->auth->authentication([
                'email'    => '',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
            ], 'wrong'));
    }

    public function testAuthenticationWrongUserArrayException()
    {
        $this->expectException(LogicException::class);
        $this->auth->authentication(['email' => ''], 'password');
    }

    public function testAuthenticationWrongRedirectArrayException()
    {
        $this->expectException(LogicException::class);
        $this->auth->authentication(
            ['email' => '', 'password' => ''], 
            'password', 
            ['admin', 'login', 'admin']
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout(): void
    {
        session_start();
        $this->auth->logout();
        $this->assertFalse($this->session->has('token'));
    }

    public function testRole(): void
    {
        $this->assertTrue($this->auth->roleBasedAccess('admin', 'admin'));
        $this->assertFalse($this->auth->roleBasedAccess('editor', 'admin'));
        $this->assertTrue($this->auth->roleBasedAccess('editor', 'editor'));

        $this->assertFalse($this->auth->roleBasedAccess('user', 'admin'));
        $this->assertFalse($this->auth->roleBasedAccess('user', 'editor'));
        $this->assertTrue($this->auth->roleBasedAccess('user', 'user'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        session_start();
        /* Regular Access */
        $this->session->set('token', 'token');
        $this->assertTrue($this->auth->authorization(null, 'API'));

        $this->auth->logout();
        $this->assertFalse($this->auth->authorization(null, 'API'));
    }

    public function testHash()
    {
        $password = 'password';
        $hash     = $this->auth->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserToken()
    {
        session_start();
        $this->session->set('token', 'someToken');
        $this->assertEquals('someToken', $this->session->get('token'));
    }

    public function testEncryptDecryptWithReflection(): void
    {
        $data   = '123ABC';
        $secret = '1234567891011121';

        $encryptMethod = new \ReflectionMethod($this->auth, 'encrypt');
        $encryptMethod->setAccessible(true);
        $encrypted = $encryptMethod->invoke($this->auth, $data, $secret);

        $decryptMethod = new \ReflectionMethod($this->auth, 'decrypt');
        $decryptMethod->setAccessible(true);
        $decrypted = $decryptMethod->invoke($this->auth, $encrypted, $secret);

        $this->assertSame($data, $decrypted);
    }
}
