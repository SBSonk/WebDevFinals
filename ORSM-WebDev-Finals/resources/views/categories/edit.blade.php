@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-2xl font-bold">Edit Category</h1>

    <form action="{{ route('categories.update', $category->category_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-1">Category Name</label>
            <input 
                type="text" 
                name="category_name" 
                class="w-full p-2 border" 
                value="{{ old('category_name', $category->category_name) }}"
                required
            >
        </div>

        <div class="flex items-center gap-2 mb-4">
            <input 
                type="checkbox" 
                name="is_active" 
                value="1"
                @checked($category->is_active)
            >
            <label>Active</label>
        </div>

        <button 
            type="submit" 
            class="px-4 py-2 text-white bg-blue-500 rounded"
        >
            Update
        </button>

        <a href="{{ route('categories.index') }}" class="ml-4 text-gray-600 hover:underline">
            Cancel
        </a>
    </form>
</div>
@endsection
