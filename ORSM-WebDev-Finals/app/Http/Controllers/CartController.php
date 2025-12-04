<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Show store (list of products)
    public function storeView()
    {
        $products = Product::with('inventory')->get();
        return view('store', compact('products'));
    }

    // Show cart
    public function index()
    {
        $cart = session('cart', []);
        return view('cart', compact('cart'));
    }

    // Add to cart
    public function add(Request $request)
    {
        $request->validate(["product_id" => 'required|integer', 'qty' => 'required|integer|min:1']);

        $product = Product::findOrFail($request->product_id);

        $cart = session('cart', []);

        if (isset($cart[$product->product_id])) {
            $cart[$product->product_id]['qty'] += $request->qty;
        } else {
            $cart[$product->product_id] = [
                'id' => $product->product_id,
                'name' => $product->product_name,
                'price' => $product->unit_price,
                'qty' => $request->qty,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->back()->with('success', 'Product added to cart');
    }

    // Update cart quantities
    public function update(Request $request)
    {
        $cart = session('cart', []);

        foreach ($request->input('quantities', []) as $id => $qty) {
            if (isset($cart[$id])) {
                $cart[$id]['qty'] = max(0, (int)$qty);
                if ($cart[$id]['qty'] === 0) {
                    unset($cart[$id]);
                }
            }
        }

        session(['cart' => $cart]);
        return redirect()->route('cart.index')->with('success', 'Cart updated');
    }

    public function remove($id)
    {
        $cart = session('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session(['cart' => $cart]);
        }

        return redirect()->route('cart.index')->with('success', 'Product removed');
    }
}
