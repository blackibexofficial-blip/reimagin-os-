<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const TYPES = ['showroom_visit', 'site_survey', 'consultation', 'installation'];

    public const STATUSES = ['requested', 'confirmed', 'completed', 'cancelled', 'no_show'];

    protected $fillable = [
        'reference', 'name', 'email', 'phone', 'type', 'starts_at', 'ends_at',
        'staff_id', 'status', 'location', 'notes',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
