@props(['product'])

@php 
    $inv = $product->inventory;
    $isActive = $product->is_active;
    $stock = $inv?->stock_quantity ?? 0;
@endphp

@if($isActive && $stock > 0)
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
        <!-- Product Image -->
        <div class="w-full h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
            <img src="https://via.placeholder.com/300x200?text={{ urlencode($product->product_name) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover">
        </div>
        
        <div class="p-4">
            <h5 class="font-semibold text-lg text-gray-800 truncate">{{ $product->product_name }}</h5>
            <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ Str::limit($product->description, 60) }}</p>
            
            <div class="mt-3 flex items-center justify-between">
                <p class="text-2xl font-bold text-blue-600">${{ number_format($product->unit_price, 2) }}</p>
                <p class="text-xs text-gray-500 font-medium">Stock: {{ $stock }}</p>
            </div>
            
            <form action="{{ route('cart.add') }}" method="post" class="mt-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                <div class="flex gap-2">
                    <input type="number" name="qty" value="1" min="1" max="{{ $stock }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
@elseif(!$isActive && $stock > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden opacity-60">
        <div class="w-full h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
            <img src="https://via.placeholder.com/300x200?text={{ urlencode($product->product_name) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover grayscale">
        </div>
        
        <div class="p-4">
            <h5 class="font-semibold text-lg text-gray-800 truncate">{{ $product->product_name }}</h5>
            <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ Str::limit($product->description, 60) }}</p>
            
            <div class="mt-3 flex items-center justify-between">
                <p class="text-2xl font-bold text-gray-400">${{ number_format($product->unit_price, 2) }}</p>
                <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Not Available</span>
            </div>
        </div>
    </div>
@elseif($isActive && $stock == 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden opacity-60">
        <div class="w-full h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
            <img src="https://via.placeholder.com/300x200?text={{ urlencode($product->product_name) }}" alt="{{ $product->product_name }}" class="w-full h-full object-cover grayscale">
        </div>
        
        <div class="p-4">
            <h5 class="font-semibold text-lg text-gray-800 truncate">{{ $product->product_name }}</h5>
            <p class="text-gray-600 text-sm mt-1 line-clamp-2">{{ Str::limit($product->description, 60) }}</p>
            
            <div class="mt-3 flex items-center justify-between">
                <p class="text-2xl font-bold text-gray-400">${{ number_format($product->unit_price, 2) }}</p>
                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Sold Out</span>
            </div>
        </div>
    </div>
@endif
