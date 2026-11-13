<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Every stock change goes through here and writes a ledger row.
 *
 * products.stock_quantity is a cache: the ledger is the source of truth and
 * rebuildBalance() can always recompute the cached figure from it.
 */
class StockService
{
    public function adjust(
        Product $product,
        int $quantity,
        string $type = 'adjustment',
        ?string $reason = null,
        ?Model $source = null,
        ?User $user = null,
        ?string $reference = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $quantity, $type, $reason, $source, $user, $reference) {
            $product->refresh();

            $before = (int) $product->stock_quantity;
            $after = max(0, $before + $quantity);

            $product->forceFill(['stock_quantity' => $after])->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $after - $before,
                'balance_after' => $after,
                'reason' => $reason,
                'reference' => $reference,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'user_id' => $user?->id,
            ]);
        });
    }

    public function receive(Product $product, int $quantity, ?Model $source = null, ?User $user = null, ?string $reference = null): StockMovement
    {
        return $this->adjust($product, abs($quantity), 'receipt', 'Stock received', $source, $user, $reference);
    }

    public function issue(Product $product, int $quantity, ?Model $source = null, ?User $user = null, ?string $reference = null): StockMovement
    {
        return $this->adjust($product, -abs($quantity), 'issue', 'Stock issued', $source, $user, $reference);
    }

    /** Recompute the cached balance from the ledger and heal the product row. */
    public function rebuildBalance(Product $product): int
    {
        $balance = (int) StockMovement::query()
            ->where('product_id', $product->id)
            ->sum('quantity');

        $balance = max(0, $balance);

        $product->forceFill(['stock_quantity' => $balance])->save();

        return $balance;
    }

    /** @return array<int, array<string, mixed>> */
    public function movementsFor(Product $product, int $limit = 20): array
    {
        return StockMovement::query()
            ->with('user')
            ->where('product_id', $product->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->all();
    }

    public function valuationMinor(): int
    {
        return (int) Product::query()
            ->where('track_stock', true)
            ->selectRaw('coalesce(sum(cost_minor * stock_quantity), 0) as total')
            ->value('total');
    }
}
