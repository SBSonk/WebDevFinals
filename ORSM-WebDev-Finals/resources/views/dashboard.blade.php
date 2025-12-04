@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900">Welcome</h2>
            <p class="mt-2 text-gray-700">You're logged in. Use the shortcuts to get where you need quickly.</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900">Store</h2>
            <p class="mt-2 text-gray-700">Browse products and manage your cart.</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('store') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Open Store</a>
                <a href="{{ route('cart.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">View Cart</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900">Account</h2>
            <p class="mt-2 text-gray-700">Manage your profile and orders.</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">{{ (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) ? 'Orders' : 'My Orders' }}</a>
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Profile</a>
            </div>
        </div>
    </div>

    @php($user = auth()->user())
    @if($user && method_exists($user, 'isAdmin') && $user->isAdmin())
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Admin</h2>
            <p class="text-gray-700">Access sales reports and admin tools.</p>
            <div class="mt-4 flex gap-3">
                <a href="{{ route('admin.sales') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Sales Dashboard</a>
            </div>
        </div>
    @elseif($user && method_exists($user, 'isManager') && $user->isManager())
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Management</h2>
            <p class="text-gray-700">Quick links for product and inventory management.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Products</a>
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Inventory</a>
                <a href="{{ route('categories.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Categories</a>
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Suppliers</a>
                <a href="{{ route('inventory_transactions.index') }}" class="px-4 py-2 bg-white text-gray-900 border border-gray-300 hover:bg-gray-50 rounded">Inventory Movements</a>
            </div>
        </div>
    @endif
</div>
@endsection
