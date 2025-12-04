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
use Illuminate\Support\Facades\Cache;

class ReportsController extends Controller
{
    /**
     * Show the main reports dashboard
     */
    public function index()
{
    // Total sales
    $total_sales = \App\Models\Order::sum('total_amount');

    // Total items sold
    $total_items_sold = \App\Models\OrderDetail::sum('quantity');

    // Low stock products
    $low_stock_count = \App\Models\Inventory::whereColumn('stock_quantity', '<=', 'reorder_level')->count();

    // Inventory products
    $inventory_products = \App\Models\Product::with('inventory')->get();

    // Latest inventory transactions
    $transactions = \App\Models\InventoryTransaction::with('items.product')
        ->latest()
        ->limit(10)
        ->get();

    // Sales chart data (last 30 days)
    $sales_chart = \App\Models\Order::select(
            DB::raw('DATE(order_date) as date'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('order_date', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $sales_chart_data = [
        'labels' => $sales_chart->pluck('date'),
        'values' => $sales_chart->pluck('total'),
    ];

    // Top products chart (last 30 days)
    $top_products_chart_data = \App\Models\OrderDetail::select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->whereHas('order', fn($q) => $q->where('order_date', '>=', now()->subDays(30)))
        ->groupBy('product_id')
        ->with('product')
        ->orderByDesc('total_sold')
        ->limit(10)
        ->get();

    $top_products_chart = [
        'labels' => $top_products_chart_data->map(fn($p) => $p->product->product_name ?? 'Unknown'),
        'values' => $top_products_chart_data->pluck('total_sold'),
    ];

    // Inventory chart data
    $inventory_chart = [
        'low' => \App\Models\Inventory::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
        'optimal' => \App\Models\Inventory::whereColumn('stock_quantity', '>', 'reorder_level')
                    ->whereColumn('stock_quantity', '<=', 'max_stock_level')->count(),
        'overstock' => \App\Models\Inventory::whereColumn('stock_quantity', '>', 'max_stock_level')->count(),
    ];

    return view('reports.dashboard', compact(
        'total_sales',
        'total_items_sold',
        'low_stock_count',
        'inventory_products',
        'transactions',
        'sales_chart_data',
        'top_products_chart',
        'inventory_chart'
    ));
}


    /**
     * SALES REPORT
     */
    public function sales(Request $request)
    {
        $start = $request->start_date ?? Carbon::now()->subMonth()->toDateString();
        $end   = $request->end_date ?? Carbon::now()->toDateString();

        // Query total sales by day
        $sales = Order::whereBetween('order_date', [$start, $end])
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Detailed orders
        $orders = Order::with('orderDetails.product')
            ->whereBetween('order_date', [$start, $end])
            ->orderBy('order_date', 'desc')
            ->paginate(20);

        return view('reports.sales', [
            'sales' => $sales,
            'orders' => $orders,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * INVENTORY REPORT
     */
    public function inventory(Request $request)
    {
        $products = Product::with(['category', 'supplier'])
            ->select('products.*')
            ->addSelect([
                'stock' => Inventory::select(DB::raw('SUM(quantity)'))
                    ->whereColumn('product_id', 'products.id')
            ])
            ->paginate(20);

        return view('reports.inventory', compact('products'));
    }

    /**
     * PRODUCT PERFORMANCE REPORT
     */
    public function productPerformance(Request $request)
    {
        $start = $request->start_date ?? Carbon::now()->subMonth()->toDateString();
        $end   = $request->end_date ?? Carbon::now()->toDateString();

        $topProducts = OrderDetail::with('product')
            ->whereHas('order', fn($q) => $q->whereBetween('order_date', [$start, $end]))
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(quantity * price) as revenue')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('reports.product_performance', [
            'topProducts' => $topProducts,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * INVENTORY MOVEMENTS (STOCK IN / STOCK OUT)
     */
    public function inventoryMovements(Request $request)
    {
        $start = $request->start_date ?? Carbon::now()->subMonth()->toDateString();
        $end   = $request->end_date ?? Carbon::now()->toDateString();

        $movements = Inventory::with('product')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reports.inventory_movements', [
            'movements' => $movements,
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * EXPORTS (CSV / EXCEL / PDF)
     * - Fill these later depending on your export library
     */
    public function exportSalesCSV(Request $request)
    {
        // TODO implement CSV export
        return back()->with('error', 'CSV export not implemented yet.');
    }

    public function exportSalesExcel(Request $request)
    {
        // TODO implement Excel export
        return back()->with('error', 'Excel export not implemented yet.');
    }

    public function exportSalesPDF(Request $request)
    {
        // TODO implement PDF export
        return back()->with('error', 'PDF export not implemented yet.');
    }
}
