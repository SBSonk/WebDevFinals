<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$start = Carbon::parse('2025-11-05')->startOfDay();
$end = Carbon::parse('2025-12-04')->endOfDay();

$orders = Order::with('details')->whereBetween('order_date', [$start, $end])->get();

$output = [];
foreach ($orders as $o) {
    $row = [
        'order_id' => $o->order_id,
        'order_date' => $o->order_date?->toDateTimeString(),
        'total_amount' => $o->total_amount,
        'details' => []
    ];
    foreach ($o->details as $d) {
        $row['details'][] = [
            'product_id' => $d->product_id,
            'quantity' => $d->quantity,
            'unit_price' => $d->unit_price,
            'subtotal' => $d->subtotal
        ];
    }
    $output[] = $row;
}

echo json_encode($output, JSON_PRETTY_PRINT) . PHP_EOL;
