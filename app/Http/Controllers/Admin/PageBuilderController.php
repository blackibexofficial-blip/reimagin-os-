<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Product;
use App\Services\MediaService;
use App\Services\PageService;
use App\Support\Blocks\BlockRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function __construct(
        private PageService $pages,
        private MediaService $media,
    ) {}

    public function edit(Page $page): View
    {
        return view('admin.pages.builder', [
            'page' => $page,
            'config' => [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'status' => $page->status,
                    'url' => $page->publicUrl(),
                    'has_unpublished_changes' => $page->hasUnpublishedChanges(),
                ],
                'endpoint' => route('admin.pages.content', $page),
                'publishEndpoint' => route('admin.pages.publish', $page),
                'csrf' => csrf_token(),
                'library' => BlockRegistry::library(),
                'blocks' => $page->blocks(),
                /* Rendered once per block type so the canvas can draw instantly. */
                'templates' => collect(array_keys(BlockRegistry::all()))
                    ->mapWithKeys(fn (string $type) => [$type => view("site.blocks.{$type}", [
                        'block' => BlockRegistry::make($type),
                        'preview' => true,
                    ])->render()])
                    ->all(),
                'media' => $this->media->recent(40),
                'products' => Product::query()->orderBy('name')->get(['id', 'name', 'slug'])->toArray(),
            ],
        ]);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'content' => ['present', 'array'],
            'autosave' => ['nullable', 'boolean'],
        ]);

        $blocks = BlockRegistry::normalize($request->input('content', []));

        $this->pages->updateDraft($page, ['content' => $blocks], $request->user());

        return response()->json([
            'saved_at' => now()->toIso8601String(),
            'blocks' => $blocks,
            'message' => 'Draft saved.',
        ]);
    }

    public function publish(Request $request, Page $page): JsonResponse
    {
        /* Publishing reads the stored draft, so a stale browser tab cannot
         * publish content it never saved. */
        $this->pages->publish($page->fresh(), $request->user());

        return response()->json([
            'published_at' => $page->fresh()->published_at?->toIso8601String(),
            'url' => $page->fresh()->publicUrl(),
        ]);
    }

    public function unpublish(Request $request, Page $page): JsonResponse
    {
        $this->pages->unpublish($page, $request->user());

        return response()->json(['status' => $page->fresh()->status]);
    }

    /** Server-rendered preview of the draft, without publishing it. */
    public function preview(Page $page): View
    {
        return view('site.page', ['page' => $page, 'blocks' => $page->blocks(), 'preview' => true]);
    }
}
