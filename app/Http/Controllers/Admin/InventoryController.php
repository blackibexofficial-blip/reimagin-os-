<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request): View
    {
        $product = $request->integer('product')
            ? Product::query()->find($request->integer('product'))
            : null;

        return view('admin.inventory.index', [
            'products' => Product::query()
                ->with('category')
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
                }))
                ->when($request->boolean('low'), fn ($query) => $query->lowStock())
                ->orderBy('stock_quantity')
                ->paginate(20)
                ->withQueryString(),
            'selected' => $product,
            'movements' => $product ? collect($this->stock->movementsFor($product, 25))->map(fn ($movement) => $movement) : [],
            'valuation' => $this->stock->valuationMinor(),
            'ledger' => StockMovement::query()->with(['product', 'user'])
                ->when($product, fn ($query) => $query->where('product_id', $product->id))
                ->latest('id')->limit(25)->get(),
            'totals' => [
                'skus' => Product::query()->count(),
                'low' => Product::query()->lowStock()->count(),
                'units' => (int) Product::query()->sum('stock_quantity'),
            ],
        ]);
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'in:adjustment,receipt,issue,damage,return'],
            'reason' => ['required', 'string', 'max:200'],
        ]);

        $this->stock->adjust(
            $product,
            (int) $data['quantity'],
            $data['type'],
            $data['reason'],
            null,
            $request->user(),
        );

        return back()->with('status', 'Stock adjusted for '.$product->name.'.');
    }
}
