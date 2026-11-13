<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('site.blog.index', [
            'posts' => BlogPost::query()->published()->with(['category', 'author', 'featuredImage'])
                ->latest('published_at')->paginate(9),
        ]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->status === 'published' && $post->published_at?->isPast(), 404);

        /* The counter is a fire-and-forget increment; it must never block the
         * reader or fail the page. */
        BlogPost::query()->whereKey($post->id)->increment('views');

        return view('site.blog.show', [
            'post' => $post->load(['category', 'author', 'featuredImage']),
            'more' => BlogPost::query()->published()->where('id', '!=', $post->id)
                ->with('featuredImage')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
