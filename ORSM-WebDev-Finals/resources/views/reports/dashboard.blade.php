@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <h1 class="mb-6 text-3xl font-bold text-gray-800">Reports Dashboard</h1>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Total Sales -->
        <div class="p-6 transition-shadow duration-200 bg-white border-l-4 border-blue-500 shadow-md rounded-xl hover:shadow-lg">
            <h2 class="text-sm font-medium tracking-wide text-gray-500 uppercase">Total Sales</h2>
            <div class="mt-2 text-3xl font-bold text-gray-800">₱{{ number_format($total_sales, 2) }}</div>
        </div>

        <!-- Total Items Sold -->
        <div class="p-6 transition-shadow duration-200 bg-white border-l-4 border-green-500 shadow-md rounded-xl hover:shadow-lg">
            <h2 class="text-sm font-medium tracking-wide text-gray-500 uppercase">Total Items Sold</h2>
            <div class="mt-2 text-3xl font-bold text-gray-800">{{ $total_items_sold }}</div>
        </div>

        <!-- Low Stock Products -->
        <div class="p-6 transition-shadow duration-200 bg-white border-l-4 border-red-500 shadow-md rounded-xl hover:shadow-lg">
            <h2 class="text-sm font-medium tracking-wide text-gray-500 uppercase">Low Stock Products</h2>
            <div class="mt-2 text-3xl font-bold text-gray-800">{{ $low_stock_count }}</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
        <!-- Sales Chart -->
        <div class="p-6 transition-shadow duration-200 bg-white shadow-md rounded-xl hover:shadow-lg">
            <h2 class="mb-4 text-lg font-semibold text-gray-700">Sales (Last 30 Days)</h2>
            <canvas id="salesChart" class="w-full h-64"></canvas>
        </div>

        <!-- Top Products Chart -->
        <div class="p-6 transition-shadow duration-200 bg-white shadow-md rounded-xl hover:shadow-lg">
            <h2 class="mb-4 text-lg font-semibold text-gray-700">Top Products (Last 30 Days)</h2>
            <canvas id="topProductsChart" class="w-full h-64"></canvas>
        </div>
    </div>

    <!-- Inventory Status -->
    <div class="p-6 mt-6 transition-shadow duration-200 bg-white shadow-md rounded-xl hover:shadow-lg">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Inventory Status</h2>
        <div class="grid grid-cols-1 gap-4 text-center md:grid-cols-3">
            <div class="p-4 rounded-lg bg-red-50">
                <span class="text-2xl font-bold text-red-600">{{ $inventory_chart['low'] }}</span>
                <p class="mt-1 text-gray-500">Low</p>
            </div>
            <div class="p-4 rounded-lg bg-green-50">
                <span class="text-2xl font-bold text-green-600">{{ $inventory_chart['optimal'] }}</span>
                <p class="mt-1 text-gray-500">Optimal</p>
            </div>
            <div class="p-4 rounded-lg bg-blue-50">
                <span class="text-2xl font-bold text-blue-600">{{ $inventory_chart['overstock'] }}</span>
                <p class="mt-1 text-gray-500">Overstock</p>
            </div>
        </div>
    </div>

    <!-- Latest Inventory Transactions -->
    <div class="p-6 mt-6 transition-shadow duration-200 bg-white shadow-md rounded-xl hover:shadow-lg">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Latest Inventory Transactions</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 tracking-wider text-gray-600 uppercase border">Date</th>
                        <th class="p-3 tracking-wider text-gray-600 uppercase border">Transaction Type</th>
                        <th class="p-3 tracking-wider text-gray-600 uppercase border">Product</th>
                        <th class="p-3 tracking-wider text-gray-600 uppercase border">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        @foreach ($transaction->items as $item)
                            <tr class="transition-colors hover:bg-gray-50">
                                <td class="p-2 border">{{ $transaction->created_at->format('Y-m-d') }}</td>
                                <td class="p-2 border">{{ $transaction->type }}</td>
                                <td class="p-2 border">{{ $item->product->product_name ?? 'Unknown' }}</td>
                                <td class="p-2 border">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Scripts -->
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: @json($sales_chart_data['labels']),
            datasets: [{
                label: 'Sales',
                data: @json($sales_chart_data['values']),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Amount (₱)' }, beginAtZero: true }
            }
        }
    });

    const topProductsChart = new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: @json($top_products_chart['labels']),
            datasets: [{
                label: 'Units Sold',
                data: @json($top_products_chart['values']),
                backgroundColor: 'rgba(16, 185, 129, 0.7)'
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { title: { display: true, text: 'Units Sold' }, beginAtZero: true },
                y: { title: { display: true, text: 'Product' } }
            }
        }
    });
</script>
@endsection
