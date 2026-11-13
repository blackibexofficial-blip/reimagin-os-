<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavigationController extends Controller
{
    public function index(): View
    {
        return view('admin.navigation.index', [
            'locations' => ['header' => 'Main menu', 'footer' => 'Footer'],
            'items' => NavigationItem::query()
                ->with(['page', 'children'])
                ->whereNull('parent_id')
                ->orderBy('location')
                ->orderBy('position')
                ->get()
                ->groupBy('location'),
            'pages' => Page::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location' => ['required', 'in:header,footer'],
            'label' => ['required', 'string', 'max:60'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'url' => ['nullable', 'string', 'max:255', 'required_without:page_id'],
            'parent_id' => ['nullable', 'integer', 'exists:navigation_items,id'],
        ]);

        $position = (int) NavigationItem::query()
            ->where('location', $data['location'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position') + 1;

        NavigationItem::create([
            ...$data,
            'position' => $position,
            'is_active' => true,
        ]);

        return back()->with('status', 'Menu item added.');
    }

    public function update(Request $request, NavigationItem $item): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'url' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'open_in_new_tab' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'label' => $data['label'],
            'url' => $data['url'] ?? null,
            'position' => (int) ($data['position'] ?? $item->position),
            'is_active' => $request->boolean('is_active'),
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
        ]);

        return back()->with('status', 'Menu item updated.');
    }

    public function destroy(NavigationItem $item): RedirectResponse
    {
        $item->children()->delete();
        $item->delete();

        return back()->with('status', 'Menu item removed.');
    }
}
