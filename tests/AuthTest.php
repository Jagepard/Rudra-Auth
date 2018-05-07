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

use Rudra\Container;
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
        $this->stubClass = new StubClass(Container::app());
    }

    public function testAccess(): void
    {
        /* Regular Access */
        $this->stubClass()->container()->setSession('token', 'token');
        $this->assertTrue($this->stubClass()->auth());

        $this->stubClass()->container()->unsetSession('token');
        $this->assertNull($this->stubClass()->auth('someToken'));

        /* User Access */
        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->assertTrue($this->stubClass()->auth( 'userIdToken'));

        $this->stubClass()->container()->unsetSession('token');
        $this->assertFalse($this->stubClass()->container()->get('auth')->access(true, 'userIdToken', ''));
        $this->assertNull($this->stubClass()->container()->get('auth')->access(false, 'userIdToken', ''));
    }

    public function testCheck(): void
    {
        $this->assertNull($this->stubClass()->checkCookie());

        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->stubClass()->checkCookie();
        $this->assertEquals('userIdToken', $this->stubClass()->container()->getSession('token'));

        $this->stubClass()->container()->setServer('REMOTE_ADDR', '127.0.0.1');
        $this->stubClass()->container()->setServer('HTTP_USER_AGENT', 'Mozilla');

        $this->stubClass()->container()->setCookie('RudraPermit', 'userIdToken');
        $this->stubClass()->container()->setCookie('RudraToken', 'userIdToken');
        $this->stubClass()->checkCookie();

        $this->assertEquals($this->stubClass()->container()->getCookie('RudraToken'), $this->stubClass()->container()->getSession('token'));
    }

    public function testLogin(): void
    {
        $this->assertNull($this->stubClass()->login('password', ''));
        $this->assertNull($this->stubClass()->login(
            'password',
            password_hash('password', PASSWORD_BCRYPT, ['cost' => 10])
        ));
    }

    public function testLogout(): void
    {
        unset($_COOKIE['RudraPermit']);
        $this->assertNull($this->stubClass()->logout());
        $this->assertFalse($this->stubClass()->container()->hasSession('token'));
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
        $this->stubClass()->container()->setSession('token', 'token');
        $this->assertTrue($this->stubClass()->container()->get('auth')->access(false, null, 'API'));

        $this->stubClass()->logout();
        $this->assertFalse($this->stubClass()->container()->get('auth')->access(true, null, 'API'));
    }

    public function testHash()
    {
        $password = 'password';
        $hash     = $this->stubClass()->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserToken()
    {
        $this->stubClass()->container()->setSession('token', 'someToken');
        $this->assertEquals($this->stubClass()->userToken(), $this->stubClass()->container()->getSession('token'));
    }

    /**
     * @return StubClass
     */
    public function stubClass(): StubClass
    {
        return $this->stubClass;
    }
}
