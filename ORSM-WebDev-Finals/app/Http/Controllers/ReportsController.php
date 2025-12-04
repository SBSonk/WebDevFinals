<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Utilities\ZipHelper;
use Illuminate\Support\Facades\Cache;

class ReportsController extends Controller
{
    /**
     * Show sales dashboard with simple charts and tables
     */
    public function index(Request $request)
    {
        $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $end = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfDay();
        $category = $request->input('category_id');
        $supplier = $request->input('supplier_id');

        // Determine which date/amount columns are available in this environment
        $dateColExists = Schema::hasColumn('orders', 'order_date');
        $amountColExists = Schema::hasColumn('orders', 'total_amount');
        $dateCol = $dateColExists ? 'order_date' : 'created_at';

        // Daily totals between selected dates
        if ($amountColExists) {
            $daily = Order::select(DB::raw("DATE({$dateCol}) as date"), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as orders_count'))
                ->whereBetween($dateCol, [$start, $end])
                ->when($category, function ($q, $category) {
                    $q->whereHas('details.product', function ($q2) use ($category) {
                        $q2->where('category_id', $category);
                    });
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->whereHas('details.product', function ($q2) use ($supplier) {
                        $q2->where('supplier_id', $supplier);
                    });
                })
                ->groupBy(DB::raw("DATE({$dateCol})"))
                ->orderBy('date')
                ->get();
        } else {
            // fallback: aggregate from order_details.subtotal when orders.total_amount is not available
            $daily = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                ->whereBetween("orders.{$dateCol}", [$start, $end])
                ->when($category, function ($q, $category) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.category_id', $category);
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.supplier_id', $supplier);
                })
                ->select(DB::raw("DATE(orders.{$dateCol}) as date"), DB::raw('SUM(order_details.subtotal) as total'), DB::raw('COUNT(DISTINCT orders.order_id) as orders_count'))
                ->groupBy(DB::raw("DATE(orders.{$dateCol})"))
                ->orderBy('date')
                ->get();
        }

        // Weekly totals
        if ($amountColExists) {
            $weekly = DB::table('orders')
                ->select(DB::raw("CONCAT(YEAR({$dateCol}),'-',LPAD(WEEK({$dateCol},1),2,'0')) as period"), DB::raw('SUM(total_amount) as total'))
                ->whereBetween($dateCol, [$start, $end])

                ->groupBy('period')
                ->orderBy('period')
                ->when($category, function ($q, $category) {
                    $q->join('order_details', 'orders.order_id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.category_id', $category);
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->join('order_details', 'orders.order_id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.supplier_id', $supplier);
                })
                ->get();
        } else {
            $weekly = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                ->select(DB::raw("CONCAT(YEAR(orders.{$dateCol}),'-',LPAD(WEEK(orders.{$dateCol},1),2,'0')) as period"), DB::raw('SUM(order_details.subtotal) as total'))
                ->whereBetween("orders.{$dateCol}", [$start, $end])
                ->when($category, function ($q, $category) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.category_id', $category);
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.supplier_id', $supplier);
                })
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        // Monthly totals
        if ($amountColExists) {
            $monthly = DB::table('orders')
                ->select(DB::raw("DATE_FORMAT({$dateCol}, '%Y-%m') as period"), DB::raw('SUM(total_amount) as total'))
                ->whereBetween($dateCol, [$start, $end])
                ->when($category, function ($q, $category) {
                    $q->join('order_details', 'orders.order_id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.category_id', $category);
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->join('order_details', 'orders.order_id', '=', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.supplier_id', $supplier);
                })
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        } else {
            $monthly = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                ->select(DB::raw("DATE_FORMAT(orders.{$dateCol}, '%Y-%m') as period"), DB::raw('SUM(order_details.subtotal) as total'))
                ->whereBetween("orders.{$dateCol}", [$start, $end])
                ->when($category, function ($q, $category) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.category_id', $category);
                })
                ->when($supplier, function ($q, $supplier) {
                    $q->join('products', 'order_details.product_id', '=', 'products.product_id')
                        ->where('products.supplier_id', $supplier);
                })
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        // Category breakdown (by subtotal)
        // determine categories primary key name (some schemas use category_id)
        $categoriesPk = Schema::hasColumn('categories', 'id') ? 'id' : (Schema::hasColumn('categories', 'category_id') ? 'category_id' : 'id');

        $byCategory = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.product_id')
            ->join('categories', 'products.category_id', '=', DB::raw("categories.{$categoriesPk}"))
            ->whereBetween("orders.{$dateCol}", [$start, $end])
            ->when($category, function ($q, $category) {
                $q->where('products.category_id', $category);
            })
            ->when($supplier, function ($q, $supplier) {
                $q->where('products.supplier_id', $supplier);
            })
            ->select('categories.category_name', DB::raw('SUM(order_details.subtotal) as total'))
            ->groupBy('categories.category_name')
            ->orderBy('total', 'desc')
            ->get();

        // Recent orders for table
        $recentOrders = Order::with('details.product')
            ->when($category, function ($q, $category) {
                $q->whereHas('details.product', function ($q2) use ($category) {
                    $q2->where('category_id', $category);
                });
            })
            ->when($supplier, function ($q, $supplier) {
                $q->whereHas('details.product', function ($q2) use ($supplier) {
                    $q2->where('supplier_id', $supplier);
                });
            })
            ->orderBy($dateCol, 'desc')
            ->paginate(15);

        $categories = Category::orderBy('category_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        // Low stock alerts: inventory records where stock_quantity <= reorder_level
        $lowStock = collect();
        if (Schema::hasTable('inventory')) {
            try {
                $lowStock = Inventory::with('product')
                    ->whereColumn('stock_quantity', '<=', 'reorder_level')
                    ->get();
            } catch (\Throwable $e) {
                // keep lowStock empty if inventory table/columns not present
                $lowStock = collect();
            }
        }

        // Use defensive calculation to avoid missing column errors
        $grandTotalOrders = Order::whereBetween($dateCol, [$start, $end])
            ->when($category, function ($q, $category) {
                $q->whereHas('details.product', function ($q2) use ($category) {
                    $q2->where('category_id', $category);
                });
            })
            ->when($supplier, function ($q, $supplier) {
                $q->whereHas('details.product', function ($q2) use ($supplier) {
                    $q2->where('supplier_id', $supplier);
                });
            })
            ->get();

        $getOrderAmount = function ($o) use ($amountColExists) {
            if ($amountColExists) return floatval($o->total_amount ?? 0);
            return floatval(collect($o->details)->sum('subtotal') ?? 0);
        };

        $grandTotal = $grandTotalOrders->sum(function ($o) use ($getOrderAmount) {
            return $getOrderAmount($o);
        });

        return view('admin.sales_dashboard', compact('daily', 'weekly', 'monthly', 'byCategory', 'recentOrders', 'categories', 'suppliers', 'lowStock', 'grandTotal', 'start', 'end', 'category', 'supplier'));
    }

    // CSV export handler
    public function exportCsv(Request $request)
    {
        $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $end = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfDay();
        $category = $request->input('category_id');
        $supplier = $request->input('supplier_id');
        $aggregate = $request->input('aggregate');
        $period = $request->input('period', 'daily');

        $makeSafe = function ($str) {
            if (empty($str)) return '';
            $s = strtolower($str);
            $s = preg_replace('/\s+/', '_', $s);
            $s = preg_replace('/[^a-z0-9_\-]/', '', $s);
            return $s;
        };
        $catSafe = '';
        $supSafe = '';
        if ($category) {
            $c = Category::find($category);
            $catSafe = $makeSafe($c ? $c->category_name : '');
        }
        if ($supplier) {
            $s = Supplier::find($supplier);
            $supSafe = $makeSafe($s ? $s->supplier_name : '');
        }

        if ($aggregate === 'period') {
            if ($period === 'weekly') {
                $rows = DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                    ->join('products', 'order_details.product_id', '=', 'products.product_id')
                    ->whereBetween('orders.order_date', [$start, $end])
                    ->when($category, function ($q, $category) {
                        $q->where('products.category_id', $category);
                    })
                    ->when($supplier, function ($q, $supplier) {
                        $q->where('products.supplier_id', $supplier);
                    })
                    ->select(DB::raw("CONCAT(YEAR(orders.order_date),'-',LPAD(WEEK(orders.order_date,1),2,'0')) as period"), DB::raw('SUM(order_details.subtotal) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } elseif ($period === 'monthly') {
                $rows = DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                    ->join('products', 'order_details.product_id', '=', 'products.product_id')
                    ->whereBetween('orders.order_date', [$start, $end])
                    ->when($category, function ($q, $category) {
                        $q->where('products.category_id', $category);
                    })
                    ->when($supplier, function ($q, $supplier) {
                        $q->where('products.supplier_id', $supplier);
                    })
                    ->select(DB::raw("DATE_FORMAT(orders.order_date, '%Y-%m') as period"), DB::raw('SUM(order_details.subtotal) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            } else {
                $rows = DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
                    ->join('products', 'order_details.product_id', '=', 'products.product_id')
                    ->whereBetween('orders.order_date', [$start, $end])
                    ->when($category, function ($q, $category) {
                        $q->where('products.category_id', $category);
                    })
                    ->when($supplier, function ($q, $supplier) {
                        $q->where('products.supplier_id', $supplier);
                    })
                    ->select(DB::raw("DATE(orders.order_date) as period"), DB::raw('SUM(order_details.subtotal) as total'))
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
            }

            $fileName = 'sales_by_period_' . $period . '_' . $start->format('Ymd') . '_to_' . $end->format('Ymd');
            if ($catSafe) $fileName .= '_cat_' . $catSafe;
            if ($supSafe) $fileName .= '_sup_' . $supSafe;
            $fileName .= '.csv';

            $response = new StreamedResponse(function () use ($rows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Period', 'Total']);
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->period, $r->total]);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

            return $response;
        }

        // Default: stream order rows via cursor to avoid memory spikes
        $query = Order::with('customer')
            ->whereBetween('order_date', [$start, $end])
            ->when($category, function ($q, $category) {
                $q->whereHas('details.product', function ($q2) use ($category) {
                    $q2->where('category_id', $category);
                });
            })
            ->when($supplier, function ($q, $supplier) {
                $q->whereHas('details.product', function ($q2) use ($supplier) {
                    $q2->where('supplier_id', $supplier);
                });
            })
            ->orderBy('order_date');

        $fileName = 'sales_' . $start->format('Ymd') . '_to_' . $end->format('Ymd');
        if ($catSafe) $fileName .= '_cat_' . $catSafe;
        elseif ($category) $fileName .= '_cat' . $category;
        if ($supSafe) $fileName .= '_sup_' . $supSafe;
        elseif ($supplier) $fileName .= '_sup' . $supplier;
        $fileName .= '.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Order Date', 'Customer ID', 'Total Amount', 'Payment Status', 'Payment Method']);

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
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);

        return $response;
    }

    /**
     * Export orders to PDF (requires barryvdh/laravel-dompdf)
     */
    public function exportPdf(Request $request)
    {
        $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $end = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfDay();

        $category = $request->input('category_id');
        $supplier = $request->input('supplier_id');

        // Build a query so we can stream results in chunks for large exports
        $ordersQuery = Order::with('customer', 'details.product')
            ->whereBetween('order_date', [$start, $end])
            ->when($category, function ($q, $category) {
                $q->whereHas('details.product', function ($q2) use ($category) {
                    $q2->where('category_id', $category);
                });
            })
            ->when($supplier, function ($q, $supplier) {
                $q->whereHas('details.product', function ($q2) use ($supplier) {
                    $q2->where('supplier_id', $supplier);
                });
            })
            ->orderBy('order_date');

        // Count matching orders (efficient enough)
        $totalOrders = $ordersQuery->count();

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            abort(500, 'PDF export requires the package barryvdh/laravel-dompdf. Run: composer require barryvdh/laravel-dompdf');
        }

        $categoryName = null;
        $supplierName = null;
        if ($category) {
            $cat = Category::find($category);
            $categoryName = $cat ? $cat->category_name : null;
        }
        if ($supplier) {
            $sup = Supplier::find($supplier);
            $supplierName = $sup ? $sup->supplier_name : null;
        }

        // We'll support chunked PDF generation for large exports to avoid memory issues.
        $chunkSize = 200;

        // compute grand total and keep totalOrders from earlier
        $grandTotal = $ordersQuery->sum('total_amount');

        // helper to sanitize filename parts
        $makeSafe = function ($str) {
            if (empty($str)) return '';
            $s = strtolower($str);
            $s = preg_replace('/\s+/', '_', $s);
            $s = preg_replace('/[^a-z0-9_\-]/', '', $s);
            return $s;
        };
        $catSafe = $makeSafe($categoryName);
        $supSafe = $makeSafe($supplierName);

        $baseFilename = 'sales_' . $start->format('Ymd') . '_to_' . $end->format('Ymd');
        if ($catSafe) $baseFilename .= '_cat_' . $catSafe;
        elseif ($category) $baseFilename .= '_cat' . $category;
        if ($supSafe) $baseFilename .= '_sup_' . $supSafe;
        elseif ($supplier) $baseFilename .= '_sup' . $supplier;

        if ($totalOrders <= $chunkSize) {
            // small export: render a single PDF
            $orders = $ordersQuery->get();
            $totalOrders = $orders->count();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.exports.sales_report', [
                'orders' => $orders,
                'start' => $start,
                'end' => $end,
                'category' => $category,
                'supplier' => $supplier,
                'categoryName' => $categoryName,
                'supplierName' => $supplierName,
                'totalOrders' => $totalOrders,
                'grandTotal' => $grandTotal,
                'partIndex' => 1,
                'totalParts' => 1,
                'overallTotalOrders' => $totalOrders,
                'overallGrandTotal' => $grandTotal,
            ]);

            return $pdf->download($baseFilename . '.pdf');
        }

        // Large export: create multiple PDFs in a temp dir and zip them
        $tmpRoot = storage_path('app/reports');
        if (!is_dir($tmpRoot)) {
            mkdir($tmpRoot, 0755, true);
        }
        $batchId = uniqid('sales_');
        $tmpDir = $tmpRoot . DIRECTORY_SEPARATOR . $batchId;
        mkdir($tmpDir, 0755);

        $totalParts = (int) ceil($totalOrders / $chunkSize);
        $part = 1;
        $buffer = [];
        foreach ($ordersQuery->cursor() as $order) {
            $buffer[] = $order;
            if (count($buffer) >= $chunkSize) {
                $ordersChunk = collect($buffer);
                $chunkTotal = $ordersChunk->sum('total_amount');
                $chunkCount = $ordersChunk->count();
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.exports.sales_report', [
                    'orders' => $ordersChunk,
                    'start' => $start,
                    'end' => $end,
                    'category' => $category,
                    'supplier' => $supplier,
                    'categoryName' => $categoryName,
                    'supplierName' => $supplierName,
                    'totalOrders' => $chunkCount,
                    'grandTotal' => $chunkTotal,
                    'partIndex' => $part,
                    'totalParts' => $totalParts,
                    'overallTotalOrders' => $totalOrders,
                    'overallGrandTotal' => $grandTotal,
                ]);
                $pdfPath = $tmpDir . DIRECTORY_SEPARATOR . ($batchId . '_part' . $part . '.pdf');
                file_put_contents($pdfPath, $pdf->output());
                $part++;
                $buffer = [];
            }
        }

        // For large exports we can optionally dispatch an async job if requested
        if ($request->input('async')) {
            $batchId = $batchId ?? uniqid('sales_');
            // set cache marker
            \Illuminate\Support\Facades\Cache::put('reports:' . $batchId, ['status' => 'queued'], 3600);
            // dispatch job
            \App\Jobs\GenerateSalesPdfReport::dispatch($batchId, $start, $end, $category, $supplier, $chunkSize);
            return response()->json([
                'status' => 'queued',
                'batch' => $batchId,
                'check_url' => route('admin.sales.export.check', ['batch' => $batchId]),
                'download_url' => route('admin.sales.export.download', ['batch' => $batchId])
            ]);
        }

        // create zip synchronously (fallback)
        $zipPath = $tmpRoot . DIRECTORY_SEPARATOR . ($batchId . '.zip');
        $files = glob($tmpDir . DIRECTORY_SEPARATOR . '*.pdf');
        if (!ZipHelper::create($zipPath, $files)) {
            abort(500, 'Unable to create zip archive for export');
        }

        // remove temporary pdf files and directory (keep zip)
        foreach ($files as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
        @rmdir($tmpDir);

        // stream the zip and delete it after send
        return response()->download($zipPath, $baseFilename . '.zip')->deleteFileAfterSend(true);
    }

    /**
     * Check async export status (returns cached status for a batch)
     */
    public function exportCheck($batch)
    {
        $key = 'reports:' . $batch;
        $data = Cache::get($key);
        if (!$data) {
            return response()->json(['status' => 'unknown'], 404);
        }
        return response()->json($data);
    }

    /**
     * Download a finished export ZIP by batch id
     */
    public function exportDownload($batch)
    {
        $key = 'reports:' . $batch;
        $data = Cache::get($key);
        if (empty($data) || empty($data['status']) || $data['status'] !== 'ready' || empty($data['path'])) {
            abort(404, 'Export not ready');
        }
        $path = $data['path'];
        if (!file_exists($path)) {
            abort(404, 'Export file not found');
        }
        return response()->download($path, basename($path));
    }
}
