@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        @if(session('success'))
            <x-alert type="success" dismissible="true">
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="error" dismissible="true">
                {{ session('error') }}
            </x-alert>
        @endif

        @if(count($cart) > 0)
            <form action="{{ route('cart.update') }}" method="post" class="bg-white rounded-lg shadow-md p-6 mb-6">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Product</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Quantity</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Subtotal</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $total = 0; @endphp
                        @foreach($cart as $id => $item)
                            @php $subtotal = $item['price'] * $item['qty']; $total += $subtotal; @endphp
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4 text-gray-800 font-medium">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 text-gray-600">${{ number_format($item['price'], 2) }}</td>
                                <td class="px-6 py-4">
                                    <input type="number" name="quantities[{{ $id }}]" value="{{ $item['qty'] }}" min="0" class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                </td>
                                <td class="px-6 py-4 text-gray-800 font-semibold">${{ number_format($subtotal, 2) }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('cart.remove', $id) }}" class="inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">Remove</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end items-center gap-4">
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">Update Cart</button>
                </div>
            </form>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Order Summary</h2>
                    <p class="text-3xl font-bold text-blue-600">Total: ${{ number_format($total, 2) }}</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('store') }}" class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors text-center">Continue Shopping</a>
                    @php($user = auth()->user())
                    @if(auth()->check() && method_exists($user, 'isAdmin') && $user->isAdmin())
                        <a href="{{ route('admin.sales') }}" class="flex-1 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors text-center">Sales Dashboard</a>
                    @else
                        <a href="{{ route('checkout.index') }}" class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-center">Proceed to Checkout</a>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg mb-6">Your cart is empty</p>
                <a href="{{ route('store') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">Continue Shopping</a>
            </div>
        @endif
    </div>
</div>
@endsection

