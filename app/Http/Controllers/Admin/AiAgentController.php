<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAgentRun;
use App\Models\Setting;
use App\Services\AiAgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAgentController extends Controller
{
    /** Settings the agent reads, with their defaults and labels. */
    private const KEYS = [
        'ai_enabled' => ['label' => 'Auto-reply on new messages', 'type' => 'boolean', 'default' => '1'],
        'ai_tone' => ['label' => 'Reply tone', 'type' => 'text', 'default' => 'warm and concise'],
        'ai_greeting' => ['label' => 'Greeting', 'type' => 'text', 'default' => 'Salam! Thanks for messaging Woodex — how can we help with your project?'],
        'ai_escalate_keywords' => ['label' => 'Hand over to a human when the customer mentions', 'type' => 'text', 'default' => 'complaint,refund,damaged,lawyer'],
        'ai_api_key' => ['label' => 'OpenAI API key', 'type' => 'password', 'default' => ''],
        'ai_model' => ['label' => 'Model', 'type' => 'text', 'default' => 'gpt-4o-mini'],
    ];

    public function __construct(private AiAgentService $agent) {}

    public function index(): View
    {
        return view('admin.ai.index', [
            'settings' => collect(self::KEYS)->map(fn (array $meta, string $key) => [
                'key' => $key,
                'value' => Setting::get($key, $meta['default']),
                'label' => $meta['label'],
                'type' => $meta['type'],
            ]),
            'stats' => $this->agent->stats(),
            'runs' => AiAgentRun::query()->with('conversation')->latest('id')->limit(20)->get(),
            'intents' => AiAgentRun::query()
                ->selectRaw('intent, count(*) as total')
                ->groupBy('intent')->orderByDesc('total')->pluck('total', 'intent'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'ai_tone' => ['nullable', 'string', 'max:120'],
            'ai_greeting' => ['nullable', 'string', 'max:400'],
            'ai_escalate_keywords' => ['nullable', 'string', 'max:300'],
            'ai_model' => ['nullable', 'string', 'max:60'],
            'ai_api_key' => ['nullable', 'string', 'max:200'],
        ]);

        foreach (self::KEYS as $key => $meta) {
            if ($meta['type'] === 'boolean') {
                Setting::set($key, $request->boolean($key) ? '1' : '0', 'ai', 'boolean');
                continue;
            }

            /* An empty key field means "leave the stored secret alone" rather
             * than "delete it" — the form never prints the current secret. */
            if ($key === 'ai_api_key' && blank($request->input($key))) {
                continue;
            }

            if ($request->has($key)) {
                Setting::set($key, (string) $request->input($key), 'ai', $meta['type']);
            }
        }

        return back()->with('status', 'Agent settings saved.');
    }
}
