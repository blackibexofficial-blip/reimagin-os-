<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ShowcaseProject extends Model
{
    protected $fillable = [
        'title', 'slug', 'location', 'client', 'year', 'summary', 'description',
        'category', 'is_published', 'is_featured', 'position', 'gallery',
    ];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'is_published' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Gallery media resolved in stored order. */
    public function galleryItems(): array
    {
        $ids = array_filter((array) ($this->gallery ?? []));

        if ($ids === []) {
            return [];
        }

        return Media::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($media) => array_search($media->id, $ids, true))
            ->values()
            ->all();
    }
}
