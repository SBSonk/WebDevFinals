@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Inventory</h1>

    <form action="{{ route('inventory.update', $inventory->inventory_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Product</label>
            <select 
                name="product_id" 
                class="border p-2 w-full" 
                required
            >
                <option value="" disabled>Select a product</option>
                @foreach($products as $product)
                    <option 
                        value="{{ $product->product_id }}"
                        @selected($product->product_id == $inventory->product_id)
                    >
                        {{ $product->product_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Stock Quantity</label>
            <input 
                type="number" 
                name="stock_quantity" 
                class="border p-2 w-full" 
                value="{{ old('stock_quantity', $inventory->stock_quantity) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Reorder Level</label>
            <input 
                type="number" 
                name="reorder_level" 
                class="border p-2 w-full" 
                value="{{ old('reorder_level', $inventory->reorder_level) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Max Stock Level</label>
            <input 
                type="number" 
                name="max_stock_level" 
                class="border p-2 w-full" 
                value="{{ old('max_stock_level', $inventory->max_stock_level) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Last Restocked</label>
            <div class="flex gap-2 items-center">
                <input 
                    type="date" 
                    id="last_restocked" 
                    name="last_restocked" 
                    class="border p-2 w-full" 
                    value="{{ old('last_restocked', \Carbon\Carbon::parse($inventory->last_restocked)->format('Y-m-d')) }}"
                    required
                >

                <button type="button" onclick="setToday()" class="bg-gray-300 px-3 py-1 rounded hover:bg-gray-400">
                    Today
                </button>
            </div>
        </div>

        <button 
            type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Update
        </button>

        <a 
            href="{{ route('inventory.index') }}" 
            class="ml-4 text-gray-600 hover:underline"
        >
            Cancel
        </a>
    </form>
</div>

<script>
    function setToday() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('last_restocked').value = today;
    }
</script>
@endsection
