@extends('layouts.app')

@section('title', 'Products')

@section('content')
<h1 class="text-2xl font-bold mb-4">Products</h1>

<a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
    Add Product
</a>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Product Name</th>
            <th class="px-4 py-2">Description</th>
            <th class="px-4 py-2">Category</th>
            <th class="px-4 py-2">Supplier</th>
            <th class="px-4 py-2">Unit Price</th>
            <th class="px-4 py-2">Cost Price</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($products as $product)
        <tr class="border-t {{ !$product->is_active ? 'bg-red-100 text-red-700' : '' }}">
            <td class="px-4 py-2">{{ $product->product_id }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('products.show', $product->product_id) }}" class="text-blue-500">{{ $product->product_name }}</a>
            </td>
            <td class="px-4 py-2">{{ $product->description }}</td>
            <td class="px-4 py-2">{{ $product->category->category_name }}</td>
            <td class="px-4 py-2">{{ $product->supplier->supplier_name }}</td>
            <td class="px-4 py-2">${{ $product->unit_price }}</td>
            <td class="px-4 py-2">${{ $product->cost_price }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('products.edit', $product->product_id) }}" class="text-blue-500">Edit</a>
                <form action="{{ route('products.destroy', $product->product_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
