<div class="min-h-screen bg-gray-50 p-6">
    <!-- Loading Overlay -->
    @if($isLoading)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
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
            <h1 class="text-2xl font-bold text-gray-900">Statement Requests</h1>
            <p class="text-gray-600 mt-1">Generate and download account statements</p>
        </div>
        <div class="flex gap-3 w-full sm:w-auto">
            <button 
                wire:click="resetStatement" 
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span wire:loading.remove wire:target="resetStatement">Reset</span>
                <span wire:loading wire:target="resetStatement">Resetting...</span>
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Statements -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Statements</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalStatements) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Total statements generated</span>
            </div>
        </div>

        <!-- Today's Statements -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Today's Statements</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($todayStatements) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Statements generated today</span>
            </div>
        </div>

        <!-- Pending Statements -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Statements</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($pendingStatements) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Statements awaiting processing</span>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Accounts</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($activeAccounts) }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span>Active accounts available</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Statement Generation Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Statement</h3>
            
            <form wire:submit.prevent="generateStatement">
                <!-- Member Verification -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Membership Number</label>
                    <div class="flex gap-2">
                        <input wire:model="membershipNumber" type="text" 
                               class="flex-1 p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter membership number">
                        <button type="button" wire:click="verifyMembership" 
                                wire:loading.attr="disabled"
                                class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
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
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Choose an account</option>
                        @foreach($memberAccounts as $account)
                            <option value="{{ $account->account_number }}">
                                {{ $account->account_name }} - {{ $this->getAccountTypeLabel($account->product_number) }} - TZS {{ number_format($account->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input wire:model="startDate" type="date" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input wire:model="endDate" type="date" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Statement Type -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statement Type</label>
                    <select wire:model="statementType" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="detailed">Detailed Statement</option>
                        <option value="summary">Summary Statement</option>
                    </select>
                    @error('statementType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Format Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Download Format</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input wire:model="format" type="radio" value="pdf" class="mr-2">
                            <span class="text-sm text-gray-700">PDF</span>
                        </label>
                        <label class="flex items-center">
                            <input wire:model="format" type="radio" value="excel" class="mr-2">
                            <span class="text-sm text-gray-700">Excel</span>
                        </label>
                    </div>
                    @error('format') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Generate Button -->
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="generateStatement">Generate Statement</span>
                    <span wire:loading wire:target="generateStatement">Generating...</span>
                </button>
                @endif
            </form>
        </div>

        <!-- Statement Preview -->
        @if($statementData)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statement Preview</h3>
            
            <div class="space-y-4">
                <!-- Account Information -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Account Information</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Account Number:</span>
                            <span class="font-medium">{{ $statementData['account']->account_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Account Name:</span>
                            <span class="font-medium">{{ $statementData['account']->account_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Account Type:</span>
                            <span class="font-medium">{{ $this->getAccountTypeLabel($statementData['account']->product_number) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Period Information -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Statement Period</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">From:</span>
                            <span class="font-medium">{{ $statementData['period_start']->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">To:</span>
                            <span class="font-medium">{{ $statementData['period_end']->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Generated:</span>
                            <span class="font-medium">{{ $statementData['generated_at']->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Balance Summary -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">Balance Summary</h4>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Opening Balance:</span>
                            <span class="font-medium text-blue-900">TZS {{ number_format($statementData['opening_balance'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Total Credits:</span>
                            <span class="font-medium text-blue-900">TZS {{ number_format($statementData['total_credits'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Total Debits:</span>
                            <span class="font-medium text-blue-900">TZS {{ number_format($statementData['total_debits'], 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-blue-200 pt-1">
                            <span class="text-blue-700 font-medium">Closing Balance:</span>
                            <span class="font-bold text-blue-900">TZS {{ number_format($statementData['closing_balance'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Transaction Count -->
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-900">{{ $statementData['transactions']->count() }}</p>
                        <p class="text-green-800 text-sm font-medium">Transactions in Period</p>
                    </div>
                </div>

                <!-- Download Button -->
                <button wire:click="downloadStatement" 
                        wire:loading.attr="disabled"
                        class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="downloadStatement">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download {{ strtoupper($format) }} Statement
                    </span>
                    <span wire:loading wire:target="downloadStatement">Downloading...</span>
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Transaction Details -->
    @if($statementData && $statementData['transactions']->count() > 0)
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Details</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($statementData['transactions'] as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction['date']->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction['reference'] }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $transaction['description'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($transaction['debit'] > 0)
                                TZS {{ number_format($transaction['debit'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($transaction['credit'] > 0)
                                TZS {{ number_format($transaction['credit'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            TZS {{ number_format($transaction['balance'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>