<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Services\DashboardService;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private DashboardService $dashboard,
        private LeadService $leads,
    ) {}

    public function index(Request $request): View
    {
        $from = $request->date('from') ?? now()->subDays(89)->startOfDay();
        $to = $request->date('to') ?? now()->endOfDay();

        $salesByCategory = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.status', ['cancelled'])
            ->whereBetween('orders.placed_at', [$from, $to])
            ->selectRaw('categories.name as category, sum(order_items.line_total_minor) as total, sum(order_items.quantity) as units')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->dashboard->summary($from, $to),
            'series' => $this->dashboard->revenueSeries($from, $to, 120),
            'pipeline' => $this->leads->pipelineSummary(),
            'topProducts' => $this->dashboard->topProducts(8),
            'salesByCategory' => $salesByCategory,
            'sources' => Lead::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('source, count(*) as total, coalesce(sum(estimated_value_minor), 0) as value')
                ->groupBy('source')->orderByDesc('total')->get(),
            'customers' => Customer::query()->orderByDesc('lifetime_value_minor')->limit(10)->get(['id', 'name', 'lifetime_value_minor', 'last_order_at']),
            'stockValue' => (int) Product::query()->where('track_stock', true)
                ->selectRaw('coalesce(sum(cost_minor * stock_quantity), 0) as total')->value('total'),
            'categories' => Category::query()->withCount('products')->get(),
            'orderCount' => Order::query()->whereBetween('placed_at', [$from, $to])->count(),
        ]);
    }
}
