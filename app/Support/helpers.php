<?php

use App\Models\Setting;
use App\Support\Money;
use Illuminate\Support\Facades\Route;

if (! function_exists('route_exists')) {
    /**
     * True when a named route is registered.
     *
     * Navigation and quick actions are declared as data, so a module can be
     * absent (for example before its controller ships) without breaking pages.
     */
    function route_exists(string $name): bool
    {
        return Route::has($name);
    }
}

if (! function_exists('money')) {
    /** Format an integer minor-unit amount for display. */
    function money(int|float|null $minor, ?string $currency = null): string
    {
        return Money::format($minor, $currency);
    }
}

if (! function_exists('settings')) {
    /** Read a persisted setting with a fallback. */
    function settings(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}
