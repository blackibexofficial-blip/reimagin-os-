<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchasingController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(): View
    {
        return view('admin.purchasing.index', [
            'orders' => PurchaseOrder::query()->with(['supplier', 'items.product'])
                ->orderByRaw("case status when 'draft' then 0 when 'ordered' then 1 else 2 end")
                ->latest('id')->paginate(15),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'cost_minor']),
            'totals' => [
                'open' => PurchaseOrder::query()->whereIn('status', ['draft', 'ordered', 'partial'])->sum('total_minor'),
                'suppliers' => Supplier::query()->where('is_active', true)->count(),
                'received' => PurchaseOrder::query()->where('status', 'received')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'expected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:draft,ordered'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $year = now()->year;
            $count = PurchaseOrder::query()->where('reference', 'like', "PO-{$year}-%")->count() + 1;

            $order = PurchaseOrder::create([
                'reference' => sprintf('PO-%d-%04d', $year, $count),
                'supplier_id' => $data['supplier_id'],
                'status' => $data['status'],
                'currency' => 'PKR',
                'expected_at' => $data['expected_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $unitCost = (int) round($item['unit_cost'] * 100);

                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => (int) $item['quantity'],
                    'received_quantity' => 0,
                    'unit_cost_minor' => $unitCost,
                    'line_total_minor' => $unitCost * (int) $item['quantity'],
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        return back()->with('status', 'Purchase order '.$order->reference.' created.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($purchaseOrder, $data, $request) {
            foreach ($purchaseOrder->items as $item) {
                $received = (int) ($data['items'][$item->id] ?? 0);

                if ($received <= 0) {
                    continue;
                }

                $remaining = $item->quantity - $item->received_quantity;
                $received = min($received, $remaining);

                if ($received <= 0) {
                    continue;
                }

                $item->increment('received_quantity', $received);

                /* Only tracked products move through the ledger; services and
                 * made-to-order items have no stock to receive. */
                if ($item->product) {
                    $this->stock->receive($item->product, $received, $purchaseOrder, $request->user(), $purchaseOrder->reference);
                }
            }

            $complete = $purchaseOrder->items()->whereColumn('received_quantity', '<', 'quantity')->doesntExist();

            $purchaseOrder->update([
                'status' => $complete ? 'received' : 'partial',
                'received_at' => $complete ? now() : null,
            ]);
        });

        return back()->with('status', 'Goods received against '.$purchaseOrder->reference.'.');
    }
}
