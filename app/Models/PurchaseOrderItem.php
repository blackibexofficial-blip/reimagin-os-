<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'description', 'quantity',
        'received_quantity', 'unit_cost_minor', 'line_total_minor',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'received_quantity' => 'integer', 'unit_cost_minor' => 'integer', 'line_total_minor' => 'integer'];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
