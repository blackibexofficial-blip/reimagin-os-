<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->published()
            ->with(['featuredImage', 'category'])
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            }))
            ->when($request->integer('category'), fn ($query, $id) => $query->where('category_id', $id))
            ->when($request->string('sort')->toString() === 'price_asc', fn ($query) => $query->orderBy('price_minor'))
            ->when($request->string('sort')->toString() === 'price_desc', fn ($query) => $query->orderByDesc('price_minor'))
            ->when($request->string('sort')->toString() === 'newest', fn ($query) => $query->latest())
            ->when(! $request->filled('sort'), fn ($query) => $query->orderByDesc('is_featured')->orderBy('name'))
            ->paginate(12)
            ->withQueryString();

        return view('site.shop.index', [
            'products' => $products,
            'categories' => Category::query()->where('is_active', true)->orderBy('position')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active && $product->status === 'published', 404);

        return view('site.shop.show', [
            'product' => $product->load(['featuredImage', 'images', 'category', 'variants']),
            'related' => Product::query()->published()
                ->where('id', '!=', $product->id)
                ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
                ->with(['featuredImage', 'category'])
                ->limit(3)
                ->get(),
        ]);
    }

    public function category(Category $category): View
    {
        return view('site.shop.index', [
            'products' => $category->products()->published()->with(['featuredImage', 'category'])->paginate(12),
            'categories' => Category::query()->where('is_active', true)->orderBy('position')->get(),
            'activeCategory' => $category,
        ]);
    }
}
