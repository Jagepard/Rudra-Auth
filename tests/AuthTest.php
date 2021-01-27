<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 *
 * phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */

namespace Rudra\Auth\Tests;

use Rudra\Redirect;
use Rudra\Container\Facades\{Request, Rudra, Session};
use Rudra\Auth\AuthFacade as Auth;
use Rudra\Container\Interfaces\RudraInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class AuthTest extends PHPUnit_Framework_TestCase
{
    protected function setUp(): void
    {
        Rudra::config([
            "siteUrl" => "http://example.com",
            "environment" => "test",
            "roles" => [
                "admin"  => 0,
                "editor" => 1,
                "user"   => 2
            ]
        ]);
        Rudra::binding([RudraInterface::class => Rudra::run()]);
        Rudra::services([
            \Rudra\Auth\Auth::class => \Rudra\Auth\Auth::class,
            Redirect\Redirect::class => Redirect\Redirect::class
        ]);
        Request::server()->set([
            "REMOTE_ADDR" => "127.0.0.1",
            "HTTP_USER_AGENT" => "Mozilla"
        ]);
    }

    public function testRegularAccess()
    {
        Session::set(["token", "token"]);
        $this->assertTrue(Auth::access());

        Session::unset("token");
        $this->assertFalse(Auth::access("someToken"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserAccess(): void
    {
        /* User Access */
        Session::set(["token", "userIdToken"]);
        $this->assertTrue(Auth::access("userIdToken"));

        Session::unset("token");
        $this->assertFalse(Auth::access("userIdToken"));
        $this->assertNull(Auth::access("userIdToken", ''));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheck(): void
    {
        $_COOKIE["RudraPermit"] = md5(
            Request::server()->get("REMOTE_ADDR") .
            Request::server()->get("HTTP_USER_AGENT")
        );;
        $_COOKIE["RudraToken"] = "userIdToken";
        $_COOKIE["RudraUser"] = json_encode((object)[]);

        Auth::updateSessionIfSetRememberMe();
        $this->assertEquals("userIdToken", Session::get("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin(): void
    {
        $this->assertNull(
            Auth::login("password", (object) [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));

        $this->assertNull(
            Auth::login("wrong", (object) [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout(): void
    {
        Auth::logout();
        $this->assertFalse(Session::has("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRole(): void
    {
        $this->assertTrue(Auth::role("admin", "admin"));
        $this->assertFalse(Auth::role("editor", "admin"));
        $this->assertFalse(Auth::role("editor", "admin", ''));
        $this->assertTrue(Auth::role("editor", "editor"));

        $this->assertFalse(Auth::role("user", "admin"));
        $this->assertFalse(Auth::role("user", "editor"));
        $this->assertFalse(Auth::role("user", "admin", ''));
        $this->assertFalse(Auth::role("user", "editor", ''));
        $this->assertTrue(Auth::role("user", "user"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        Session::set(["token", "token"]);
        $this->assertTrue(Auth::access(null, "API"));

        Auth::logout();
        $this->assertNull(Auth::access(null, "API"));
    }

    public function testHash()
    {
        $password = "password";
        $hash     = Auth::bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserToken()
    {
        Session::set(["token", "someToken"]);
        $this->assertEquals("someToken", Session::get("token"));
    }
}
