@extends('layouts.app')

@section('title', 'Sales Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Sales Dashboard</h1>

            <div class="flex space-x-2">
                <form method="get" action="{{ route('admin.sales') }}" class="flex items-center space-x-2">
                    <input type="date" name="start" value="{{ $start->toDateString() }}" class="px-2 py-1 border rounded" />
                    <input type="date" name="end" value="{{ $end->toDateString() }}" class="px-2 py-1 border rounded" />
                    <select name="category_id" class="px-2 py-1 border rounded">
                        <option value="">All categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->category_id }}" {{ request('category_id') == $c->category_id ? 'selected' : '' }}>{{ $c->category_name }}</option>
                        @endforeach
                    </select>
                    <select name="supplier_id" class="px-2 py-1 border rounded">
                        <option value="">All suppliers</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->supplier_id }}" {{ request('supplier_id') == $s->supplier_id ? 'selected' : '' }}>{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1 text-white bg-blue-600 rounded">Filter</button>
                </form>

                <a href="{{ route('admin.sales.export.csv', array_merge(request()->only(['start', 'end', 'category_id', 'supplier_id']))) }}"
                    class="px-3 py-1 text-white bg-green-600 rounded">Export CSV</a>
                <a id="syncPdfExport"
                    href="{{ route('admin.sales.export.pdf', array_merge(request()->only(['start', 'end', 'category_id', 'supplier_id']))) }}"
                    class="px-3 py-1 text-white bg-gray-800 rounded">Export PDF</a>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div class="col-span-2 p-4 bg-white rounded shadow">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-semibold">Sales</h2>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm">Period:</label>
                        <select id="periodSelect" class="px-2 py-1 border rounded">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        <a id="mainChartExport" href="#" class="px-3 py-1 text-sm text-white bg-yellow-500 rounded">Export
                            Chart CSV</a>
                    </div>
                </div>
                {{-- ADD THIS CONTAINER --}}
                <div class="h-64">
                    <canvas id="salesChart"></canvas> {{-- REMOVE height="120" --}}
                </div>
            </div>

            <div class="p-4 bg-white rounded shadow">
                <h2 class="mb-2 font-semibold">Low Stock Alerts</h2>
                @if($lowStock->isEmpty())
                    <div class="text-sm text-gray-600">No low stock items.</div>
                @else
                    <ul class="text-sm">
                        @foreach($lowStock as $p)
                            <li class="py-1 border-b">{{ $p->product_name }} — <strong>{{ $p->stock_quantity }}</strong> (reorder:
                                {{ $p->reorder_level }})</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6 mt-6">
    <div class="p-4 bg-white rounded shadow">
        <h2 class="mb-2 font-semibold">Sales by Category</h2>
        <div class="flex justify-end mb-2">
            <a id="categoryChartExport"
                href="{{ route('admin.sales.export.csv', array_merge(request()->only(['start', 'end', 'category_id', 'supplier_id']), ['aggregate' => 'category'])) }}"
                class="px-3 py-1 text-sm text-white bg-yellow-500 rounded">Export Category CSV</a>
        </div>
        
        {{-- Debug output (remove after fixing) --}}
        @if($byCategory->isEmpty())
            <div class="p-4 text-center text-red-500 bg-gray-100 rounded">
                No category data available for the selected period.
            </div>
        @endif
        
        {{-- Chart container with fixed height --}}
        <div class="h-96"> {{-- Increased from h-72 to h-96 (24rem) --}}
            <canvas id="categoryChart"></canvas>
        </div>
        
        {{-- Debug data display --}}
        <div class="hidden mt-4 text-xs text-gray-600">
            <div>Categories Count: {{ $byCategory->count() }}</div>
            <div>Labels: {{ json_encode($byCategory->pluck('category_name')->toArray()) }}</div>
            <div>Values: {{ json_encode($byCategory->pluck('total')->toArray()) }}</div>
        </div>
    </div>
    
    {{-- ... rest of your code ... --}}
</div>

        <div class="p-4 mt-6 bg-white rounded shadow">
            <h2 class="mb-2 font-semibold">Recent Orders</h2>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $o)
                        <tr class="border-b cursor-pointer hover:bg-gray-50 recent-order-row" data-order='@json($o)'>
                            <td class="py-2">{{ $o->order_id }}</td>
                            <td>{{ optional($o->order_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format($o->total_amount, 2) }}</td>
                            <td>{{ $o->details->sum('quantity') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">{{ $recentOrders->withQueryString()->links() }}</div>
        </div>

        <!-- Order details modal -->
        <div id="orderModal" class="fixed inset-0 items-center justify-center hidden bg-black bg-opacity-50">
            <div class="w-11/12 p-4 bg-white rounded shadow-lg md:w-2/3 lg:w-1/2">
                <div class="flex items-center justify-between mb-3">
                    <h3 id="modalTitle" class="text-lg font-semibold">Order Details</h3>
                    <button id="modalClose" class="text-gray-600">Close</button>
                </div>
                <div id="modalBody" class="text-sm">
                    <!-- populated by JS -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Changed to use Chart.js v3 instead of v4 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const datasets = {
                    daily: {
                        labels: {!! json_encode($daily->pluck('date')->toArray()) !!},
                        data: {!! json_encode($daily->pluck('total')->map(function ($v) {
            return (float) $v; })->toArray()) !!}
                    },
                    weekly: {
                        labels: {!! json_encode($weekly->pluck('period')->toArray()) !!},
                        data: {!! json_encode($weekly->pluck('total')->map(function ($v) {
            return (float) $v; })->toArray()) !!}
                    },
                    monthly: {
                        labels: {!! json_encode($monthly->pluck('period')->toArray()) !!},
                        data: {!! json_encode($monthly->pluck('total')->map(function ($v) {
            return (float) $v; })->toArray()) !!}
                    }
                };

                const categoryLabels = {!! json_encode($byCategory->pluck('category_name')->toArray()) !!};
                const categoryValues = {!! json_encode($byCategory->pluck('total')->map(function ($v) {
            return (float) $v; })->toArray()) !!};

                // Chart instances
                let salesChart, categoryChart;

                function initCharts() {
    console.log('Initializing charts...');
    
    // Debug: Check if data exists
    console.log('Category Data:', {
        labels: categoryLabels,
        values: categoryValues
    });
    
    // Check if we have data
    if (categoryLabels.length === 0 || categoryValues.length === 0) {
        console.warn('No category data available for chart');
        // Optionally show a message in the UI
        document.getElementById('categoryChart').parentElement.innerHTML += 
            '<div class="mt-4 text-center text-gray-500">No category data available</div>';
    }
    
    // Sales chart initialization (keep your existing code)
    const ctx = document.getElementById('salesChart').getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: datasets.daily.labels,
            datasets: [{
                label: 'Sales',
                data: datasets.daily.data,
                backgroundColor: 'rgba(59,130,246,0.7)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Category chart initialization - FIXED
    const catCanvas = document.getElementById('categoryChart');
    
    // Only create chart if canvas exists and has dimensions
    if (catCanvas) {
        const ctx2 = catCanvas.getContext('2d');
        
        // Check if canvas has width/height
        catCanvas.style.display = 'block';
        catCanvas.width = catCanvas.parentElement.clientWidth;
        catCanvas.height = catCanvas.parentElement.clientHeight;
        
        categoryChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: [
                        '#4F46E5', '#06B6D4', '#10B981', '#F59E0B',
                        '#EF4444', '#8B5CF6', '#EC4899', '#84CC16',
                        '#F97316', '#14B8A6', '#3B82F6', '#8B5A2B'
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += '₱' + context.parsed.toLocaleString('en-PH', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                return label;
                            }
                        }
                    }
                }
            }
        });
        
        console.log('Category chart created successfully');
    } else {
        console.error('Category chart canvas not found!');
    }
    
    // Force a resize after a short delay to ensure proper rendering
    setTimeout(() => {
        if (categoryChart) {
            categoryChart.resize();
        }
    }, 100);
}
                // Period select change handler
                document.getElementById('periodSelect').addEventListener('change', function (e) {
                    const period = e.target.value;
                    const d = datasets[period];
                    if (salesChart) {
                        salesChart.data.labels = d.labels;
                        salesChart.data.datasets[0].data = d.data;
                        salesChart.update();
                    }
                });

                // Update main chart export link (CSV aggregated by period)
                const mainExportLink = document.getElementById('mainChartExport');
                const baseExportUrl = '{{ route('admin.sales.export.csv') }}';

                function updateMainExport() {
                    const period = document.getElementById('periodSelect').value;
                    const params = new URLSearchParams(window.location.search);
                    params.set('aggregate', 'period');
                    params.set('period', period);
                    mainExportLink.href = baseExportUrl + '?' + params.toString();
                }

                // Initialize export link
                updateMainExport();
                document.getElementById('periodSelect').addEventListener('change', updateMainExport);

                // Order details modal handling
                function openModal() {
                    document.getElementById('orderModal').classList.remove('hidden');
                    document.getElementById('orderModal').classList.add('flex');
                }

                function closeModal() {
                    document.getElementById('orderModal').classList.add('hidden');
                    document.getElementById('orderModal').classList.remove('flex');
                    document.getElementById('modalBody').innerHTML = '';
                }

                document.getElementById('modalClose').addEventListener('click', closeModal);
                document.getElementById('orderModal').addEventListener('click', function (e) {
                    if (e.target === this) closeModal();
                });

                // Order row click handlers
                document.querySelectorAll('.recent-order-row').forEach(function (row) {
                    row.addEventListener('click', function () {
                        const data = JSON.parse(this.getAttribute('data-order'));
                        document.getElementById('modalTitle').textContent = 'Order #' + data.order_id;
                        let html = '';
                        html += '<div class="mb-2">Date: ' + (data.order_date || '') + '</div>';
                        html += '<div class="mb-2">Customer ID: ' + (data.customer_id || '') + '</div>';
                        html += '<table class="w-full text-sm border-collapse">';
                        html += '<thead><tr><th class="text-left">Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>';
                        html += '<tbody>';
                        (data.details || []).forEach(function (d) {
                            const prod = d.product || {};
                            html += '<tr class="border-t"><td>' + (prod.product_name || ('#' + (d.product_id || ''))) + '</td>';
                            html += '<td class="text-center">' + (d.quantity || '') + '</td>';
                            html += '<td class="text-right">' + (d.unit_price || '') + '</td>';
                            html += '<td class="text-right">' + (d.subtotal || '') + '</td></tr>';
                        });
                        html += '</tbody></table>';
                        html += '<div class="mt-3 font-bold">Total: ' + (data.total_amount || '') + '</div>';
                        document.getElementById('modalBody').innerHTML = html;
                        openModal();
                    });
                });

                // Initialize charts
                if (typeof Chart !== 'undefined') {
                    initCharts();
                } else {
                    console.error('Chart.js not loaded');
                }
            });
        </script>
    @endpush
@endsection