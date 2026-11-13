<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();

        $collected = (int) Payment::query()
            ->whereIn('status', ['confirmed'])
            ->whereBetween('paid_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw("coalesce(sum(case when type = 'refund' then -amount_minor else amount_minor end), 0) as net")
            ->value('net');

        $invoiced = (int) Order::query()->revenue()
            ->whereBetween('placed_at', [$from, $to->copy()->endOfDay()])->sum('total_minor');

        $outstanding = (int) Order::query()->revenue()->whereNotIn('status', ['cancelled'])
            ->selectRaw('coalesce(sum(total_minor - paid_minor), 0) as balance')->value('balance');

        $payables = (int) PurchaseOrder::query()->whereIn('status', ['draft', 'ordered', 'partial'])->sum('total_minor');

        $byMethod = Payment::query()
            ->where('status', 'confirmed')
            ->whereBetween('paid_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('method, sum(amount_minor) as total, count(*) as payments')
            ->groupBy('method')->orderByDesc('total')->get();

        /* Grouped in PHP rather than SQL so the query stays portable between
         * the SQLite development database and MySQL in production. */
        $monthly = Order::query()->revenue()
            ->where('placed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['placed_at', 'total_minor'])
            ->groupBy(fn (Order $order) => $order->placed_at?->format('Y-m'))
            ->map(fn ($orders) => (int) $orders->sum('total_minor'))
            ->sortKeys();

        return view('admin.finance.index', [
            'from' => $from,
            'to' => $to,
            'summary' => [
                'collected' => max(0, $collected),
                'invoiced' => $invoiced,
                'outstanding' => max(0, $outstanding),
                'payables' => $payables,
            ],
            'byMethod' => $byMethod,
            'monthly' => $monthly,
            'recent' => Payment::query()->with(['order.customer', 'recorder'])->latest('paid_at')->limit(15)->get(),
            'debtors' => Order::query()->with('customer')->revenue()
                ->whereNotIn('status', ['cancelled'])
                ->whereColumn('paid_minor', '<', 'total_minor')
                ->orderByRaw('total_minor - paid_minor desc')
                ->limit(10)->get(),
        ]);
    }
}
