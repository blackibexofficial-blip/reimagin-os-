<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiAgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(private AiAgentService $agent) {}

    public function index(Request $request): View
    {
        return view('admin.conversations.index', [
            'conversations' => Conversation::query()
                ->with(['customer', 'lead'])
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->string('channel')->toString(), fn ($query, $channel) => $query->where('channel', $channel))
                ->orderByDesc('last_message_at')
                ->paginate(25)
                ->withQueryString(),
            'stats' => [
                'open' => Conversation::query()->where('status', 'open')->count(),
                'unread' => Conversation::query()->where('unread_for_staff', '>', 0)->count(),
                'handover' => Conversation::query()->where('status', 'handed_over')->count(),
            ],
        ]);
    }

    public function show(Conversation $conversation): View
    {
        /* Opening a thread clears its unread badge for the staff side only. */
        if ($conversation->unread_for_staff > 0) {
            $conversation->forceFill(['unread_for_staff' => 0])->save();
        }

        return view('admin.conversations.show', [
            'conversation' => $conversation->load(['customer', 'lead', 'messages.user', 'runs']),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
            'use_ai' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('use_ai')) {
            /* Ask the agent for a suggestion first — it does not post. The draft
             * is shown in the thread tagged as AI so a person can rewrite it
             * before anything is actually sent to the customer. */
            $suggestion = $this->agent->reply($conversation, $data['body']);

            Message::create([
                'conversation_id' => $conversation->id,
                'author_type' => 'agent',
                'user_id' => $request->user()->id,
                'body' => $suggestion['body'],
                'meta' => ['drafted_by' => 'ai_agent', 'intent' => $suggestion['intent'], 'model' => $suggestion['model']],
            ]);
        } else {
            Message::create([
                'conversation_id' => $conversation->id,
                'author_type' => 'staff',
                'user_id' => $request->user()->id,
                'body' => $data['body'],
                'meta' => null,
            ]);
        }

        $conversation->forceFill([
            'last_message_at' => now(),
            'status' => $conversation->status === 'open' ? 'open' : 'handed_over',
        ])->save();

        return back()->with('status', 'Reply added to the thread.');
    }

    public function status(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,handed_over,resolved'],
        ]);

        $conversation->forceFill([
            'status' => $data['status'],
            'assigned_to' => $data['status'] === 'handed_over' ? $request->user()->id : $conversation->assigned_to,
        ])->save();

        return back()->with('status', 'Conversation marked '.str_replace('_', ' ', $data['status']).'.');
    }
}
