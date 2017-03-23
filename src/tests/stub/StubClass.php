<?php
/**
 * Date: 23.03.17
 * Time: 14:31
 *
 * @author    : Korotkov Danila <dankorot@gmail.com>
 * @copyright Copyright (c) 2016, Korotkov Danila
 * @license   http://www.gnu.org/licenses/gpl.html GNU GPLv3.0
 */

use Rudra\AuthTrait;
use Rudra\IContainer;
use Rudra\Auth;

class StubClass
{

    use AuthTrait;

    protected $container;

    public function __construct(IContainer $container)
    {
        $this->container = $container;
        $this->container->set('auth', new Auth($this->container), 'raw');
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
     * @return mixed
     */
    public function container()
    {
        return $this->container;
    }
}