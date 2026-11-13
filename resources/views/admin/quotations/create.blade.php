<x-layouts.admin title="New quotation" heading="New quotation" subheading="Price the work, then send it for approval."
                 :breadcrumbs="['Quotations' => route('admin.quotations.index')]">
    <form method="POST" action="{{ route('admin.quotations.store') }}"
          x-data="{
              items: [{ description: '', quantity: 1, unit_price: 0, product_id: '', customisation: '' }],
              discount: 0,
              delivery: 0,
              taxPercent: 0,
              get subtotal() { return this.items.reduce((sum, i) => sum + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0), 0); },
              get tax() { return this.subtotal * (Number(this.taxPercent) || 0) / 100; },
              get total() { return Math.max(0, this.subtotal - (Number(this.discount) || 0) + this.tax + (Number(this.delivery) || 0)); },
              money(value) { return 'Rs ' + (Number(value) || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }); },
              pick(index, event) {
                  const option = event.target.selectedOptions[0];
                  if (!option || !option.dataset.price) return;
                  this.items[index].unit_price = Number(option.dataset.price) / 100;
                  if (!this.items[index].description) this.items[index].description = option.textContent.split(' — ')[0];
              },
          }"
          class="grid gap-4 lg:grid-cols-3">
        @csrf

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Customer">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Customer" name="customer_id" required>
                        <select name="customer_id" required class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Choose a customer…</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomer) == $customer->id)>
                                    {{ $customer->name }}{{ $customer->email ? ' — '.$customer->email : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-500">
                            New enquiry? <a href="{{ route('admin.customers.create') }}" class="font-medium text-brand-700">Add a customer</a>, or convert a lead first.
                        </p>
                    </x-ui.field>

                    <x-ui.field label="Linked lead" name="lead_id" hint="Optional, for pipeline reporting.">
                        <select name="lead_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">None</option>
                            @foreach ($leads as $lead)
                                <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>{{ $lead->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Line items" description="Pick a product to prefill the price, or type a custom line.">
                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-lg border border-ink-150 p-3">
                            <div class="grid gap-2 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] font-medium uppercase tracking-wide text-ink-500">Description</label>
                                    <input type="text" :name="`items[${index}][description]`" x-model="item.description" required
                                           placeholder="Fitted wardrobe, 3 doors"
                                           class="mt-0.5 w-full rounded-md border-ink-200 px-2 py-1.5 text-sm focus:border-brand-400 focus:ring-brand-400">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-[10px] font-medium uppercase tracking-wide text-ink-500">Product</label>
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="pick(index, $event)"
                                            class="mt-0.5 w-full rounded-md border-ink-200 px-2 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
                                        <option value="">Custom line</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price_minor }}">
                                                {{ $product->name }} — {{ $product->sku }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-[10px] font-medium uppercase tracking-wide text-ink-500">Qty</label>
                                    <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity"
                                           class="mt-0.5 w-full rounded-md border-ink-200 px-2 py-1.5 text-sm focus:border-brand-400 focus:ring-brand-400">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-medium uppercase tracking-wide text-ink-500">Unit (Rs)</label>
                                    <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price"
                                           class="mt-0.5 w-full rounded-md border-ink-200 px-2 py-1.5 text-sm focus:border-brand-400 focus:ring-brand-400">
                                </div>
                                <div class="flex items-end sm:col-span-1">
                                    <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1"
                                            class="mb-1 rounded-md p-1.5 text-ink-400 hover:bg-red-50 hover:text-red-600" title="Remove line">
                                        <x-nav-icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            </div>
                            <input type="text" :name="`items[${index}][customisation]`" x-model="item.customisation"
                                   placeholder="Customisation notes (veneer, hardware…)"
                                   class="mt-2 w-full rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <p class="mt-1 text-right text-xs font-medium tabular-nums text-ink-700" x-text="money((item.quantity || 0) * (item.unit_price || 0))"></p>
                        </div>
                    </template>

                    <button type="button" @click="items.push({ description: '', quantity: 1, unit_price: 0, product_id: '', customisation: '' })"
                            class="w-full rounded-lg border border-dashed border-ink-300 py-2 text-xs font-medium text-ink-600 hover:bg-ink-50">
                        + Add another line
                    </button>
                </div>
            </x-ui.card>

            <x-ui.card title="Notes and terms">
                <div class="space-y-4">
                    <x-ui.field label="Notes for the customer" name="notes">
                        <textarea name="notes" rows="3" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes') }}</textarea>
                    </x-ui.field>
                    <x-ui.field label="Terms" name="terms">
                        <textarea name="terms" rows="3"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('terms', '50% on order, balance before delivery. Quote valid for 14 days.') }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Totals">
                <div class="space-y-3">
                    <x-ui.field label="Discount (Rs)" name="discount">
                        <input type="number" name="discount" min="0" step="0.01" x-model.number="discount"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Delivery (Rs)" name="delivery">
                        <input type="number" name="delivery" min="0" step="0.01" x-model.number="delivery"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Sales tax %" name="tax_percent">
                        <input type="number" name="tax_percent" min="0" max="100" step="0.1" x-model.number="taxPercent"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>

                    <dl class="space-y-1 border-t border-ink-150 pt-3 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd class="tabular-nums" x-text="money(subtotal)"></dd></div>
                        <div class="flex justify-between text-emerald-700"><dt>Discount</dt><dd class="tabular-nums" x-text="'−' + money(discount)"></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-600">Tax</dt><dd class="tabular-nums" x-text="money(tax)"></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd class="tabular-nums" x-text="money(delivery)"></dd></div>
                        <div class="flex justify-between border-t border-ink-200 pt-2 font-semibold"><dt>Total</dt><dd class="tabular-nums" x-text="money(total)"></dd></div>
                    </dl>
                </div>
            </x-ui.card>

            <x-ui.card title="Validity">
                <x-ui.field label="Valid until" name="valid_until">
                    <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(14)->toDateString()) }}"
                           class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </x-ui.field>
                <p class="mt-2 text-[11px] text-ink-500">The quotation starts as a draft; mark it sent when the customer has it.</p>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.quotations.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Create quotation</x-ui.button>
            </div>
        </div>
    </form>
</x-layouts.admin>
