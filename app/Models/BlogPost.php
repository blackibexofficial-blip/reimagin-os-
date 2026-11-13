<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'category_id', 'author_id', 'featured_image_id', 'excerpt',
        'body', 'status', 'tags', 'seo_title', 'seo_description', 'published_at', 'views',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array', 'published_at' => 'datetime', 'views' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
