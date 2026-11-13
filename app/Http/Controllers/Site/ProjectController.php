<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseProject;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('site.projects.index', [
            'projects' => ShowcaseProject::query()->where('is_published', true)
                ->orderByDesc('is_featured')->orderBy('position')->paginate(9),
        ]);
    }

    public function show(ShowcaseProject $project): View
    {
        abort_unless($project->is_published, 404);

        $gallery = collect($project->gallery ?? [])
            ->map(fn ($id) => \App\Models\Media::find($id))
            ->filter()
            ->values();

        return view('site.projects.show', [
            'project' => $project,
            'gallery' => $gallery,
            'more' => ShowcaseProject::query()->where('is_published', true)
                ->where('id', '!=', $project->id)->orderByDesc('is_featured')->limit(3)->get(),
        ]);
    }
}
