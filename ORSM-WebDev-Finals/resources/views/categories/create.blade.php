@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-2xl font-bold">Add Category</h1>

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">Category Name</label>
            <input 
                type="text" 
                name="category_name" 
                class="w-full p-2 border" 
                placeholder="Enter category name"
                required
            >
        </div>

        <div class="flex items-center gap-2 mb-4">
            <input type="checkbox" name="is_active" value="1" checked>
            <label>Active</label>
        </div>

        <button 
            type="submit" 
            class="px-4 py-2 text-white bg-blue-500 rounded"
        >
            Save
        </button>

        <a href="{{ route('categories.index') }}" class="ml-4 text-gray-600 hover:underline">
            Cancel
        </a>
    </form>
</div>
@endsection
