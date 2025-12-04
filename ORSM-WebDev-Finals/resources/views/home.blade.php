@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8 md:p-12">
            <div class="md:flex md:items-center md:justify-between">
                <div class="md:w-2/3">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Welcome to the Inventory & Store System</h1>
                    <p class="mt-4 text-gray-600">Browse our store, manage your cart, and place orders. Team members with the right roles can manage products, inventory, suppliers, and more.</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('store') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md">Shop Now</a>
                        <a href="{{ route('cart.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">View Cart</a>

                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">Login</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">Register</a>
                        @endguest

                        @auth
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">My Orders</a>

                            {{-- Always hide Checkout for admin accounts per policy (updated) --}}
                            @php($user = auth()->user())
                            @if(!(method_exists($user, 'isAdmin') && $user->isAdmin()))
                                <a href="{{ route('checkout.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">Checkout</a>
                            @else
                                <a href="{{ route('admin.sales') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded-md">Sales Dashboard</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="mt-8 md:mt-0 md:w-1/3">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-md p-4">
                        @guest
                            <h2 class="font-semibold text-indigo-900">New here?</h2>
                            <p class="mt-2 text-sm text-indigo-800">Create an account to track your orders and enjoy a faster checkout.</p>
                        @endguest

                        @auth
                            @php($user = auth()->user())
                            <h2 class="font-semibold text-indigo-900">Hello, {{ $user->name }}</h2>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-indigo-700 hover:underline">Go to Dashboard</a>
                                </li>
                                @if(method_exists($user, 'isManager') && $user->isManager())
                                    <li class="text-gray-700">Management shortcuts:</li>
                                    <li><a href="{{ route('products.index') }}" class="text-indigo-700 hover:underline">Products</a></li>
                                    <li><a href="{{ route('inventory.index') }}" class="text-indigo-700 hover:underline">Inventory</a></li>
                                @endif
                                @if(method_exists($user, 'isAdmin') && $user->isAdmin())
                                    <li><a href="{{ route('admin.sales') }}" class="text-indigo-700 hover:underline">Admin Sales Dashboard</a></li>
                                @endif
                            </ul>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900">Fast Ordering</h3>
                <p class="mt-2 text-gray-600 text-sm">Add items to your cart and complete checkout quickly when logged in.</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900">Real-time Inventory</h3>
                <p class="mt-2 text-gray-600 text-sm">Managers keep product stock accurate through the management panel.</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900">Secure Access</h3>
                <p class="mt-2 text-gray-600 text-sm">Role-based permissions keep admin and management areas protected.</p>
            </div>
        </div>
    </div>
@endsection
