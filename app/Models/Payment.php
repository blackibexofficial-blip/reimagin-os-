<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'reference', 'method', 'type', 'status', 'amount_minor', 'currency',
        'paid_at', 'transaction_id', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'paid_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
