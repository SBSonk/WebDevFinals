<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$start = Carbon::parse('2025-11-05')->startOfDay();
$end = Carbon::parse('2025-12-04')->endOfDay();

try {
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
} catch (Throwable $e) {
    echo "error: " . $e->getMessage() . PHP_EOL;
}
