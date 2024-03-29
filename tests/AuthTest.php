<?php

declare(strict_types = 1);

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
            "url"         => "http://example.com",
            "environment" => "test",
            "roles"       => [
                "admin"  => 0,
                "editor" => 1,
                "user"   => 2
            ]
        ]);
        Rudra::binding([RudraInterface::class => Rudra::run()]);
        Rudra::serviceList([
            \Rudra\Auth\Auth::class  => \Rudra\Auth\Auth::class,
            Redirect\Redirect::class => Redirect\Redirect::class
        ]);
        Request::server()->set([
            "REMOTE_ADDR"     => "127.0.0.1",
            "HTTP_USER_AGENT" => "Mozilla"
        ]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegularAccess()
    {
        session_start();
        Session::set(["token", "token"]);
        $this->assertTrue(Auth::authorization());

        Session::unset("token");
        $this->assertFalse(Auth::authorization("someToken"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserAccess(): void
    {
        /* User Access */
        session_start();
        Session::set(["token", "userIdToken"]);
        $this->assertTrue(Auth::authorization("userIdToken"));

        Session::unset("token");
        $this->assertFalse(Auth::authorization("userIdToken"));
        $this->assertNull(Auth::authorization("userIdToken", ''));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheck(): void
    {
        session_start();
        $_COOKIE["RudraPermit" . Auth::getSessionHash()] = md5(
            Request::server()->get("REMOTE_ADDR") .
            Request::server()->get("HTTP_USER_AGENT")
        );;
        $_COOKIE["RudraToken" . Auth::getSessionHash()] = "userIdToken";
        $_COOKIE["RudraUser" . Auth::getSessionHash()]  = json_encode((object)[]);

        Auth::restoreSessionIfSetRememberMe();
        Session::set(["token", "userIdToken"]);
        $this->assertEquals("userIdToken", Session::get("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin(): void
    {
        session_start();
        $this->assertNull(
            Auth::authentication([
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ], "password"));

        $this->assertNull(
            Auth::authentication([
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ], "wrong"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout(): void
    {
        session_start();
        Auth::exitAuthenticationSession();
        $this->assertFalse(Session::has("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRole(): void
    {
        $this->assertTrue(Auth::roleBasedAccess("admin", "admin"));
        $this->assertFalse(Auth::roleBasedAccess("editor", "admin"));
        $this->assertFalse(Auth::roleBasedAccess("editor", "admin", ''));
        $this->assertTrue(Auth::roleBasedAccess("editor", "editor"));

        $this->assertFalse(Auth::roleBasedAccess("user", "admin"));
        $this->assertFalse(Auth::roleBasedAccess("user", "editor"));
        $this->assertFalse(Auth::roleBasedAccess("user", "admin", ''));
        $this->assertFalse(Auth::roleBasedAccess("user", "editor", ''));
        $this->assertTrue(Auth::roleBasedAccess("user", "user"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        session_start();
        /* Regular Access */
        Session::set(["token", "token"]);
        $this->assertTrue(Auth::authorization(null, "API"));

        Auth::exitAuthenticationSession();
        $this->assertNull(Auth::authorization(null, "API"));
    }

    public function testHash()
    {
        $password = "password";
        $hash     = Auth::bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserToken()
    {
        session_start();
        Session::set(["token", "someToken"]);
        $this->assertEquals("someToken", Session::get("token"));
    }
}
