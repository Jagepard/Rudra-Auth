<?php

declare(strict_types=1);

/**
 * @author    : Jagepard <jagepard@yandex.ru">
 * @license   https://mit-license.org/ MIT
 *
 * phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */

namespace Rudra\Auth\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;
use Rudra\Auth\Tests\Stub\StubClass;
use Rudra\Container\Facades\{RequestFacade as Request, RudraFacade as Rudra};

class AuthTest extends PHPUnit_Framework_TestCase
{
    private StubClass $stubClass;

    protected function setUp(): void
    {
        $_SESSION = [];
        Request::server()->set(["REMOTE_ADDR" => "127.0.0.1"]);
        Request::server()->set(["REMOTE_ADDR" => "127.0.0.1"]);
        Request::server()->set(["HTTP_USER_AGENT" => "Mozilla"]);
        $this->stubClass = new StubClass();
    }

    public function testRegularAccess()
    {
        Rudra::session()->set(["token", "token"]);
        $this->assertTrue($this->stubClass->auth());

        Rudra::session()->unset("token");
        $this->assertNull($this->stubClass->auth("someToken"));
    }

    public function testUserAccess(): void
    {
        /* User Access */
        Rudra::session()->set(["token", "userIdToken"]);
        $this->assertTrue($this->stubClass->auth("userIdToken"));

        Rudra::session()->unset("token");
        $this->assertFalse(Rudra::get("auth")->access("userIdToken"));
        $this->assertNull(Rudra::get("auth")->access("userIdToken", ''));
    }

    public function testCheck(): void
    {
        $this->assertNull($this->stubClass->updateSessionIfSetRememberMe());

        Rudra::session()->set(["token", "userIdToken"]);
        $this->stubClass->updateSessionIfSetRememberMe();
        $this->assertEquals("userIdToken", Rudra::session()->get("token"));

        Rudra::cookie()->set(["RudraPermit", "userIdToken"]);
        Rudra::cookie()->set(["RudraToken", "userIdToken"]);
        $this->stubClass->updateSessionIfSetRememberMe();

        $this->assertEquals(Rudra::cookie()->get("RudraToken"), Rudra::session()->get("token"));
    }

    public function testLogin(): void
    {
        $this->assertNull(
            $this->stubClass->login("password", [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));

        $this->assertNull(
            $this->stubClass->login("wrong", [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ]));
    }

    public function testLogout(): void
    {
        $this->assertNull($this->stubClass->logout());
        $this->assertFalse(Rudra::session()->has("token"));
    }

    public function testRole(): void
    {
        $this->assertTrue($this->stubClass->role("admin", 'C'));
        $this->assertTrue($this->stubClass->role("admin", 'U'));
        $this->assertTrue($this->stubClass->role("admin", 'D'));

        $this->assertFalse($this->stubClass->role("editor", 'D'));
        $this->assertFalse($this->stubClass->role("editor", 'D', ''));
        $this->assertTrue($this->stubClass->role("editor", 'C'));
        $this->assertTrue($this->stubClass->role("editor", 'U'));

        $this->assertFalse($this->stubClass->role("user", 'D'));
        $this->assertFalse($this->stubClass->role("user", 'U'));
        $this->assertFalse($this->stubClass->role("user", 'D', ''));
        $this->assertFalse($this->stubClass->role("user", 'U', ''));
        $this->assertTrue($this->stubClass->role("user", 'C'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        Rudra::session()->set(["token", "token"]);
        $this->assertTrue(Rudra::get("auth")->access(null, "API"));

        $this->stubClass->logout();
        $this->assertNull(Rudra::get("auth")->access(null, "API"));
    }

    public function testHash()
    {
        $password = "password";
        $hash     = $this->stubClass->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserToken()
    {
        Rudra::session()->set(["token", "someToken"]);
        $this->assertEquals($this->stubClass->userToken(), Rudra::session()->get("token"));
    }
}
