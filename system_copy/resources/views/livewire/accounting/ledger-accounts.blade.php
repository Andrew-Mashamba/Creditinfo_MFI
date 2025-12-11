<div class="w-full">


    <!-- Professional Summary Cards Grid -->
    <div class="bg-white border-b">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Financial Position Summary</h2>
            
            <!-- Main Summary Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <!-- Assets Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-blue-50 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">TOTAL ASSETS</h3>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ number_format($accountSummary['summary']['ASSET']['balance'] ?? 0, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $accountSummary['summary']['ASSET']['count'] ?? 0 }} Accounts
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liabilities Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-red-50 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">TOTAL LIABILITIES</h3>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ number_format($accountSummary['summary']['LIABILITY']['balance'] ?? 0, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $accountSummary['summary']['LIABILITY']['count'] ?? 0 }} Accounts
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equity Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-2 bg-blue-50 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-600">TOTAL EQUITY</h3>
                                    <p class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ number_format($accountSummary['summary']['EQUITY']['balance'] ?? 0, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $accountSummary['summary']['EQUITY']['count'] ?? 0 }} Accounts
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Income Statement Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Revenue Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">REVENUE</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ number_format($accountSummary['summary']['INCOME']['balance'] ?? 0, 2) }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $accountSummary['summary']['INCOME']['count'] ?? 0 }} Accounts</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>

                <!-- Expenses Card -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">EXPENSES</h3>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ number_format($accountSummary['summary']['EXPENSE']['balance'] ?? 0, 2) }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $accountSummary['summary']['EXPENSE']['count'] ?? 0 }} Accounts</p>
                        </div>
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>

                <!-- Net Income Card -->
                <div class="bg-blue-900 text-white rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">NET INCOME</h3>
                            <p class="text-xl font-bold mt-1">
                                {{ number_format($accountSummary['metrics']['net_income'], 2) }}
                            </p>
                            <p class="text-xs text-blue-200">Period Result</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accounting Equation Validation -->
    <div class="bg-white px-6 py-3 border-b">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                @if($accountSummary['metrics']['accounting_equation_check'])
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-blue-900 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium text-gray-700">Accounting Equation:</span>
                        <span class="ml-2 text-gray-600">
                            Assets ({{ number_format($accountSummary['metrics']['total_assets'], 2) }}) = 
                            Liabilities ({{ number_format($accountSummary['metrics']['total_liabilities'], 2) }}) + 
                            Equity ({{ number_format($accountSummary['metrics']['total_equity'], 2) }})
                        </span>
                        <span class="ml-3 px-2 py-1 bg-blue-50 text-blue-900 text-xs font-medium rounded">BALANCED</span>
                    </div>
                @else
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium text-gray-700">Accounting Equation:</span>
                        <span class="ml-2 text-gray-600">
                            Assets ({{ number_format($accountSummary['metrics']['total_assets'], 2) }}) ≠ 
                            Liabilities ({{ number_format($accountSummary['metrics']['total_liabilities'], 2) }}) + 
                            Equity ({{ number_format($accountSummary['metrics']['total_equity'], 2) }})
                        </span>
                        <span class="ml-3 px-2 py-1 bg-red-50 text-red-600 text-xs font-medium rounded">IMBALANCED</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-gray-50 border-b px-6 py-4">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filter & Search</h3>
                <div class="flex items-center mt-2 text-xs text-gray-500 space-x-4">
                    <div class="flex items-center">
                        <span class="inline-block w-2 h-2 bg-blue-900 rounded-full mr-1"></span>
                        <span>Level 1: Major Categories</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-2 h-2 bg-blue-700 rounded-full mr-1"></span>
                        <span>Level 2: Categories</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-2 h-2 bg-gray-400 rounded-full mr-1"></span>
                        <span>Level 3: Sub-Categories</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-2 h-2 bg-gray-300 rounded-full mr-1"></span>
                        <span>Level 4: Detail Accounts</span>
                    </div>
                </div>
            </div>
            <button wire:click="openCreateModal()" 
                class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Account
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Search Accounts</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                    placeholder="Search by name, number or code..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Account Type</label>
                <select wire:model="selectedType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Types</option>
                    @foreach($accountTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Account Level</label>
                <select wire:model="selectedLevel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Levels</option>
                    @foreach($accountLevels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="resetFilters" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium w-full">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Chart of Accounts Table -->
    <div class="bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                Account Hierarchy
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Account Number</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Credit</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            <div class="flex items-center justify-end">
                                Balance
                                <svg class="w-3 h-3 ml-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accountsHierarchy as $account)
                        @include('livewire.accounting.partials.account-row', ['account' => $account, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">No accounts found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filter criteria</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trial Balance Footer -->
    <div class="bg-gray-50 px-6 py-4 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm font-medium text-gray-700">
                Trial Balance Summary
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-sm">
                    <span class="text-gray-600">Total Debits:</span>
                    <span class="font-bold text-gray-900">{{ number_format($trialBalance['totals']['debits'], 2) }}</span>
                </div>
                <div class="text-sm">
                    <span class="text-gray-600">Total Credits:</span>
                    <span class="font-bold text-gray-900">{{ number_format($trialBalance['totals']['credits'], 2) }}</span>
                </div>
                <div class="text-sm">
                    @if($trialBalance['totals']['is_balanced'])
                        <span class="px-3 py-1 bg-blue-50 text-blue-900 rounded text-xs font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            BALANCED
                        </span>
                    @else
                        <span class="px-3 py-1 bg-red-50 text-red-600 rounded text-xs font-medium">
                            Difference: {{ number_format($trialBalance['totals']['difference'], 2) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Modal -->
    @if($showLedgerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div wire:click="closeLedgerModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="relative inline-block w-full max-w-6xl px-4 pt-5 pb-4 text-left align-bottom bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:p-6">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button wire:click="closeLedgerModal" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Account Ledger: {{ $selectedAccountData['account_name'] ?? '' }}
                                </h3>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-sm text-gray-500">Account: {{ $selectedAccount }}</span>
                                    <span class="text-sm text-gray-500">Type: {{ $selectedAccountData['type'] ?? '' }}</span>
                                    <span class="text-sm font-semibold text-gray-700">
                                        Current Balance: {{ number_format(floatval($selectedAccountData['balance'] ?? 0), 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($ledgerEntries as $entry)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2 text-xs text-gray-900">
                                                    {{ \Carbon\Carbon::parse($entry['created_at'])->format('Y-m-d') }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-600 font-mono">
                                                    {{ $entry['record_on_account_number'] ?? '' }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-900">{{ $entry['reference_number'] ?? '' }}</td>
                                                <td class="px-3 py-2 text-xs text-gray-900">
                                                    {{ Str::limit($entry['narration'] ?? '', 50) }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-900 text-right font-mono">
                                                    {{ isset($entry['debit']) && $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                                                </td>
                                                <td class="px-3 py-2 text-xs text-gray-900 text-right font-mono">
                                                    {{ isset($entry['credit']) && $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                                                </td>
                                                <td class="px-3 py-2 text-xs font-semibold text-gray-900 text-right font-mono">
                                                    {{ number_format($entry['record_on_account_number_balance'] ?? 0, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                    No ledger entries found for this account
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Account Details Modal -->
    @if($showDetailsModal && $selectedAccountData)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div wire:click="closeDetailsModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="relative inline-block w-full max-w-4xl px-4 pt-5 pb-4 text-left align-bottom bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:p-6">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button wire:click="closeDetailsModal" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Details</h3>
                            
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Account Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $selectedAccountData['account_name'] ?? '' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedAccountData['account_number'] ?? '' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $selectedAccountData['type'] ?? 'Not specified' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Account Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900">Level {{ $selectedAccountData['account_level'] ?? '' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Category Codes</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">
                                            {{ $selectedAccountData['major_category_code'] ?? '' }}-{{ $selectedAccountData['category_code'] ?? '' }}-{{ $selectedAccountData['sub_category_code'] ?? '' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Account Use</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($selectedAccountData['account_use'] ?? 'Not specified') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ number_format(floatval($selectedAccountData['balance'] ?? 0), 2) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 py-1 text-xs rounded-full {{ ($selectedAccountData['status'] ?? '') == 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $selectedAccountData['status'] ?? '' }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                                
                                @if(isset($selectedAccountData['notes']) && $selectedAccountData['notes'])
                                    <div class="mt-4">
                                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $selectedAccountData['notes'] }}</dd>
                                    </div>
                                @endif
                            </div>
                            
                            @if(isset($selectedAccountData['parent']) && $selectedAccountData['parent'])
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Parent Account</h4>
                                    <div class="bg-blue-50 rounded p-3">
                                        <span class="text-sm text-gray-900">{{ $selectedAccountData['parent']['account_name'] ?? '' }}</span>
                                        <span class="text-xs text-gray-500 ml-2">({{ $selectedAccountData['parent']['account_number'] ?? '' }})</span>
                                    </div>
                                </div>
                            @endif
                            
                            @if(isset($selectedAccountData['children']) && count($selectedAccountData['children']) > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Child Accounts ({{ count($selectedAccountData['children']) }})</h4>
                                    <div class="bg-gray-50 rounded-lg p-3 max-h-40 overflow-y-auto">
                                        @foreach($selectedAccountData['children'] as $child)
                                            <div class="text-sm text-gray-900 py-1">
                                                {{ $child['account_name'] ?? '' }}
                                                <span class="text-xs text-gray-500 ml-2">({{ $child['account_number'] ?? '' }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Account Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ isset($parentAccountNumber) && $parentAccountNumber ? 'Add Sub-Account' : 'Create New Account' }}
                            </h3>
                            <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="createAccount" class="mt-4 space-y-4">
                            @if(isset($parentAccountNumber) && $parentAccountNumber && isset($parentAccountData) && $parentAccountData)
                                <!-- Parent Account Information -->
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Parent Account Details</h4>
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-600">Name:</span>
                                            <span class="text-gray-900 ml-2">{{ $parentAccountData['account_name'] ?? '' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Number:</span>
                                            <span class="text-gray-900 ml-2 font-mono">{{ $parentAccountNumber }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Type:</span>
                                            <span class="text-gray-900 ml-2">{{ $parentAccountData['type'] ?? '' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Level:</span>
                                            <span class="text-gray-900 ml-2">{{ $parentAccountData['account_level'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-2 bg-blue-100 rounded text-xs text-blue-800">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        The new sub-account will inherit all properties from the parent account
                                    </div>
                                </div>

                                <!-- Only Account Name Field for Sub-Accounts -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Sub-Account Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                        wire:model.defer="newAccount.account_name" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="Enter the sub-account name"
                                        required
                                        autofocus>
                                    @error('newAccount.account_name')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <!-- Full form for top-level accounts -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Account Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" wire:model.defer="newAccount.account_name" 
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            required>
                                        @error('newAccount.account_name')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Account Type <span class="text-red-500">*</span>
                                            </label>
                                            <select wire:model.defer="newAccount.type" 
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                required>
                                                <option value="">Select Type</option>
                                                <option value="ASSET">Asset</option>
                                                <option value="LIABILITY">Liability</option>
                                                <option value="EQUITY">Equity</option>
                                                <option value="INCOME">Income</option>
                                                <option value="EXPENSE">Expense</option>
                                            </select>
                                            @error('newAccount.type')
                                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Number <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model.defer="newAccount.product_number" 
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                required>
                                            @error('newAccount.product_number')
                                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" wire:click="closeCreateModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <span wire:loading.remove>Create Account</span>
                                    <span wire:loading>Creating...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Account Modal -->
    @if($showEditModal && $editingAccount)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">Edit Account</h3>
                            <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="updateAccount" class="mt-4 space-y-4">
                            <!-- Account Information Display -->
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Account Number:</span>
                                    <span class="text-gray-900 ml-2 font-mono">{{ $editingAccount['account_number'] ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Account Type:</span>
                                    <span class="text-gray-900 ml-2">{{ $editingAccount['type'] ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Account Level:</span>
                                    <span class="text-gray-900 ml-2">{{ $editingAccount['account_level'] ?? '' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Current Balance:</span>
                                    <span class="text-gray-900 ml-2 font-mono">{{ number_format(floatval($editingAccount['balance'] ?? 0), 2) }}</span>
                                </div>
                            </div>

                            <!-- Editable Fields -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Account Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    wire:model.defer="editingAccount.account_name" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    required>
                                @error('editingAccount.account_name')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea 
                                    wire:model.defer="editingAccount.notes" 
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    rows="3"></textarea>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" wire:click="closeEditModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800">
                                    <span wire:loading.remove>Update Account</span>
                                    <span wire:loading>Updating...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Block Account Confirmation Modal -->
    @if($showBlockModal && $blockingAccount)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-lg sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <h3 class="text-lg font-medium text-gray-900">Block Account</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to block this account?
                                </p>
                                <div class="mt-3 bg-gray-50 rounded-lg p-3 text-left">
                                    <div class="text-sm space-y-1">
                                        <div>
                                            <span class="font-medium text-gray-600">Account:</span>
                                            <span class="text-gray-900 ml-2">{{ $blockingAccount['account_name'] ?? '' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-600">Number:</span>
                                            <span class="text-gray-900 ml-2 font-mono">{{ $blockingAccount['account_number'] ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-red-600 mt-3">
                                    This action will prevent any transactions on this account until it is unblocked.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 sm:mt-6 flex space-x-3">
                            <button type="button" wire:click="closeBlockModal"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="button" wire:click="blockAccount"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                <span wire:loading.remove>Block Account</span>
                                <span wire:loading>Blocking...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>