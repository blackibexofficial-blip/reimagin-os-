<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'status', 'excerpt', 'content', 'published_content', 'meta',
        'template', 'seo_title', 'seo_description', 'og_image_path', 'is_homepage',
        'show_in_navigation', 'navigation_order', 'author_id', 'published_at', 'scheduled_for',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'published_content' => 'array',
            'meta' => 'array',
            'is_homepage' => 'boolean',
            'show_in_navigation' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_for' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('is_homepage', true);
    }

    /** Blocks the public storefront renders: only ever the published snapshot. */
    public function blocksForPublic(): array
    {
        return $this->published_content ?? [];
    }

    /** Draft blocks, used by the builder and the preview endpoint. */
    public function blocks(): array
    {
        return $this->content ?? [];
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function hasUnpublishedChanges(): bool
    {
        return $this->published_content !== $this->content;
    }

    public function publicUrl(): string
    {
        return $this->slug === 'home' ? url('/') : url($this->slug);
    }
}
