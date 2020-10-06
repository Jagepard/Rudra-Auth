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
use Rudra\Auth\Auth as AuthService;
use Rudra\Container\Interfaces\RudraInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class AuthTest extends PHPUnit_Framework_TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Rudra::setConfig([
            "siteUrl" => "http://example.com",
            "environment" => "test"
        ]);
        Rudra::setServices(
            [
                "contracts" => [
                    RudraInterface::class => Rudra::run(),
                ],
                "services" => []
            ]
        );
        Request::server()->set(["REMOTE_ADDR" => "127.0.0.1"]);
        Request::server()->set(["REMOTE_ADDR" => "127.0.0.1"]);
        Request::server()->set(["HTTP_USER_AGENT" => "Mozilla"]);
        $roles = [
            "admin"  => ['C', 'U', 'D'],
            "editor" => ['C', 'U'],
            "user"   => ['C']
        ];

        Rudra::set([\Rudra\Auth\Auth::class, [new AuthService(Rudra::run(), "test", $roles)]]);
        Rudra::set([Redirect\Redirect::class, Redirect\Redirect::class]);
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
        Auth::updateSessionIfSetRememberMe();
        Session::set(["token", "userIdToken"]);
        Auth::updateSessionIfSetRememberMe();
        $this->assertEquals("userIdToken", Session::get("token"));

        Rudra::cookie()->set(["RudraPermit", "userIdToken"]);
        Rudra::cookie()->set(["RudraToken", "userIdToken"]);
        Auth::updateSessionIfSetRememberMe();

        $this->assertEquals(Rudra::cookie()->get("RudraToken"), Session::get("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin(): void
    {
        $this->assertNull(
            Auth::login("password", [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));

        $this->assertNull(
            Auth::login("wrong", [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout(): void
    {
        $this->assertNull(Auth::logout());
        $this->assertFalse(Session::has("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRole(): void
    {
        $this->assertTrue(Auth::role("admin", 'C'));
        $this->assertTrue(Auth::role("admin", 'U'));
        $this->assertTrue(Auth::role("admin", 'D'));

        $this->assertFalse(Auth::role("editor", 'D'));
        $this->assertFalse(Auth::role("editor", 'D', ''));
        $this->assertTrue(Auth::role("editor", 'C'));
        $this->assertTrue(Auth::role("editor", 'U'));

        $this->assertFalse(Auth::role("user", 'D'));
        $this->assertFalse(Auth::role("user", 'U'));
        $this->assertFalse(Auth::role("user", 'D', ''));
        $this->assertFalse(Auth::role("user", 'U', ''));
        $this->assertTrue(Auth::role("user", 'C'));
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
