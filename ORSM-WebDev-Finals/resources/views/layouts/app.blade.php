<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Inventory System')</title>
    @vite('resources/css/app.css') {{-- Tailwind CSS --}}
</head>

<body class="bg-gray-100">

    {{-- NAVBAR (appears on every page) --}}
    <nav class="bg-gray-800 text-white px-6 py-3 shadow">
        <ul class="flex space-x-6">
            <li><a href="{{ route('products.index') }}" class="hover:text-gray-300">Products</a></li>
            <li><a href="{{ route('inventory.index') }}" class="hover:text-gray-300">Inventory</a></li>
            {{-- <li><a href="{{ route('categories.index') }}" class="hover:text-gray-300">Categories</a></li>
            <li><a href="{{ route('suppliers.index') }}" class="hover:text-gray-300">Suppliers</a></li> --}}
        </ul>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="p-6">
        @yield('content')
    </main>

</body>
</html>
