<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class QuotationController extends Controller
{
    public function __construct(private QuotationService $quotations) {}

    public function index(Request $request): View
    {
        return view('admin.quotations.index', [
            'quotations' => Quotation::query()
                ->with(['customer', 'creator'])
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('reference', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$term}%"));
                }))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'summary' => [
                'open' => Quotation::query()->open()->sum('total_minor'),
                'won' => Quotation::query()->whereIn('status', ['approved', 'converted'])->sum('total_minor'),
                'count' => Quotation::query()->whereIn('status', ['draft', 'sent'])->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.quotations.create', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'email', 'phone']),
            'leads' => Lead::query()->open()->orderByDesc('score')->limit(40)->get(['id', 'name', 'estimated_value_minor']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku', 'price_minor', 'is_quote_only']),
            'selectedCustomer' => $request->integer('customer'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.customisation' => ['nullable', 'string', 'max:300'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'delivery' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $quotation = $this->quotations->create([
            'customer_id' => $data['customer_id'],
            'lead_id' => $data['lead_id'] ?? null,
            'valid_until' => $data['valid_until'] ?? now()->addDays(14)->toDateString(),
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'discount_minor' => (int) round(($data['discount'] ?? 0) * 100),
            'delivery_minor' => (int) round(($data['delivery'] ?? 0) * 100),
            'tax_percent' => (float) ($data['tax_percent'] ?? 0),
            'items' => collect($data['items'])->map(fn (array $item) => [
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'customisation' => $item['customisation'] ?? null,
                'quantity' => (int) $item['quantity'],
                'unit_price_minor' => (int) round($item['unit_price'] * 100),
            ])->all(),
        ], $request->user());

        return redirect()->route('admin.quotations.show', $quotation)
            ->with('status', 'Quotation '.$quotation->reference.' created.');
    }

    public function show(Quotation $quotation): View
    {
        return view('admin.quotations.show', [
            'quotation' => $quotation->load(['customer', 'lead', 'items.product', 'creator', 'revisions.user', 'order']),
        ]);
    }

    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        if (! in_array($quotation->status, ['draft', 'sent'], true)) {
            return back()->with('error', 'Only draft or sent quotations can be edited.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'valid_until' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.id' => ['nullable', 'integer'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'delivery' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->quotations->syncItems($quotation, collect($data['items'])->map(fn (array $item) => [
            'id' => $item['id'] ?? null,
            'description' => $item['description'],
            'quantity' => (int) $item['quantity'],
            'unit_price_minor' => (int) round($item['unit_price'] * 100),
        ])->all(), $request->user());

        $quotation->update([
            'notes' => $data['notes'] ?? $quotation->notes,
            'terms' => $data['terms'] ?? $quotation->terms,
            'valid_until' => $data['valid_until'] ?? $quotation->valid_until,
            'discount_minor' => (int) round(($data['discount'] ?? 0) * 100),
            'delivery_minor' => (int) round(($data['delivery'] ?? 0) * 100),
        ]);

        $quotation->recalculateTotals();

        return back()->with('status', 'Quotation updated.');
    }

    public function send(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->quotations->send($quotation, $request->user());

        return back()->with('status', 'Quotation marked as sent.');
    }

    public function approve(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->quotations->approve($quotation, $request->user());

        return back()->with('status', 'Quotation approved. Convert it when the customer confirms.');
    }

    public function reject(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:300']]);

        $this->quotations->reject($quotation, $data['reason'] ?? null, $request->user());

        return back()->with('status', 'Quotation marked as rejected.');
    }

    public function convert(Request $request, Quotation $quotation): RedirectResponse
    {
        try {
            $order = $this->quotations->convert($quotation, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('status', 'Quotation converted into order '.$order->reference.'.');
    }
}
