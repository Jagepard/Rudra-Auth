<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */

namespace Rudra\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

/**
 * Class AuthTest
 */
class AuthTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var StubClass
     */
    protected $stubClass;

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
        $this->assertFalse(rudra()->get('auth')->access(true, 'userIdToken', ''));
        $this->assertNull(rudra()->get('auth')->access(false, 'userIdToken', ''));
    }

    public function testCheck(): void
    {
        $this->assertNull($this->stubClass()->checkCookie());

        rudra()->setSession('token', 'userIdToken');
        $this->stubClass()->checkCookie();
        $this->assertEquals('userIdToken', rudra()->getSession('token'));

        rudra()->setServer('REMOTE_ADDR', '127.0.0.1');
        rudra()->setServer('HTTP_USER_AGENT', 'Mozilla');

        rudra()->setCookie('RudraPermit', 'userIdToken');
        rudra()->setCookie('RudraToken', 'userIdToken');
        $this->stubClass()->checkCookie();

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
        $this->assertTrue($this->stubClass()->role('admin', 'admin'));
        $this->assertTrue($this->stubClass()->role('admin', 'editor'));
        $this->assertTrue($this->stubClass()->role('admin', 'user'));

        $this->assertFalse($this->stubClass()->role('editor', 'admin'));
        $this->assertFalse($this->stubClass()->role('editor', 'admin', true));
        $this->assertTrue($this->stubClass()->role('editor', 'editor'));
        $this->assertTrue($this->stubClass()->role('editor', 'user'));

        $this->assertFalse($this->stubClass()->role('user', 'admin'));
        $this->assertFalse($this->stubClass()->role('user', 'editor'));
        $this->assertFalse($this->stubClass()->role('user', 'admin', true));
        $this->assertFalse($this->stubClass()->role('user', 'editor', true));
        $this->assertTrue($this->stubClass()->role('user', 'user'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        rudra()->setSession('token', 'token');
        $this->assertTrue(rudra()->get('auth')->access(false, null, 'API'));

        $this->stubClass()->logout();
        $this->assertFalse(rudra()->get('auth')->access(true, null, 'API'));
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
