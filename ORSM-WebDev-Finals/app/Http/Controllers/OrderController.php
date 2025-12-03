<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Display orders for current user
    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('customer_id', $user->id)->with('details.product')->get();
        return view('orders.index', compact('orders'));
    }

    public function show($orderId)
    {
        $order = Order::with('details.product')->where('order_id', $orderId)->firstOrFail();
        return view('orders.show', compact('order'));
    }

    // Create an order from session cart
    public function store(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('store')->with('error', 'Cart is empty');
        }

        // TODO: Remove this dummy user once testing is done
        // For now, use the dummy customer (customer@example.com) to test order creation
        $user = Auth::user() ?? \App\Models\User::where('email', 'customer@example.com')->firstOrFail();

        // Use DB transaction for consistency
        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($cart as $item) {
                $total += ($item['price'] * $item['qty']);
            }

            $order = Order::create([
                'customer_id' => $user->id,
                'order_date' => now(),
                'order_status' => 'pending',
                'total_amount' => $total,
                'payment_status' => 'pending',
                'payment_method' => $request->input('payment_method', 'manual'),
                'shipping_address' => $request->input('shipping_address', ''),
            ]);

            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                $unitPrice = $item['price'];
                $qty = $item['qty'];
                $subtotal = $unitPrice * $qty;

                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Update inventory (prevent negative stock)
                $inventory = Inventory::where('product_id', $product->product_id)->first();
                if ($inventory) {
                    $inventory->stock_quantity = max(0, $inventory->stock_quantity - $qty);
                    $inventory->save();
                }
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            return redirect()->route('orders.index')->with('success', 'Order placed successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }
}
