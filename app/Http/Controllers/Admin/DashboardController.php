<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Task;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function __invoke(Request $request): View
    {
        $range = $request->string('range', '30d')->toString();

        [$from, $to] = match ($range) {
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '90d' => [now()->subDays(89)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
        };

        return view('admin.dashboard.index', [
            'summary' => $this->dashboard->summary($from, $to),
            'series' => $this->dashboard->revenueSeries($from, $to),
            'pipeline' => $this->dashboard->pipeline(),
            'topProducts' => $this->dashboard->topProducts(5),
            'recentOrders' => $this->dashboard->recentOrders(6),
            'lowStock' => $this->dashboard->lowStockProducts(5),
            'range' => $range,
            'today' => $this->dashboard->summary(Carbon::today(), Carbon::today()->endOfDay()),
            'followUps' => Lead::query()
                ->open()
                ->whereNotNull('next_follow_up_at')
                ->where('next_follow_up_at', '<=', now()->addDays(2)->endOfDay())
                ->with('assignee')
                ->orderBy('next_follow_up_at')
                ->limit(5)
                ->get(),
            'appointments' => Appointment::query()
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->whereDate('starts_at', today())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'openQuotations' => Quotation::query()
                ->open()
                ->with('customer')
                ->orderByDesc('total_minor')
                ->limit(5)
                ->get(),
            'myTasks' => Task::query()
                ->whereIn('status', ['open', 'in_progress'])
                ->when($request->user()->role->value === 'staff', fn ($q) => $q->where('assigned_to', $request->user()->id))
                ->orderByRaw('due_at is null')
                ->orderBy('due_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
