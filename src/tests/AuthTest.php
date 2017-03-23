<?php

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Container;
use stub\Redirect;

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

    protected $auth;
    protected $container;

    protected function setUp()
    {
        $this->container = Container::app();
        $this->auth      = new \Rudra\Auth($this->container);

        $this->auth->setToken(true);
        $this->container->set('redirect', new Redirect(), 'raw');
    }

    public function testRegularAccess()
    {
        $this->container()->setSession('token', true);
        $this->auth()->setToken(true);
        $this->assertTrue($this->auth()->auth());

        $this->container()->setSession('token', 'undefined');
        $this->auth()->setToken(false);
        $this->assertFalse($this->auth()->auth(true));
        $this->assertNull($this->auth()->auth());
    }

    public function testUserAccess()
    {
        $this->container()->setSession('token', 'userIdToken');
        $this->auth()->setToken('userIdToken');
        $this->assertTrue($this->auth()->auth(false, 'userIdToken'));

        $this->auth()->setToken(false);
        $this->container()->unsetSession('token');
        $this->assertFalse($this->auth()->auth(true, 'userIdToken'));
        $this->assertNull($this->auth()->auth(false, 'userIdToken'));
    }


    public function testCheck()
    {
        $this->auth()->check();
        $this->assertFalse($this->auth()->isToken());

        $this->container()->setSession('token', 'userIdToken');
        $this->auth()->check();
        $this->assertEquals('userIdToken', $this->container()->getSession('token'));

        /* Установка данных для remember_me */
        $this->container()->setServer([
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla'
        ]);

        $this->container()->setCookie('RUDRA', md5($this->container()->getServer('REMOTE_ADDR')
            . $this->container()->getServer('HTTP_USER_AGENT')));

        $this->container()->setCookie('RUDRA_INVOICE', $this->auth()->getUserToken('user', 'password'));

        $this->auth()->check();
        $this->assertEquals($this->auth()->getUserToken('user', 'password'), $this->container()->getSession('token'));

        $this->container()->setServer([
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Chrome'
        ]);

        $this->container()->setCookie('RUDRA', md5($this->container()->getServer('REMOTE_ADDR')
            . $this->container()->getServer('HTTP_USER_AGENT')));

        $this->container()->setCookie('RUDRA_INVOICE', $this->auth()->getUserToken('user', 'password'));

        $this->assertNull($this->auth()->check());
    }

    /**
     * @return mixed
     */
    public function auth()
    {
        return $this->auth;
    }

    /**
     * @return mixed
     */
    public function container()
    {
        return $this->container;
    }
}
