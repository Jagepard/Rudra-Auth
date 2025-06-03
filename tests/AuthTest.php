<?php

declare(strict_types = 1);

/**
 * @author  : Jagepard <jagepard@yandex.ru">
 * @license https://mit-license.org/ MIT
 *
 * phpunit src/tests/AuthTest --coverage-html src/tests/build/coverage-html
 */

namespace Rudra\Auth\Tests;

use Rudra\Auth\Auth;
use Rudra\Container\Rudra;
use Rudra\Redirect\Redirect;
use Rudra\Container\Interfaces\RudraInterface;
use PHPUnit\Framework\TestCase as PHPUnit_Framework_TestCase;

class AuthTest extends PHPUnit_Framework_TestCase
{
    private RudraInterface $rudra;

    protected function setUp(): void
    {
        $this->rudra = Rudra::run();
        $this->rudra->config([
            "url"         => "http://example.com",
            "environment" => "test",
            "roles"       => [
                "admin"  => 0,
                "editor" => 1,
                "user"   => 2
            ]
        ]);
        $this->rudra->binding([RudraInterface::class => $this->rudra]);
        $this->rudra->request()->server()->set([
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
        $this->rudra->session()->set(["token", "token"]);
        $this->assertTrue($this->rudra->get(Auth::class)->authorization());
        $this->rudra->session()->unset("token");
        $this->assertFalse($this->rudra->get(Auth::class)->authorization("someToken"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserAccess(): void
    {
        /* User Access */
        session_start();
        $this->rudra->session()->set(["token", "userIdToken"]);
        $this->assertTrue($this->rudra->get(Auth::class)->authorization("userIdToken"));

        $this->rudra->session()->unset("token");
        $this->assertFalse($this->rudra->get(Auth::class)->authorization("userIdToken"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheck(): void
    {
        session_start();
        $_COOKIE["RudraPermit" . $this->rudra->get(Auth::class)->getSessionHash()] = md5(
            $this->rudra->request()->server()->get("REMOTE_ADDR") .
            $this->rudra->request()->server()->get("HTTP_USER_AGENT")
        );;
        $_COOKIE["RudraToken" . $this->rudra->get(Auth::class)->getSessionHash()] = "userIdToken";
        $_COOKIE["RudraUser" . $this->rudra->get(Auth::class)->getSessionHash()]  = json_encode((object)[]);

        $this->rudra->get(Auth::class)->restoreSessionIfSetRememberMe();
        $this->rudra->session()->set(["token", "userIdToken"]);
        $this->assertEquals("userIdToken", $this->rudra->session()->get("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin(): void
    {
        session_start();
        $this->assertNull(
            $this->rudra->get(Auth::class)->authentication([
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ["cost" => 10])
            ], "password"));

        $this->assertNull(
            $this->rudra->get(Auth::class)->authentication([
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
        $this->rudra->get(Auth::class)->exitAuthenticationSession();
        $this->assertFalse($this->rudra->session()->has("token"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRole(): void
    {
        $this->assertTrue($this->rudra->get(Auth::class)->roleBasedAccess("admin", "admin"));
        $this->assertFalse($this->rudra->get(Auth::class)->roleBasedAccess("editor", "admin"));
        $this->assertTrue($this->rudra->get(Auth::class)->roleBasedAccess("editor", "editor"));

        $this->assertFalse($this->rudra->get(Auth::class)->roleBasedAccess("user", "admin"));
        $this->assertFalse($this->rudra->get(Auth::class)->roleBasedAccess("user", "editor"));
        $this->assertTrue($this->rudra->get(Auth::class)->roleBasedAccess("user", "user"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        session_start();
        /* Regular Access */
        $this->rudra->session()->set(["token", "token"]);
        $this->assertTrue($this->rudra->get(Auth::class)->authorization(null, "API"));

        $this->rudra->get(Auth::class)->exitAuthenticationSession();
        $this->assertNull($this->rudra->get(Auth::class)->authorization(null, "API"));
    }

    public function testHash()
    {
        $password = "password";
        $hash     = $this->rudra->get(Auth::class)->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUserToken()
    {
        session_start();
        $this->rudra->session()->set(["token", "someToken"]);
        $this->assertEquals("someToken", $this->rudra->session()->get("token"));
    }
}
