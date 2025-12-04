@extends('layouts.app')

@section('content')
{{-- TODO: Uncomment user permission check when implementing auth --}}
@php
    // $isAdmin = Auth::check() && Auth::user()->role === 'admin';
    $isAdmin = true; // Default to admin view for testing
@endphp

{{-- TODO: REMOVE DEBUG BUTTONS - Temporary debug buttons for testing admin/customer views --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 bg-yellow-50 border-b-2 border-yellow-300 mb-4">
    <p class="text-sm font-semibold text-yellow-800 mb-2">DEBUG: Toggle View (Remove in production)</p>
    <div class="flex gap-2">
        <button onclick="toggleView('admin')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
            Show Admin View
        </button>
        <button onclick="toggleView('customer')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
            Show Customer View
        </button>
        <span class="px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg">
            Current: <span id="viewIndicator">{{ $isAdmin ? 'Admin' : 'Customer' }}</span>
        </span>
    </div>
</div>

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
                                <th data-admin-only class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
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
                                <td data-admin-only class="px-6 py-4 text-sm text-gray-600">{{ $order->customer?->first_name ?? 'N/A' }} {{ $order->customer?->last_name ?? '' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @php $count = $order->details()->count(); @endphp
                                    {{ $count }} {{ $count == 1 ? 'item' : 'items' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">${{ number_format($order->details()->sum(\DB::raw('quantity * unit_price')), 2) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <div data-admin-only style="display:">
                                        <select name="status" class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-semibold" data-order-id="{{ $order->order_id }}" onchange="updateOrderStatus(this)">
                                            <option value="pending" @selected($order->status == 'pending')>Pending</option>
                                            <option value="completed" @selected($order->status == 'completed')>Completed</option>
                                            <option value="cancelled" @selected($order->status == 'cancelled')>Cancelled</option>
                                        </select>
                                    </div>
                                    <span data-customer-only style="display:none" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if($order->status == 'pending')
                                            bg-yellow-100 text-yellow-800
                                        @elseif($order->status == 'completed')
                                            bg-green-100 text-green-800
                                        @elseif($order->status == 'cancelled')
                                            bg-red-100 text-red-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif
                                    ">
                                        {{ ucfirst($order->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center space-x-2 flex justify-center">
                                    <a href="{{ route('orders.show', $order->order_id) }}" class="text-blue-600 hover:text-blue-900 font-semibold transition-colors">
                                        View
                                    </a>
                                    <button data-admin-only onclick="deleteOrder({{ $order->order_id }})" class="text-red-600 hover:text-red-900 font-semibold transition-colors">
                                        Delete
                                    </button>
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

{{-- TODO: REMOVE DEBUG FUNCTIONS - Temporary debug toggle for testing --}}
function toggleView(viewType) {
    // Store preference in session storage
    sessionStorage.setItem('debugView', viewType);
    
    // Update indicator
    document.getElementById('viewIndicator').textContent = viewType === 'admin' ? 'Admin' : 'Customer';
    document.getElementById('pageTitle').textContent = viewType === 'admin' ? 'All Orders' : 'My Orders';
    document.getElementById('pageSubtitle').textContent = viewType === 'admin' ? 'Manage all customer orders' : 'Track and manage your orders';
    
    // Toggle visibility
    const adminElements = document.querySelectorAll('[data-admin-only]');
    const customerElements = document.querySelectorAll('[data-customer-only]');
    
    if (viewType === 'admin') {
        adminElements.forEach(el => el.style.display = '');
        customerElements.forEach(el => el.style.display = 'none');
    } else {
        adminElements.forEach(el => el.style.display = 'none');
        customerElements.forEach(el => el.style.display = '');
    }
    
    showToast(`Switched to ${viewType} view`, 'success');
}

// Check if there's a stored view preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = sessionStorage.getItem('debugView');
    if (savedView) {
        toggleView(savedView);
    }
});

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
@endsection
