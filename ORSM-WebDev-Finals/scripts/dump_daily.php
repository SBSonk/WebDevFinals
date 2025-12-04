<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;

$start = Carbon::parse('2025-11-05')->startOfDay();
$end = Carbon::parse('2025-12-04')->endOfDay();

$dateColExists = Schema::hasColumn('orders', 'order_date');
$dateCol = $dateColExists ? 'order_date' : 'created_at';

$daily = Order::select(DB::raw("DATE({$dateCol}) as date"), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as orders_count'))
    ->whereBetween($dateCol, [$start, $end])
    ->groupBy(DB::raw("DATE({$dateCol})"))
    ->orderBy('date')
    ->get();

echo json_encode($daily, JSON_PRETTY_PRINT) . PHP_EOL;
