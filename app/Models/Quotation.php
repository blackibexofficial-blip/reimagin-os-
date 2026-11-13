<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'sent', 'approved', 'rejected', 'converted', 'expired'];

    protected $fillable = [
        'reference', 'customer_id', 'lead_id', 'status', 'revision', 'customer_snapshot',
        'subtotal_minor', 'discount_minor', 'discount_type', 'discount_percent', 'tax_minor',
        'delivery_minor', 'total_minor', 'currency', 'notes', 'terms', 'valid_until',
        'sent_at', 'approved_at', 'rejected_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'customer_snapshot' => 'array',
            'subtotal_minor' => 'integer',
            'discount_minor' => 'integer',
            'tax_minor' => 'integer',
            'delivery_minor' => 'integer',
            'total_minor' => 'integer',
            'revision' => 'integer',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('position');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class)->latest('id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'sent', 'approved']);
    }

    /**
     * Totals are always derived from the line items plus the stored adjustments,
     * so a stale total can never be sent to a customer.
     */
    public function recalculateTotals(): self
    {
        $subtotal = (int) $this->items()->sum('line_total_minor');

        $discount = $this->discount_type === 'percent'
            ? \App\Support\Money::percentOf($subtotal, (float) $this->discount_percent)
            : (int) $this->discount_minor;

        $discount = min($discount, $subtotal);

        $this->forceFill([
            'subtotal_minor' => $subtotal,
            'discount_minor' => $discount,
            'total_minor' => max(0, $subtotal - $discount + (int) $this->tax_minor + (int) $this->delivery_minor),
        ])->save();

        return $this;
    }
}
