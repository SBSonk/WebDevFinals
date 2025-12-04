@extends('layouts.app')

@section('title', 'Sales Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Sales Dashboard</h1>

        <div class="flex space-x-2">
            <form method="get" action="{{ route('admin.sales') }}" class="flex space-x-2 items-center">
                <input type="date" name="start" value="{{ $start->toDateString() }}" class="border rounded px-2 py-1" />
                <input type="date" name="end" value="{{ $end->toDateString() }}" class="border rounded px-2 py-1" />
                <select name="category_id" class="border rounded px-2 py-1">
                    <option value="">All categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->category_id }}" {{ request('category_id') == $c->category_id ? 'selected' : '' }}>{{ $c->category_name }}</option>
                    @endforeach
                </select>
                <select name="supplier_id" class="border rounded px-2 py-1">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ request('supplier_id') == $s->supplier_id ? 'selected' : '' }}>{{ $s->supplier_name }}</option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
            </form>

            <a href="{{ route('admin.sales.export.csv', array_merge(request()->only(['start','end','category_id','supplier_id']))) }}" class="bg-green-600 text-white px-3 py-1 rounded">Export CSV</a>
            <a id="syncPdfExport" href="{{ route('admin.sales.export.pdf', array_merge(request()->only(['start','end','category_id','supplier_id']))) }}" class="bg-gray-800 text-white px-3 py-1 rounded">Export PDF</a>
            <button id="asyncPdfExport" class="bg-indigo-600 text-white px-3 py-1 rounded">Async PDF</button>
            <div id="asyncExportStatus" class="ml-3 text-sm"></div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 bg-white p-4 rounded shadow">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-semibold">Sales</h2>
                <div class="flex items-center space-x-2">
                    <label class="text-sm">Period:</label>
                    <select id="periodSelect" class="border rounded px-2 py-1">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                    <a id="mainChartExport" href="#" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Export Chart CSV</a>
                </div>
            </div>
            <canvas id="salesChart" height="120"></canvas>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Low Stock Alerts</h2>
            @if($lowStock->isEmpty())
                <div class="text-sm text-gray-600">No low stock items.</div>
            @else
                <ul class="text-sm">
                    @foreach($lowStock as $p)
                        <li class="py-1 border-b">{{ $p->product_name }} — <strong>{{ $p->stock_quantity }}</strong> (reorder: {{ $p->reorder_level }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 mt-6">
        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Sales by Category</h2>
            <div class="flex justify-end mb-2">
                <a id="categoryChartExport" href="{{ route('admin.sales.export.csv', array_merge(request()->only(['start','end','category_id','supplier_id']), ['aggregate' => 'category'])) }}" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">Export Category CSV</a>
            </div>
            <canvas id="categoryChart" height="160"></canvas>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h2 class="font-semibold mb-2">Totals</h2>
            <div class="text-lg font-bold">Total Orders: {{ $recentOrders->count() }}</div>
            <div class="text-lg font-bold">Total Sales (shown range): ₱{{ number_format(collect($daily->pluck('total'))->sum(),2) }}</div>
        </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Recent Orders</h2>
        <table class="w-full text-left text-sm">
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
                    <tr class="border-b hover:bg-gray-50 cursor-pointer recent-order-row" data-order='@json($o)'>
                        <td class="py-2">{{ $o->order_id }}</td>
                        <td>{{ optional($o->order_date)->format('Y-m-d H:i') }}</td>
                        <td>{{ number_format($o->total_amount,2) }}</td>
                        <td>{{ $o->details->sum('quantity') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $recentOrders->withQueryString()->links() }}</div>
    </div>

    <!-- Order details modal -->
    <div id="orderModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded shadow-lg w-11/12 md:w-2/3 lg:w-1/2 p-4">
            <div class="flex justify-between items-center mb-3">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
    const datasets = {
        daily: {
            labels: {!! json_encode($daily->pluck('date')->toArray()) !!},
            data: {!! json_encode($daily->pluck('total')->map(function($v){ return (float) $v; })->toArray()) !!}
        },
        weekly: {
            labels: {!! json_encode($weekly->pluck('period')->toArray()) !!},
            data: {!! json_encode($weekly->pluck('total')->map(function($v){ return (float) $v; })->toArray()) !!}
        },
        monthly: {
            labels: {!! json_encode($monthly->pluck('period')->toArray()) !!},
            data: {!! json_encode($monthly->pluck('total')->map(function($v){ return (float) $v; })->toArray()) !!}
        }
    };

    const categoryLabels = {!! json_encode($byCategory->pluck('category_name')->toArray()) !!};
    const categoryValues = {!! json_encode($byCategory->pluck('total')->map(function($v){ return (float) $v; })->toArray()) !!};

    function initCharts() {
        console.log('Chart global typeof:', typeof Chart);
        if (typeof Chart === 'undefined') {
            throw new Error('Chart.js not available');
        }

        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: datasets.daily.labels,
            datasets: [{ label: 'Sales', data: datasets.daily.data, backgroundColor: 'rgba(59,130,246,0.7)' }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { ticks: { maxRotation: 45, minRotation: 0 } }, y: { beginAtZero: true } } }
    });

        // Expose for debugging in browser console under unique names
        window.__appDatasets = datasets;
        window.__salesChartInstance = salesChart;
        console.log('salesChart created', { labels: datasets.daily.labels.length, data: datasets.daily.data.length });
    }

    document.getElementById('periodSelect').addEventListener('change', function(e){
        const period = e.target.value;
        const d = datasets[period];
        salesChart.data.labels = d.labels;
        salesChart.data.datasets[0].data = d.data;
        salesChart.update();
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
    // initialize
    updateMainExport();
    document.getElementById('periodSelect').addEventListener('change', updateMainExport);

    try {
        // Category pie chart (uses the existing canvas in the markup)
        const catCanvas = document.getElementById('categoryChart');
        const categoryChart = new Chart(catCanvas.getContext('2d'), {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{ data: categoryValues, backgroundColor: [
                '#4F46E5','#06B6D4','#10B981','#F59E0B','#EF4444','#8B5CF6','#06B6D4'
            ] }]
        },
        options: { responsive: true }
    });

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
    document.getElementById('orderModal').addEventListener('click', function(e){ if (e.target === this) closeModal(); });

    document.querySelectorAll('.recent-order-row').forEach(function(row){
        row.addEventListener('click', function(){
            const data = JSON.parse(this.getAttribute('data-order'));
            document.getElementById('modalTitle').textContent = 'Order #' + data.order_id;
            let html = '';
            html += '<div class="mb-2">Date: ' + (data.order_date || '') + '</div>';
            html += '<div class="mb-2">Customer ID: ' + (data.customer_id || '') + '</div>';
            html += '<table class="w-full text-sm border-collapse">';
            html += '<thead><tr><th class="text-left">Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr></thead>';
            html += '<tbody>';
            (data.details || []).forEach(function(d){
                const prod = d.product || {};
                html += '<tr class="border-t"><td>' + (prod.product_name || ('#' + (d.product_id||''))) + '</td>';
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

    // Async PDF export: request async export and poll status
    (function(){
        const asyncBtn = document.getElementById('asyncPdfExport');
        const statusDiv = document.getElementById('asyncExportStatus');
        if (!asyncBtn) return;

        asyncBtn.addEventListener('click', function(){
            asyncBtn.disabled = true;
            statusDiv.textContent = 'Queueing export...';

            const params = new URLSearchParams(window.location.search);
            params.set('async', '1');
            const url = '{{ route('admin.sales.export.pdf') }}' + '?' + params.toString();

            fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.batch) {
                        statusDiv.textContent = 'Failed to queue export';
                        asyncBtn.disabled = false;
                        return;
                    }
                    statusDiv.innerHTML = 'Queued. Batch: <code>' + data.batch + '</code>. Waiting...';
                    pollStatus(data.check_url, data.download_url, data.batch, statusDiv, asyncBtn);
                    window.__categoryChartInstance = categoryChart;
                    console.log('categoryChart created', { labels: categoryLabels.length, data: categoryValues.length });
                } catch (err) {
                    console.error('categoryChart init error', err);
                }

                // Ensure Chart.js is loaded; dynamically load if missing
                if (typeof Chart === 'undefined') {
                    console.log('Chart.js not found, loading CDN...');
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
                    s.onload = function() {
                        try {
                            initCharts();
                        } catch (e) {
                            console.error('initCharts after load failed', e);
                        }
                    };
                    s.onerror = function(e) {
                        console.error('Failed to load Chart.js', e);
                    };
                    document.head.appendChild(s);
                } else {
                    try {
                        initCharts();
                    } catch (e) {
                        console.error('initCharts failed', e);
                    }
                }

                }); // DOMContentLoaded
                .catch(err => {
                    console.error(err);
                    statusDiv.textContent = 'Error queuing export';
                    asyncBtn.disabled = false;
                });
        });

        function pollStatus(checkUrl, downloadUrl, batch, statusDiv, button) {
            let attempts = 0;
            const intv = setInterval(function(){
                attempts++;
                fetch(checkUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(d => {
                        if (!d || !d.status) return;
                        if (d.status === 'ready') {
                            clearInterval(intv);
                            statusDiv.innerHTML = '<a href="' + downloadUrl + '" class="bg-green-600 text-white px-2 py-1 rounded">Download ZIP</a> <span class="ml-2 text-xs text-gray-600">(batch ' + batch + ')</span>';
                            button.disabled = false;
                        } else if (d.status === 'failed') {
                            clearInterval(intv);
                            statusDiv.textContent = 'Export failed';
                            button.disabled = false;
                        } else {
                            // still queued/processing
                            statusDiv.textContent = 'Processing... (attempt ' + attempts + ')';
                        }
                    })
                    .catch(err => {
                        console.error('poll error', err);
                        // keep trying; after many attempts give up
                        if (attempts > 120) {
                            clearInterval(intv);
                            statusDiv.textContent = 'Timed out waiting for export';
                            button.disabled = false;
                        }
                    });
            }, 2000);
        }
    })();
</script>
@endpush

@endsection
