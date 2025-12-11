<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New MFI Instance</h1>
                    <p class="text-gray-600">Set up a new microfinance institution with automated provisioning</p>
                </div>
                <a href="{{ route('mfi.all-institutions') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All Institutions
                </a>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        <form wire:submit="createMfi" class="space-y-8">
            <!-- MFI Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">MFI Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="mfi_name" class="block text-sm font-medium text-gray-700 mb-2">Institution Name *</label>
                        <input type="text" id="mfi_name" wire:model.blur="mfi_name" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('mfi_name') border-red-500 @enderror"
                               placeholder="e.g., ABC Microfinance Limited">
                        @error('mfi_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="mfi_code" class="block text-sm font-medium text-gray-700 mb-2">Institution Code *</label>
                        <input type="text" id="mfi_code" wire:model="mfi_code" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('mfi_code') border-red-500 @enderror font-mono"
                               placeholder="e.g., abc_microfinance">
                        @error('mfi_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Used for database and folder naming. Auto-generated from institution name.</p>
                    </div>

                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">License Number</label>
                        <input type="text" id="license_number" wire:model="license_number" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('license_number') border-red-500 @enderror"
                               placeholder="e.g., MFI-2024-001">
                        @error('license_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">Primary Contact Person *</label>
                        <input type="text" id="contact_person" wire:model="contact_person" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('contact_person') border-red-500 @enderror"
                               placeholder="e.g., John Doe">
                        @error('contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                        <input type="email" id="contact_email" wire:model="contact_email" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('contact_email') border-red-500 @enderror"
                               placeholder="e.g., contact@abcmicrofinance.com">
                        @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">Contact Phone *</label>
                        <input type="tel" id="contact_phone" wire:model="contact_phone" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('contact_phone') border-red-500 @enderror"
                               placeholder="e.g., +255 123 456 789">
                        @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Physical Address *</label>
                    <textarea id="address" wire:model="address" rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('address') border-red-500 @enderror"
                              placeholder="Enter the complete physical address of the institution..."></textarea>
                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Admin User Setup -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Administrator Account Setup</h2>
                <p class="text-gray-600 mb-6">Create the initial administrator account for this MFI instance.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="admin_first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" id="admin_first_name" wire:model="admin_first_name" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('admin_first_name') border-red-500 @enderror"
                               placeholder="e.g., John">
                        @error('admin_first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="admin_last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" id="admin_last_name" wire:model="admin_last_name" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('admin_last_name') border-red-500 @enderror"
                               placeholder="e.g., Doe">
                        @error('admin_last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Admin Email *</label>
                        <input type="email" id="admin_email" wire:model="admin_email" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('admin_email') border-red-500 @enderror"
                               placeholder="e.g., admin@abcmicrofinance.com">
                        @error('admin_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <!-- Empty div for spacing -->
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" id="admin_password" wire:model="admin_password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('admin_password') border-red-500 @enderror"
                               placeholder="Minimum 8 characters">
                        @error('admin_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input type="password" id="admin_password_confirmation" wire:model="admin_password_confirmation" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('admin_password_confirmation') border-red-500 @enderror"
                               placeholder="Re-enter password">
                        @error('admin_password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Instance Configuration Preview -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Instance Configuration Preview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Database Name:</span>
                        <span class="text-gray-600 font-mono">{{ $mfi_code ? $mfi_code . '_db' : 'mfi_code_db' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Folder Path:</span>
                        <span class="text-gray-600 font-mono">/mfi/{{ $mfi_code ?: 'mfi_code' }}/</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Portal URL:</span>
                        <span class="text-gray-600">{{ url('/mfi/' . ($mfi_code ?: 'mfi_code')) }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Admin Login:</span>
                        <span class="text-gray-600">{{ $admin_email ?: 'admin@example.com' }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-gradient-to-r from-red-600 to-red-700 text-white px-8 py-3 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg flex items-center"
                        wire:loading.attr="disabled">
                    <svg wire:loading wire:target="createMfi" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="createMfi">Create MFI Instance</span>
                    <span wire:loading wire:target="createMfi">Creating...</span>
                </button>
            </div>
        </form>
    </div>
</div>