@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Shopping Cart</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('cart.update') }}" method="post">
        @csrf
        <table class="table">
            <thead>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
            @php $total = 0; @endphp
            @forelse($cart as $id => $item)
                @php $subtotal = $item['price'] * $item['qty']; $total += $subtotal; @endphp
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>${{ number_format($item['price'],2) }}</td>
                    <td><input class="form-control" type="number" name="quantities[{{ $id }}]" value="{{ $item['qty'] }}" min="0" /></td>
                    <td>${{ number_format($subtotal,2) }}</td>
                    <td><a href="{{ route('cart.remove', $id) }}" class="btn btn-sm btn-danger">Remove</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Cart is empty</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-between">
            <div>
                <button class="btn btn-secondary" type="submit">Update Cart</button>
            </div>
            <div>
                <strong>Total: ${{ number_format($total,2) }}</strong>
                <a href="{{ route('checkout.index') }}" class="btn btn-primary ms-3">Proceed to Checkout</a>
            </div>
        </div>
    </form>
</div>
@endsection
