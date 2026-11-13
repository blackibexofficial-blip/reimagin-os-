<?php
/* Run an arbitrary snippet with the app booted. Usage: artisan-like exec.file */
require __DIR__.'/bootstrap.php';

$app = require RL_BASE_PATH.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$file = $GLOBALS['RL_EXEC_FILE'];
try {
    require $file;
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    foreach (array_slice($e->getTrace(), 0, 8) as $i => $frame) {
        echo "#{$i} ".($frame['file'] ?? '?').':'.($frame['line'] ?? '?').' '.($frame['class'] ?? '').($frame['type'] ?? '').($frame['function'] ?? '')."\n";
    }
}
