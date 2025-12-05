<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Display orders for current user
    public function index()
    {
        // If auth is not enforced (testing), fall back to dummy customer
        $user = Auth::user() ?? User::where('email', 'customer@example.com')->first();

        // Admins should see ALL orders; other users see only their own
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        if ($isAdmin) {
            $orders = Order::with(['details.product', 'customer'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Return empty collection if no user found
            $orders = $user
                ? Order::where('customer_id', $user->id)
                    ->with(['details.product'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();
        }

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

    // Update order status (admin only)
    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $user = Auth::user();
        $order = Order::findOrFail($orderId);

        // Normalize property names (some code uses `status`, some `order_status`)
        $currentStatus = $order->status ?? $order->order_status ?? null;
        $newStatus = $request->input('status');

        // Admins can update any status
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        if (! $isAdmin) {
            // Only allow the owner to cancel their own pending order
            if ((int) $order->customer_id !== (int) ($user?->id)) {
                abort(403, 'Unauthorized.');
            }

            if ($newStatus !== 'cancelled') {
                abort(403, 'Only cancellation is allowed for customers.');
            }

            if ($currentStatus !== 'pending') {
                abort(403, 'Only pending orders can be cancelled.');
            }
        }

        // Apply the new status to whichever attribute exists
        if (isset($order->status) || !isset($order->order_status)) {
            $order->status = $newStatus;
        } else {
            $order->order_status = $newStatus;
        }
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'status' => $newStatus,
        ]);
    }

    // Delete an order (admin only)
    public function destroy($orderId)
    {
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        if (! $isAdmin) {
            abort(403, 'Unauthorized. Admins only.');
        }

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($orderId);

            // Get order details to restore inventory
            $orderDetails = OrderDetail::where('order_id', $orderId)->get();

            foreach ($orderDetails as $detail) {
                // Restore inventory when order is deleted
                $inventory = Inventory::where('product_id', $detail->product_id)->first();
                if ($inventory) {
                    $inventory->stock_quantity += $detail->quantity;
                    $inventory->save();
                }

                // Delete order detail
                $detail->delete();
            }

            // Delete the order
            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
