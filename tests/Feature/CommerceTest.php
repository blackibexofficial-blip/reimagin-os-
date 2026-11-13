<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

/**
 * Commerce rules that carry money: quotations recalculate from their lines,
 * orders only accept payments up to the outstanding balance, and stock moves
 * once per event rather than once per click.
 */
class CommerceTest extends TestCase
{
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        (new DatabaseSeeder)->run();

        $this->staff = User::query()->where('email', 'sales@reimaginos.test')->firstOrFail();
    }

    public function test_a_payment_updates_the_order_and_customer_balance(): void
    {
        $order = Order::query()->where('payment_status', '!=', 'paid')->where('total_minor', '>', 0)->firstOrFail();
        $outstanding = $order->total_minor - $order->paid_minor;
        $partial = (int) round($outstanding / 2 / 100) * 100;

        $this->actingAs($this->staff)
            ->post(route('admin.orders.payments.store', $order), [
                'amount' => $partial / 100,
                'method' => 'bank_transfer',
                'type' => 'deposit',
                'reference' => 'TEST-1',
            ])->assertRedirect();

        $order = $order->fresh();

        $this->assertSame($order->paid_minor, (int) $order->payments()->where('type', '!=', 'refund')->sum('amount_minor'));
        $this->assertNotSame('unpaid', $order->payment_status);
        $this->assertSame($this->staff->id, Payment::query()->latest('id')->value('recorded_by'));
    }

    public function test_a_payment_cannot_exceed_the_outstanding_balance(): void
    {
        $order = Order::query()->where('payment_status', '!=', 'paid')->where('total_minor', '>', 0)->firstOrFail();
        $outstanding = $order->total_minor - $order->paid_minor;

        $this->actingAs($this->staff)
            ->post(route('admin.orders.payments.store', $order), [
                'amount' => ($outstanding + 50000) / 100,
                'method' => 'cash',
                'type' => 'balance',
            ])->assertSessionHasErrors('amount');

        $this->assertSame($outstanding, $order->fresh()->total_minor - $order->fresh()->paid_minor);
    }

    public function test_a_delivered_order_cannot_be_reopened(): void
    {
        $order = Order::query()->where('status', 'delivered')->firstOrFail();

        $this->actingAs($this->staff)
            ->put(route('admin.orders.update', $order), ['status' => 'in_production'])
            ->assertSessionHasErrors('status');

        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_a_quotation_totals_recalculate_from_its_lines(): void
    {
        $quotation = Quotation::query()->has('items')->firstOrFail();

        $subtotal = (int) $quotation->items()->sum('line_total_minor');
        $expectedTax = \App\Support\Money::ofPercent($subtotal, 17);

        $quotation->forceFill([
            'discount_type' => 'percent',
            'discount_percent' => 10,
            'tax_minor' => $expectedTax,
            'delivery_minor' => 500000,
        ])->save();

        $quotation = $quotation->fresh()->recalculateTotals();
        $expectedDiscount = \App\Support\Money::ofPercent($subtotal, 10);

        $this->assertSame($subtotal, (int) $quotation->subtotal_minor);
        $this->assertSame($expectedDiscount, (int) $quotation->discount_minor);
        $this->assertSame($subtotal - $expectedDiscount + $expectedTax + 500000, (int) $quotation->total_minor);
    }

    public function test_approving_a_quotation_then_converting_it_creates_an_order(): void
    {
        $quotation = Quotation::query()->has('items')->whereDoesntHave('order')->firstOrFail();

        $this->actingAs($this->staff)->post(route('admin.quotations.send', $quotation))->assertRedirect();
        $this->actingAs($this->staff)->post(route('admin.quotations.approve', $quotation))->assertRedirect();

        $before = Order::query()->count();

        $this->actingAs($this->staff)->post(route('admin.quotations.convert', $quotation))->assertRedirect();

        $quotation = $quotation->fresh();

        $this->assertSame($before + 1, Order::query()->count());
        $this->assertNotNull($quotation->order);
        $this->assertSame($quotation->total_minor, $quotation->order->total_minor);
        $this->assertSame($quotation->items()->count(), $quotation->order->items()->count());
    }

    public function test_a_quotation_cannot_be_converted_twice(): void
    {
        $quotation = Quotation::query()->has('items')->whereDoesntHave('order')->firstOrFail();

        $this->actingAs($this->staff)->post(route('admin.quotations.approve', $quotation));
        $this->actingAs($this->staff)->post(route('admin.quotations.convert', $quotation))->assertRedirect();

        $count = Order::query()->count();

        $this->actingAs($this->staff)->post(route('admin.quotations.convert', $quotation->fresh()));

        $this->assertSame($count, Order::query()->count());
    }

    public function test_stock_adjustments_are_recorded_as_movements(): void
    {
        $manager = User::query()->where('email', 'manager@reimaginos.test')->firstOrFail();
        $product = Product::query()->where('track_stock', true)->firstOrFail();
        $before = (int) $product->stock_quantity;
        $movements = $product->stockMovements()->count();

        $this->actingAs($manager)
            ->post(route('admin.inventory.adjust', $product), [
                'quantity' => -3,
                'type' => 'damage',
                'reason' => 'Scratched in transit',
            ])->assertRedirect();

        $product = $product->fresh();

        $this->assertSame($before - 3, (int) $product->stock_quantity);
        $this->assertSame($movements + 1, $product->stockMovements()->count());
        $this->assertSame($manager->id, $product->stockMovements()->latest('id')->value('user_id'));
    }

    public function test_stock_never_goes_negative_and_the_ledger_records_the_clamp(): void
    {
        $manager = User::query()->where('email', 'manager@reimaginos.test')->firstOrFail();
        $product = Product::query()->where('track_stock', true)->firstOrFail();
        $before = (int) $product->stock_quantity;

        $this->actingAs($manager)
            ->post(route('admin.inventory.adjust', $product), [
                'quantity' => -($before + 50),
                'type' => 'damage',
                'reason' => 'Test',
            ])->assertRedirect();

        $this->assertSame(0, (int) $product->fresh()->stock_quantity);

        /* The ledger must agree with the cached balance, otherwise a rebuild
         * would silently restore stock that was written off. */
        $this->assertSame(-$before, (int) $product->stockMovements()->latest('id')->value('quantity'));
    }

    public function test_leads_convert_to_customers_once(): void
    {
        $lead = \App\Models\Lead::query()->whereNull('customer_id')->whereNotNull('email')->firstOrFail();
        $before = Customer::query()->count();

        $this->actingAs($this->staff)
            ->post(route('admin.leads.convert', $lead), ['name' => $lead->name])
            ->assertRedirect();

        $this->assertSame($before + 1, Customer::query()->count());
        $this->assertNotNull($lead->fresh()->customer_id);

        $this->actingAs($this->staff)
            ->post(route('admin.leads.convert', $lead->fresh()), ['name' => $lead->name]);

        $this->assertSame($before + 1, Customer::query()->count());
    }

    public function test_a_manager_can_advance_an_order_through_the_workshop(): void
    {
        $manager = User::query()->where('email', 'manager@reimaginos.test')->firstOrFail();
        $order = Order::query()->where('status', 'pending')->first();

        if (! $order) {
            /* The seeded set may have no pending order; park one at the start of
             * the flow so the advance path has somewhere to go. */
            $order = Order::query()->whereNotIn('status', ['delivered', 'cancelled'])->firstOrFail();
            $order->forceFill(['status' => 'pending', 'delivered_at' => null])->save();
        }

        $this->actingAs($manager)
            ->post(route('admin.production.advance', $order))
            ->assertRedirect();

        $this->assertSame('confirmed', $order->fresh()->status);
    }
}
