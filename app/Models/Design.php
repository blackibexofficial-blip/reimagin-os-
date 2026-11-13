<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Design extends Model
{
    protected $fillable = ['project_id', 'title', 'version', 'status', 'media_id', 'notes', 'approved_at'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
