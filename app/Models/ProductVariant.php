<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'sku', 'price_minor', 'stock_quantity', 'options', 'is_active'];

    protected function casts(): array
    {
        return ['options' => 'array', 'is_active' => 'boolean', 'price_minor' => 'integer', 'stock_quantity' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
