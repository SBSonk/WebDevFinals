<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Models\Order;
use App\Models\Category;
use App\Models\Supplier;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('report:smoke-test {start?} {end?}', function ($start = null, $end = null) {
    $this->comment('Running smoke-test exports...');

    $start = $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
    $end = $end ? Carbon::parse($end)->endOfDay() : Carbon::now()->endOfDay();

    $tmpRoot = storage_path('app/reports');
    if (!is_dir($tmpRoot)) {
        mkdir($tmpRoot, 0755, true);
    }

    // CSV
    $csvName = 'smoke_sales_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';
    $csvPath = $tmpRoot . DIRECTORY_SEPARATOR . $csvName;
    $handle = fopen($csvPath, 'w');
    fputcsv($handle, ['Order ID', 'Order Date', 'Customer ID', 'Total Amount', 'Payment Status', 'Payment Method']);

    $dateCol = Schema::hasColumn('orders', 'order_date') ? 'order_date' : 'created_at';
    $query = Order::with('customer')
        ->whereBetween($dateCol, [$start, $end])
        ->orderBy($dateCol);

    foreach ($query->cursor() as $o) {
        fputcsv($handle, [
            $o->order_id,
            $o->order_date,
            $o->customer_id,
            $o->total_amount,
            $o->payment_status,
            $o->payment_method,
        ]);
    }
    fclose($handle);
    $this->info('CSV written: ' . $csvPath);

    // PDF (small sample)
    $orders = Order::with('details.product')
        ->whereBetween($dateCol, [$start, $end])
        ->orderBy($dateCol)
        ->get();

    $grandTotal = $orders->sum(function ($o) {
        return floatval($o->total_amount ?? 0);
    });

    $pdfName = 'smoke_sales_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.pdf';
    $pdfPath = $tmpRoot . DIRECTORY_SEPARATOR . $pdfName;

    $pdf = Pdf::loadView('admin.exports.sales_report', [
        'orders' => $orders,
        'start' => $start,
        'end' => $end,
        'category' => null,
        'supplier' => null,
        'categoryName' => null,
        'supplierName' => null,
        'totalOrders' => $orders->count(),
        'grandTotal' => $grandTotal,
        'partIndex' => 1,
        'totalParts' => 1,
        'overallTotalOrders' => $orders->count(),
        'overallGrandTotal' => $grandTotal,
    ]);

    file_put_contents($pdfPath, $pdf->output());
    $this->info('PDF written: ' . $pdfPath);

    $this->comment('Smoke-test exports completed.');
})->describe('Generate smoke-test CSV and PDF reports into storage/app/reports');


Artisan::command('report:dispatch-async {start?} {end?}', function ($start = null, $end = null) {
    $this->comment('Dispatching async PDF export job...');
    $start = $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
    $end = $end ? Carbon::parse($end)->endOfDay() : Carbon::now()->endOfDay();

    $batchId = uniqid('sales_');
    \Illuminate\Support\Facades\Cache::put('reports:' . $batchId, ['status' => 'queued'], 3600);

    \App\Jobs\GenerateSalesPdfReport::dispatch($batchId, $start, $end, null, null, 200);

    $checkUrl = route('admin.sales.export.check', ['batch' => $batchId]);
    $downloadUrl = route('admin.sales.export.download', ['batch' => $batchId]);

    $this->info(json_encode(['status' => 'queued', 'batch' => $batchId, 'check_url' => $checkUrl, 'download_url' => $downloadUrl]));
})->describe('Dispatch async PDF export job and output batch/check/download URLs');
