<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'planning', 'design', 'approved', 'production', 'qc', 'delivery', 'installed', 'closed', 'on_hold',
    ];

    protected $fillable = [
        'reference', 'name', 'customer_id', 'order_id', 'manager_id', 'status',
        'start_date', 'due_date', 'budget_minor', 'site_address', 'notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'due_date' => 'date', 'budget_minor' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProjectStage::class)->orderBy('position');
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }

    /** Percentage of stages marked done; 0 when no stages exist. */
    public function progress(): int
    {
        $total = $this->stages->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->stages->where('status', 'done')->count();

        return (int) round($done / $total * 100);
    }
}
