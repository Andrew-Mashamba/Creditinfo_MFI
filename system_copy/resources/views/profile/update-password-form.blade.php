<div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ __('Update Password') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
        <span class="px-3 py-1 text-xs font-medium text-nbc-primary bg-nbc-light rounded-full">Security</span>
    </div>

    @php
        $nextAllowedChange = auth()->user()->password_changed_at ? auth()->user()->password_changed_at->addMonths(6) : now();
        $canChangePassword = !auth()->user()->password_changed_at || now()->isAfter($nextAllowedChange);
    @endphp

    @if(!$canChangePassword)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Password Change Not Available</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>You can change your password after {{ $nextAllowedChange->format('F j, Y') }} (6 months from your last password change).</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="updatePassword" class="space-y-6" @if(!$canChangePassword) disabled @endif>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('Current Password') }}</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="password" wire:model.defer="state.current_password" id="current_password" class="pl-10 block w-full rounded-md border-gray-300 focus:ring-nbc-primary focus:border-nbc-primary sm:text-sm" placeholder="••••••••" autocomplete="current-password" @if(!$canChangePassword) disabled @endif>
                </div>
                <x-jet-input-error for="current_password" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="password" wire:model.defer="state.password" id="password" class="pl-10 block w-full rounded-md border-gray-300 focus:ring-nbc-primary focus:border-nbc-primary sm:text-sm" placeholder="••••••••" autocomplete="new-password" @if(!$canChangePassword) disabled @endif>
                </div>
                <x-jet-input-error for="password" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="password" wire:model.defer="state.password_confirmation" id="password_confirmation" class="pl-10 block w-full rounded-md border-gray-300 focus:ring-nbc-primary focus:border-nbc-primary sm:text-sm" placeholder="••••••••" autocomplete="new-password" @if(!$canChangePassword) disabled @endif>
                </div>
                <x-jet-input-error for="password_confirmation" class="mt-2" />
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <x-jet-action-message class="text-sm text-green-600" on="saved">
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Saved.') }}
                </div>
            </x-jet-action-message>

            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-nbc-primary hover:bg-nbc-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-nbc-primary transition-colors duration-200" @if(!$canChangePassword) disabled @endif>
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
