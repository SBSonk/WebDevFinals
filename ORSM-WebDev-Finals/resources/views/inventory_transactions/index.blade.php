@extends('layouts.app')

@section('title', 'Inventory Movements')

@section('content')

<h1 class="mb-4 text-2xl font-bold">Inventory Movements</h1>

<a href="{{ route('inventory_transactions.create') }}" class="inline-block px-4 py-2 mb-4 text-white bg-blue-500 rounded">
    Add Movement
</a>

<table class="min-w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Type</th>
            <th class="px-4 py-2">Reference</th>
            <th class="px-4 py-2">Remarks</th>
            <th class="px-4 py-2">Items</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($transactions as $transaction)
        <tr class="border-t {{ $transaction->transaction_type === 'out' ? 'bg-red-100 text-red-700' : '' }}">
            <td class="px-4 py-2">{{ $transaction->transaction_id }}</td>
            <td class="px-4 py-2 font-bold {{ $transaction->transaction_type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                {{ strtoupper($transaction->transaction_type) }}
            </td>
            <td class="px-4 py-2">{{ $transaction->reference_number ?? '-' }}</td>
            <td class="px-4 py-2">{{ $transaction->remarks ?? '-' }}</td>
            <td class="px-4 py-2">
                <ul class="ml-5 list-disc">
                    @foreach ($transaction->items as $item)
                        <li>
                            {{ $item->product?->product_name ?? 'Deleted Product' }} — {{ $item->quantity }}
                        </li>
                    @endforeach
                </ul>
            </td>
            <td class="px-4 py-2">{{ $transaction->created_at->format('m-d-Y H:i') }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('inventory_transactions.edit', $transaction->transaction_id) }}" class="text-blue-500">Edit</a>
                <form action="{{ route('inventory_transactions.destroy', $transaction->transaction_id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ml-2 text-red-500">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection
