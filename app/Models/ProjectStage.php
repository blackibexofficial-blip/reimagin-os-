<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStage extends Model
{
    protected $fillable = [
        'project_id', 'name', 'status', 'position', 'started_on', 'completed_on', 'assigned_to', 'notes',
    ];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'completed_on' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
