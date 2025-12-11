<div class="max-w-6xl mx-auto p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Funds Transfer</h1>
        <p class="mt-1 text-sm text-gray-600">Transfer funds internally within NBC or externally to other banks and mobile wallets</p>
    </div>

    {{-- Phase Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center space-x-8">
                {{-- Step 1: Select & Fill --}}
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium
                        @if($currentPhase === 'form') border-2 border-blue-500 bg-blue-500 text-white
                        @elseif(in_array($currentPhase, ['verify', 'processing', 'complete'])) border-2 border-green-500 bg-green-500 text-white
                        @else border-2 border-gray-300 bg-gray-100 text-gray-500 @endif">
                        @if(in_array($currentPhase, ['verify', 'processing', 'complete']))
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            1
                        @endif
                    </div>
                    <span class="text-sm font-medium @if($currentPhase === 'form') text-blue-600 @elseif(in_array($currentPhase, ['verify', 'processing', 'complete'])) text-green-600 @else text-gray-500 @endif">
                        Details
                    </span>
                </div>

                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>

                {{-- Step 2: Verify --}}
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium
                        @if($currentPhase === 'verify') border-2 border-blue-500 bg-blue-500 text-white
                        @elseif(in_array($currentPhase, ['processing', 'complete'])) border-2 border-green-500 bg-green-500 text-white
                        @else border-2 border-gray-300 bg-gray-100 text-gray-500 @endif">
                        @if(in_array($currentPhase, ['processing', 'complete']))
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            2
                        @endif
                    </div>
                    <span class="text-sm font-medium @if($currentPhase === 'verify') text-blue-600 @elseif(in_array($currentPhase, ['processing', 'complete'])) text-green-600 @else text-gray-500 @endif">
                        Verify
                    </span>
                </div>

                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>

                {{-- Step 3: Complete --}}
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium
                        @if($currentPhase === 'complete') border-2 border-green-500 bg-green-500 text-white
                        @else border-2 border-gray-300 bg-gray-100 text-gray-500 @endif">
                        @if($currentPhase === 'complete')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            3
                        @endif
                    </div>
                    <span class="text-sm font-medium @if($currentPhase === 'complete') text-green-600 @else text-gray-500 @endif">
                        Complete
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Enhanced Alerts with Alpine.js --}}
    @if($errorMessage)
        <div class="mb-6" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-md">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-red-800">Transfer Error</h3>
                            <p class="text-sm text-red-700 mt-1">{{ $errorMessage }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="ml-4 text-red-400 hover:text-red-600">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($successMessage)
        <div class="mb-6" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-green-800">Success</h3>
                            <p class="text-sm text-green-700 mt-1">{{ $successMessage }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="ml-4 text-green-400 hover:text-green-600">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="bg-white rounded-lg shadow-lg border border-gray-200">
        <div class="p-6">
            @switch($currentPhase)
                @case('form')
                    {{-- FORM PHASE --}}
                    <div class="space-y-6">
                        {{-- Primary Transfer Type Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Transfer Category</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Internal Transfer --}}
                                <button wire:click="$set('transferCategory', 'internal')"
                                    class="relative p-6 border-2 rounded-lg transition-all duration-200 hover:shadow-md
                                        @if($transferCategory === 'internal') border-blue-500 bg-blue-50 @else border-gray-200 hover:border-gray-300 @endif">
                                    <div class="flex flex-col items-center space-y-3">
                                        <svg class="w-12 h-12 @if($transferCategory === 'internal') text-blue-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                        <div class="text-center">
                                            <div class="font-semibold text-lg @if($transferCategory === 'internal') text-blue-900 @else text-gray-900 @endif">Internal Transfer</div>
                                            <div class="text-sm text-gray-500 mt-1">Transfer between NBC accounts</div>
                                            <div class="text-xs text-gray-400 mt-2">Same bank • Instant • No limits</div>
                                        </div>
                                    </div>
                                    @if($transferCategory === 'internal')
                                        <div class="absolute top-2 right-2">
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </button>

                                {{-- External Transfer --}}
                                <button wire:click="$set('transferCategory', 'external')"
                                    class="relative p-6 border-2 rounded-lg transition-all duration-200 hover:shadow-md
                                        @if($transferCategory === 'external') border-blue-500 bg-blue-50 @else border-gray-200 hover:border-gray-300 @endif">
                                    <div class="flex flex-col items-center space-y-3">
                                        <svg class="w-12 h-12 @if($transferCategory === 'external') text-blue-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                        <div class="text-center">
                                            <div class="font-semibold text-lg @if($transferCategory === 'external') text-blue-900 @else text-gray-900 @endif">External Transfer</div>
                                            <div class="text-sm text-gray-500 mt-1">To other banks or mobile wallets</div>
                                            <div class="text-xs text-gray-400 mt-2">TIPS/TISS • Processing time varies</div>
                                        </div>
                                    </div>
                                    @if($transferCategory === 'external')
                                        <div class="absolute top-2 right-2">
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </button>
                            </div>
                        </div>

                        {{-- Secondary Selection for External Transfers --}}
                        @if($transferCategory === 'external')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">External Transfer Type</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Bank Transfer --}}
                                    <button wire:click="$set('transferType', 'bank')"
                                        class="relative p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md
                                            @if($transferType === 'bank') border-blue-500 bg-blue-50 @else border-gray-200 hover:border-gray-300 @endif">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-8 h-8 @if($transferType === 'bank') text-blue-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <div class="text-left">
                                                <div class="font-medium @if($transferType === 'bank') text-blue-900 @else text-gray-900 @endif">Bank Account</div>
                                                <div class="text-xs text-gray-500">Transfer to another bank</div>
                                            </div>
                                        </div>
                                        @if($transferType === 'bank')
                                            <div class="absolute top-2 right-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </button>

                                    {{-- Mobile Wallet --}}
                                    <button wire:click="$set('transferType', 'wallet')"
                                        class="relative p-4 border-2 rounded-lg transition-all duration-200 hover:shadow-md
                                            @if($transferType === 'wallet') border-blue-500 bg-blue-50 @else border-gray-200 hover:border-gray-300 @endif">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-8 h-8 @if($transferType === 'wallet') text-blue-600 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <div class="text-left">
                                                <div class="font-medium @if($transferType === 'wallet') text-blue-900 @else text-gray-900 @endif">Mobile Wallet</div>
                                                <div class="text-xs text-gray-500">M-Pesa, TigoPesa, etc</div>
                                            </div>
                                        </div>
                                        @if($transferType === 'wallet')
                                            <div class="absolute top-2 right-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Transfer Form - Only show after selection --}}
                        @if(($transferCategory === 'internal') || ($transferCategory === 'external' && $transferType))
                            <form wire:submit.prevent="verifyBeneficiary" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Source Account --}}
                                    <div class="md:col-span-2">
                                        <label for="debitAccount" class="block text-sm font-medium text-gray-700 mb-1">Source Account</label>
                                        <input type="text" wire:model.defer="debitAccount" id="debitAccount"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Enter your NBC account number">
                                        @error('debitAccount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Internal Transfer Fields --}}
                                    @if($transferCategory === 'internal')
                                        {{-- Destination Account --}}
                                        <div class="md:col-span-2">
                                            <label for="internalAccount" class="block text-sm font-medium text-gray-700 mb-1">Destination Account</label>
                                            <input type="text" wire:model.defer="internalAccount" id="internalAccount"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Enter destination NBC account number">
                                            @error('internalAccount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    {{-- External Bank Transfer Fields --}}
                                    @if($transferCategory === 'external' && $transferType === 'bank')
                                        {{-- Bank Selection --}}
                                        <div>
                                            <label for="bankCode" class="block text-sm font-medium text-gray-700 mb-1">Select Bank</label>
                                            <select wire:model.defer="bankCode" id="bankCode"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">-- Choose Bank --</option>
                                                @foreach($availableBanks as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('bankCode') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Beneficiary Account --}}
                                        <div>
                                            <label for="beneficiaryAccount" class="block text-sm font-medium text-gray-700 mb-1">Beneficiary Account</label>
                                            <input type="text" wire:model.defer="beneficiaryAccount" id="beneficiaryAccount"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Enter beneficiary account number">
                                            @error('beneficiaryAccount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    {{-- Mobile Wallet Fields --}}
                                    @if($transferCategory === 'external' && $transferType === 'wallet')
                                        {{-- Wallet Provider --}}
                                        <div>
                                            <label for="walletProvider" class="block text-sm font-medium text-gray-700 mb-1">Mobile Network</label>
                                            <select wire:model.defer="walletProvider" id="walletProvider"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">-- Choose Provider --</option>
                                                @foreach($availableWallets as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('walletProvider') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Phone Number --}}
                                        <div>
                                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                                            <input type="text" wire:model.defer="phoneNumber" id="phoneNumber"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="0715000000 or 255715000000">
                                            @error('phoneNumber') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    {{-- Amount --}}
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (TZS)</label>
                                        <input type="number" wire:model.defer="amount" id="amount" min="1000" step="0.01"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Minimum 1,000 TZS">
                                        @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        @if($transferType === 'wallet')
                                            <p class="text-xs text-gray-500 mt-1">Maximum: 20,000,000 TZS for mobile wallets</p>
                                        @endif
                                    </div>

                                    {{-- Charge Bearer (for external only) --}}
                                    @if($transferCategory === 'external')
                                        <div>
                                            <label for="chargeBearer" class="block text-sm font-medium text-gray-700 mb-1">Charge Bearer</label>
                                            <select wire:model.defer="chargeBearer" id="chargeBearer"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="OUR">OUR (Sender pays all charges)</option>
                                                <option value="BEN">BEN (Beneficiary pays all charges)</option>
                                                <option value="SHA">SHA (Charges are shared)</option>
                                            </select>
                                            @error('chargeBearer') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                    {{-- Remarks --}}
                                    <div class="md:col-span-2">
                                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                        <textarea wire:model.defer="remarks" id="remarks" rows="2" maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Purpose of transfer (max 50 characters)"></textarea>
                                        @error('remarks') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Submit Button --}}
                                <div class="flex justify-end pt-4">
                                    <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="verifyBeneficiary">Verify & Continue</span>
                                        <span wire:loading wire:target="verifyBeneficiary" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Verifying Accounts...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                    @break

                @case('verify')
                    {{-- VERIFY PHASE --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900">Verify Transfer Details</h3>
                        
                        {{-- Verification Data --}}
                        @if($verificationData)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <h4 class="text-lg font-medium text-green-900">Account Verified</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($verificationData['type'] === 'internal')
                                        <div>
                                            <p class="text-sm text-gray-600">Account Name</p>
                                            <p class="font-medium">{{ $verificationData['account_name'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Account Number</p>
                                            <p class="font-medium">{{ $verificationData['account_number'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Branch</p>
                                            <p class="font-medium">{{ $verificationData['branch'] ?? 'NBC Branch' }}</p>
                                        </div>
                                    @elseif($verificationData['type'] === 'bank')
                                        <div>
                                            <p class="text-sm text-gray-600">Account Name</p>
                                            <p class="font-semibold text-green-700">{{ $verificationData['account_name'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Account Number</p>
                                            <p class="font-medium">{{ $verificationData['account_number'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Bank</p>
                                            <p class="font-medium">{{ $verificationData['bank_name'] ?? 'N/A' }}</p>
                                        </div>
                                        @if($verificationData['engine_ref'] ?? false)
                                        <div>
                                            <p class="text-sm text-gray-600">Reference ID</p>
                                            <p class="font-mono text-xs">{{ $verificationData['engine_ref'] }}</p>
                                        </div>
                                        @endif
                                    @elseif($verificationData['type'] === 'wallet')
                                        <div>
                                            <p class="text-sm text-gray-600">Name</p>
                                            <p class="font-semibold text-green-700">{{ $verificationData['account_name'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Phone Number</p>
                                            <p class="font-medium">{{ $verificationData['phone_number'] ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600">Provider</p>
                                            <p class="font-medium">{{ $verificationData['provider'] ?? 'N/A' }}</p>
                                        </div>
                                        @if($verificationData['engine_ref'] ?? false)
                                        <div>
                                            <p class="text-sm text-gray-600">Reference ID</p>
                                            <p class="font-mono text-xs">{{ $verificationData['engine_ref'] }}</p>
                                        </div>
                                        @endif
                                    @endif
                                    <div>
                                        <p class="text-sm text-gray-600">Can Receive</p>
                                        <p class="font-medium">
                                            @if($verificationData['can_receive'] ?? false)
                                                <span class="text-green-600">✓ Yes</span>
                                            @else
                                                <span class="text-red-600">✗ No</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Transfer Summary --}}
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Transfer Summary</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">From Account</span>
                                    <span class="font-medium">{{ $debitAccount }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">To</span>
                                    <span class="font-medium">
                                        @if($transferCategory === 'internal')
                                            {{ $internalAccount }}
                                        @elseif($transferType === 'bank')
                                            {{ $beneficiaryAccount }}
                                        @else
                                            {{ $phoneNumber }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Amount</span>
                                    <span class="font-medium">{{ number_format($amount ?? 0, 2) }} TZS</span>
                                </div>
                                @if($transferCategory === 'external')
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Charge Bearer</span>
                                        <span class="font-medium">{{ $chargeBearer }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Remarks</span>
                                    <span class="font-medium">{{ $remarks }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex justify-between">
                            <button wire:click="goBack" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back
                            </button>
                            
                            <button wire:click="executeTransfer" 
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="executeTransfer">Confirm Transfer</span>
                                <span wire:loading wire:target="executeTransfer" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                    @break

                @case('processing')
                    {{-- PROCESSING PHASE --}}
                    <div class="text-center py-12">
                        <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Processing Transfer</h3>
                        <p class="text-gray-600 mt-2">Please wait while we process your transfer...</p>
                    </div>
                    @break

                @case('complete')
                    {{-- ENHANCED COMPLETE PHASE --}}
                    <div class="text-center py-8">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-green-100 to-green-200 mb-4 shadow-lg">
                            <svg class="h-12 w-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Transfer Successful!</h3>
                        <p class="text-gray-600 mb-8">Your funds have been transferred successfully.</p>
                        
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 text-left max-w-lg mx-auto border border-green-200 shadow-inner">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Transaction Details</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Reference Number</span>
                                    <span class="font-mono text-sm bg-white px-3 py-1 rounded border border-gray-200">{{ $transactionReference ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Amount Transferred</span>
                                    <span class="text-xl font-bold text-green-600">{{ number_format($amount ?? 0, 2) }} <span class="text-xs text-gray-600">TZS</span></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Transfer Type</span>
                                    <span class="text-sm">
                                        @if($transferCategory === 'internal')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Internal Transfer
                                            </span>
                                        @elseif($transferType === 'bank')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Bank Transfer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                Mobile Wallet
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
                                        Completed
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">Timestamp</span>
                                        <span class="text-xs text-gray-600">{{ now()->format('d M Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-center space-x-4">
                            <button wire:click="resetForm" 
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transform transition hover:scale-105">
                                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                New Transfer
                            </button>
                            <button onclick="window.print()" 
                                class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transform transition hover:scale-105">
                                <svg class="mr-2 -ml-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Receipt
                            </button>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>