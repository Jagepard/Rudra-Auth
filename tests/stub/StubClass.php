<?php

declare(strict_types=1);

namespace Rudra\Tests;

use Rudra\Auth;
use Rudra\ExternalTraits\AuthTrait;
use Rudra\Interfaces\ContainerInterface;

class StubClass
{

    use AuthTrait;

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $roles = [
            'admin'  => 1,
            'editor' => 3,
            'user'   => 5
        ];

        $this->container = $container;
        $this->container->set('auth', new Auth($this->container, 'test', $roles), 'raw');
        $this->container->set('redirect', $this, 'raw');
    }

    public function run(...$params)
    {
        return null;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
