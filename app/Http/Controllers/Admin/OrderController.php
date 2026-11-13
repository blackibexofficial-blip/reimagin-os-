<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.orders.index', [
            'orders' => Order::query()
                ->with('customer')
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('reference', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$term}%"));
                }))
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->string('payment')->toString(), fn ($query, $state) => $query->where('payment_status', $state))
                ->latest('placed_at')
                ->paginate(15)
                ->withQueryString(),
            'totals' => [
                'open' => Order::query()->revenue()->whereNotIn('status', ['delivered', 'cancelled'])->sum('total_minor'),
                'outstanding' => (int) Order::query()->revenue()
                    ->selectRaw('coalesce(sum(total_minor - paid_minor), 0) as balance')->value('balance'),
                'count' => Order::query()->count(),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load(['customer', 'items.product', 'payments.recorder', 'quotation']),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Order::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'cancellation_reason' => ['nullable', 'string', 'max:300', 'required_if:status,cancelled'],
        ]);

        /* Delivered and cancelled are terminal states; anything else has to be
         * reachable in both directions so a mis-click can be undone. */
        if (in_array($order->status, ['delivered', 'cancelled'], true) && $data['status'] !== $order->status) {
            throw ValidationException::withMessages([
                'status' => 'A '.$order->status.' order cannot be moved back into the workshop.',
            ]);
        }

        $order->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $order->notes,
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
            'confirmed_at' => $data['status'] === 'confirmed' ? ($order->confirmed_at ?? now()) : $order->confirmed_at,
            'delivered_at' => $data['status'] === 'delivered' ? ($order->delivered_at ?? now()) : $order->delivered_at,
        ]);

        return back()->with('status', 'Order updated.');
    }

    public function storePayment(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,bank_transfer,card,cheque,online'],
            'type' => ['required', 'in:deposit,balance,installment,refund'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (int) round($data['amount'] * 100);

        DB::transaction(function () use ($order, $data, $amount, $request) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            $outstanding = $order->total_minor - $order->paid_minor;

            if ($data['type'] !== 'refund' && $amount > $outstanding) {
                throw ValidationException::withMessages([
                    'amount' => 'That is more than the '.money(max(0, $outstanding)).' still outstanding.',
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'reference' => $data['reference'] ?: 'PAY-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(4)),
                'method' => $data['method'],
                'type' => $data['type'],
                'status' => 'confirmed',
                'amount_minor' => $amount,
                'currency' => $order->currency ?: 'PKR',
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);

            $order->recalculatePaymentState();
            $order->customer?->recalculateLifetimeValue();
        });

        return back()->with('status', 'Payment recorded.');
    }
}
