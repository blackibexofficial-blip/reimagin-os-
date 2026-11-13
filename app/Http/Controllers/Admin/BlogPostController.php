<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.posts.index', [
            'posts' => BlogPost::query()
                ->with(['category', 'author'])
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where('title', 'like', "%{$term}%"))
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', [
            'post' => new BlogPost(['status' => 'draft']),
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = BlogPost::create($this->validated($request) + ['author_id' => $request->user()->id]);

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Article created.');
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.posts.form', [
            'post' => $post,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $post->update($this->validated($request, $post));

        return back()->with('status', 'Article saved.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', 'Article deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180'],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:400'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string', 'max:300'],
        ]);

        $data['slug'] = Slug::unique(($data['slug'] ?? null) ?: $data['title'], 'blog_posts', $post?->id);
        $data['tags'] = filled($data['tags'] ?? null)
            ? array_values(array_filter(array_map('trim', explode(',', $data['tags']))))
            : [];

        /* A published article with no date would never appear on the site. */
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $post?->published_at ?? now();
        }

        return $data;
    }
}
