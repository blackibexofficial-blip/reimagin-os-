<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'address_line_1', 'address_line_2', 'city',
        'state', 'postal_code', 'country', 'notes', 'tags', 'owner_id',
        'lifetime_value_minor', 'last_order_at',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array', 'lifetime_value_minor' => 'integer', 'last_order_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Lifetime value is derived from confirmed payments, never typed in by hand,
     * so the CRM figure always matches the sales ledger.
     */
    /** Notes timeline shared with leads, so one profile shows every touch. */
    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest('id');
    }

    public function recalculateLifetimeValue(): void
    {
        $paid = (int) DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.customer_id', $this->id)
            ->whereNull('orders.deleted_at')
            ->where('payments.status', 'confirmed')
            ->where('payments.type', '!=', 'refund')
            ->sum('payments.amount_minor');

        $lastOrder = $this->orders()->max('placed_at');

        $this->forceFill([
            'lifetime_value_minor' => $paid,
            'last_order_at' => $lastOrder,
        ])->save();
    }
}
