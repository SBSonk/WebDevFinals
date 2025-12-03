@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Orders</h1>

    <table class="table">
        <thead><tr><th>Order #</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_id }}</td>
                <td>{{ $order->order_date }}</td>
                <td>{{ $order->order_status }}</td>
                <td>${{ number_format($order->total_amount,2) }}</td>
                <td><a href="{{ route('orders.show', $order->order_id) }}" class="btn btn-sm btn-primary">View</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
