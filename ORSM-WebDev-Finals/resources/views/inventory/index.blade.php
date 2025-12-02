@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<h1 class="mb-4 text-2xl font-bold">Inventory</h1>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Product Name</th>
            <th class="px-4 py-2">Stock Quantity</th>
            <th class="px-4 py-2">Reorder Level</th>
            <th class="px-4 py-2">Max Stock Level</th>
            <th class="px-4 py-2">Last Restocked</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inventory as $item)
        <tr class="border-t {{ $item->stock_quantity <= $item->reorder_level ? 'bg-red-100 text-red-700' : '' }}">
            <td class="px-4 py-2">{{ $item->inventory_id }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('products.show', ['product' => $item->product_id, 'fromInventory' => 1]) }}" class="text-blue-500">{{ $item->product->product_name }}</a>
            </td>
            <td class="px-4 py-2">{{ $item->stock_quantity }}</td>
            <td class="px-4 py-2">{{ $item->reorder_level }}</td>
            <td class="px-4 py-2">{{ $item->max_stock_level }}</td>
            <td class="px-4 py-2">{{ $item->last_restocked->format('m-d-y') }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('inventory.edit', $item->inventory_id) }}" class="text-blue-500">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
