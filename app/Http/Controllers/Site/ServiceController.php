<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('site.services', [
            'services' => Service::query()->where('is_published', true)->orderBy('position')->get(),
        ]);
    }
}
