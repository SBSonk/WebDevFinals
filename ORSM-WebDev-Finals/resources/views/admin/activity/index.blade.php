@extends('layouts.app')

@section('title', 'Account Changes')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Account Changes</h1>
    </div>

    <form method="get" class="p-4 mb-6 bg-white rounded shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Action</label>
                <select name="action" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
                    <option value="">All</option>
                    @php($actions = [
                        'create','update','delete','login','logout','view','restore','deactivate','activate',
                        'password_changed','password_reset',
                        // New account lifecycle actions
                        'account_created','name_changed','email_changed','account_deleted'
                    ])
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ (isset($filters['action']) && $filters['action'] === $a) ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">User</label>
                <select name="user_id" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
                    <option value="">All</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (isset($filters['user_id']) && (int)$filters['user_id'] === (int)$u->id) ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Start date</label>
                <input type="date" name="start" value="{{ $filters['start'] ?? '' }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">End date</label>
                <input type="date" name="end" value="{{ $filters['end'] ?? '' }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded">
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Filter</button>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="w-full text-sm text-left">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">Details</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $log->subject_type }} @if($log->subject_id)#{{ $log->subject_id }}@endif</td>
                        <td class="px-4 py-3 text-gray-700">
                            @php($changes = $log->changes ?? [])
                            @if(is_array($changes) && !empty($changes))
                                <details>
                                    <summary class="cursor-pointer text-indigo-700">View</summary>
                                    <pre class="p-2 mt-2 text-xs bg-gray-100 rounded">{{ json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-600">No activity found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
