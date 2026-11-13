<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Session cart.
 *
 * Deliberately session-backed rather than a `carts` table: the storefront is
 * quote-led (most pieces are made to order), so a cart is a short-lived enquiry
 * that becomes an order on checkout. Prices are always re-read from products
 * when totals are calculated, never from the session payload.
 */
class Cart
{
    public const KEY = 'cart';

    /** @return array<string, array{product_id:int, quantity:int}> */
    public static function contents(Request $request): array
    {
        $cart = $request->session()->get(self::KEY, []);

        return is_array($cart) ? $cart : [];
    }

    public static function put(Request $request, string $row, array $line): void
    {
        $cart = self::contents($request);
        $cart[$row] = $line;
        $request->session()->put(self::KEY, $cart);
    }

    public static function remove(Request $request, string $row): void
    {
        $cart = self::contents($request);
        unset($cart[$row]);
        $request->session()->put(self::KEY, $cart);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget(self::KEY);
    }

    public static function count(Request $request): int
    {
        return array_sum(array_map(fn (array $line) => (int) $line['quantity'], self::contents($request)));
    }

    /**
     * Hydrated lines with live pricing.
     *
     * @return array{lines: array<int, array<string, mixed>>, subtotal_minor: int, count: int}
     */
    public static function resolve(Request $request): array
    {
        $cart = self::contents($request);

        if ($cart === []) {
            return ['lines' => [], 'subtotal_minor' => 0, 'count' => 0];
        }

        $products = Product::query()
            ->whereIn('id', array_column($cart, 'product_id'))
            ->with('featuredImage')
            ->get()
            ->keyBy('id');

        $lines = [];
        $subtotal = 0;

        foreach ($cart as $row => $line) {
            $product = $products->get($line['product_id'] ?? 0);

            /* A product withdrawn from sale disappears from the basket rather
             * than being silently charged at an old price. */
            if (! $product || ! $product->is_active || $product->status !== 'published') {
                continue;
            }

            $quantity = max(1, min(20, (int) $line['quantity']));
            $lineTotal = $product->price_minor * $quantity;
            $subtotal += $lineTotal;

            $lines[] = [
                'row' => $row,
                'product' => $product,
                'quantity' => $quantity,
                'unit_price_minor' => $product->price_minor,
                'line_total_minor' => $lineTotal,
            ];
        }

        return ['lines' => $lines, 'subtotal_minor' => $subtotal, 'count' => array_sum(array_column($lines, 'quantity'))];
    }
}
