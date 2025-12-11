<div class="bg-gray-50 p-6">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-xl p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Trial Balance</h1>
                <p class="text-gray-500 mt-1">Complete listing of all account balances</p>
            </div>
            <div class="flex space-x-2">
                <button wire:click="exportToExcel" 
                    class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel</span>
                </button>
                <button wire:click="exportToPDF" 
                    class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Export PDF</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filters and Controls Section --}}
    <div class="bg-white border-x border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- Date Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Balance as at</label>
                <input type="date" wire:model="selectedDate" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>

            {{-- Account Level Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Level</label>
                <select wire:model="accountLevel" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                    <option value="all">All Levels</option>
                    <option value="L1">Level 1 - Main</option>
                    <option value="L2">Level 2 - Category</option>
                    <option value="L3">Level 3 - Sub-Category</option>
                    <option value="L4">Level 4 - Detail</option>
                </select>
            </div>

            {{-- Category Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select wire:model="selectedCategory" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                    <option value="all">All Categories</option>
                    <option value="assets">Assets</option>
                    <option value="liabilities">Liabilities</option>
                    <option value="equity">Equity</option>
                    <option value="income">Income</option>
                    <option value="expenses">Expenses</option>
                </select>
            </div>

            {{-- Search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                    placeholder="Account name or number..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
            </div>
        </div>

        {{-- Additional Options --}}
        <div class="flex flex-wrap gap-4 items-center">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" wire:model="showZeroBalances" 
                    class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                <span class="text-sm text-gray-700">Show zero balances</span>
            </label>

            <button wire:click="toggleComparison" 
                class="px-4 py-2 text-sm {{ $showComparison ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded-lg hover:bg-red-700 transition-colors">
                <span class="flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>{{ $showComparison ? 'Hide' : 'Show' }} Comparison</span>
                </span>
            </button>
        </div>
    </div>

    {{-- Report Header --}}
    <div class="bg-white border-x border-gray-200 px-6 pb-4 text-center">
        <h2 class="text-lg font-bold text-gray-900">{{ $companyName }}</h2>
        <h3 class="text-base font-semibold text-gray-800">TRIAL BALANCE</h3>
        <p class="text-sm text-gray-600">
            As at {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}
            @if($showComparison)
                (Compared with {{ \Carbon\Carbon::parse($comparisonDate)->format('d F Y') }})
            @endif
        </p>
    </div>

    {{-- Trial Balance Table --}}
    <div class="bg-white border border-gray-200 rounded-b-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-32">
                            <button wire:click="sortByColumn('account_number')" class="flex items-center space-x-1 hover:text-red-600">
                                <span>Account No.</span>
                                @if($sortBy === 'account_number')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">
                            <button wire:click="sortByColumn('account_name')" class="flex items-center space-x-1 hover:text-red-600">
                                <span>Account Name</span>
                                @if($sortBy === 'account_name')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-24">Level</th>
                        <th class="border-r border-gray-200 px-4 py-3 text-left font-semibold text-gray-700 w-28">Category</th>
                        
                        @if($showComparison)
                            {{-- Current Period --}}
                            <th colspan="2" class="border-r border-gray-200 px-4 py-2 text-center font-semibold text-gray-700 bg-red-50">
                                Current Period
                            </th>
                            {{-- Previous Period --}}
                            <th colspan="2" class="border-r border-gray-200 px-4 py-2 text-center font-semibold text-gray-700 bg-gray-100">
                                Previous Period
                            </th>
                            {{-- Variance --}}
                            <th colspan="2" class="px-4 py-2 text-center font-semibold text-gray-700 bg-yellow-50">
                                Variance
                            </th>
                        @else
                            <th class="border-r border-gray-200 px-4 py-3 text-right font-semibold text-gray-700 w-32">
                                <button wire:click="sortByColumn('debit')" class="flex items-center justify-end space-x-1 hover:text-red-600 w-full">
                                    <span>Debit</span>
                                    @if($sortBy === 'debit')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 w-32">
                                <button wire:click="sortByColumn('credit')" class="flex items-center justify-end space-x-1 hover:text-red-600 w-full">
                                    <span>Credit</span>
                                    @if($sortBy === 'credit')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </button>
                            </th>
                        @endif
                    </tr>
                    @if($showComparison)
                        <tr class="border-t border-gray-200">
                            <th colspan="4" class="border-r border-gray-200"></th>
                            <th class="border-r border-gray-200 px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-red-50">Debit</th>
                            <th class="border-r border-gray-200 px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-red-50">Credit</th>
                            <th class="border-r border-gray-200 px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-gray-100">Debit</th>
                            <th class="border-r border-gray-200 px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-gray-100">Credit</th>
                            <th class="border-r border-gray-200 px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-yellow-50">Debit</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-600 text-xs bg-yellow-50">Credit</th>
                        </tr>
                    @endif
                </thead>
                
                <tbody>
                    @forelse($accounts as $index => $account)
                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-red-50 transition-colors">
                            <td class="border-r border-gray-200 px-4 py-2 text-gray-900 font-mono text-xs">
                                {{ $account['account_number'] }}
                            </td>
                            <td class="border-r border-gray-200 px-4 py-2 text-gray-800">
                                <span class="ml-{{ ($account['account_level'] - 1) * 4 }}">
                                    {{ $account['account_name'] }}
                                </span>
                            </td>
                            <td class="border-r border-gray-200 px-4 py-2 text-gray-600 text-center">
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">L{{ $account['account_level'] }}</span>
                            </td>
                            <td class="border-r border-gray-200 px-4 py-2 text-gray-600">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($account['category'] === 'Assets') bg-green-100 text-green-700
                                    @elseif($account['category'] === 'Liabilities') bg-red-100 text-red-700
                                    @elseif($account['category'] === 'Equity') bg-red-100 text-red-700
                                    @elseif($account['category'] === 'Income') bg-yellow-100 text-yellow-700
                                    @elseif($account['category'] === 'Expenses') bg-purple-100 text-purple-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ $account['category'] }}
                                </span>
                            </td>
                            
                            @if($showComparison)
                                {{-- Current Period --}}
                                <td class="border-r border-gray-200 px-4 py-2 text-right text-gray-900 bg-red-50/30">
                                    {{ $account['current_debit'] > 0 ? $this->formatNumber($account['current_debit']) : '-' }}
                                </td>
                                <td class="border-r border-gray-200 px-4 py-2 text-right text-gray-900 bg-red-50/30">
                                    {{ $account['current_credit'] > 0 ? $this->formatNumber($account['current_credit']) : '-' }}
                                </td>
                                {{-- Previous Period --}}
                                <td class="border-r border-gray-200 px-4 py-2 text-right text-gray-900 bg-gray-100/30">
                                    {{ $account['previous_debit'] > 0 ? $this->formatNumber($account['previous_debit']) : '-' }}
                                </td>
                                <td class="border-r border-gray-200 px-4 py-2 text-right text-gray-900 bg-gray-100/30">
                                    {{ $account['previous_credit'] > 0 ? $this->formatNumber($account['previous_credit']) : '-' }}
                                </td>
                                {{-- Variance --}}
                                <td class="border-r border-gray-200 px-4 py-2 text-right bg-yellow-50/30
                                    {{ $account['variance_debit'] > 0 ? 'text-green-600' : ($account['variance_debit'] < 0 ? 'text-red-600' : 'text-gray-900') }}">
                                    {{ $account['variance_debit'] != 0 ? $this->formatNumber($account['variance_debit']) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-right bg-yellow-50/30
                                    {{ $account['variance_credit'] > 0 ? 'text-green-600' : ($account['variance_credit'] < 0 ? 'text-red-600' : 'text-gray-900') }}">
                                    {{ $account['variance_credit'] != 0 ? $this->formatNumber($account['variance_credit']) : '-' }}
                                </td>
                            @else
                                <td class="border-r border-gray-200 px-4 py-2 text-right text-gray-900">
                                    {{ $account['current_debit'] > 0 ? $this->formatNumber($account['current_debit']) : '-' }}
                                </td>
                                <td class="px-4 py-2 text-right text-gray-900">
                                    {{ $account['current_credit'] > 0 ? $this->formatNumber($account['current_credit']) : '-' }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showComparison ? 10 : 6 }}" class="px-4 py-8 text-center text-gray-500">
                                No accounts found matching your criteria
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
                {{-- Totals Footer --}}
                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                    <tr class="font-bold">
                        <td colspan="4" class="border-r border-gray-200 px-4 py-3 text-right text-gray-900">
                            TOTAL
                        </td>
                        
                        @if($showComparison)
                            {{-- Current Period Totals --}}
                            <td class="border-r border-gray-200 px-4 py-3 text-right text-gray-900 bg-red-100">
                                {{ $this->formatNumber($totals['current']['debit']) }}
                            </td>
                            <td class="border-r border-gray-200 px-4 py-3 text-right text-gray-900 bg-red-100">
                                {{ $this->formatNumber($totals['current']['credit']) }}
                            </td>
                            {{-- Previous Period Totals --}}
                            <td class="border-r border-gray-200 px-4 py-3 text-right text-gray-900 bg-gray-200">
                                {{ $this->formatNumber($totals['previous']['debit']) }}
                            </td>
                            <td class="border-r border-gray-200 px-4 py-3 text-right text-gray-900 bg-gray-200">
                                {{ $this->formatNumber($totals['previous']['credit']) }}
                            </td>
                            {{-- Variance Totals --}}
                            <td class="border-r border-gray-200 px-4 py-3 text-right bg-yellow-100
                                {{ ($totals['current']['debit'] - $totals['previous']['debit']) > 0 ? 'text-green-600' : (($totals['current']['debit'] - $totals['previous']['debit']) < 0 ? 'text-red-600' : 'text-gray-900') }}">
                                {{ $this->formatNumber($totals['current']['debit'] - $totals['previous']['debit']) }}
                            </td>
                            <td class="px-4 py-3 text-right bg-yellow-100
                                {{ ($totals['current']['credit'] - $totals['previous']['credit']) > 0 ? 'text-green-600' : (($totals['current']['credit'] - $totals['previous']['credit']) < 0 ? 'text-red-600' : 'text-gray-900') }}">
                                {{ $this->formatNumber($totals['current']['credit'] - $totals['previous']['credit']) }}
                            </td>
                        @else
                            <td class="border-r border-gray-200 px-4 py-3 text-right text-gray-900">
                                {{ $this->formatNumber($totals['current']['debit']) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                {{ $this->formatNumber($totals['current']['credit']) }}
                            </td>
                        @endif
                    </tr>
                    
                    {{-- Balance Check Row --}}
                    <tr class="border-t border-gray-300">
                        <td colspan="4" class="border-r border-gray-200 px-4 py-2 text-right text-gray-700 text-sm">
                            Balance Check (Debit - Credit):
                        </td>
                        @if($showComparison)
                            <td colspan="2" class="border-r border-gray-200 px-4 py-2 text-center font-bold 
                                {{ $totals['current']['balance'] == 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->formatNumber($totals['current']['balance']) }}
                                @if($totals['current']['balance'] == 0)
                                    <span class="ml-2 text-xs">(Balanced ✓)</span>
                                @else
                                    <span class="ml-2 text-xs">(Out of Balance ✗)</span>
                                @endif
                            </td>
                            <td colspan="2" class="border-r border-gray-200 px-4 py-2 text-center font-bold 
                                {{ $totals['previous']['balance'] == 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->formatNumber($totals['previous']['balance']) }}
                                @if($totals['previous']['balance'] == 0)
                                    <span class="ml-2 text-xs">(Balanced ✓)</span>
                                @else
                                    <span class="ml-2 text-xs">(Out of Balance ✗)</span>
                                @endif
                            </td>
                            <td colspan="2" class="px-4 py-2"></td>
                        @else
                            <td colspan="2" class="px-4 py-2 text-center font-bold 
                                {{ $totals['current']['balance'] == 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->formatNumber($totals['current']['balance']) }}
                                @if($totals['current']['balance'] == 0)
                                    <span class="ml-2 text-xs">(Balanced ✓)</span>
                                @else
                                    <span class="ml-2 text-xs">(Out of Balance ✗)</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Account Summary</h4>
            <p class="text-2xl font-bold text-gray-900">{{ count($accounts) }}</p>
            <p class="text-xs text-gray-600">Total accounts displayed</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Total Debits</h4>
            <p class="text-2xl font-bold text-gray-900">{{ $this->formatNumber($totals['current']['debit']) }}</p>
            <p class="text-xs text-gray-600">Current period</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Total Credits</h4>
            <p class="text-2xl font-bold text-gray-900">{{ $this->formatNumber($totals['current']['credit']) }}</p>
            <p class="text-xs text-gray-600">Current period</p>
        </div>
    </div>
</div>