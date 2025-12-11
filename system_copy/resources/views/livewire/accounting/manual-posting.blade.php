<div class="bg-white rounded-lg shadow-sm">
    <!-- Compact Header -->
    <div class="bg-blue-900 text-white px-4 py-2 rounded-t-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <h2 class="text-sm font-semibold">Manual Transaction Posting</h2>
            </div>
            <span class="text-xs bg-blue-800 px-2 py-1 rounded">Double Entry</span>
        </div>
    </div>

    <!-- Alert Messages -->
    <div class="px-4 pt-2">
        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-2 py-1 rounded text-xs mb-2">
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-2 py-1 rounded text-xs mb-2">
                <div class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>





















    <!-- Main Content -->
    <div class="p-4">
        <!-- Account Selection Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
            <!-- Debit Account Section -->
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-2">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">DEBIT ACCOUNT</h3>
                    </div>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded">DR</span>
                </div>


                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search Account</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.debounce.300ms="search" 
                               placeholder="Type account name or number..."
                               class="w-full bg-white border border-gray-300 text-sm rounded-md px-2 py-1.5 pr-8 focus:border-blue-900 focus:ring-1 focus:ring-blue-900" />
                        <svg class="w-4 h-4 text-gray-400 absolute right-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Search Results Dropdown -->
                    @if ($showDropdown && !empty($results))
                        <div class="absolute bg-white border border-gray-200 rounded-md shadow-lg mt-1 w-full z-20 max-h-48 overflow-y-auto">
                            @foreach ($results as $result)
                                <div class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0"
                                     wire:click="selectAccount('{{ $result->id }}', '{{ $result->source }}')">
                                    <div class="text-sm font-medium text-gray-900">{{ $result->account_name }}</div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">{{ $result->account_number }}</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-1 py-0.5 rounded">{{ ucfirst($result->source) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Selected Account Display -->
                @if (!empty($selectedAccount))
                    <div class="mt-2 bg-white border border-blue-200 rounded-md p-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-blue-900">Selected Account</span>
                            <button wire:click="$set('selectedAccount', [])" class="text-gray-400 hover:text-red-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500">Name:</span>
                                <p class="font-medium text-gray-900 truncate">{{ $selectedAccount['account_name'] }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Number:</span>
                                <p class="font-mono text-gray-900">{{ $selectedAccount['account_number'] }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Balance:</span>
                                <p class="font-semibold text-gray-900">{{ number_format($selectedAccount['balance'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Status:</span>
                                <span class="inline-flex px-1 py-0.5 rounded text-xs font-medium
                                    {{ $selectedAccount['status'] == 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $selectedAccount['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-2 bg-gray-100 border border-gray-200 rounded-md p-2 text-center">
                        <p class="text-xs text-gray-500">No account selected</p>
                    </div>
                @endif
            </div>

            <!-- Credit Account Section -->
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-2">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">CREDIT ACCOUNT</h3>
                    </div>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">CR</span>
                </div>



                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search Account</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.debounce.300ms="searchTwo" 
                               placeholder="Type account name or number..."
                               class="w-full bg-white border border-gray-300 text-sm rounded-md px-2 py-1.5 pr-8 focus:border-blue-900 focus:ring-1 focus:ring-blue-900" />
                        <svg class="w-4 h-4 text-gray-400 absolute right-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Search Results Dropdown -->
                    @if ($showDropdownTwo && !empty($resultsTwo))
                        <div class="absolute bg-white border border-gray-200 rounded-md shadow-lg mt-1 w-full z-20 max-h-48 overflow-y-auto">
                            @foreach ($resultsTwo as $result)
                                <div class="px-2 py-1.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0"
                                     wire:click="selectAccountTwo('{{ $result->id }}', '{{ $result->source }}')">
                                    <div class="text-sm font-medium text-gray-900">{{ $result->account_name }}</div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">{{ $result->account_number }}</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-1 py-0.5 rounded">{{ ucfirst($result->source) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Selected Account Display -->
                @if (!empty($selectedAccountTwo))
                    <div class="mt-2 bg-white border border-blue-200 rounded-md p-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-blue-900">Selected Account</span>
                            <button wire:click="$set('selectedAccountTwo', [])" class="text-gray-400 hover:text-red-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500">Name:</span>
                                <p class="font-medium text-gray-900 truncate">{{ $selectedAccountTwo['account_name'] }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Number:</span>
                                <p class="font-mono text-gray-900">{{ $selectedAccountTwo['account_number'] }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Balance:</span>
                                <p class="font-semibold text-gray-900">{{ number_format($selectedAccountTwo['balance'] ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Status:</span>
                                <span class="inline-flex px-1 py-0.5 rounded text-xs font-medium
                                    {{ $selectedAccountTwo['status'] == 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $selectedAccountTwo['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-2 bg-gray-100 border border-gray-200 rounded-md p-2 text-center">
                        <p class="text-xs text-gray-500">No account selected</p>
                    </div>
                @endif
            </div>
        </div>



        <!-- Transaction Details Section -->
        <div class="bg-white border border-gray-200 rounded-lg p-3">
            <div class="flex items-center mb-2">
                <svg class="w-4 h-4 text-blue-900 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-700">TRANSACTION DETAILS</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Amount Input -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
                    <div class="relative">
                        <span class="absolute left-2 top-1.5 text-gray-500 text-sm">$</span>
                        <input wire:model="amount" 
                               type="number" 
                               step="0.01" 
                               min="0.01" 
                               class="w-full pl-6 pr-2 py-1.5 bg-white border @error('amount') border-red-500 @else border-gray-300 @enderror text-sm rounded-md focus:border-blue-900 focus:ring-1 focus:ring-blue-900" 
                               placeholder="0.00" 
                               required />
                    </div>
                    @error('amount')
                        <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>
                
                <!-- Narration Input -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description/Narration</label>
                    <input wire:model="narration" 
                           type="text" 
                           class="w-full px-2 py-1.5 bg-white border @error('narration') border-red-500 @else border-gray-300 @enderror text-sm rounded-md focus:border-blue-900 focus:ring-1 focus:ring-blue-900" 
                           placeholder="Enter transaction description" 
                           required />
                    @error('narration')
                        <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Transaction Summary -->
            @if(!empty($selectedAccount) && !empty($selectedAccountTwo) && $amount)
                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-md p-2">
                    <h4 class="text-xs font-semibold text-blue-900 mb-1">Transaction Preview</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center">
                            <span class="text-red-600 mr-1">DR:</span>
                            <span class="text-gray-700">{{ $selectedAccount['account_name'] ?? 'Not selected' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-green-600 mr-1">CR:</span>
                            <span class="text-gray-700">{{ $selectedAccountTwo['account_name'] ?? 'Not selected' }}</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-blue-200">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-semibold text-gray-900 ml-1">${{ number_format($amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-3">
                <button wire:click="resetInputFields" 
                        type="button"
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear
                </button>
                
                <div class="flex items-center space-x-2">
                    <!-- Loading State -->
                    <div wire:loading wire:target="post">
                        <button class="px-3 py-1.5 text-xs font-medium text-white bg-blue-400 rounded-md cursor-not-allowed" disabled>
                            <svg class="animate-spin h-3 w-3 inline mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Posting...
                        </button>
                    </div>
                    
                    <!-- Submit Button -->
                    <button wire:loading.remove 
                            wire:click="post" 
                            @if(empty($selectedAccount) || empty($selectedAccountTwo) || !$amount || !$narration) disabled @endif
                            class="px-4 py-1.5 text-xs font-medium text-white bg-blue-900 rounded-md hover:bg-blue-800 transition-colors
                                   @if(empty($selectedAccount) || empty($selectedAccountTwo) || !$amount || !$narration) opacity-50 cursor-not-allowed @endif">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Post Transaction
                    </button>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Optional: Recent Transactions Quick View -->
    <div class="px-4 pb-3">
        <div class="bg-gray-50 rounded-lg p-2 border border-gray-200">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-xs font-semibold text-gray-700">Quick Tips</h4>
                <svg class="w-3 h-3 text-blue-900" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <ul class="text-xs text-gray-600 space-y-0.5">
                <li class="flex items-start">
                    <span class="text-blue-900 mr-1">•</span>
                    <span>Search accounts by typing name or account number</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-900 mr-1">•</span>
                    <span>The system automatically determines debit/credit based on account types</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-900 mr-1">•</span>
                    <span>All transactions are recorded with unique reference numbers for tracking</span>
                </li>
            </ul>
        </div>
    </div>
</div>
