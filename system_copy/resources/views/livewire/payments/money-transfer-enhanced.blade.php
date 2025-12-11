<div class="max-w-6xl mx-auto p-6" x-data="{ showNotification: false, notificationType: '', notificationMessage: '' }">
    {{-- Header with Status Badge --}}
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Funds Transfer</h1>
                <p class="mt-2 text-sm text-gray-600">Fast, secure transfers to NBC accounts, other banks, and mobile wallets</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                    System Online
                </span>
            </div>
        </div>
    </div>

    {{-- Enhanced Progress Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-center">
            <div class="flex items-center space-x-8">
                {{-- Step 1: Details --}}
                <div class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300
                            @if($currentPhase === 'form') 
                                border-2 border-blue-500 bg-blue-500 text-white shadow-lg ring-4 ring-blue-100
                            @elseif(in_array($currentPhase, ['verify', 'processing', 'complete'])) 
                                border-2 border-green-500 bg-green-500 text-white
                            @else 
                                border-2 border-gray-300 bg-gray-100 text-gray-500 
                            @endif">
                            @if(in_array($currentPhase, ['verify', 'processing', 'complete']))
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                1
                            @endif
                        </div>
                        @if($currentPhase === 'form')
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-blue-500 rounded-full animate-ping"></div>
                        @endif
                    </div>
                    <div>
                        <span class="text-sm font-semibold @if($currentPhase === 'form') text-blue-600 @elseif(in_array($currentPhase, ['verify', 'processing', 'complete'])) text-green-600 @else text-gray-500 @endif">
                            Enter Details
                        </span>
                        <p class="text-xs text-gray-500">Transfer information</p>
                    </div>
                </div>

                <div class="flex-1 h-0.5 bg-gray-200 relative">
                    <div class="h-full bg-gradient-to-r from-green-500 to-green-400 transition-all duration-500"
                         style="width: {{ in_array($currentPhase, ['verify', 'processing', 'complete']) ? '100%' : '0%' }}">
                    </div>
                </div>

                {{-- Step 2: Verify --}}
                <div class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300
                            @if($currentPhase === 'verify') 
                                border-2 border-blue-500 bg-blue-500 text-white shadow-lg ring-4 ring-blue-100
                            @elseif(in_array($currentPhase, ['processing', 'complete'])) 
                                border-2 border-green-500 bg-green-500 text-white
                            @else 
                                border-2 border-gray-300 bg-gray-100 text-gray-500 
                            @endif">
                            @if(in_array($currentPhase, ['processing', 'complete']))
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                2
                            @endif
                        </div>
                        @if($currentPhase === 'verify')
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-blue-500 rounded-full animate-ping"></div>
                        @endif
                    </div>
                    <div>
                        <span class="text-sm font-semibold @if($currentPhase === 'verify') text-blue-600 @elseif(in_array($currentPhase, ['processing', 'complete'])) text-green-600 @else text-gray-500 @endif">
                            Verify & Confirm
                        </span>
                        <p class="text-xs text-gray-500">Review details</p>
                    </div>
                </div>

                <div class="flex-1 h-0.5 bg-gray-200 relative">
                    <div class="h-full bg-gradient-to-r from-green-500 to-green-400 transition-all duration-500"
                         style="width: {{ $currentPhase === 'complete' ? '100%' : '0%' }}">
                    </div>
                </div>

                {{-- Step 3: Complete --}}
                <div class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300
                            @if($currentPhase === 'complete') 
                                border-2 border-green-500 bg-green-500 text-white shadow-lg ring-4 ring-green-100
                            @else 
                                border-2 border-gray-300 bg-gray-100 text-gray-500 
                            @endif">
                            @if($currentPhase === 'complete')
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                3
                            @endif
                        </div>
                        @if($currentPhase === 'complete')
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full animate-ping"></div>
                        @endif
                    </div>
                    <div>
                        <span class="text-sm font-semibold @if($currentPhase === 'complete') text-green-600 @else text-gray-500 @endif">
                            Complete
                        </span>
                        <p class="text-xs text-gray-500">Transfer successful</p>
                    </div>
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
                            <h3 class="text-sm font-semibold text-red-800">Transfer Failed</h3>
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

    {{-- Keep existing main content but add this at the end of complete phase --}}
    <!-- Rest of the original blade file content goes here -->
    <!-- Just showing the enhanced complete phase as example -->

    @if($currentPhase === 'complete')
        {{-- Enhanced Complete Phase --}}
        <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-1"></div>
            <div class="p-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-4">
                        <svg class="h-16 w-16 text-green-600 animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">Transfer Successful!</h3>
                    <p class="text-gray-600 mb-8">Your funds have been transferred successfully.</p>
                    
                    <div class="bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 rounded-xl p-6 text-left max-w-2xl mx-auto border border-green-200 shadow-inner">
                        <h4 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">Transaction Receipt</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Reference Number</p>
                                <p class="font-mono text-sm font-bold text-gray-900 bg-white px-3 py-2 rounded mt-1 border border-gray-200">{{ $transactionReference ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Amount</p>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($amount ?? 0, 2) }} <span class="text-sm text-gray-600">TZS</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Transfer Type</p>
                                <p class="font-semibold text-gray-900 mt-1">
                                    @if($transferCategory === 'internal')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Internal Transfer
                                        </span>
                                    @elseif($transferType === 'bank')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Bank Transfer ({{ $routingSystem ?? 'TIPS' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Mobile Wallet
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                    Completed
                                </span>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Timestamp</p>
                                <p class="text-sm text-gray-700 mt-1">{{ now()->format('l, d F Y at H:i:s') }}</p>
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
            </div>
        </div>
    @endif
</div>