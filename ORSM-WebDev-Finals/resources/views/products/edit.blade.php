@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Product</h1>

    <form action="{{ route('products.update', $product->product_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Product Name</label>
            <input 
                type="text" 
                name="product_name" 
                class="border p-2 w-full"
                value="{{ old('product_name', $product->product_name) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <textarea 
                name="description" 
                class="border p-2 w-full"
                placeholder="Enter product description"
            >{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select 
                name="category_id" 
                class="border p-2 w-full" 
                required
            >
                @foreach($categories as $category)
                    <option 
                        value="{{ $category->category_id }}"
                        @selected($category->category_id == $product->category_id)
                    >
                        {{ $category->category_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Supplier</label>
            <select 
                name="supplier_id" 
                class="border p-2 w-full" 
                required
            >
                @foreach($suppliers as $supplier)
                    <option 
                        value="{{ $supplier->supplier_id }}"
                        @selected($supplier->supplier_id == $product->supplier_id)
                    >
                        {{ $supplier->supplier_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Unit Price</label>
            <input 
                type="number" 
                step="0.01" 
                name="unit_price" 
                class="border p-2 w-full"
                value="{{ old('unit_price', $product->unit_price) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Cost Price</label>
            <input 
                type="number" 
                step="0.01" 
                name="cost_price" 
                class="border p-2 w-full"
                value="{{ old('cost_price', $product->cost_price) }}"
                required
            >
        </div>

        <div class="mb-4 flex items-center gap-2">
            <label class="block mb-1">Active:</label>

            {{-- Hidden field ensures 0 is sent if checkbox is unchecked --}}
            <input type="hidden" name="is_active" value="0">

            <input 
                type="checkbox" 
                name="is_active" 
                value="1"
                @checked(old('is_active', $product->is_active))
            >
        </div>

        <button 
            type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Update
        </button>

        <a 
            href="{{ route('products.index') }}" 
            class="ml-4 text-gray-600 hover:underline"
        >
            Cancel
        </a>
    </form>
</div>
@endsection
