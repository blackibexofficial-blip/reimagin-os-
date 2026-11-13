<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        return view('site.cart', ['cart' => Cart::resolve($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);

        if ($product->is_quote_only) {
            return redirect()->route('site.contact', ['piece' => $product->name])
                ->with('status', 'This piece is priced per project — send us the details and we will quote it.');
        }

        $cart = Cart::contents($request);
        $row = 'p'.$product->id;
        $quantity = (int) ($data['quantity'] ?? 1);
        $existing = (int) ($cart[$row]['quantity'] ?? 0);
        $newQuantity = min(20, $existing + $quantity);
        $lastAdded = min($quantity, 20 - $existing);

        if ($lastAdded <= 0) {
            return back()->with('error', 'You already have the maximum quantity of this piece in the basket.');
        }

        Cart::put($request, $row, ['product_id' => $product->id, 'quantity' => $newQuantity]);

        return redirect()->route('site.cart')
            ->with('status', $product->name.' added to your basket.');
    }

    public function update(Request $request, string $row): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:20']]);

        if ((int) $data['quantity'] === 0) {
            Cart::remove($request, $row);
        } else {
            $cart = Cart::contents($request);

            if (isset($cart[$row])) {
                Cart::put($request, $row, ['product_id' => $cart[$row]['product_id'], 'quantity' => (int) $data['quantity']]);
            }
        }

        return back()->with('status', 'Basket updated.');
    }

    public function destroy(Request $request, string $row): RedirectResponse
    {
        Cart::remove($request, $row);

        return back()->with('status', 'Removed from your basket.');
    }
}
