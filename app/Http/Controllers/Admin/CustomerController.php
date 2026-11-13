<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private LeadService $leads) {}

    public function index(Request $request): View
    {
        return view('admin.customers.index', [
            'customers' => Customer::query()
                ->withCount(['orders'])
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%");
                }))
                ->orderByDesc('lifetime_value_minor')
                ->paginate(20)
                ->withQueryString(),
            'summary' => [
                'count' => Customer::query()->count(),
                'value' => (int) Customer::query()->sum('lifetime_value_minor'),
                'repeat' => Customer::query()->has('orders', '>', 1)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.form', [
            'customer' => new Customer(['country' => 'Pakistan']),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = Customer::create($this->validated($request));

        return redirect()->route('admin.customers.show', $customer)->with('status', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        return view('admin.customers.show', [
            'customer' => $customer->load(['owner', 'orders.items', 'quotations', 'activities.user']),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', [
            'customer' => $customer,
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request, $customer));

        return back()->with('status', 'Customer details saved.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:180', 'unique:customers,email'.($customer ? ','.$customer->id : '')],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'address_line_1' => ['nullable', 'string', 'max:180'],
            'address_line_2' => ['nullable', 'string', 'max:180'],
            'city' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:80'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'tags' => ['nullable', 'string', 'max:300'],
        ]);

        $data['tags'] = filled($data['tags'] ?? null)
            ? array_values(array_filter(array_map('trim', explode(',', $data['tags']))))
            : [];

        return $data;
    }
}
