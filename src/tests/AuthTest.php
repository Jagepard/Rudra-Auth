<?php

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container;

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
class AuthTest extends PHPUnit_Framework_TestCase
{

    protected $stubClass;

    protected function setUp()
    {
        $this->stubClass = new StubClass(Container::app());
    }

    public function testRegularAccess()
    {
        $this->stubClass()->container()->setSession('token', true);
        $this->stubClass()->container()->get('auth')->setToken(true);
        $this->assertTrue($this->stubClass()->auth());

        $this->stubClass()->container()->setSession('token', 'undefined');
        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->assertFalse($this->stubClass()->auth(true));
        $this->assertNull($this->stubClass()->auth());
    }

    public function testUserAccess()
    {
        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->stubClass()->container()->get('auth')->setToken('userIdToken');
        $this->assertTrue($this->stubClass()->auth(false, 'userIdToken'));

        $this->stubClass()->container()->get('auth')->setToken(false);
        $this->stubClass()->container()->unsetSession('token');
        $this->assertFalse($this->stubClass()->auth(true, 'userIdToken'));
        $this->assertNull($this->stubClass()->auth(false, 'userIdToken'));
    }

    public function testCheck()
    {
        $this->stubClass()->check();
        $this->assertFalse($this->stubClass()->container()->get('auth')->isToken());

        $this->stubClass()->container()->setSession('token', 'userIdToken');
        $this->stubClass()->check();
        $this->assertEquals('userIdToken', $this->stubClass()->container()->getSession('token'));

        /* Установка данных для remember_me */
        $this->stubClass()->container()->setServer([
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla'
        ]);

        $this->stubClass()->container()->setCookie('RUDRA', md5($this->stubClass()->container()->getServer('REMOTE_ADDR')
            . $this->stubClass()->container()->getServer('HTTP_USER_AGENT')));

        $this->stubClass()->container()->setCookie('RUDRA_INVOICE', $this->stubClass()->container()->get('auth')->getUserToken('user', 'password'));

        $this->stubClass()->check();
        $this->assertEquals($this->stubClass()->container()->get('auth')->getUserToken('user', 'password'),
            $this->stubClass()->container()->getSession('token'));

        $this->stubClass()->container()->setServer([
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Chrome'
        ]);

        $this->stubClass()->container()->setCookie('RUDRA', md5($this->stubClass()->container()->getServer('REMOTE_ADDR')
            . $this->stubClass()->container()->getServer('HTTP_USER_AGENT')));

        $this->stubClass()->container()->setCookie('RUDRA_INVOICE', $this->stubClass()->container()->get('auth')->getUserToken('user', 'password'));

        $this->assertNull($this->stubClass()->check());
    }

    /**
     * @return mixed
     */
    public function stubClass()
    {
        return $this->stubClass;
    }
}
