@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Store</h1>
                <p class="text-gray-600 mt-1">Browse our collection of products</p>
            </div>
            <a href="{{ route('cart.index') }}" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-md">
                View Cart
            </a>
        </div>
        
        <!-- Alert Messages -->
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
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                    <p class="text-gray-600 text-lg">No products available.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

