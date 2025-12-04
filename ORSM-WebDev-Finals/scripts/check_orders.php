<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Carbon\Carbon;

$s = Carbon::parse('2025-11-05')->startOfDay();
$e = Carbon::parse('2025-12-04')->endOfDay();

try {
    $count = Order::whereBetween('order_date', [$s, $e])->count();
    echo "orders_count={$count}\n";
} catch (Throwable $e) {
    echo "error: " . $e->getMessage() . PHP_EOL;
}
