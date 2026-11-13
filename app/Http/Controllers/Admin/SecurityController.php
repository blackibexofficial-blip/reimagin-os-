<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(): View
    {
        return view('admin.security.index', [
            'sessions' => DB::table('sessions')
                ->where('last_activity', '>=', now()->subDays(7)->getTimestamp())
                ->orderByDesc('last_activity')
                ->limit(25)
                ->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'user' => filled($row->user_id) ? User::query()->find($row->user_id)?->name : 'Guest',
                    'ip' => $row->ip_address,
                    'agent' => \Illuminate\Support\Str::limit((string) $row->user_agent, 60),
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($row->last_activity),
                ]),
            'recent' => Activity::query()->with('user')->latest('id')->limit(20)->get(),
            'checks' => [
                ['label' => 'Production debug mode', 'ok' => ! (config('app.debug') && app()->environment('production')), 'detail' => config('app.debug') ? 'Debug is on' : 'Debug is off'],
                ['label' => 'App key set', 'ok' => filled(config('app.key')), 'detail' => filled(config('app.key')) ? 'Encrypted sessions enabled' : 'No key'],
                ['label' => 'HTTPS enforced in production', 'ok' => app()->environment('production') ? (bool) config('session.secure') : true, 'detail' => config('session.secure') ? 'Secure cookies' : 'Session cookies are not marked secure'],
                ['label' => 'AI provider connected', 'ok' => filled(\App\Models\Setting::get('ai_api_key')), 'detail' => filled(\App\Models\Setting::get('ai_api_key')) ? 'OpenAI key stored' : 'Falling back to the rules engine'],
                ['label' => 'Unread escalated chats', 'ok' => Conversation::query()->where('status', 'pending')->count() === 0, 'detail' => Conversation::query()->where('status', 'pending')->count().' awaiting a human'],
            ],
            'users' => User::query()->orderByDesc('last_login_at')->limit(10)->get(),
        ]);
    }

    /** Force a sign-out of one browser session. */
    public function destroySession(Request $request, string $session): RedirectResponse
    {
        if ($session === $request->session()->getId()) {
            return back()->with('error', 'That is your current session. Use sign out instead.');
        }

        DB::table('sessions')->where('id', $session)->delete();

        return back()->with('status', 'Session revoked.');
    }
}
