<?php
/*
 * php-wasm autoloader.
 *
 * Composer is unavailable in the sandbox, so this stands in for the generated
 * vendor/autoload.php: it registers the psr-4 prefixes and classmap produced by
 * tools/wasm/gen-autoload.py using the ClassLoader substitute in stubs/.
 */

if (! defined('RL_BASE_PATH')) {
    define('RL_BASE_PATH', dirname(__DIR__, 2));
}

require_once __DIR__.'/stubs/Composer/Autoload/ClassLoader.php';

$map = require __DIR__.'/psr4.php';

$loader = new Composer\Autoload\ClassLoader();

foreach ($map['psr4'] as $prefix => $dirs) {
    $loader->addPsr4($prefix, $dirs);
}

if (! empty($map['classmap'])) {
    $loader->addClassMap($map['classmap']);
}

$loader->register();

foreach ($map['files'] as $file) {
    if (is_file($file)) {
        require_once $file;
    }
}

return $loader;
