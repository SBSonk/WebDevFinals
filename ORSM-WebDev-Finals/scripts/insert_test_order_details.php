<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

$orderId = 13;

try {
    $order = Order::find($orderId);
    if (! $order) {
        echo "Order {$orderId} not found\n";
        exit(1);
    }

    // find a product with a category assigned
    $product = Product::whereNotNull('category_id')->first();
    if (! $product) {
        $product = Product::first();
    }

    if (! $product) {
        echo "No product found to create order details.\n";
        exit(1);
    }

    // create a single order detail
    $qty = 2;
    $unit = $product->unit_price ?? 100;
    $subtotal = bcmul((string)$unit, (string)$qty, 2);

    $now = Carbon::now();

    DB::table('order_details')->insert([
        'order_id' => $orderId,
        'product_id' => $product->product_id,
        'quantity' => $qty,
        'unit_price' => $unit,
        'subtotal' => $subtotal,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Recalculate order total_amount from order_details
    $sum = DB::table('order_details')->where('order_id', $orderId)->sum('subtotal');
    DB::table('orders')->where('order_id', $orderId)->update(['total_amount' => $sum]);

    echo "Inserted order_detail for order {$orderId}, product {$product->product_id}, qty={$qty}, subtotal={$subtotal}\n";
    echo "Updated order total_amount to {$sum}\n";
} catch (Throwable $e) {
    echo "error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
