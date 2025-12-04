<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Show payment simulator dashboard
     */
    public function index()
    {
        $orders = Order::with('customer', 'details.product')
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        return view('admin.payments.simulator', compact('orders'));
    }

    /**
     * Simulate COD (Cash on Delivery) payment
     */
    public function simulateCod(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Set payment method to COD and mark as pending
        $order->update([
            'payment_method' => 'Cash on Delivery',
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order marked for Cash on Delivery',
            'order' => $order,
        ]);
    }

    /**
     * Simulate successful payment (fake gateway response)
     */
    public function simulatePaymentSuccess(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Simulate successful payment
        $order->update([
            'payment_method' => $request->input('payment_method', 'Credit Card'),
            'payment_status' => 'completed',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment marked as completed',
            'order' => $order,
        ]);
    }

    /**
     * Simulate failed payment (fake gateway error)
     */
    public function simulatePaymentFailed(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Simulate failed payment
        $order->update([
            'payment_method' => $request->input('payment_method', 'Credit Card'),
            'payment_status' => 'failed',
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Payment simulation marked as failed',
            'order' => $order,
        ]);
    }

    /**
     * Create a test order with simulated payment
     */
    public function createTestOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'payment_method' => 'required|in:Cash on Delivery,Credit Card,Debit Card,Online Banking',
            'payment_status' => 'required|in:pending,completed,failed',
        ]);

        // Create a test order
        $order = Order::create([
            'customer_id' => $validated['customer_id'],
            'order_date' => Carbon::now(),
            'order_status' => 'pending',
            'total_amount' => 0, // Will be calculated from order_details
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'shipping_address' => 'Test Address',
        ]);

        return response()->json([
            'status' => 'created',
            'message' => 'Test order created',
            'order' => $order,
        ], 201);
    }

    /**
     * Bulk update payment status for orders
     */
    public function bulkUpdatePaymentStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:orders,order_id',
            'payment_status' => 'required|in:pending,completed,failed',
            'payment_method' => 'nullable|in:Cash on Delivery,Credit Card,Debit Card,Online Banking',
        ]);

        $updates = [
            'payment_status' => $validated['payment_status'],
        ];

        if ($validated['payment_method'] ?? null) {
            $updates['payment_method'] = $validated['payment_method'];
        }

        $count = Order::whereIn('order_id', $validated['order_ids'])->update($updates);

        return response()->json([
            'status' => 'success',
            'message' => "$count order(s) updated",
            'updated_count' => $count,
        ]);
    }

    /**
     * Get payment statistics
     */
    public function paymentStats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'completed_payments' => Order::where('payment_status', 'completed')->count(),
            'pending_payments' => Order::where('payment_status', 'pending')->count(),
            'failed_payments' => Order::where('payment_status', 'failed')->count(),
            'payment_methods' => Order::select('payment_method')
                ->groupBy('payment_method')
                ->selectRaw('COUNT(*) as count')
                ->get(),
            'completion_rate' => Order::where('payment_status', 'completed')->count() / max(Order::count(), 1) * 100,
        ];

        return response()->json($stats);
    }
}
