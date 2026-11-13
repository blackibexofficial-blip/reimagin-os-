<?php

namespace App\Services;

use App\Models\AiAgentRun;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * The AI sales agent.
 *
 * Runs against a deterministic, rule-based reply engine by default so the chat
 * is fully functional with no external service, no API key and no data leaving
 * the server. When an API key is configured in Settings the same prompt is sent
 * to the model and the reply is used instead; either path records an AiAgentRun
 * for auditing and cost tracking.
 */
class AiAgentService
{
    public function __construct(private LeadService $leads) {}

    public function handle(Conversation $conversation, string $incoming): AiAgentRun
    {
        $started = microtime(true);

        $context = $this->buildContext($conversation);
        $reply = $this->reply($conversation, $incoming, $context);

        $latency = (int) round((microtime(true) - $started) * 1000);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'author_type' => 'agent',
            'body' => $reply['body'],
            'meta' => ['intent' => $reply['intent']],
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
            'unread_for_staff' => (int) $conversation->unread_for_staff + ($reply['escalate'] ? 1 : 0),
            'status' => $reply['escalate'] ? 'pending' : $conversation->status,
        ])->save();

        $run = AiAgentRun::create([
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'model' => $reply['model'],
            'intent' => $reply['intent'],
            'prompt_context' => $context,
            'reply' => $reply['body'],
            'prompt_tokens' => str_word_count($incoming),
            'completion_tokens' => str_word_count($reply['body']),
            'latency_ms' => $latency,
            'escalated' => $reply['escalate'],
            'status' => 'completed',
        ]);

        $this->captureLead($conversation, $incoming, $reply['intent']);

        return $run;
    }

    /**
     * Classify the message and produce a reply.
     *
     * @return array{body:string, intent:string, escalate:bool, model:string}
     */
    public function reply(Conversation $conversation, string $incoming, array $context = []): array
    {
        $intent = $this->classify($incoming);

        $apiKey = Setting::get('ai_api_key');

        if (filled($apiKey)) {
            $remote = $this->askModel($incoming, $intent, $context, (string) $apiKey);

            if ($remote !== null) {
                return [
                    'body' => $remote,
                    'intent' => $intent,
                    'escalate' => in_array($intent, ['handover', 'complaint'], true),
                    'model' => (string) (Setting::get('ai_model') ?: 'gpt-4o-mini'),
                ];
            }
        }

        return [
            'body' => $this->canned($intent, $conversation, $incoming),
            'intent' => $intent,
            'escalate' => in_array($intent, ['handover', 'complaint'], true),
            'model' => 'rules-v1',
        ];
    }

    /** Cheap, deterministic intent classifier — no tokens, no latency. */
    public function classify(string $message): string
    {
        $text = Str::lower($message);

        $rules = [
            'handover' => ['human', 'agent', 'call me', 'speak to someone', 'manager', 'complain'],
            'complaint' => ['damaged', 'broken', 'late', 'delay', 'wrong', 'refund', 'poor'],
            'pricing' => ['price', 'cost', 'rate', 'quote', 'how much', 'budget', 'rs', 'pkr'],
            'appointment' => ['visit', 'showroom', 'appointment', 'come over', 'meeting', 'survey'],
            'timeline' => ['how long', 'lead time', 'delivery', 'when can', 'timeline', 'weeks'],
            'materials' => ['wood', 'material', 'mdf', 'ply', 'veneer', 'walnut', 'oak', 'finish'],
            'catalogue' => ['catalogue', 'catalog', 'product', 'wardrobe', 'kitchen', 'table', 'bed'],
        ];

        foreach ($rules as $intent => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    private function canned(string $intent, Conversation $conversation, string $incoming): string
    {
        $company = Setting::get('company_name', config('app.name'));
        $phone = Setting::get('phone', 'our showroom line');
        $whatsapp = Setting::get('whatsapp');

        $reply = match ($intent) {
            'pricing' => "Most fitted wardrobes land between Rs 250,000 and Rs 650,000 depending on span, internals and finish. Send me the room dimensions and I will get an itemised quotation from the studio — usually within one working day.",
            'timeline' => "Typical lead time is four to six weeks from signed drawings: one week for shop drawings, three to four weeks in production, then installation. Rush jobs are possible when the material is in stock.",
            'materials' => "We work mainly in marine ply carcasses with walnut, oak or ash veneers, and solid hardwood for tables. Hardware is Hettich or Blum. You are welcome to see and feel the samples at the showroom.",
            'appointment' => "I can book you a showroom visit — we are open Monday to Saturday, 11am to 8pm in Gulberg III. Tell me a day that suits you and I will confirm the slot.",
            'catalogue' => "Our range covers fitted wardrobes, modular kitchens, dining and workspace pieces. Everything is made to your dimensions rather than sold off the shelf, so sizes are flexible.",
            'complaint' => "I am sorry to hear that. I am flagging this to the studio manager right now so a person picks it up immediately — please add your order number if you have it.",
            'handover' => "Of course — let me hand you to a member of the studio team. Someone will reply here shortly. If it is urgent you can call {$phone}.",
            default => "Happy to help. {$company} designs and builds furniture to your dimensions — wardrobes, kitchens, tables and joinery. What space are you working on?",
        };

        if ($intent !== 'handover' && $whatsapp && Str::contains(Str::lower($incoming), 'whatsapp')) {
            $reply .= " You can also reach us on WhatsApp at {$whatsapp}.";
        }

        return $reply;
    }

    private function askModel(string $incoming, string $intent, array $context, string $apiKey): ?string
    {
        $model = (string) (Setting::get('ai_model') ?: 'gpt-4o-mini');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.4,
                    'max_tokens' => 400,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt($context)],
                        ['role' => 'user', 'content' => $incoming],
                    ],
                ]);

            if (! $response->successful()) {
                return null;
            }

            return trim((string) $response->json('choices.0.message.content')) ?: null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function systemPrompt(array $context): string
    {
        $company = Setting::get('company_name', config('app.name'));

        return "You are the sales assistant for {$company}, a bespoke furniture studio in Lahore. "
            .'Answer in at most four sentences, in British English, with no emoji. '
            .'Quote prices in PKR and never invent a final price — say a quotation follows. '
            .'If the visitor asks for a human or reports a problem, say you are passing them to the studio team. '
            .'Context: '.json_encode($context);
    }

    /** Turn a qualified conversation into a lead exactly once. */
    private function captureLead(Conversation $conversation, string $incoming, string $intent): void
    {
        if ($conversation->lead_id || in_array($intent, ['handover', 'general'], true)) {
            return;
        }

        $lead = $this->leads->create([
            'name' => $conversation->guest_name ?? 'Chat visitor',
            'email' => $conversation->guest_email,
            'phone' => $conversation->guest_phone,
            'source' => $conversation->channel === 'whatsapp' ? 'whatsapp' : 'ai_agent',
            'status' => 'contacted',
            'priority' => $intent === 'pricing' ? 'high' : 'normal',
            'message' => $incoming,
            'customer_id' => $conversation->customer_id,
        ]);

        $conversation->forceFill(['lead_id' => $lead->id])->save();
    }

    private function buildContext(Conversation $conversation): array
    {
        return [
            'company' => Setting::get('company_name', config('app.name')),
            'phone' => Setting::get('phone'),
            'channel' => $conversation->channel,
            'visitor' => $conversation->displayName(),
            'customer' => $conversation->customer?->name,
            'time' => Carbon::now()->format('l H:i'),
            'recent' => $conversation->messages()->latest('id')->limit(6)->get()
                ->reverse()
                ->map(fn (Message $m) => ['from' => $m->author_type, 'body' => Str::limit($m->body, 240)])
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, int> */
    public function stats(): array
    {
        return [
            'runs' => AiAgentRun::query()->count(),
            'escalated' => AiAgentRun::query()->where('escalated', true)->count(),
            'avg_latency' => (int) AiAgentRun::query()->avg('latency_ms'),
        ];
    }
}
