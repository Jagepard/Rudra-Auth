<?php

namespace Rudra\Auth\Tests\Stub;

use Rudra\Auth\Auth;
use Rudra\Container\Application;
use Rudra\Auth\AuthTrait;

class StubClass
{
    use AuthTrait;

    public function __construct()
    {
        $roles = [
            "admin"  => ['C', 'U', 'D'],
            "editor" => ['C', 'U'],
            "user"   => ['C']
        ];

        Application::run()->objects()->set(["auth", [new Auth(Application::run(), "test", $roles), "raw"]]);
        Application::run()->objects()->set(["redirect", [$this, "raw"]]);
    }

    public function run(...$params)
    {
        return null;
    }
}
