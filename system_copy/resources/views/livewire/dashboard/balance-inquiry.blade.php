<div class="min-h-screen bg-gray-50 p-6">
    <!-- Loading Overlay -->
    @if($isLoading)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Loading...</p>
        </div>
    </div>
    @endif

    <!-- Error Messages -->
    @if($errorMessage)
    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
        <p class="font-bold">Error</p>
        <p>{{ $errorMessage }}</p>
    </div>
    @endif

    <!-- Success Messages -->
    @if($successMessage)
    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
        <p class="font-bold">Success</p>
        <p>{{ $successMessage }}</p>
    </div>
    @endif

    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Balance Inquiry</h1>
            <p class="text-gray-600 mt-1">Check account balances and transaction history</p>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
            <button 
                wire:click="resetInquiry" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span wire:loading.remove wire:target="resetInquiry">Reset</span>
                <span wire:loading wire:target="resetInquiry">Resetting...</span>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Accounts -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Accounts</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalAccounts) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Total active accounts in system</span>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Accounts</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($activeAccounts) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Currently active accounts</span>
            </div>
        </div>

        <!-- Total Balance -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Balance</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">TZS {{ number_format($totalBalance, 2) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Total balance across all accounts</span>
            </div>
        </div>

        <!-- Today's Inquiries -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Today's Inquiries</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($todayInquiries) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Balance inquiries today</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Member Verification & Account Selection -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Member Verification</h3>
            
            <!-- Membership Number Input -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Membership Number</label>
                <div class="flex gap-2">
                    <input wire:model="membershipNumber" type="text" 
                           class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter membership number">
                    <button wire:click="verifyMembership" 
                            wire:loading.attr="disabled"
                            class="px-4 py-3 bg-blue-900 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                        <span wire:loading.remove wire:target="verifyMembership">Verify</span>
                        <span wire:loading wire:target="verifyMembership">Verifying...</span>
                    </button>
                </div>
                @error('membershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            @if($verifiedMember)
            <!-- Member Details -->
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-800 font-medium">{{ $verifiedMember['name'] ?? 'Member Verified' }}</span>
                </div>
            </div>

            <!-- Account Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Account</label>
                <select wire:model="selectedAccount" 
                        wire:loading.attr="disabled"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Choose an account</option>
                    @foreach($memberAccounts as $account)
                        <option value="{{ $account->account_number }}">
                            {{ $account->account_name }} - {{ $this->getAccountTypeLabel($account->product_number) }} - TZS {{ number_format($account->balance, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            @endif
        </div>

        <!-- Balance Information -->
        @if($selectedAccount && $accountDetails)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Balance Information</h3>
            
            <div class="space-y-4">
                <!-- Current Balance -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-blue-800 font-medium">Current Balance</span>
                        <span class="text-2xl font-bold text-blue-900">TZS {{ number_format($currentBalance, 2) }}</span>
                    </div>
                </div>

                <!-- Available Balance -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-green-800 font-medium">Available Balance</span>
                        <span class="text-xl font-semibold text-green-900">TZS {{ number_format($availableBalance, 2) }}</span>
                    </div>
                </div>

                <!-- Locked Amount -->
                @if($lockedAmount > 0)
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-yellow-800 font-medium">Locked Amount</span>
                        <span class="text-lg font-semibold text-yellow-900">TZS {{ number_format($lockedAmount, 2) }}</span>
                    </div>
                </div>
                @endif

                <!-- Account Status -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-800 font-medium">Account Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColor($accountStatus) }}">
                            {{ $accountStatus }}
                        </span>
                    </div>
                </div>

                <!-- Account Type -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-800 font-medium">Account Type</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getAccountTypeColor($accountDetails->product_number) }}">
                            {{ $this->getAccountTypeLabel($accountDetails->product_number) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Transaction Summary -->
    @if($selectedAccount && $accountDetails)
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Summary</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Today's Transactions -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-900">{{ $todayTransactions }}</p>
                    <p class="text-blue-800 text-sm font-medium">Today's Transactions</p>
                </div>
            </div>

            <!-- This Month's Transactions -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-900">{{ $thisMonthTransactions }}</p>
                    <p class="text-green-800 text-sm font-medium">This Month's Transactions</p>
                </div>
            </div>

            <!-- Last Transaction -->
            @if($lastTransactionDate)
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <div class="text-center">
                    <p class="text-lg font-bold text-purple-900">TZS {{ number_format($lastTransactionAmount, 2) }}</p>
                    <p class="text-purple-800 text-sm font-medium">{{ $lastTransactionType }}</p>
                    <p class="text-purple-600 text-xs">{{ $lastTransactionDate->format('M d, Y') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>