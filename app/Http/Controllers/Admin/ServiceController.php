<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::query()->orderBy('position')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', ['service' => new Service(['is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('admin.services.index')->with('status', 'Service added.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request, $service));

        return back()->with('status', 'Service saved.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160'],
            'icon' => ['nullable', 'string', 'max:40'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ]);

        $data['slug'] = Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'services', $service?->id);
        $data['is_published'] = $request->boolean('is_published');
        $data['position'] = (int) ($data['position'] ?? 0);

        return $data;
    }
}
