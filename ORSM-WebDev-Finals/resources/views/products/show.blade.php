@extends('layouts.app')

@section('title', 'View Product | {{ $product->product_name }}')

@section('content')
<div class="container p-6 mx-auto">

    <h1 class="mb-6 text-3xl font-bold">Product Details</h1>

    <div class="p-6 bg-white rounded shadow">
        
        {{-- Product Name --}}
        <div class="mb-4">
            <h2 class="text-xl font-semibold">{{ $product->product_name }}</h2>
            <p class="text-gray-600">Product ID: {{ $product->product_id }}</p>
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <h3 class="font-semibold">Description</h3>
            <p class="text-gray-700">
                {{ $product->description ?? 'No description available.' }}
            </p>
        </div>

        {{-- Category --}}
        <div class="mb-4">
            <h3 class="font-semibold">Category</h3>
            <p class="{{ $product->category ? '' : 'text-red-600 font-semibold' }}">
                {{ $product->category->category_name ?? 'Deleted Category' }}
            </p>
        </div>

        {{-- Supplier --}}
        <div class="mb-4">
            <h3 class="font-semibold">Supplier</h3>
            <p class="{{ $product->supplier ? '' : 'text-red-600 font-semibold' }}">
                {{ $product->supplier->supplier_name ?? 'Deleted Supplier' }}
            </p>
        </div>


        {{-- Pricing --}}
        <div class="mb-4">
            <h3 class="font-semibold">Pricing</h3>
            <p>Unit Price: <span class="font-bold">₱{{ number_format($product->unit_price, 2) }}</span></p>
            <p>Cost Price: <span class="font-bold">₱{{ number_format($product->cost_price, 2) }}</span></p>
        </div>

        {{-- Active Status --}}
        <div class="mb-4">
            <h3 class="font-semibold">Status</h3>
            <p class="{{ $product->is_active ? 'text-green-600' : 'text-red-600' }}">
                {{ $product->is_active ? 'Active' : 'Inactive' }}
            </p>
        </div>

        {{-- Inventory --}}
        <div class="mb-4">
            <h3 class="font-semibold">Inventory</h3>
            
            @if ($product->inventory)
                <p>Stock Quantity: <strong>{{ $product->inventory->stock_quantity }}</strong></p>
                <p>Reorder Level: {{ $product->inventory->reorder_level }}</p>
                <p>Max Stock Level: {{ $product->inventory->max_stock_level }}</p>
                <p>Last Restocked: {{ $product->inventory->last_restocked }}</p>
            @else
                <p class="italic text-gray-500">No inventory record found.</p>
            @endif
        </div>

        {{-- Buttons --}}
        <div class="flex gap-4 mt-6">
            <a 
                href="{{ route('products.edit', $product->product_id) }}"
                class="px-4 py-2 text-white bg-blue-500 rounded"
            >
                Edit Product
            </a>

            
            <a 
                href="{{ $fromInventory ? route('inventory.index') : route('products.index') }}"
                class="px-4 py-2 text-black bg-gray-300 rounded"
            >
                Back to List
            </a>
        </div>

    </div>

</div>
@endsection
