<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Utilities\ZipHelper;

class GenerateSalesPdfReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batchId;
    public $start;
    public $end;
    public $category;
    public $supplier;
    public $chunkSize;

    public function __construct($batchId, $start, $end, $category = null, $supplier = null, $chunkSize = 200)
    {
        $this->batchId = $batchId;
        $this->start = $start;
        $this->end = $end;
        $this->category = $category;
        $this->supplier = $supplier;
        $this->chunkSize = $chunkSize;
    }

    public function handle()
    {
        $tmpRoot = storage_path('app/reports');
        if (!is_dir($tmpRoot)) mkdir($tmpRoot, 0755, true);
        $tmpDir = $tmpRoot . DIRECTORY_SEPARATOR . $this->batchId;
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        // Log detected schema for debugging queue worker environment
        $dateColExists = Schema::hasColumn('orders', 'order_date');
        $amountColExists = Schema::hasColumn('orders', 'total_amount');
        \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport: dateCol=' . ($dateColExists ? 'order_date' : 'created_at') . ', amountCol=' . ($amountColExists ? 'total_amount' : 'fallback'));

        $ordersQuery = \App\Models\Order::with('customer', 'details.product')
            ->when($this->category, function ($q) {
                // placeholder; will apply whereBetween on chosen date column below
            });

        // pick the date column (order_date if available, otherwise created_at)
        $dateCol = $dateColExists ? 'order_date' : 'created_at';

        $ordersQuery = $ordersQuery->whereBetween($dateCol, [$this->start, $this->end])
            ->when($this->category, function ($q) {
                $q->whereHas('details.product', function ($q2) {
                    $q2->where('category_id', $this->category);
                });
            })
            ->when($this->supplier, function ($q) {
                $q->whereHas('details.product', function ($q2) {
                    $q2->where('supplier_id', $this->supplier);
                });
            })
            ->orderBy($dateCol);

        $totalOrders = $ordersQuery->count();
        $hasAmountCol = $amountColExists;

        \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::handle start', [
            'batchId' => $this->batchId,
            'dateRange' => "{$this->start} to {$this->end}",
            'totalOrders' => $totalOrders,
            'chunkSize' => $this->chunkSize,
        ]);

        $part = 1;
        $buffer = [];
        $totalParts = (int) ceil($totalOrders / $this->chunkSize);
        $grandTotal = 0;

        // we'll compute grand total while streaming to avoid doing a SQL SUM that may reference a missing column
        $getOrderAmount = function ($o) use ($hasAmountCol) {
            if ($hasAmountCol) {
                return floatval($o->total_amount ?? 0);
            }
            // fallback: sum order detail subtotals
            return floatval(collect($o->details)->sum('subtotal') ?? 0);
        };

        foreach ($ordersQuery->cursor() as $order) {
            $grandTotal += $getOrderAmount($order);
            $buffer[] = $order;
            if (count($buffer) >= $this->chunkSize) {
                $ordersChunk = collect($buffer);
                $chunkTotal = $ordersChunk->sum(function ($o) use ($getOrderAmount) {
                    return $getOrderAmount($o);
                });
                $chunkCount = $ordersChunk->count();
                $pdf = Pdf::loadView('admin.exports.sales_report', [
                    'orders' => $ordersChunk,
                    'start' => $this->start,
                    'end' => $this->end,
                    'category' => $this->category,
                    'supplier' => $this->supplier,
                    'categoryName' => null,
                    'supplierName' => null,
                    'totalOrders' => $chunkCount,
                    'grandTotal' => $chunkTotal,
                    'partIndex' => $part,
                    'totalParts' => $totalParts,
                    'overallTotalOrders' => $totalOrders,
                    'overallGrandTotal' => $grandTotal,
                ]);
                $pdfPath = $tmpDir . DIRECTORY_SEPARATOR . ($this->batchId . '_part' . $part . '.pdf');
                file_put_contents($pdfPath, $pdf->output());
                \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::wrote PDF part', [
                    'batchId' => $this->batchId,
                    'part' => $part,
                    'filePath' => $pdfPath,
                    'fileSize' => filesize($pdfPath),
                ]);
                $part++;
                $buffer = [];
            }
        }

        if (count($buffer) > 0) {
            $ordersChunk = collect($buffer);
            $chunkTotal = $ordersChunk->sum(function ($o) use ($getOrderAmount) {
                return $getOrderAmount($o);
            });
            $chunkCount = $ordersChunk->count();
            $pdf = Pdf::loadView('admin.exports.sales_report', [
                'orders' => $ordersChunk,
                'start' => $this->start,
                'end' => $this->end,
                'category' => $this->category,
                'supplier' => $this->supplier,
                'categoryName' => null,
                'supplierName' => null,
                'totalOrders' => $chunkCount,
                'grandTotal' => $chunkTotal,
                'partIndex' => $part,
                'totalParts' => $totalParts,
                'overallTotalOrders' => $totalOrders,
                'overallGrandTotal' => $grandTotal,
            ]);
            $pdfPath = $tmpDir . DIRECTORY_SEPARATOR . ($this->batchId . '_part' . $part . '.pdf');
            file_put_contents($pdfPath, $pdf->output());
            \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::wrote final PDF part', [
                'batchId' => $this->batchId,
                'part' => $part,
                'filePath' => $pdfPath,
                'fileSize' => filesize($pdfPath),
            ]);
            $part++;
            $buffer = [];
        }

        \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::PDF generation complete', [
            'batchId' => $this->batchId,
            'totalPartsWritten' => $part - 1,
            'expectedParts' => $totalParts,
        ]);

        $zipPath = $tmpRoot . DIRECTORY_SEPARATOR . ($this->batchId . '.zip');
        $files = glob($tmpDir . DIRECTORY_SEPARATOR . '*.pdf');
        if (!ZipHelper::create($zipPath, $files)) {
            \Illuminate\Support\Facades\Log::error('GenerateSalesPdfReport::ZIP creation failed', [
                'batchId' => $this->batchId,
                'zipPath' => $zipPath,
                'fileCount' => count($files),
            ]);
            Cache::put('reports:' . $this->batchId, ['status' => 'failed'], 3600);
            return;
        }

        \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::ZIP created successfully', [
            'batchId' => $this->batchId,
            'zipPath' => $zipPath,
            'zipSize' => filesize($zipPath),
            'fileCount' => count($files),
        ]);

        // clean parts
        foreach ($files as $f) {
            @unlink($f);
        }
        @rmdir($tmpDir);

        \Illuminate\Support\Facades\Log::info('GenerateSalesPdfReport::completed', [
            'batchId' => $this->batchId,
            'zipPath' => $zipPath,
        ]);
        Cache::put('reports:' . $this->batchId, ['status' => 'ready', 'path' => $zipPath], 86400);
    }
}
