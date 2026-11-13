<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $record = Page::query()->published()->where('slug', $page)->firstOrFail();

        return view('site.page', [
            'page' => $record,
            'blocks' => $record->blocksForPublic(),
        ]);
    }
}
