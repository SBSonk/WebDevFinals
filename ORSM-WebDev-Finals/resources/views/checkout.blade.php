@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Checkout</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Order Summary -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Product</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Qty</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php $cart = session('cart', []); $total = 0; @endphp
                            @foreach($cart as $item)
                                @php $subtotal = $item['price'] * $item['qty']; $total += $subtotal; @endphp
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-800">{{ $item['name'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $item['qty'] }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800">${{ number_format($subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-4 border-t flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-3xl font-bold text-blue-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Shipping & Payment</h2>
                    
                    <form method="post" action="{{ route('orders.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="shipping_address" class="block text-sm font-semibold text-gray-700 mb-2">Shipping Address *</label>
                            <textarea name="shipping_address" id="shipping_address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" required>{{ old('shipping_address', '') }}</textarea>
                            @error('shipping_address')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-6">
                            <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">Payment Method *</label>
                            <select name="payment_method" id="payment_method" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Select payment method</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="paypal" {{ old('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                                <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                            </select>
                            @error('payment_method')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                        </div>

                        <div class="flex flex-col gap-3">
                            <button class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors" type="submit">Place Order</button>
                            <a href="{{ route('cart.index') }}" class="w-full px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors text-center">Back to Cart</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

