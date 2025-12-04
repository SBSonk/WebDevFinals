@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<h1 class="mb-4 text-2xl font-bold">Suppliers</h1>

<a href="{{ route('suppliers.create') }}" class="inline-block px-4 py-2 mb-4 text-white bg-blue-500 rounded">
    Add Supplier
</a>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Supplier Name</th>
            <th class="px-4 py-2">Contact Person</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Contact No.</th>
            <th class="px-4 py-2">Address</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($suppliers as $supplier)
        <tr class="border-t {{ !$supplier->is_active ? 'bg-red-100 text-red-700' : '' }}">
            <td class="px-4 py-2">{{ $supplier->supplier_id }}</td>
            <td class="px-4 py-2">
                {{ $supplier->supplier_name }}
            </td>
            <td class="px-4 py-2">{{ $supplier->contact_person }}</td>
            <td class="px-4 py-2">{{ $supplier->email }}</td>
            <td class="px-4 py-2">{{ $supplier->phone }}</td>
            <td class="px-4 py-2">{{ $supplier->address }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('suppliers.edit', $supplier->supplier_id) }}" class="text-blue-500">Edit</a>
            
                <form action="{{ route('suppliers.destroy', $supplier->supplier_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
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
