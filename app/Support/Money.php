<?php

namespace App\Support;

/**
 * Money is always stored and moved as integer minor units (paisa/cents).
 *
 * Floating point arithmetic is never used for totals; this class is the only
 * place where a human-entered decimal becomes an integer and back again.
 */
final class Money
{
    public const DEFAULT_CURRENCY = 'PKR';

    /** @var array<string, string> */
    private const SYMBOLS = [
        'PKR' => 'Rs ',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'AED ',
    ];

    public static function format(?int $minor, string $currency = self::DEFAULT_CURRENCY): string
    {
        return self::symbol($currency).number_format(($minor ?? 0) / 100, 2);
    }

    public static function symbol(string $currency = self::DEFAULT_CURRENCY): string
    {
        return self::SYMBOLS[strtoupper($currency)] ?? strtoupper($currency).' ';
    }

    /** Parse a human-entered amount ("1,250.50") into minor units. */
    public static function toMinor(int|float|string|null $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return (int) round((float) $clean * 100);
    }

    public static function toDecimal(int $minor): float
    {
        return round($minor / 100, 2);
    }

    /** Inverse of toMinor(): a plain decimal string for form inputs and exports. */
    public static function toMajorString(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    /** Apply a percentage discount to a minor-unit amount, rounded once. */
    public static function percentOf(int $minor, float $percent): int
    {
        return (int) round($minor * $percent / 100);
    }
}
