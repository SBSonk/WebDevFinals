@extends('layouts.app')

@section('title', 'Edit Suppliers')

@section('content')
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-2xl font-bold">Edit Supplier</h1>

    <form action="{{ route('suppliers.update', $supplier->supplier_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Supplier Name</label>
            <input 
                type="text" 
                name="supplier_name" 
                class="w-full p-2 border"
                value="{{ old('supplier_name', $supplier->supplier_name) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Contact Person</label>
            <input 
                type="text" 
                name="contact_person" 
                class="w-full p-2 border"
                value="{{ old('contact_person', $supplier->contact_person) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Email</label>
            <input 
                type="email" 
                name="email" 
                class="w-full p-2 border"
                value="{{ old('email', $supplier->email) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Phone</label>
            <input 
                type="text" 
                name="phone" 
                class="w-full p-2 border"
                value="{{ old('phone', $supplier->phone) }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="block mb-1">Address</label>
            <textarea 
                name="address" 
                class="w-full p-2 border"
                required
            >{{ old('address', $supplier->address) }}</textarea>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <label class="block mb-1">Active:</label>
            <input 
                type="checkbox" 
                name="is_active" 
                value="1"
                @checked($supplier->is_active)
            >
        </div>

        <button 
            type="submit" 
            class="px-4 py-2 text-white bg-blue-500 rounded"
        >
            Update
        </button>

        <a href="{{ route('suppliers.index') }}" class="ml-4 text-gray-600 hover:underline">
            Cancel
        </a>
    </form>
</div>
@endsection
