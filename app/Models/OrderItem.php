<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'name', 'sku', 'quantity', 'unit_price_minor',
        'line_total_minor', 'options',
    ];

    protected function casts(): array
    {
        return ['options' => 'array', 'quantity' => 'float', 'unit_price_minor' => 'integer', 'line_total_minor' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
