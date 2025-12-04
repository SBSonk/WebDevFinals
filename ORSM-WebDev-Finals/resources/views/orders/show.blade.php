@extends('layouts.app')

@section('content')
{{-- TODO: Uncomment user permission check when implementing auth --}}
@php
    $user = auth()->user();
    $isAdmin = auth()->check() && (method_exists($user, 'isAdmin') ? $user->isAdmin() : (isset($user->role) && strtolower((string) $user->role) === 'admin'));
    $isOwner = auth()->check() && ((int) ($user->id) === (int) ($order->customer_id));
    $__status = ($order->status ?? $order->order_status ?? 'pending');
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-900 font-semibold mb-4 inline-flex items-center">
                <span class="mr-2">←</span> Back to Orders
            </a>
            <h1 class="text-4xl font-bold text-gray-900 mt-4">Order #{{ $order->order_id }}</h1>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pb-6 border-b">
                <div>
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Order Date</p>
                    <p class="text-lg text-gray-900 font-semibold mt-1">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Status</p>
                    <p class="mt-1">
                        <select
                            name="status"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold {{ $isAdmin ? '' : 'opacity-60 cursor-not-allowed bg-gray-100 text-gray-700' }}"
                            @if($isAdmin) onchange="updateOrderStatus(this.value)" @else disabled aria-disabled="true" title="Status cannot be changed by your account" @endif
                        >
                            <option value="pending" @selected($__status == 'pending')>Pending</option>
                            <option value="completed" @selected($__status == 'completed')>Completed</option>
                            <option value="cancelled" @selected($__status == 'cancelled')>Cancelled</option>
                        </select>
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Order Total</p>
                    <p class="text-lg text-blue-600 font-bold mt-1">${{ number_format($order->details()->sum(\DB::raw('quantity * unit_price')), 2) }}</p>
                </div>
            </div>

            <!-- Shipping Address -->
            <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Shipping Address</p>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $order->shipping_address ?? 'Not provided' }}</p>
            </div>

            @if($isAdmin)
                <div class="mt-6 pt-6 border-t">
                    <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Customer Information</p>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700"><strong>Name:</strong> {{ $order->customer?->first_name ?? 'N/A' }} {{ $order->customer?->last_name ?? '' }}</p>
                        <p class="text-gray-700"><strong>Email:</strong> {{ $order->customer?->email ?? 'N/A' }}</p>
                        <p class="text-gray-700"><strong>Phone:</strong> {{ $order->customer?->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-100 border-b">
                <h2 class="text-xl font-bold text-gray-900">Order Items</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Product</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Quantity</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Unit Price</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($order->details as $item)
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product?->product_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-800">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Order Total Summary -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="flex justify-end">
                    <div class="w-full sm:w-64">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="text-gray-900 font-semibold">${{ number_format($order->details()->sum(\DB::raw('quantity * unit_price')), 2) }}</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between">
                            <span class="text-lg font-bold text-gray-900">Total:</span>
                            <span class="text-2xl font-bold text-blue-600">${{ number_format($order->details()->sum(\DB::raw('quantity * unit_price')), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="mt-8 flex gap-3">
            <a href="{{ route('orders.index') }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors inline-block">
                Back to Orders
            </a>
            @if($isAdmin)
                <button onclick="deleteOrder({{ $order->order_id }})" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                    Delete Order
                </button>
            @else
                @if($isOwner && $__status === 'pending')
                    <button onclick="cancelOrder({{ $order->order_id }})" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors">
                        Cancel Order
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
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

@if($isAdmin)
function updateOrderStatus(newStatus) {
    const orderId = {{ $order->order_id }};
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
            showToast('Status updated successfully', 'success');
        } else {
            showToast('Failed to update status', 'error');
            location.reload();
        }
    })
    .catch(() => {
        showToast('Error updating status', 'error');
        location.reload();
    });
}

function deleteOrder(orderId) {
    if (!confirm('Are you sure you want to delete order #' + orderId + '? This action cannot be undone.')) return;
    fetch(`/orders/${orderId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Order deleted successfully', 'success');
            setTimeout(() => window.location.href = '{{ route('orders.index') }}', 1200);
        } else {
            showToast('Failed to delete order', 'error');
        }
    })
    .catch(() => showToast('Error deleting order', 'error'));
}
@else
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
@endif
</script>
@endpush
@endsection
