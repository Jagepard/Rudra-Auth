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
