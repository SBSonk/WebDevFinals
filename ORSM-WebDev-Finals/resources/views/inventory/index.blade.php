@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<h1 class="text-2xl font-bold mb-4">Inventory</h1>

<a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
    Add Product
</a>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Product ID | Name</th>
            <th class="px-4 py-2">Stock Quantity</th>
            <th class="px-4 py-2">Reorder Level</th>
            <th class="px-4 py-2">Max Stock Level</th>
            <th class="px-4 py-2">Last Restocked</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inventory as $item)
        <tr class="border-t">
            <td class="px-4 py-2">{{ $item->inventory_id }}</td>
            <td class="px-4 py-2">
                {{ $item->product_id }} | <a href="{{ route('products.show', $item->product_id) }}" class="text-blue-500">{{ $item->product->product_name }}</a>
            </td>
            <td class="px-4 py-2">{{ $item->stock_quantity }}</td>
            <td class="px-4 py-2">{{ $item->reorder_level }}</td>
            <td class="px-4 py-2">{{ $item->max_stock_level }}</td>
            <td class="px-4 py-2">{{ $item->last_restocked }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('products.edit', $item->product_id) }}" class="text-blue-500">Edit</a>
                <form action="{{ route('products.destroy', $item->product_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
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
