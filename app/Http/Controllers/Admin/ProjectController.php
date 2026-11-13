<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Media;
use App\Models\Project;
use App\Models\ShowcaseProject;
use App\Models\User;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Two related things share the "projects" address: live commissions (Project,
 * with customers, stages and money) and published showcase pieces
 * (ShowcaseProject). The admin screen presents both, side by side.
 */
class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.projects.index', [
            'projects' => Project::query()->with(['customer', 'manager'])
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->orderByRaw("case status when 'delivery' then 0 when 'production' then 1 when 'qc' then 2 else 3 end")
                ->latest('id')->paginate(10, ['*'], 'projects'),
            'showcase' => ShowcaseProject::query()->orderByDesc('is_featured')->orderBy('position')
                ->paginate(10, ['*'], 'showcase'),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', [
            'project' => new Project(['status' => 'planning']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->validated($request));

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project created.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', [
            'project' => $project->load('stages'),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validated($request, $project));

        return back()->with('status', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'Project deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'in:planning,design,production,qc,delivery,installed,closed,on_hold'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget_minor' => ['nullable', 'numeric', 'min:0'],
            'site_address' => ['nullable', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (isset($data['budget_minor'])) {
            $data['budget_minor'] = (int) round($data['budget_minor'] * 100);
        }

        $data['reference'] = $project?->reference ?? $this->nextReference();

        return $data;
    }

    private function nextReference(): string
    {
        $year = now()->year;
        $count = Project::query()->where('reference', 'like', "PRJ-{$year}-%")->count() + 1;

        return sprintf('PRJ-%d-%04d', $year, $count);
    }
}
