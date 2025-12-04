<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rows = DB::table('order_details')->where('order_id', 13)->get();
    echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL;
}
