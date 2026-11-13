<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentRun extends Model
{
    protected $fillable = [
        'conversation_id', 'message_id', 'model', 'intent', 'prompt_context', 'reply',
        'prompt_tokens', 'completion_tokens', 'latency_ms', 'escalated', 'status',
    ];

    protected function casts(): array
    {
        return [
            'prompt_context' => 'array',
            'escalated' => 'boolean',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'latency_ms' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
