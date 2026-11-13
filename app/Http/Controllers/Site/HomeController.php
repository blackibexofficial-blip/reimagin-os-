<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $homepage = Page::query()->published()->homepage()->first();

        /* Until a homepage is published we still need a storefront: fall back to
         * the newest published page, then to a section built from live data. */
        if (! $homepage) {
            $homepage = Page::query()->published()->latest('published_at')->first();
        }

        return view('site.home', [
            'page' => $homepage,
            'blocks' => $homepage?->blocksForPublic() ?? [],
            'featured' => Product::query()->published()->featured()->with(['featuredImage', 'category'])->limit(6)->get(),
        ]);
    }
}
