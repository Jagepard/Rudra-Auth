<?php

declare(strict_types=1);

/**
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2018, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

namespace Rudra\Tests;

use Rudra\Auth;
use Rudra\ContainerInterface;
use Rudra\ExternalTraits\AuthTrait;

/**
 * Class StubClass
 */
class StubClass
{

    use AuthTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * StubClass constructor.
     *
     * @param ContainerInterface $container
     */
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

    /**
     * @param array ...$params
     *
     * @return null
     */
    public function run(...$params)
    {
        return null;
    }

    /**
     * @return ContainerInterface
     */
    public function container(): ContainerInterface
    {
        return $this->container;
    }
}