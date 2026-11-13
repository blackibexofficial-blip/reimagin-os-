<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(private LeadService $leads) {}

    public function index(Request $request): View
    {
        return view('admin.leads.index', [
            'leads' => Lead::query()
                ->with(['assignee', 'product'])
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                }))
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->string('source')->toString(), fn ($query, $source) => $query->where('source', $source))
                ->orderByRaw('next_follow_up_at is null')
                ->orderBy('next_follow_up_at')
                ->orderByDesc('score')
                ->paginate(20)
                ->withQueryString(),
            'pipeline' => $this->leads->pipelineSummary(),
            'summary' => [
                'open' => Lead::query()->open()->count(),
                'value' => (int) Lead::query()->open()->sum('estimated_value_minor'),
                'conversion' => $this->leads->conversionRate(),
            ],
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'sources' => Lead::query()->whereNotNull('source')->distinct()->orderBy('source')->pluck('source'),
        ]);
    }

    public function create(): View
    {
        return view('admin.leads.form', [
            'lead' => new Lead(['status' => 'new', 'source' => 'manual', 'priority' => 'normal']),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $lead = $this->leads->create($this->validated($request), $request->user());

        return redirect()->route('admin.leads.show', $lead)->with('status', 'Lead added to the pipeline.');
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', [
            'lead' => $lead->load(['assignee', 'product', 'customer', 'activities.user', 'quotations', 'tasks']),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Lead $lead): View
    {
        return view('admin.leads.form', [
            'lead' => $lead,
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $lead->update($this->validated($request));

        return back()->with('status', 'Lead updated.');
    }

    public function changeStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,qualified,proposal,won,lost'],
            'lost_reason' => ['nullable', 'string', 'max:200', 'required_if:status,lost'],
        ]);

        if ($data['status'] === 'lost') {
            $lead->forceFill(['lost_reason' => $data['lost_reason']])->save();
        }

        $this->leads->changeStatus($lead, $data['status'], $request->user());

        return back()->with('status', 'Lead moved to '.$data['status'].'.');
    }

    public function assign(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate(['assigned_to' => ['required', 'integer', 'exists:users,id']]);

        $this->leads->assign($lead, User::query()->findOrFail($data['assigned_to']), $request->user());

        return back()->with('status', 'Lead assigned.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        $customer = $this->leads->convertToCustomer($lead, $request->user());

        return redirect()->route('admin.customers.show', $customer)
            ->with('status', 'Lead converted to a customer. You can raise a quotation now.');
    }

    public function note(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:1000'],
            'next_follow_up_at' => ['nullable', 'date', 'after:now'],
        ]);

        $this->leads->logActivity($lead, 'note', $data['title'], $data['body'] ?? null, $request->user());

        if (! empty($data['next_follow_up_at'])) {
            $this->leads->scheduleFollowUp($lead, \Carbon\Carbon::parse($data['next_follow_up_at']), $request->user());
        }

        return back()->with('status', 'Activity logged.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'source' => ['required', 'string', 'max:40'],
            'status' => ['required', 'in:new,contacted,qualified,proposal,won,lost'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        /* The form collects rupees; the domain stores minor units. */
        $data['estimated_value_minor'] = isset($data['estimated_value'])
            ? (int) round($data['estimated_value'] * 100)
            : null;

        unset($data['estimated_value']);

        return $data;
    }
}
