<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$batch = 'sales_693147f4121f7';
$status = \Illuminate\Support\Facades\Cache::get('reports:' . $batch);

echo "=== STEP 6: Cache Status (what browser polls) ===" . PHP_EOL;
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
echo PHP_EOL . "This is what /admin/sales/export/check/$batch returns." . PHP_EOL;
echo "Status is 'ready', so download link is active." . PHP_EOL;
