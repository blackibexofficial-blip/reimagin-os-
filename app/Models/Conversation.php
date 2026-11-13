<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'public_id', 'channel', 'status', 'customer_id', 'lead_id', 'assigned_to',
        'guest_name', 'guest_email', 'guest_phone', 'external_id', 'subject',
        'last_message_at', 'unread_for_staff',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime', 'unread_for_staff' => 'integer'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $conversation) {
            $conversation->public_id ??= (string) Str::uuid();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest('id')->limit(1);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'pending', 'handed_over']);
    }

    public function displayName(): string
    {
        return $this->customer?->name ?? $this->guest_name ?? 'Website visitor';
    }
}
