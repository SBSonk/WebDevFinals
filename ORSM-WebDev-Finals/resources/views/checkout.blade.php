@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @php $cart = session('cart', []); $total = 0; @endphp
    @foreach($cart as $item) @php $total += $item['price'] * $item['qty']; @endphp @endforeach

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
                        </thead>
                        <tbody>
                        @foreach($cart as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['qty'] }}</td>
                                <td>${{ number_format($item['price'] * $item['qty'], 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <hr />
                    <p class="text-end"><strong>Total: ${{ number_format($total, 2) }}</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shipping & Payment</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('orders.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address *</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3" required>{{ old('shipping_address', '') }}</textarea>
                            @error('shipping_address')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="">Select payment method</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                            </select>
                            @error('payment_method')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" type="submit">Place Order</button>
                            <a href="{{ route('cart.index') }}" class="btn btn-secondary">Back to Cart</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
