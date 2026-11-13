<?php

namespace App\Support;

use NumberFormatter;

/**
 * Money is stored as integer minor units so that totals never drift, and this
 * class is the only place that knows how to turn those integers into text.
 */
class Money
{
    public const DEFAULT_CURRENCY = 'PKR';

    private const SYMBOLS = [
        'PKR' => 'Rs',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€',
        'AED' => 'AED',
    ];

    public static function symbol(?string $currency = null): string
    {
        $currency = $currency ?: self::DEFAULT_CURRENCY;

        return self::SYMBOLS[$currency] ?? $currency;
    }

    public static function toMinor(int|float|string|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        return (int) round(((float) $amount) * 100);
    }

    public static function toDecimal(int $minor): float
    {
        return round($minor / 100, 2);
    }

    /** Plain decimal string without a currency symbol — safe for inputs and CSV. */
    public static function toMajorString(int $minor): string
    {
        return number_format(self::toDecimal($minor), 2, '.', '');
    }

    public static function format(int|float|null $minor, ?string $currency = null): string
    {
        $currency = $currency ?: self::DEFAULT_CURRENCY;
        $minor = (int) round((float) ($minor ?? 0));

        $decimals = $minor % 100 === 0 ? 0 : 2;
        $formatted = number_format(abs($minor) / 100, $decimals, '.', ',');
        $sign = $minor < 0 ? '-' : '';

        return $sign.self::symbol($currency).' '.$formatted;
    }

    /** Percentage of a whole, rounded to one decimal place (0 when the whole is 0). */
    public static function percentOf(int $part, int $whole): float
    {
        if ($whole === 0) {
            return 0.0;
        }

        return round($part / $whole * 100, 1);
    }

    /** Apply a percentage to an amount, rounded to the nearest minor unit. */
    public static function ofPercent(int $minor, float $percent): int
    {
        return (int) round($minor * max(0, $percent) / 100);
    }

    /** Locale-aware formatting for invoices where grouping style matters. */
    public static function localised(int $minor, string $currency = self::DEFAULT_CURRENCY, string $locale = 'en_PK'): string
    {
        if (! class_exists(NumberFormatter::class)) {
            return self::format($minor, $currency);
        }

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return $formatter->formatCurrency(self::toDecimal($minor), $currency) ?: self::format($minor, $currency);
    }
}
