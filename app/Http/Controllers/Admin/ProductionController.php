<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The workshop board.
 *
 * Orders drive the funnel (confirmed → in production → ready → delivered) and
 * projects carry the detailed stage plan. Both are shown together because the
 * floor works from both at once.
 */
class ProductionController extends Controller
{
    /** The forward path an order takes; cancelling is handled on the order page. */
    private const FLOW = ['pending' => 'confirmed', 'confirmed' => 'in_production', 'in_production' => 'ready', 'ready' => 'delivered'];

    public function index(): View
    {
        return view('admin.production.index', [
            'columns' => collect(self::FLOW)
                ->mapWithKeys(fn ($next, $status) => [$status => Order::query()
                    ->with('customer')
                    ->where('status', $status)
                    ->orderBy('placed_at')
                    ->limit(20)
                    ->get()])
                ->all(),
            'delivered' => Order::query()->with('customer')->where('status', 'delivered')->latest('delivered_at')->limit(10)->get(),
            'projects' => Project::query()->with(['customer', 'stages' => fn ($query) => $query->orderBy('position')])
                ->whereNotIn('status', ['closed', 'installed'])
                ->orderBy('due_date')
                ->limit(8)
                ->get(),
        ]);
    }

    public function advance(Request $request, Order $order): RedirectResponse
    {
        $next = self::FLOW[$order->status] ?? null;

        if (! $next) {
            return back()->with('error', 'A '.$order->status.' order cannot be advanced further.');
        }

        $order->update([
            'status' => $next,
            'confirmed_at' => $next === 'confirmed' ? ($order->confirmed_at ?? now()) : $order->confirmed_at,
            'delivered_at' => $next === 'delivered' ? now() : $order->delivered_at,
        ]);

        return back()->with('status', $order->reference.' moved to '.str_replace('_', ' ', $next).'.');
    }

    public function stageStatus(Request $request, ProjectStage $stage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:todo,in_progress,blocked,done'],
        ]);

        $stage->update([
            'status' => $data['status'],
            'started_on' => $data['status'] !== 'todo' ? ($stage->started_on ?? now()->toDateString()) : null,
            'completed_on' => $data['status'] === 'done' ? now()->toDateString() : null,
        ]);

        return back()->with('status', $stage->name.' marked '.str_replace('_', ' ', $data['status']).'.');
    }
}
