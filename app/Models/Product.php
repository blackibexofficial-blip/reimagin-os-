<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'sku', 'category_id', 'status', 'short_description', 'description',
        'price_minor', 'compare_at_price_minor', 'cost_minor', 'currency', 'is_quote_only',
        'is_featured', 'is_active', 'track_stock', 'stock_quantity', 'low_stock_threshold',
        'material', 'finish', 'color', 'dimensions', 'width_cm', 'depth_cm', 'height_cm',
        'weight_kg', 'lead_time_days', 'featured_image_id', 'seo_title', 'seo_description',
        'specifications',
    ];

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'compare_at_price_minor' => 'integer',
            'cost_minor' => 'integer',
            'is_quote_only' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'specifications' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_images')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_relations',
            'product_id',
            'related_product_id',
        )->withPivot('position')->orderByPivot('position');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('track_stock', false)->orWhere('stock_quantity', '>', 0));
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('track_stock', true)->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function primaryImageUrl(): ?string
    {
        if ($this->featuredImage) {
            return $this->featuredImage->url();
        }

        $first = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $first?->url();
    }

    public function displayPrice(): string
    {
        return $this->is_quote_only
            ? 'Price on quotation'
            : \App\Support\Money::format((int) $this->price_minor, $this->currency);
    }
}
