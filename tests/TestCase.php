<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Sleep;

abstract class TestCase extends BaseTestCase
{
    /** The schema is built once per process and rolled back per test. */
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        /* Vite is compiled before the suite runs; pages must not try to reach a
         * dev server from inside the test process. */
        $this->withoutVite();

        /* Laravel only auto-disables CSRF when the app believes it is running in
         * a console; under php-wasm that flag is false, so every POST from a
         * test would 419 without this. */
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        /* Auth::attempt() pads failed logins to a fixed duration with usleep(),
         * which traps inside php-wasm. Faking Sleep keeps the timing-attack
         * guard in place for real requests while leaving the suite runnable. */
        Sleep::fake();

        if (! static::$migrated) {
            /* A file database is used rather than :memory: because every test
             * boots a fresh application container, and a new SQLite in-memory
             * connection starts empty. */
            $file = database_path('testing.sqlite');

            if (! is_file($file)) {
                touch($file);
            }

            Artisan::call('migrate:fresh', ['--force' => true]);
            static::$migrated = true;
        }

        /* Every test runs inside a rolled-back transaction, which keeps the
         * suite fast without the framework's RefreshDatabase trait — that trait
         * traps the php-wasm runtime it is run under. */
        $this->app['db']->connection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        while ($this->app['db']->connection()->transactionLevel() > 0) {
            $this->app['db']->connection()->rollBack();
        }

        parent::tearDown();
    }
}
