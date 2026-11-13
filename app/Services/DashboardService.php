<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Read model for the admin dashboard.
 *
 * Every figure is derived from the same tables the rest of the admin writes to,
 * so the dashboard cannot drift from the modules below it.
 */
class DashboardService
{
    public function __construct(private LeadService $leads = new LeadService) {}

    /** @return array<string, mixed> */
    public function summary(?Carbon $from = null, ?Carbon $to = null): array
    {
        [$from, $to] = $this->range($from, $to);
        $days = max(1, $from->diffInDays($to) + 1);
        $previousFrom = $from->copy()->subDays($days);
        $previousTo = $from->copy()->subSecond();

        $revenue = $this->revenueBetween($from, $to);
        $previousRevenue = $this->revenueBetween($previousFrom, $previousTo);

        $orders = Order::query()->whereBetween('placed_at', [$from, $to])->count();
        $previousOrders = Order::query()->whereBetween('placed_at', [$previousFrom, $previousTo])->count();

        $collected = (int) DB::table('payments')
            ->where('status', 'confirmed')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("coalesce(sum(case when type = 'refund' then -amount_minor else amount_minor end), 0) as net")
            ->value('net');

        $outstanding = (int) Order::query()
            ->revenue()
            ->selectRaw('coalesce(sum(total_minor - paid_minor), 0) as balance')
            ->value('balance');

        return [
            'from' => $from,
            'to' => $to,
            'revenue_minor' => $revenue,
            'revenue_change' => $this->change($revenue, $previousRevenue),
            'orders' => $orders,
            'orders_change' => $this->change($orders, $previousOrders),
            'average_order_minor' => $orders > 0 ? (int) round($revenue / $orders) : 0,
            'collected_minor' => max(0, $collected),
            'outstanding_minor' => max(0, $outstanding),
            'open_leads' => Lead::query()->open()->count(),
            'conversion_rate' => $this->leads->conversionRate(),
            'open_quotations' => Quotation::query()->open()->count(),
            'quotation_value_minor' => (int) Quotation::query()->open()->sum('total_minor'),
            'low_stock' => Product::query()->lowStock()->count(),
            'appointments_today' => Appointment::query()
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->whereDate('starts_at', today())
                ->count(),
        ];
    }

    /** @return array<int, array{date:string, label:string, total_minor:int}> */
    public function revenueSeries(?Carbon $from = null, ?Carbon $to = null, int $days = 30): array
    {
        [$from, $to] = $this->range($from, $to);

        if ($from->diffInDays($to) > 120) {
            $from = $to->copy()->subDays(120);
        }

        $rows = Order::query()
            ->revenue()
            ->whereBetween('placed_at', [$from, $to])
            ->selectRaw('date(placed_at) as day, sum(total_minor) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $series[] = [
                'date' => $key,
                'label' => $cursor->format('d M'),
                'total_minor' => (int) ($rows[$key] ?? 0),
            ];
        }

        return array_slice($series, -$days);
    }

    /** @return array<string, array{count:int, value_minor:int}> */
    public function pipeline(): array
    {
        return $this->leads->pipelineSummary();
    }

    public function recentOrders(int $limit = 6)
    {
        return Order::query()->with('customer')->latest('placed_at')->limit($limit)->get();
    }

    public function lowStockProducts(int $limit = 6)
    {
        return Product::query()->lowStock()->orderBy('stock_quantity')->limit($limit)->get();
    }

    /** @return array<int, array{name:string, quantity:float, revenue_minor:int}> */
    public function topProducts(int $limit = 5): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.name as name, sum(order_items.quantity) as quantity, sum(order_items.line_total_minor) as revenue_minor')
            ->groupBy('order_items.name')
            ->orderByDesc('revenue_minor')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->name,
                'quantity' => (float) $row->quantity,
                'revenue_minor' => (int) $row->revenue_minor,
            ])
            ->all();
    }

    private function revenueBetween(Carbon $from, Carbon $to): int
    {
        return (int) Order::query()
            ->revenue()
            ->whereBetween('placed_at', [$from, $to])
            ->sum('total_minor');
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function range(?Carbon $from, ?Carbon $to): array
    {
        return [$from ?? now()->subDays(29)->startOfDay(), $to ?? now()->endOfDay()];
    }

    private function change(int|float $current, int|float $previous): ?int
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }
}
