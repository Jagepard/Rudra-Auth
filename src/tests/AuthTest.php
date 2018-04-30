<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */


use Rudra\Auth;
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
        $this->stubClass()->container()->setSession('token', '1');
        $this->stubClass()->container()->get('auth')->setToken(true);
        $this->assertTrue($this->stubClass()->auth());

        $this->stubClass()->container()->unsetSession('token');
        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->assertFalse($this->stubClass()->auth(true));

        /* User Access */
        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->stubClass()->container()->get('auth')->setToken('userIdToken');
        $this->assertTrue($this->stubClass()->auth(false, 'userIdToken'));

        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->stubClass()->container()->unsetSession('token');
        $this->assertFalse($this->stubClass()->auth(true, 'userIdToken'));
        $this->assertNull($this->stubClass()->auth(false, 'userIdToken'));
    }

    public function testCheck(): void
    {
        $this->stubClass()->check();
        $this->assertFalse($this->stubClass()->container()->get('auth')->getToken());

        $this->stubClass()->container()->setSession('token', 'check');
        $this->stubClass()->check();
        $this->assertEquals('check', $this->stubClass()->container()->getSession('token'));

        $this->stubClass()->container()->setServer('REMOTE_ADDR', '127.0.0.1');
        $this->stubClass()->container()->setServer('HTTP_USER_AGENT', 'Mozilla');

        $this->stubClass()->container()->setCookie('RUDRA', 'check');
        $this->stubClass()->container()->setCookie('RUDRA_INVOICE', 'check');
        $this->stubClass()->check();

        $this->assertEquals($this->stubClass()->container()->getCookie('RUDRA_INVOICE'), $this->stubClass()->container()->getSession('token'));
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
        unset($_COOKIE['RUDRA']);
        $this->assertNull($this->stubClass()->logout());
        $this->assertFalse($this->stubClass()->container()->hasSession('token'));
    }

    public function testRole(): void
    {
        $this->assertTrue($this->stubClass()->role('admin', 'admin'));
        $this->assertTrue($this->stubClass()->role('admin', 'redactor'));
        $this->assertTrue($this->stubClass()->role('admin', 'editor'));

        $this->assertFalse($this->stubClass()->role('redactor', 'admin'));
        $this->assertNull($this->stubClass()->role('redactor', 'admin', true));
        $this->assertTrue($this->stubClass()->role('redactor', 'redactor'));
        $this->assertTrue($this->stubClass()->role('redactor', 'editor'));

        $this->assertFalse($this->stubClass()->role('editor', 'admin'));
        $this->assertFalse($this->stubClass()->role('editor', 'redactor'));
        $this->assertNull($this->stubClass()->role('editor', 'admin', true));
        $this->assertNull($this->stubClass()->role('editor', 'redactor', true));
        $this->assertTrue($this->stubClass()->role('editor', 'editor'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        $this->stubClass()->container()->setSession('token', '1');
        $this->stubClass()->container()->get('auth')->setToken(true);
        $this->assertTrue($this->stubClass()->auth(false, null, ['API', 'API']));

        $this->stubClass()->container()->setSession('token', 'undefined');
        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->assertFalse($this->stubClass()->auth(true, null, ['API', 'API']));
    }

    public function testHash()
    {
        $password = 'password';
        $hash     = Auth::bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));

        $this->stubClass()->container()->get('auth')->bcrypt($password);
        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * @return StubClass
     */
    public function stubClass(): StubClass
    {
        return $this->stubClass;
    }
}
