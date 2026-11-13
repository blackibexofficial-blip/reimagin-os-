<?php
/* Console entry point used by tools/wasm/artisan.mjs. */
require __DIR__.'/bootstrap.php';

$_SERVER['argv'] = $GLOBALS['RL_ARGV'];
$_SERVER['argc'] = count($GLOBALS['RL_ARGV']);
$_SERVER['SCRIPT_NAME'] = 'artisan';
$_SERVER['SCRIPT_FILENAME'] = RL_BASE_PATH.'/artisan';

/* Boot exactly like ./artisan: build the kernel through bootstrap/app.php, then
   hand it real ConsoleOutput objects so framework error rendering works. */
$app = require RL_BASE_PATH.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$output = new Symfony\Component\Console\Output\ConsoleOutput();

try {
    $status = $kernel->handle(new Symfony\Component\Console\Input\ArgvInput($_SERVER['argv']), $output);
} catch (Throwable $e) {
    fwrite(STDERR, '\n'.get_class($e).': '.$e->getMessage()."\n".$e->getFile().':'.$e->getLine()."\n");
    $status = 1;
}

exit($status);
