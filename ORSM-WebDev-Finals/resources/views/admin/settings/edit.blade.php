<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('System Settings') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                ← Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <!-- Shop Information Section -->
                        <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6">Shop Information</h3>

                            <!-- Shop Name -->
                            <div class="mb-6">
                                <label for="shop_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shop Name</label>
                                <input type="text" id="shop_name" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('shop_name') border-red-500 @enderror">
                                @error('shop_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Shop Description -->
                            <div class="mb-6">
                                <label for="shop_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shop Description</label>
                                <textarea id="shop_description" name="shop_description" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('shop_description') border-red-500 @enderror">{{ old('shop_description', $settings['shop_description']) }}</textarea>
                                @error('shop_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Shop Email -->
                            <div class="mb-6">
                                <label for="shop_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shop Email</label>
                                <input type="email" id="shop_email" name="shop_email" value="{{ old('shop_email', $settings['shop_email']) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('shop_email') border-red-500 @enderror">
                                @error('shop_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Shop Phone -->
                            <div class="mb-6">
                                <label for="shop_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shop Phone</label>
                                <input type="tel" id="shop_phone" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('shop_phone') border-red-500 @enderror">
                                @error('shop_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Shop Address -->
                            <div class="mb-6">
                                <label for="shop_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shop Address</label>
                                <textarea id="shop_address" name="shop_address" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('shop_address') border-red-500 @enderror">{{ old('shop_address', $settings['shop_address']) }}</textarea>
                                @error('shop_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Branding removed -->

                        <!-- Financial Settings Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6">Financial Settings</h3>

                            <!-- Currency -->
                            <div class="mb-6">
                                <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label>
                                <select id="currency" name="currency" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('currency') border-red-500 @enderror">
                                    <option value="USD" {{ old('currency', $settings['currency']) === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency', $settings['currency']) === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ old('currency', $settings['currency']) === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="CAD" {{ old('currency', $settings['currency']) === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="AUD" {{ old('currency', $settings['currency']) === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tax Rate -->
                            <div class="mb-6">
                                <label for="tax_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100" value="{{ old('tax_rate', $settings['tax_rate']) }}" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('tax_rate') border-red-500 @enderror">
                                @error('tax_rate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex space-x-3">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Save Settings
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-700">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Branding removed: color pickers and sync script removed -->
</x-app-layout>
