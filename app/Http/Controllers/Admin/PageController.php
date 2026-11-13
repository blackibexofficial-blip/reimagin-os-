<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Services\PageService;
use App\Support\Blocks\BlockRegistry;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private PageService $pages) {}

    public function index(Request $request): View
    {
        $pages = Page::query()
            ->with('author')
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where('title', 'like', "%{$term}%"))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('is_homepage')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pages.index', [
            'pages' => $pages,
            'counts' => [
                'all' => Page::query()->count(),
                'published' => Page::query()->where('status', 'published')->count(),
                'draft' => Page::query()->where('status', 'draft')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(['status' => 'draft', 'template' => 'default'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $page = $this->pages->create($data, $request->user());

        return redirect()
            ->route('admin.pages.builder', $page)
            ->with('status', 'Page created. Add blocks to build the layout.');
    }

    /**
     * A page's canonical admin view is the builder, so /admin/pages/{page}
     * forwards there rather than duplicating the editor. The URL is kept so
     * existing links and bookmarks still resolve.
     */
    public function show(Page $page): RedirectResponse
    {
        return redirect()->route('admin.pages.builder', $page);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $this->pages->updateDraft($page, $this->validated($request, $page), $request->user());

        return back()->with('status', 'Page details updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        if ($page->is_homepage) {
            return back()->with('error', 'The homepage cannot be deleted. Set another page as the homepage first.');
        }

        $this->pages->delete($page);

        return redirect()->route('admin.pages.index')->with('status', 'Page moved to the archive.');
    }

    public function duplicate(Request $request, Page $page): RedirectResponse
    {
        $copy = $this->pages->duplicate($page, $request->user());

        return redirect()->route('admin.pages.edit', $copy)->with('status', 'Page duplicated as a draft.');
    }

    public function revisions(Page $page): View
    {
        return view('admin.pages.revisions', [
            'page' => $page,
            'revisions' => $page->revisions()->with('user')->paginate(20),
        ]);
    }

    public function restore(Request $request, Page $page, int $revision): RedirectResponse
    {
        $this->pages->restoreRevision($page, $page->revisions()->findOrFail($revision), $request->user());

        return redirect()->route('admin.pages.builder', $page)->with('status', 'Revision restored into the draft.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'template' => ['required', 'in:default,landing,full-width'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
            'is_homepage' => ['nullable', 'boolean'],
            'show_in_navigation' => ['nullable', 'boolean'],
            'scheduled_for' => ['nullable', 'date'],
        ]);

        $data['slug'] = Slug::unique(
            ($data['slug'] ?? null) ?: $data['title'],
            'pages',
            $page?->id
        );

        $data['is_homepage'] = $request->boolean('is_homepage');
        $data['show_in_navigation'] = $request->boolean('show_in_navigation');

        return $data;
    }
}
