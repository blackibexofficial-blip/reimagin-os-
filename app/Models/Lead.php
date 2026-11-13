<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUSES = ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];

    public const SOURCES = [
        'website', 'whatsapp', 'phone', 'walk_in', 'referral', 'ai_agent', 'showroom', 'other',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'source', 'status', 'priority', 'score',
        'estimated_value_minor', 'message', 'notes', 'assigned_to', 'customer_id',
        'product_id', 'next_follow_up_at', 'last_contacted_at', 'converted_at', 'lost_reason',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value_minor' => 'integer',
            'score' => 'integer',
            'next_follow_up_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['won', 'lost']);
    }

    public function scopeDueFollowUp(Builder $query): Builder
    {
        return $query->open()->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<=', now());
    }

    /**
     * Deterministic 0–100 score. Weighted on intent signals rather than guesses,
     * so the sales team can see exactly why a lead ranks where it does.
     */
    public function calculateScore(): int
    {
        $score = 0;

        $score += match ($this->status) {
            'qualified' => 30,
            'proposal' => 35,
            'contacted' => 15,
            'won' => 40,
            'lost' => 0,
            default => 8,
        };

        $score += match (true) {
            (int) $this->estimated_value_minor >= 2_000_000 => 25,
            (int) $this->estimated_value_minor >= 500_000 => 18,
            (int) $this->estimated_value_minor >= 100_000 => 10,
            (int) $this->estimated_value_minor > 0 => 5,
            default => 0,
        };

        $score += match ($this->priority) {
            'high' => 15,
            'normal' => 8,
            default => 3,
        };

        if (filled($this->phone)) {
            $score += 10;
        }

        if (filled($this->email)) {
            $score += 5;
        }

        if (filled($this->product_id)) {
            $score += 8;
        }

        if ($this->last_contacted_at && $this->last_contacted_at->gt(now()->subDays(7))) {
            $score += 7;
        }

        return max(0, min(100, $score));
    }
}
