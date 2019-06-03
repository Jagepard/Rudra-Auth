<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @copyright Copyright (c) 2019, Jagepard
 * @license   https://mit-license.org/ MIT
 *
 * phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */

namespace Rudra\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class AuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StubClass
     */
    private $stubClass;

    protected function setUp(): void
    {
        $this->stubClass = new StubClass();
    }

    public function testRegularAccess()
    {
        rudra()->setSession('token', 'token');
        $this->assertTrue($this->stubClass()->auth());

        rudra()->unsetSession('token');
        $this->assertNull($this->stubClass()->auth('someToken'));
    }

    public function testUserAccess(): void
    {
        /* User Access */
        rudra()->setSession('token', 'userIdToken');
        $this->assertTrue($this->stubClass()->auth('userIdToken'));

        rudra()->unsetSession('token');
        $this->assertFalse(rudra()->get('auth')->access('userIdToken'));
        $this->assertNull(rudra()->get('auth')->access('userIdToken', ''));
    }

    public function testCheck(): void
    {
        $this->assertNull($this->stubClass()->updateSessionIfSetRememberMe());

        rudra()->setSession('token', 'userIdToken');
        $this->stubClass()->updateSessionIfSetRememberMe();
        $this->assertEquals('userIdToken', rudra()->getSession('token'));

        rudra()->setServer('REMOTE_ADDR', '127.0.0.1');
        rudra()->setServer('HTTP_USER_AGENT', 'Mozilla');

        rudra()->setCookie('RudraPermit', 'userIdToken');
        rudra()->setCookie('RudraToken', 'userIdToken');
        $this->stubClass()->updateSessionIfSetRememberMe();

        $this->assertEquals(rudra()->getCookie('RudraToken'), rudra()->getSession('token'));
    }

    public function testLogin(): void
    {
        $this->assertNull(
            $this->stubClass()->login('password', [
                'email'    => '',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
            ]));

        $this->assertNull(
            $this->stubClass()->login('wrong', [
                'email'    => '',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
            ]));
    }

    public function testLogout(): void
    {
        unset($_COOKIE['RudraPermit']);
        $this->assertNull($this->stubClass()->logout());
        $this->assertFalse(rudra()->hasSession('token'));
    }

    public function testRole(): void
    {
        $this->assertTrue($this->stubClass()->role('admin', 'C'));
        $this->assertTrue($this->stubClass()->role('admin', 'U'));
        $this->assertTrue($this->stubClass()->role('admin', 'D'));

        $this->assertFalse($this->stubClass()->role('editor', 'D'));
        $this->assertFalse($this->stubClass()->role('editor', 'D', ''));
        $this->assertTrue($this->stubClass()->role('editor', 'C'));
        $this->assertTrue($this->stubClass()->role('editor', 'U'));

        $this->assertFalse($this->stubClass()->role('user', 'D'));
        $this->assertFalse($this->stubClass()->role('user', 'U'));
        $this->assertFalse($this->stubClass()->role('user', 'D', ''));
        $this->assertFalse($this->stubClass()->role('user', 'U', ''));
        $this->assertTrue($this->stubClass()->role('user', 'C'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        rudra()->setSession('token', 'token');
        $this->assertTrue(rudra()->get('auth')->access(null, 'API'));

        $this->stubClass()->logout();
        $this->assertNull(rudra()->get('auth')->access(null, 'API'));
    }

    public function testHash()
    {
        $password = 'password';
        $hash     = $this->stubClass()->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserToken()
    {
        rudra()->setSession('token', 'someToken');
        $this->assertEquals($this->stubClass()->userToken(), rudra()->getSession('token'));
    }

    /**
     * @return StubClass
     */
    public function stubClass(): StubClass
    {
        return $this->stubClass;
    }
}
