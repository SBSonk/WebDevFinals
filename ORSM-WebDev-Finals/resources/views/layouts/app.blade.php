<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Inventory System')</title>
    @vite('resources/css/app.css') {{-- Tailwind CSS --}}
</head>

<body class="bg-gray-100">

    {{-- NAVBAR (appears on every page) --}}
    <nav class="px-6 py-3 text-white bg-gray-800 shadow">
        <ul class="flex items-center space-x-6">
            <li><a href="{{ route('products.index') }}" class="hover:text-gray-300">Products</a></li>
            <li><a href="{{ route('inventory.index') }}" class="hover:text-gray-300">Inventory</a></li>
            <li><a href="{{ route('categories.index') }}" class="hover:text-gray-300">Categories</a></li>
            <li><a href="{{ route('suppliers.index') }}" class="hover:text-gray-300">Suppliers</a></li>
            <li><a href="{{ route('inventory_transactions.index') }}" class="hover:text-gray-300">Transactions</a></li>
            <li class="pl-6 border-l border-gray-700">
                <a href="{{ route('store') }}" class="font-semibold hover:text-gray-300">Store</a>
            </li>
            <li>
                <a href="{{ route('cart.index') }}" class="hover:text-gray-300">Cart</a>
            </li>
            <li>
                <a href="{{ route('orders.index') }}" class="hover:text-gray-300">My Orders</a>
            </li>
        </ul>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="p-6">
        @yield('content')
    </main>
    </main>

        {{-- allow pages to push scripts into this stack (e.g. Chart.js) --}}
        @stack('scripts')
        @yield('scripts')


    </body>
</html>
