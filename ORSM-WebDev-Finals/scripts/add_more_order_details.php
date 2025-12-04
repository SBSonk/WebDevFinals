<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

$start = Carbon::parse('2025-11-01')->startOfDay();
$end = Carbon::parse('2025-12-04')->endOfDay();

$orders = Order::whereBetween('order_date', [$start, $end])->orderBy('order_date')->limit(10)->get();
if ($orders->isEmpty()) {
    echo "No orders in range to augment\n";
    exit(0);
}

$products = Product::whereNotNull('category_id')->get()->groupBy('category_id');
if ($products->isEmpty()) {
    // fallback: all products
    $products = Product::all()->groupBy('category_id');
}

$inserted = 0;
foreach ($orders as $order) {
    // add 1-2 details per order
    $countToAdd = rand(1, 2);
    for ($i = 0; $i < $countToAdd; $i++) {
        // pick a random category group
        $catGroups = $products->keys()->toArray();
        if (empty($catGroups)) break;
        $catId = $catGroups[array_rand($catGroups)];
        $group = $products->get($catId)->values();
        $product = $group[array_rand(range(0, $group->count() - 1))];

        $qty = rand(1, 5);
        $unit = $product->unit_price ?? 100;
        $subtotal = bcmul((string)$unit, (string)$qty, 2);
        $now = Carbon::now();

        DB::table('order_details')->insert([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => $qty,
            'unit_price' => $unit,
            'subtotal' => $subtotal,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $inserted++;
    }
    // Recalculate order total_amount from order_details
    $sum = DB::table('order_details')->where('order_id', $order->order_id)->sum('subtotal');
    DB::table('orders')->where('order_id', $order->order_id)->update(['total_amount' => $sum]);
}

echo "Inserted {$inserted} order_details across {$orders->count()} orders\n";

// show updated by-category totals
$rows = DB::table('order_details')
    ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
    ->join('products', 'order_details.product_id', '=', 'products.product_id')
    ->join('categories', 'products.category_id', '=', 'categories.category_id')
    ->whereBetween('orders.order_date', [$start, $end])
    ->select('categories.category_name', DB::raw('SUM(order_details.subtotal) as total'))
    ->groupBy('categories.category_name')
    ->orderBy('total', 'desc')
    ->get();

echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
