<?php

declare(strict_types = 1);

define('DEV', 'test');

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/Auth.php';
require_once dirname(__DIR__) . '/AuthTrait.php';
require_once 'stub/StubClass.php';