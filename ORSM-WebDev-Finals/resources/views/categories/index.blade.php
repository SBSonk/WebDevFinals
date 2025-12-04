@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<h1 class="mb-4 text-2xl font-bold">Categories</h1>

<a href="{{ route('categories.create') }}" class="inline-block px-4 py-2 mb-4 text-white bg-blue-500 rounded">
    Add Category
</a>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Category Name</th>
            <th class="px-4 py-2">Active</th>
            <th class="px-4 py-2">Created At</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $category)
        <tr class="border-t {{ !$category->is_active ? 'bg-red-100 text-red-700' : '' }}">
            <td class="px-4 py-2">{{ $category->category_id }}</td>
            <td class="px-4 py-2">{{ $category->category_name }}</td>
            <td class="px-4 py-2">{{ $category->is_active ? 'Yes' : 'No' }}</td>
            <td class="px-4 py-2">{{ $category->created_at->format('Y-m-d') }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('categories.edit', $category->category_id) }}" class="text-blue-500">Edit</a>
                <form action="{{ route('categories.destroy', $category->category_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
