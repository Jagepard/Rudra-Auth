<?php

namespace Rudra\Auth\Tests\Stub;

use Rudra\Auth\Auth;
use Rudra\Container\Facades\RudraFacade as Rudra;
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

        Rudra::set(["auth", [new Auth(Rudra::run(), "test", $roles)]]);
        Rudra::set(["redirect", [$this, "raw"]]);
    }

    public function run(...$params)
    {
        return null;
    }
}
