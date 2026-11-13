<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Quotation lifecycle and quotation → order conversion.
 *
 * Conversion is guarded twice: orders.quotation_id is unique in the schema, and
 * convert() throws when an order already exists, so a double submit cannot
 * duplicate an order or double-count revenue.
 */
class QuotationService
{
    public static function nextReference(): string
    {
        $prefix = 'QT-'.now()->format('Y').'-';
        $count = Quotation::withTrashed()->where('reference', 'like', $prefix.'%')->count() + 1;

        do {
            $reference = $prefix.str_pad((string) $count++, 4, '0', STR_PAD_LEFT);
        } while (Quotation::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    public function create(array $data, ?User $user = null): Quotation
    {
        return DB::transaction(function () use ($data, $user) {
            $quotation = Quotation::create([
                'reference' => self::nextReference(),
                'customer_id' => $data['customer_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'status' => 'draft',
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_percent' => $data['discount_percent'] ?? null,
                'discount_minor' => $data['discount_minor'] ?? 0,
                'tax_minor' => $data['tax_minor'] ?? 0,
                'delivery_minor' => $data['delivery_minor'] ?? 0,
                'currency' => $data['currency'] ?? \App\Support\Money::DEFAULT_CURRENCY,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'valid_until' => $data['valid_until'] ?? now()->addDays(14),
                'created_by' => $user?->id,
                'customer_snapshot' => $this->snapshotFor($data['customer_id'] ?? null),
            ]);

            foreach ($data['items'] ?? [] as $index => $item) {
                $this->addItem($quotation, $item, $index);
            }

            $quotation->recalculateTotals();
            $this->snapshot($quotation, $user, 'Created');

            return $quotation->fresh();
        });
    }

    public function addItem(Quotation $quotation, array $item, ?int $position = null): QuotationItem
    {
        $quantity = (float) ($item['quantity'] ?? 1);
        $unit = (int) ($item['unit_price_minor'] ?? 0);

        return QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $item['product_id'] ?? null,
            'description' => $item['description'] ?? 'Item',
            'customisation' => $item['customisation'] ?? null,
            'quantity' => $quantity,
            'unit_price_minor' => $unit,
            'line_total_minor' => (int) round($unit * $quantity),
            'position' => $position ?? (int) $quotation->items()->max('position') + 1,
        ]);
    }

    /** Replace every line and write a new revision so history stays intact. */
    public function syncItems(Quotation $quotation, array $items, ?User $user = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $items, $user) {
            $quotation->items()->delete();

            foreach (array_values($items) as $index => $item) {
                $this->addItem($quotation, $item, $index);
            }

            $quotation->recalculateTotals();

            $quotation->forceFill(['revision' => (int) $quotation->revision + 1])->save();
            $this->snapshot($quotation, $user, 'Line items updated');

            return $quotation->fresh();
        });
    }

    public function send(Quotation $quotation, ?User $user = null): Quotation
    {
        $quotation->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
        $this->snapshot($quotation, $user, 'Marked as sent');

        return $quotation;
    }

    public function approve(Quotation $quotation, ?User $user = null): Quotation
    {
        $quotation->forceFill(['status' => 'approved', 'approved_at' => now()])->save();
        $this->snapshot($quotation, $user, 'Approved by customer');

        return $quotation;
    }

    public function reject(Quotation $quotation, ?string $reason = null, ?User $user = null): Quotation
    {
        $quotation->forceFill(['status' => 'rejected', 'rejected_at' => now()])->save();
        $this->snapshot($quotation, $user, 'Rejected'.($reason ? ': '.$reason : ''));

        return $quotation;
    }

    /**
     * Convert an approved quotation into an order.
     *
     * @throws \RuntimeException when the quotation is not convertible
     */
    public function convert(Quotation $quotation, ?User $user = null): Order
    {
        return DB::transaction(function () use ($quotation, $user) {
            $locked = Quotation::query()->whereKey($quotation->id)->lockForUpdate()->firstOrFail();

            if ($locked->order()->exists()) {
                throw new \RuntimeException('This quotation has already been converted to an order.');
            }

            if (in_array($locked->status, ['rejected', 'expired'], true)) {
                throw new \RuntimeException('A rejected or expired quotation cannot be converted.');
            }

            if ($locked->items()->doesntExist()) {
                throw new \RuntimeException('Add at least one line item before converting.');
            }

            $order = Order::create([
                'reference' => $this->nextOrderReference(),
                'customer_id' => $locked->customer_id,
                'quotation_id' => $locked->id,
                'status' => 'confirmed',
                'subtotal_minor' => $locked->subtotal_minor,
                'discount_minor' => $locked->discount_minor,
                'tax_minor' => $locked->tax_minor,
                'delivery_minor' => $locked->delivery_minor,
                'total_minor' => $locked->total_minor,
                'currency' => $locked->currency,
                'notes' => $locked->notes,
                'phone' => $locked->customer?->phone,
                'city' => $locked->customer?->city,
                'placed_at' => now(),
                'confirmed_at' => now(),
                'created_by' => $user?->id,
            ]);

            foreach ($locked->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->description,
                    'sku' => $item->product?->sku,
                    'quantity' => $item->quantity,
                    'unit_price_minor' => $item->unit_price_minor,
                    'line_total_minor' => $item->line_total_minor,
                    'options' => $item->customisation,
                ]);
            }

            $locked->forceFill(['status' => 'converted'])->save();
            $this->snapshot($locked, $user, 'Converted to order '.$order->reference);

            $locked->customer?->recalculateLifetimeValue();

            return $order;
        });
    }

    public function nextOrderReference(): string
    {
        $prefix = 'SO-'.now()->format('Y').'-';
        $count = Order::withTrashed()->where('reference', 'like', $prefix.'%')->count() + 1;

        do {
            $reference = $prefix.str_pad((string) $count++, 4, '0', STR_PAD_LEFT);
        } while (Order::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    /** Immutable store of the quotation as it stood at the time of the action. */
    public function snapshot(Quotation $quotation, ?User $user, string $note): QuotationRevision
    {
        $quotation->refresh();

        $next = (int) $quotation->revisions()->max('revision') + 1;

        return QuotationRevision::create([
            'quotation_id' => $quotation->id,
            'revision' => $next,
            'user_id' => $user?->id,
            'note' => $note,
            'snapshot' => [
                'status' => $quotation->status,
                'subtotal_minor' => $quotation->subtotal_minor,
                'discount_minor' => $quotation->discount_minor,
                'tax_minor' => $quotation->tax_minor,
                'delivery_minor' => $quotation->delivery_minor,
                'total_minor' => $quotation->total_minor,
                'items' => $quotation->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price_minor' => $item->unit_price_minor,
                    'line_total_minor' => $item->line_total_minor,
                ])->all(),
            ],
        ]);
    }

    private function snapshotFor(?int $customerId): ?array
    {
        if (! $customerId) {
            return null;
        }

        $customer = Customer::query()->find($customerId);

        return $customer ? [
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'company' => $customer->company,
            'address' => collect([$customer->address_line_1, $customer->city])->filter()->implode(', '),
        ] : null;
    }
}
