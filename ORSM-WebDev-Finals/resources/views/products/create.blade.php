@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Add Product</h1>

    <form action="{{ route('products.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">Product Name</label>
            <input 
                type="text" 
                name="product_name" 
                class="border p-2 w-full" 
                placeholder="Enter product name"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <textarea 
                name="description" 
                class="border p-2 w-full"
                placeholder="Enter product description"
            ></textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Category</label>
            <select 
                name="category_id" 
                class="border p-2 w-full" 
                required
            >
                <option value="" disabled selected>Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}">
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
                <option value="" disabled selected>Select a supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->supplier_id }}">
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
                placeholder="0.00"
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
                placeholder="0.00"
                required
            >
        </div>

        <button 
            type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded"
        >
            Save
        </button>
    </form>
</div>
@endsection
