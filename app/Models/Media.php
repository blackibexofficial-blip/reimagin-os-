<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'name', 'file_name', 'disk', 'path', 'mime_type', 'size', 'width', 'height',
        'alt_text', 'caption', 'focal_point', 'folder_id', 'uploaded_by', 'usage_count',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer', 'usage_count' => 'integer'];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function url(): string
    {
        return $this->exists && $this->path
            ? Storage::disk($this->disk ?: 'public')->url($this->path)
            : '';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $unit === 'B' ? 0 : 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }

    public function dimensions(): ?string
    {
        return $this->width && $this->height ? $this->width.' × '.$this->height : null;
    }
}
