<?php

declare(strict_types = 1);

/**
 * Date: 17.02.17
 * Time: 13:23
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 *
 *  phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */


use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container;


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

        $this->stubClass()->container()->setSession('token', 'undefined');
        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->assertFalse($this->stubClass()->auth(true));
        $this->assertNull($this->stubClass()->auth());

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

        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->stubClass()->check();
        $this->assertEquals('userIdToken', $this->stubClass()->container()->getSession('token'));

        $this->stubClass()->container()->setServer('REMOTE_ADDR', '127.0.0.1');
        $this->stubClass()->container()->setServer('HTTP_USER_AGENT', 'Mozilla');

        $this->stubClass()->container()->setCookie('RUDRA', md5($this->stubClass()->container()->getServer('REMOTE_ADDR')
            . $this->stubClass()->container()->getServer('HTTP_USER_AGENT')));
        $this->stubClass()->container()->setCookie('RUDRA_INVOICE', md5('user' . 'password'));
        $this->stubClass()->check();
        $this->assertEquals(md5('user' . 'password'), $this->stubClass()->container()->getSession('token'));

        $this->stubClass()->container()->setServer('REMOTE_ADDR', '127.0.0.1');
        $this->stubClass()->container()->setServer('HTTP_USER_AGENT', 'Chrome');

        $this->stubClass()->container()->setCookie('RUDRA_INVOICE', md5('user' . 'password'));
        $this->assertNull($this->stubClass()->check());
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

    /**
     * @return StubClass
     */
    public function stubClass(): StubClass
    {
        return $this->stubClass;
    }
}
