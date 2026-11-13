<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SEO health for the public site.
 *
 * The report is computed from the records themselves rather than cached, so it
 * can never claim a page is optimised after its meta tags were removed.
 */
class SeoController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()->orderBy('title')->get();
        $products = Product::query()->where('status', 'published')->get(['id', 'name', 'slug', 'seo_title', 'seo_description']);
        $posts = BlogPost::query()->where('status', 'published')->get(['id', 'title', 'slug', 'seo_title', 'seo_description', 'excerpt']);

        $audit = collect()
            ->merge($pages->map(fn (Page $page) => $this->row(
                'Page', $page->title, $page->slug, $page->seo_title, $page->seo_description, $page->status === 'published'
            )))
            ->merge($products->map(fn (Product $product) => $this->row(
                'Product', $product->name, $product->slug, $product->seo_title, $product->seo_description, true
            )))
            ->merge($posts->map(fn (BlogPost $post) => $this->row(
                'Article', $post->title, $post->slug, $post->seo_title, $post->seo_description ?: $post->excerpt, true
            )));

        return view('admin.seo.index', [
            'audit' => $audit,
            'pages' => $pages,
            'summary' => [
                'total' => $audit->count(),
                'healthy' => $audit->where('ok', true)->count(),
                'missing_title' => $audit->where('has_title', false)->count(),
                'missing_description' => $audit->where('has_description', false)->count(),
                'long' => $audit->where('length_ok', false)->count(),
            ],
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:180'],
        ]);

        $page->update($data);

        return back()->with('status', 'Meta tags saved for '.$page->title.'.');
    }

    /** @return array<string, mixed> */
    private function row(string $type, string $label, ?string $slug, ?string $title, ?string $description, bool $live): array
    {
        $title = trim((string) $title);
        $description = trim((string) $description);

        return [
            'type' => $type,
            'label' => $label,
            'slug' => $slug,
            'live' => $live,
            'has_title' => $title !== '',
            'has_description' => $description !== '',
            'title_length' => mb_strlen($title),
            'description_length' => mb_strlen($description),
            'length_ok' => $description === '' || (mb_strlen($description) >= 60 && mb_strlen($description) <= 180),
            'ok' => $title !== '' && $description !== '' && mb_strlen($description) >= 60,
            'issues' => array_values(array_filter([
                $title === '' ? 'No SEO title' : null,
                $description === '' ? 'No meta description' : null,
                $description !== '' && mb_strlen($description) < 60 ? 'Meta description is short' : null,
                mb_strlen($title) > 70 ? 'SEO title too long' : null,
            ])),
        ];
    }
}
