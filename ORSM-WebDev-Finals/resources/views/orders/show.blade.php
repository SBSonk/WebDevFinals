@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Order #{{ $order->order_id }}</h1>

    <p>Status: {{ $order->order_status }}</p>
    <p>Total: ${{ number_format($order->total_amount,2) }}</p>

    <h4>Items</h4>
    <table class="table">
        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
        @foreach($order->details as $item)
            <tr>
                <td>{{ $item->product?->product_name ?? '—' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price,2) }}</td>
                <td>${{ number_format($item->subtotal,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
