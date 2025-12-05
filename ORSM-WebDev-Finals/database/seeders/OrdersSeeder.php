<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersSeeder extends Seeder
{
    /**
     * Seed sample orders with details and correct totals.
     */
    public function run(): void
    {
        $products = Product::with('inventory')->get();
        if ($products->isEmpty()) {
            // Nothing to do if there are no products
            return;
        }

        // Prefer non-admin active users as customers
        $customers = User::whereRaw('LOWER(COALESCE(role, "")) != ?', ['admin'])
            ->where('is_active', true)
            ->get();
        if ($customers->isEmpty()) {
            // Fallback: any users
            $customers = User::all();
        }
        if ($customers->isEmpty()) {
            return; // no users to attach orders to
        }

        $orderCount = 15;

        for ($i = 0; $i < $orderCount; $i++) {
            DB::transaction(function () use ($customers, $products) {
                $customer = $customers->random();

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'order_date' => now()->subDays(rand(0, 60))->setTime(rand(8, 20), rand(0, 59)),
                    'order_status' => collect(['pending', 'completed', 'cancelled'])->random(),
                    'total_amount' => 0, // will compute below
                    'payment_status' => collect(['pending', 'paid', 'failed'])->random(),
                    'payment_method' => collect(['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'])->random(),
                    'shipping_address' => fake()->address(),
                ]);

                $itemsCount = rand(1, 4);
                $picked = $products->random(min($itemsCount, max(1, $products->count())));
                $picked = $picked instanceof \Illuminate\Support\Collection ? $picked : collect([$picked]);

                $total = 0;
                foreach ($picked as $product) {
                    $qty = rand(1, 3);
                    $unit = (float) $product->unit_price;
                    $subtotal = $qty * $unit;

                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'product_id' => $product->product_id,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'subtotal' => $subtotal,
                    ]);

                    // Adjust inventory if exists
                    $inv = Inventory::where('product_id', $product->product_id)->first();
                    if ($inv) {
                        $inv->stock_quantity = max(0, (int) $inv->stock_quantity - $qty);
                        $inv->save();
                    }

                    $total += $subtotal;
                }

                // Save computed total
                $order->total_amount = $total;
                $order->save();
            });
        }
    }
}
