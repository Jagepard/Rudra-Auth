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
use Rudra\Container\Application;

class AuthTest extends PHPUnit_Framework_TestCase
{
    private StubClass $stubClass;

    protected function setUp(): void
    {
        Application::run()->request()->server()->set(["REMOTE_ADDR" => "127.0.0.1"]);
        Application::run()->request()->server()->set(["HTTP_USER_AGENT" => "Mozilla"]);
        $this->stubClass = new StubClass();
    }

    public function testRegularAccess()
    {
        Application::run()->session()->set(["token", "token"]);
        $this->assertTrue($this->stubClass()->auth());

        Application::run()->session()->unset("token");
        $this->assertNull($this->stubClass()->auth('someToken'));
    }

    public function testUserAccess(): void
    {
        /* User Access */
        Application::run()->session()->set(["token", "userIdToken"]);
        $this->assertTrue($this->stubClass()->auth("userIdToken"));

        Application::run()->session()->unset("token");
        $this->assertFalse(Application::run()->objects()->get("auth")->access("userIdToken"));
        $this->assertNull(Application::run()->objects()->get("auth")->access("userIdToken", ''));
    }

    public function testCheck(): void
    {
        $this->assertNull($this->stubClass()->updateSessionIfSetRememberMe());

        Application::run()->session()->set(["token", "userIdToken"]);
        $this->stubClass()->updateSessionIfSetRememberMe();
        $this->assertEquals("userIdToken", Application::run()->session()->get("token"));

        Application::run()->cookie()->set(["RudraPermit", "userIdToken"]);
        Application::run()->cookie()->set(["RudraToken", "userIdToken"]);
        $this->stubClass()->updateSessionIfSetRememberMe();

        $this->assertEquals(Application::run()->cookie()->get("RudraToken"), Application::run()->session()->get("token"));
    }

    public function testLogin(): void
    {
        $this->assertNull(
            $this->stubClass()->login("password", [
                "email"    => "",
                "password" => password_hash("password", PASSWORD_BCRYPT, ['cost' => 10])
            ]));

        $this->assertNull(
            $this->stubClass()->login('wrong', [
                "email"    => '',
                "password" => password_hash("password", PASSWORD_BCRYPT, ['cost' => 10])
            ]));
    }

    public function testLogout(): void
    {
        unset($_COOKIE['RudraPermit']);
        $this->assertNull($this->stubClass()->logout());
        $this->assertFalse(Application::run()->hasSession("token"));
    }

    public function testRole(): void
    {
        $this->assertTrue($this->stubClass()->role('admin', 'C'));
        $this->assertTrue($this->stubClass()->role('admin', 'U'));
        $this->assertTrue($this->stubClass()->role('admin', 'D'));

        $this->assertFalse($this->stubClass()->role('editor', 'D'));
        $this->assertFalse($this->stubClass()->role('editor', 'D', ''));
        $this->assertTrue($this->stubClass()->role('editor', 'C'));
        $this->assertTrue($this->stubClass()->role('editor', 'U'));

        $this->assertFalse($this->stubClass()->role('user', 'D'));
        $this->assertFalse($this->stubClass()->role('user', 'U'));
        $this->assertFalse($this->stubClass()->role('user', 'D', ''));
        $this->assertFalse($this->stubClass()->role('user', 'U', ''));
        $this->assertTrue($this->stubClass()->role('user', 'C'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testJsonResponse()
    {
        /* Regular Access */
        Application::run()->setSession("token", "token");
        $this->assertTrue(Application::run()->get('auth')->access(null, 'API'));

        $this->stubClass()->logout();
        $this->assertNull(Application::run()->get('auth')->access(null, 'API'));
    }

    public function testHash()
    {
        $password = "password";
        $hash     = $this->stubClass()->bcrypt($password);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserToken()
    {
        Application::run()->setSession("token", 'someToken');
        $this->assertEquals($this->stubClass()->userToken(), Application::run()->getSession("token"));
    }

    /**
     * @return StubClass
     */
    public function stubClass(): StubClass
    {
        return $this->stubClass;
    }
}
