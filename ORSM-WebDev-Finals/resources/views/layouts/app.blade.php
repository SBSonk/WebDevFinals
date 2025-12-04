<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Inventory System')</title>
    @vite('resources/css/app.css') {{-- Tailwind CSS --}}
</head>

<body class="bg-gray-100">

    {{-- NAVBAR (appears on every page) --}}
    <nav class="px-6 py-3 text-white bg-gray-800 shadow">
        <div class="flex items-center justify-between">
            <ul class="flex items-center space-x-6">
                <li>
                    <a href="{{ route('store') }}" class="font-semibold hover:text-gray-300">Store</a>
                </li>
                <li>
                    <a href="{{ route('cart.index') }}" class="hover:text-gray-300">Cart</a>
                </li>

                {{-- Management: only for manager and admin --}}
                @auth
                    @php($user = auth()->user())
                    @if(method_exists($user, 'isManager') && method_exists($user, 'isAdmin') && ($user->isManager() || $user->isAdmin()))
                        <li class="pl-6 ml-2 border-l border-gray-700 text-gray-300">Management</li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-gray-300">Products</a></li>
                        <li><a href="{{ route('inventory.index') }}" class="hover:text-gray-300">Inventory</a></li>
                        <li><a href="{{ route('categories.index') }}" class="hover:text-gray-300">Categories</a></li>
                        <li><a href="{{ route('suppliers.index') }}" class="hover:text-gray-300">Suppliers</a></li>
                        <li><a href="{{ route('inventory_transactions.index') }}" class="hover:text-gray-300">Inventory Movements</a></li>
                    @endif

                    {{-- Admin quick link --}}
                    @if(method_exists($user, 'isAdmin') && $user->isAdmin())
                        <li class="pl-6 ml-2 border-l border-gray-700">
                            <a href="{{ route('admin.sales') }}" class="hover:text-gray-300">Sales Dashboard</a>
                        </li>
                    @endif
                @endauth

                {{-- Authenticated: Orders --}}
                @auth
                    <li>
                        <a href="{{ route('orders.index') }}" class="hover:text-gray-300">{{ (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) ? 'Orders' : 'My Orders' }}</a>
                    </li>
                @endauth
            </ul>

            {{-- Right side: auth links --}}
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:text-gray-300">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-gray-300">Register</a>
                @endguest

                @auth
                    <span class="text-sm text-gray-300">{{ auth()->user()->name }} @if(isset(auth()->user()->role))<span class="opacity-75">({{ auth()->user()->role }})</span>@endif</span>
                    <a href="{{ route('profile.edit') }}" class="hover:text-gray-300">Profile</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-sm text-gray-900 bg-white rounded hover:bg-gray-100">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="p-6">
        @yield('content')
    </main>

    {{-- allow pages to push scripts into this stack (e.g. Chart.js) --}}
    @stack('scripts')
    @yield('scripts')

</body>
</html>
