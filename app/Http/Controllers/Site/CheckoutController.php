<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\QuotationService;
use App\Services\StockService;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private QuotationService $quotations,
        private StockService $stock,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $cart = Cart::resolve($request);

        if ($cart['lines'] === []) {
            return redirect()->route('site.cart')->with('error', 'Your basket is empty.');
        }

        return view('site.checkout', ['cart' => $cart]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cart = Cart::resolve($request);

        if ($cart['lines'] === []) {
            return redirect()->route('site.cart')->with('error', 'Your basket is empty.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'address_line_1' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($data, $cart) {
            /* One customer record per email, so the storefront and the admin
             * desk write to the same row rather than creating duplicates. */
            $customer = Customer::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'address_line_1' => $data['address_line_1'],
                    'city' => $data['city'],
                    'country' => 'Pakistan',
                ]
            );

            $order = Order::create([
                'reference' => $this->quotations->nextOrderReference(),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal_minor' => $cart['subtotal_minor'],
                'discount_minor' => 0,
                'tax_minor' => 0,
                'delivery_minor' => 0,
                'total_minor' => $cart['subtotal_minor'],
                'paid_minor' => 0,
                'currency' => 'PKR',
                'notes' => $data['notes'] ?? null,
                'shipping_address' => $data['address_line_1'],
                'city' => $data['city'],
                'phone' => $data['phone'],
                'placed_at' => now(),
            ]);

            foreach ($cart['lines'] as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'quantity' => $line['quantity'],
                    'unit_price_minor' => $line['unit_price_minor'],
                    'line_total_minor' => $line['line_total_minor'],
                    'options' => null,
                ]);

                if ($line['product']->track_stock) {
                    $this->stock->issue($line['product'], $line['quantity'], $order, null, $order->reference);
                }
            }

            $order->recalculatePaymentState();
            $customer->recalculateLifetimeValue();

            return $order;
        });

        Cart::clear($request);

        return redirect()->route('site.order', $order->reference)
            ->with('status', 'Order placed. Our studio will call to confirm the details.');
    }

    public function show(string $reference): View
    {
        $order = Order::query()
            ->where('reference', $reference)
            ->with(['items', 'payments'])
            ->firstOrFail();

        return view('site.order', ['order' => $order]);
    }
}
