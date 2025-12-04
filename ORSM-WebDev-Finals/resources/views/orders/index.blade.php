@extends('layouts.app')

@section('content')
{{-- TODO: Uncomment user permission check when implementing auth --}}
@php
    $isAdmin = auth()->check() && (method_exists(auth()->user(), 'isAdmin') ? auth()->user()->isAdmin() : (isset(auth()->user()->role) && auth()->user()->role === 'admin'));
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900" id="pageTitle">{{ $isAdmin ? 'All Orders' : 'My Orders' }}</h1>
                <p class="mt-2 text-gray-600" id="pageSubtitle">{{ $isAdmin ? 'Manage all customer orders' : 'Track and manage your orders' }}</p>
            </div>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-4">You haven't placed any orders yet.</p>
                <a href="{{ route('store') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Order ID</th>
                                @if($isAdmin)
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                                @endif
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Items</th>
                                <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr class="border-b hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ $order->order_id }}</td>
                                @if($isAdmin)
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->customer?->first_name ?? 'N/A' }} {{ $order->customer?->last_name ?? '' }}</td>
                                @endif
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @php $count = $order->details()->count(); @endphp
                                    {{ $count }} {{ $count == 1 ? 'item' : 'items' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">${{ number_format($order->details()->sum(\DB::raw('quantity * unit_price')), 2) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    @php($__status = ($order->status ?? $order->order_status ?? 'pending'))
                                    <div>
                                        <select
                                            name="status"
                                            class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-semibold {{ $isAdmin ? '' : 'opacity-60 cursor-not-allowed bg-gray-100 text-gray-700' }}"
                                            data-order-id="{{ $order->order_id }}"
                                            @if($isAdmin) onchange="updateOrderStatus(this)" @else disabled aria-disabled="true" title="Status cannot be changed by your account" @endif
                                        >
                                            <option value="pending" @selected($__status == 'pending')>Pending</option>
                                            <option value="completed" @selected($__status == 'completed')>Completed</option>
                                            <option value="cancelled" @selected($__status == 'cancelled')>Cancelled</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-center space-x-2 flex justify-center">
                                    <a href="{{ route('orders.show', $order->order_id) }}" class="text-blue-600 hover:text-blue-900 font-semibold transition-colors">
                                        View
                                    </a>
                                    @if($isAdmin)
                                        <button onclick="deleteOrder({{ $order->order_id }})" class="text-red-600 hover:text-red-900 font-semibold transition-colors">
                                            Delete
                                        </button>
                                    @else
                                        @php($__status = $order->status ?? $order->order_status ?? 'pending')
                                        @if($__status === 'pending')
                                            <button onclick="cancelOrder({{ $order->order_id }})" class="text-red-600 hover:text-red-900 font-semibold transition-colors">
                                                Cancel
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

@if($isAdmin)
<script>
function updateOrderStatus(selectElement) {
    const orderId = selectElement.dataset.orderId;
    const newStatus = selectElement.value;

    fetch(`/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            showToast('Status updated successfully', 'success');
        } else {
            showToast('Failed to update status', 'error');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating status', 'error');
        location.reload();
    });
}

function deleteOrder(orderId) {
    if (confirm('Are you sure you want to delete order #' + orderId + '? This action cannot be undone.')) {
        fetch(`/orders/${orderId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Order deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Failed to delete order', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting order', 'error');
        });
    }
}

function showToast(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed inset-x-0 top-0 flex justify-center pt-4 z-50`;
    alertDiv.innerHTML = `
        <div class="px-6 py-3 rounded-lg text-white font-semibold shadow-lg ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }">
            ${message}
        </div>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.3s ease-in-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}
</script>
@endif

@if(!$isAdmin)
<script>
function cancelOrder(orderId) {
    if (!confirm('Cancel order #' + orderId + '?')) return;
    fetch(`/orders/${orderId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: 'cancelled' })
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) {
            showToast('Order cancelled.', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Failed to cancel order.', 'error');
        }
    })
    .catch(() => showToast('Error cancelling order.', 'error'));
}

function showToast(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed inset-x-0 top-0 flex justify-center pt-4 z-50`;
    alertDiv.innerHTML = `
        <div class="px-6 py-3 rounded-lg text-white font-semibold shadow-lg ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        }">
            ${message}
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.3s ease-in-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}
</script>
@endif
@endsection
