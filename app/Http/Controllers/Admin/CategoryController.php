<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('position')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true]),
            'parents' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->whereKeyNot($category->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return back()->with('status', 'Category saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Move or delete the products in this category first.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:600'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ]);

        $data['slug'] = Slug::unique(($data['slug'] ?? null) ?: $data['name'], 'categories', $category?->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['position'] = (int) ($data['position'] ?? 0);

        return $data;
    }
}
