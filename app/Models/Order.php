<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /** Fulfilment states, in the order the workshop moves through them. */
    public const STATUSES = [
        'pending', 'confirmed', 'in_production', 'ready', 'delivered', 'cancelled',
    ];

    public const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid', 'refunded'];

    protected $fillable = [
        'reference', 'customer_id', 'quotation_id', 'status', 'payment_status',
        'subtotal_minor', 'discount_minor', 'tax_minor', 'delivery_minor', 'total_minor',
        'paid_minor', 'currency', 'notes', 'shipping_address', 'city', 'phone',
        'placed_at', 'confirmed_at', 'delivered_at', 'cancellation_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'discount_minor' => 'integer',
            'tax_minor' => 'integer',
            'delivery_minor' => 'integer',
            'total_minor' => 'integer',
            'paid_minor' => 'integer',
            'placed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    /** Revenue counts everything except cancelled orders. */
    public function scopeRevenue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function balanceMinor(): int
    {
        return max(0, (int) $this->total_minor - (int) $this->paid_minor);
    }

    /**
     * Recompute the cached paid amount and payment status from the payments
     * ledger. Refunds are subtracted, so the figure is always auditable.
     */
    public function recalculatePaymentState(): self
    {
        $net = (int) $this->payments()
            ->where('status', 'confirmed')
            ->selectRaw("coalesce(sum(case when type = 'refund' then -amount_minor else amount_minor end), 0) as net")
            ->value('net');

        $paid = max(0, $net);
        $total = (int) $this->total_minor;

        $status = match (true) {
            $paid <= 0 && $net < 0 => 'refunded',
            $paid <= 0 => 'unpaid',
            $paid >= $total => 'paid',
            default => 'partial',
        };

        $this->forceFill(['paid_minor' => $paid, 'payment_status' => $status])->save();

        return $this;
    }
}
