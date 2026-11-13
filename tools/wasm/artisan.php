<?php
/* Console entry point used by tools/wasm/artisan.mjs. */
require __DIR__.'/bootstrap.php';

$_SERVER['argv'] = $GLOBALS['RL_ARGV'];
$_SERVER['argc'] = count($GLOBALS['RL_ARGV']);
$_SERVER['SCRIPT_NAME'] = 'artisan';
$_SERVER['SCRIPT_FILENAME'] = RL_BASE_PATH.'/artisan';

/* Boot exactly like the real ./artisan script: build the kernel through bootstrap/app.php. */
$app = require RL_BASE_PATH.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

exit($kernel->handle(new Symfony\Component\Console\Input\ArgvInput($_SERVER['argv'])));
