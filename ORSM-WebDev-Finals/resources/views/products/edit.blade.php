@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-2xl font-bold">Edit Product</h1>

    <form action="{{ route('products.update', $product->product_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Product Name</label>
            <input 
                type="text" 
                name="product_name" 
                class="w-full p-2 border"
                value="{{ old('product_name', $product->product_name) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <textarea 
                name="description" 
                class="w-full p-2 border"
                placeholder="Enter product description"
            >{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select 
                name="category_id" 
                class="w-full p-2 border" 
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
                class="w-full p-2 border" 
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
                class="w-full p-2 border"
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
                class="w-full p-2 border"
                value="{{ old('cost_price', $product->cost_price) }}"
                required
            >
        </div>

        <div class="flex items-center gap-2 mb-4">
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
            class="px-4 py-2 text-white bg-blue-500 rounded"
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
