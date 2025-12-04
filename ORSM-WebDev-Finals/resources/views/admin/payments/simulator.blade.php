@extends('layouts.app')

@section('title', 'Payment Simulator')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Payment Simulator</h1>
        <p class="text-gray-600 mt-2">Simulate payment methods and statuses for testing purposes.</p>
    </div>

    <!-- Payment Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-600">Total Orders</div>
            <div class="text-2xl font-bold" id="totalOrders">—</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-600">Completed</div>
            <div class="text-2xl font-bold text-green-600" id="completedPayments">—</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-600">Pending</div>
            <div class="text-2xl font-bold text-yellow-600" id="pendingPayments">—</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-600">Failed</div>
            <div class="text-2xl font-bold text-red-600" id="failedPayments">—</div>
        </div>
    </div>

    <!-- Bulk Action Controls -->
    <div class="bg-white p-4 rounded shadow mb-6">
        <h2 class="font-semibold mb-4">Bulk Payment Actions</h2>
        <div class="flex gap-3 items-end">
            <div>
                <label class="block text-sm font-medium mb-1">Target Status</label>
                <select id="bulkStatus" class="border rounded px-3 py-2">
                    <option value="">— Select —</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Payment Method</label>
                <select id="bulkMethod" class="border rounded px-3 py-2">
                    <option value="">— Keep Current —</option>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Online Banking">Online Banking</option>
                </select>
            </div>
            <button id="bulkApplyBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Apply to Selected</button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="selectAll" />
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Order ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Customer</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Total</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Payment Method</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr class="border-b hover:bg-gray-50 order-row" data-order-id="{{ $order->order_id }}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="order-checkbox" value="{{ $order->order_id }}" />
                        </td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $order->order_id }}</td>
                        <td class="px-4 py-3 text-sm">{{ optional($order->order_date)->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->customer->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">₱{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $order->payment_method ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if($order->payment_status === 'completed') bg-green-100 text-green-800
                                @elseif($order->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->payment_status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($order->payment_status ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm flex gap-2">
                            <button class="action-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs" data-action="cod" title="Simulate COD">COD</button>
                            <button class="action-btn bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs" data-action="success" title="Simulate Success">✓</button>
                            <button class="action-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs" data-action="failed" title="Simulate Failed">✗</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load payment stats
    loadPaymentStats();

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Individual order actions
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const row = this.closest('.order-row');
            const orderId = row.dataset.orderId;
            performAction(orderId, action);
        });
    });

    // Bulk apply
    document.getElementById('bulkApplyBtn').addEventListener('click', function() {
        const status = document.getElementById('bulkStatus').value;
        if (!status) {
            alert('Please select a status');
            return;
        }

        const method = document.getElementById('bulkMethod').value || null;
        const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked'))
            .map(cb => parseInt(cb.value));

        if (selectedIds.length === 0) {
            alert('Please select at least one order');
            return;
        }

        bulkUpdate(selectedIds, status, method);
    });
});

function loadPaymentStats() {
    fetch('{{ route("admin.payments.stats") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('totalOrders').textContent = data.total_orders;
        document.getElementById('completedPayments').textContent = data.completed_payments;
        document.getElementById('pendingPayments').textContent = data.pending_payments;
        document.getElementById('failedPayments').textContent = data.failed_payments;
    })
    .catch(err => console.error('Error loading stats:', err));
}

function performAction(orderId, action) {
    const methods = {
        'cod': '{{ route("admin.payments.simulate-cod", ":id") }}'.replace(':id', orderId),
        'success': '{{ route("admin.payments.simulate-success", ":id") }}'.replace(':id', orderId),
        'failed': '{{ route("admin.payments.simulate-failed", ":id") }}'.replace(':id', orderId),
    };

    fetch(methods[action], {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success' || data.status === 'error') {
            console.log(data.message);
            location.reload(); // Reload to see changes
        }
    })
    .catch(err => console.error('Error performing action:', err));
}

function bulkUpdate(orderIds, status, method) {
    fetch('{{ route("admin.payments.bulk-update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_ids: orderIds,
            payment_status: status,
            payment_method: method
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        }
    })
    .catch(err => console.error('Error updating payments:', err));
}
</script>
@endsection
