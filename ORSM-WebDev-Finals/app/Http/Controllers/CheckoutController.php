<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('store')->with('error', 'Cart is empty');
        }

        $user = Auth::user();
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));

        return view('checkout', compact('cart', 'total', 'user'));
    }
}
