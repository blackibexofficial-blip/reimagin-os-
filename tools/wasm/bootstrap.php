<?php
/*
 * Shared bootstrapper for the php-wasm runners (artisan, tests, server).
 * Keeps every entry point on the same autoloader and working directory.
 */

if (! defined('RL_BASE_PATH')) {
    define('RL_BASE_PATH', dirname(__DIR__, 2));
}

chdir(RL_BASE_PATH);

require RL_BASE_PATH.'/vendor/autoload.php';

if (! defined('STDIN')) { define('STDIN', fopen('php://stdin', 'r')); }
if (! defined('STDOUT')) { define('STDOUT', fopen('php://stdout', 'w')); }
if (! defined('STDERR')) { define('STDERR', fopen('php://stderr', 'w')); }

if (! ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

return true;
