<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    protected $fillable = [
        'location', 'label', 'url', 'page_id', 'parent_id', 'position', 'is_active', 'open_in_new_tab',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'open_in_new_tab' => 'boolean'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function href(): string
    {
        if ($this->page_id && $this->page) {
            return $this->page->slug === 'home' ? url('/') : url($this->page->slug);
        }

        return $this->url ?: '#';
    }
}
