<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.products.index', [
            'products' => Product::query()
                ->with(['category', 'featuredImage'])
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
                }))
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->string('stock')->toString() === 'low', fn ($query) => $query->lowStock())
                ->orderByDesc('is_featured')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'counts' => [
                'all' => Product::query()->count(),
                'published' => Product::query()->where('status', 'published')->count(),
                'draft' => Product::query()->where('status', 'draft')->count(),
                'low' => Product::query()->lowStock()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product([
                'status' => 'draft', 'currency' => 'PKR', 'is_active' => true,
                'track_stock' => true, 'low_stock_threshold' => 2, 'lead_time_days' => 21,
            ]),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::create($this->validated($request));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('images'),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return back()->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'This piece appears on existing orders, so it cannot be deleted. Set it to draft instead.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:40'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string'],
            'price' => ['required_if:is_quote_only,0', 'nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'material' => ['nullable', 'string', 'max:120'],
            'finish' => ['nullable', 'string', 'max:120'],
            'dimensions' => ['nullable', 'string', 'max:120'],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ]);

        $data['slug'] = Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'products', $product?->id);
        $data['price_minor'] = (int) round(((float) ($data['price'] ?? 0)) * 100);
        $data['compare_at_price_minor'] = isset($data['compare_at_price']) ? (int) round($data['compare_at_price'] * 100) : null;
        $data['cost_minor'] = isset($data['cost']) ? (int) round($data['cost'] * 100) : null;

        unset($data['price'], $data['compare_at_price'], $data['cost']);

        $data['currency'] = $request->string('currency', 'PKR')->toString();
        $data['is_quote_only'] = $request->boolean('is_quote_only');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['track_stock'] = $request->boolean('track_stock');

        return $data;
    }
}
