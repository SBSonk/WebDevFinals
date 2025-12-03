<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Activity Log Details') }}
            </h2>
            <a href="{{ route('admin.logs.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                ← Back to Logs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Activity ID</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">#{{ $log->id }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">User</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $log->user?->name ?? 'System' }}
                                @if($log->user)
                                    <span class="text-sm text-gray-600 dark:text-gray-400">({{ $log->user->email }})</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Action</h3>
                            <p class="mt-1">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($log->action === 'create') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($log->action === 'update') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($log->action === 'delete') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($log->action === 'login') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject Type</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $log->subject_type ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Subject ID</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $log->subject_id ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</h3>
                            <p class="mt-1 text-lg font-mono text-gray-900 dark:text-white">{{ $log->ip_address }}</p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">User Agent</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 break-words">
                                {{ substr($log->user_agent, 0, 50) }}{{ strlen($log->user_agent) > 50 ? '...' : '' }}
                            </p>
                        </div>
                    </div>

                    <!-- Changes Section -->
                    @if($log->changes)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Changes Made</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-sm text-gray-900 dark:text-gray-100 font-mono">{{ json_encode($log->changes, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="mt-8 flex space-x-3">
                        <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700">
                            Back to Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
