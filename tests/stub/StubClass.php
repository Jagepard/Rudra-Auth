<?php

declare(strict_types=1);

namespace Rudra\Tests;

use Rudra\Auth;
use Rudra\ExternalTraits\AuthTrait;

class StubClass
{

    use AuthTrait;

    public function __construct()
    {
        $roles = [
            'admin'  => ['C', 'U', 'D'],
            'editor' => ['C', 'U'],
            'user'   => ['C']
        ];

        rudra()->set('auth', new Auth(rudra(), 'test', $roles), 'raw');
        rudra()->set('redirect', $this, 'raw');
    }

    public function run(...$params)
    {
        return null;
    }
}
