<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id', 'description', 'customisation', 'quantity',
        'unit_price_minor', 'line_total_minor', 'position',
    ];

    protected function casts(): array
    {
        return [
            'customisation' => 'array',
            'quantity' => 'float',
            'unit_price_minor' => 'integer',
            'line_total_minor' => 'integer',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
