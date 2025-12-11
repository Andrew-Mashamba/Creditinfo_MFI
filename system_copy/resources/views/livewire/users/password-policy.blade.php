
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Password Policy</h1>
                        <p class="text-gray-600 mt-1">Configure password security requirements and policies</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Min Length</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $minLength ?? 8 }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Expiry Days</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $expiryDays ?? 90 }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button wire:click="savePolicy" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                        <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Policy
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Password Requirements -->
            <div class="space-y-6">
                <!-- Basic Requirements -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Basic Requirements</h3>
                        <p class="text-sm text-gray-600 mt-1">Set minimum password requirements</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="minLength" class="block text-sm font-medium text-gray-700 mb-2">Minimum Length</label>
                            <input type="number" id="minLength" wire:model="minLength" min="6" max="32" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Minimum number of characters required (6-32)</p>
                            @error('minLength') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="maxLength" class="block text-sm font-medium text-gray-700 mb-2">Maximum Length</label>
                            <input type="number" id="maxLength" wire:model="maxLength" min="8" max="128" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Maximum number of characters allowed (8-128)</p>
                            @error('maxLength') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="requireUppercase" class="flex items-center">
                                <input type="checkbox" id="requireUppercase" wire:model="requireUppercase" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Require Uppercase Letters</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Password must contain at least one uppercase letter (A-Z)</p>
                        </div>

                        <div>
                            <label for="requireLowercase" class="flex items-center">
                                <input type="checkbox" id="requireLowercase" wire:model="requireLowercase" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Require Lowercase Letters</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Password must contain at least one lowercase letter (a-z)</p>
                        </div>

                        <div>
                            <label for="requireNumbers" class="flex items-center">
                                <input type="checkbox" id="requireNumbers" wire:model="requireNumbers" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Require Numbers</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Password must contain at least one number (0-9)</p>
                        </div>

                        <div>
                            <label for="requireSpecialChars" class="flex items-center">
                                <input type="checkbox" id="requireSpecialChars" wire:model="requireSpecialChars" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Require Special Characters</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Password must contain at least one special character (!@#$%^&*)</p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Advanced Settings</h3>
                        <p class="text-sm text-gray-600 mt-1">Additional security configurations</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="preventCommonPasswords" class="flex items-center">
                                <input type="checkbox" id="preventCommonPasswords" wire:model="preventCommonPasswords" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Prevent Common Passwords</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Block commonly used passwords like "password123"</p>
                        </div>

                        <div>
                            <label for="preventSequentialChars" class="flex items-center">
                                <input type="checkbox" id="preventSequentialChars" wire:model="preventSequentialChars" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Prevent Sequential Characters</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Block sequential characters like "123" or "abc"</p>
                        </div>

                        <div>
                            <label for="preventRepeatedChars" class="flex items-center">
                                <input type="checkbox" id="preventRepeatedChars" wire:model="preventRepeatedChars" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Prevent Repeated Characters</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Block repeated characters like "aaa" or "111"</p>
                        </div>

                        <div>
                            <label for="maxRepeatedChars" class="block text-sm font-medium text-gray-700 mb-2">Maximum Repeated Characters</label>
                            <input type="number" id="maxRepeatedChars" wire:model="maxRepeatedChars" min="1" max="5" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Maximum number of consecutive repeated characters allowed</p>
                            @error('maxRepeatedChars') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Expiry & History -->
            <div class="space-y-6">
                <!-- Password Expiry -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Password Expiry</h3>
                        <p class="text-sm text-gray-600 mt-1">Configure password expiration settings</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="enableExpiry" class="flex items-center">
                                <input type="checkbox" id="enableExpiry" wire:model="enableExpiry" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Enable Password Expiry</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Force users to change passwords after a specified period</p>
                        </div>

                        <div>
                            <label for="expiryDays" class="block text-sm font-medium text-gray-700 mb-2">Expiry Period (Days)</label>
                            <input type="number" id="expiryDays" wire:model="expiryDays" min="30" max="365" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Number of days before password expires (30-365)</p>
                            @error('expiryDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="warningDays" class="block text-sm font-medium text-gray-700 mb-2">Warning Period (Days)</label>
                            <input type="number" id="warningDays" wire:model="warningDays" min="1" max="30" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Days before expiry to start showing warnings (1-30)</p>
                            @error('warningDays') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="gracePeriod" class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                            <input type="number" id="gracePeriod" wire:model="gracePeriod" min="0" max="7" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Days after expiry before account is locked (0-7)</p>
                            @error('gracePeriod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Password History -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Password History</h3>
                        <p class="text-sm text-gray-600 mt-1">Prevent password reuse</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="enableHistory" class="flex items-center">
                                <input type="checkbox" id="enableHistory" wire:model="enableHistory" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Enable Password History</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Prevent users from reusing recent passwords</p>
                        </div>

                        <div>
                            <label for="historyCount" class="block text-sm font-medium text-gray-700 mb-2">History Count</label>
                            <input type="number" id="historyCount" wire:model="historyCount" min="1" max="20" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Number of previous passwords to remember (1-20)</p>
                            @error('historyCount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Lockout -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Account Lockout</h3>
                        <p class="text-sm text-gray-600 mt-1">Protect against brute force attacks</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="enableLockout" class="flex items-center">
                                <input type="checkbox" id="enableLockout" wire:model="enableLockout" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Enable Account Lockout</span>
                            </label>
                            <p class="text-sm text-gray-500 mt-1 ml-6">Lock accounts after multiple failed login attempts</p>
                        </div>

                        <div>
                            <label for="maxAttempts" class="block text-sm font-medium text-gray-700 mb-2">Maximum Attempts</label>
                            <input type="number" id="maxAttempts" wire:model="maxAttempts" min="3" max="10" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Number of failed attempts before lockout (3-10)</p>
                            @error('maxAttempts') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="lockoutDuration" class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (Minutes)</label>
                            <input type="number" id="lockoutDuration" wire:model="lockoutDuration" min="5" max="1440" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            <p class="text-sm text-gray-500 mt-1">Minutes to keep account locked (5-1440)</p>
                            @error('lockoutDuration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy Preview -->
        <div class="mt-8 bg-white rounded-xl shadow-lg border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Policy Preview</h3>
                <p class="text-sm text-gray-600 mt-1">See how your current policy affects password requirements</p>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Current Password Requirements:</h4>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Minimum {{ $minLength ?? 8 }} characters
                        </li>
                        @if($requireUppercase)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            At least one uppercase letter (A-Z)
                        </li>
                        @endif
                        @if($requireLowercase)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            At least one lowercase letter (a-z)
                        </li>
                        @endif
                        @if($requireNumbers)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            At least one number (0-9)
                        </li>
                        @endif
                        @if($requireSpecialChars)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            At least one special character (!@#$%^&*)
                        </li>
                        @endif
                        @if($enableExpiry)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Passwords expire after {{ $expiryDays ?? 90 }} days
                        </li>
                        @endif
                        @if($enableHistory)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Cannot reuse last {{ $historyCount ?? 5 }} passwords
                        </li>
                        @endif
                        @if($enableLockout)
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Account locked after {{ $maxAttempts ?? 5 }} failed attempts
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
