<?php
/*
 * php-wasm replacement for PHPUnit's CLI entry script.
 *
 * vendor/phpunit/phpunit/phpunit declares strict_types, so it cannot be
 * require()d from here. This file sets the CLI prerequisites it needs and hands
 * off to the same TextUI\Application entry point.
 */
require __DIR__.'/bootstrap.php';

if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', RL_BASE_PATH.'/vendor/autoload.php');
}

exit((new PHPUnit\TextUI\Application())->run($_SERVER['argv']));
