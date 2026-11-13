<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'title', 'slug', 'icon', 'excerpt', 'body', 'featured_image_id',
        'is_published', 'position', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
