<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="$set('activeTab', 'overview')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Interest Receivable
            </button>
            <button wire:click="$set('activeTab', 'accruals')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'accruals' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Daily Accruals
            </button>
            <button wire:click="$set('activeTab', 'income-report')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'income-report' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Income Report
            </button>
            <button wire:click="$set('activeTab', 'audit-trail')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'audit-trail' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Income Audit Trail
            </button>
        </nav>
    </div>

    {{-- OVERVIEW TAB --}}
    @if($activeTab === 'overview')
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Loans</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_loans'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Interest Receivable</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($summary['total_interest_receivable'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Overdue Interest</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($summary['overdue_interest'] ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Collection Rate</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($collectionRate, 1) }}%</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aged Receivables Analysis --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aged Interest Receivables</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Current (0-30 days)</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($agedReceivables['current'] ?? 0, 2) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">31-60 Days</p>
                    <p class="text-lg font-bold text-yellow-600">{{ number_format($agedReceivables['31_60_days'] ?? 0, 2) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">61-90 Days</p>
                    <p class="text-lg font-bold text-orange-600">{{ number_format($agedReceivables['61_90_days'] ?? 0, 2) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">91-180 Days</p>
                    <p class="text-lg font-bold text-red-600">{{ number_format($agedReceivables['91_180_days'] ?? 0, 2) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Over 180 Days</p>
                    <p class="text-lg font-bold text-red-800">{{ number_format($agedReceivables['over_180_days'] ?? 0, 2) }}</p>
                </div>
                <div class="text-center border-l-2 border-gray-300">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($agedReceivables['total'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="w-full p-4 bg-white rounded-lg shadow">
            {{-- Header with Filters and Actions --}}
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    {{-- Status Filter --}}
                    <select wire:model="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                        <option value="all">All Loans</option>
                        <option value="current">Current</option>
                        <option value="overdue">Overdue</option>
                    </select>

                    {{-- Search Input --}}
                    <div class="relative">
                        <input type="text"
                               wire:model.debounce.300ms="search"
                               placeholder="Search loan, client..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    {{-- Per Page Selector --}}
                    <select wire:model="perPage" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>

                    {{-- Export Button --}}
                    <button wire:click="exportToExcel" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('loan_account_number')">
                                <div class="flex items-center">
                                    Loan Account
                                    @if($sortField === 'loan_account_number')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Number</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('principle')">
                                <div class="flex items-center justify-end">
                                    Principal
                                    @if($sortField === 'principle')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Scheduled</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Paid</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Receivable</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue Interest</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($interestReceivables as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $loan->loan_account_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->loan_id }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loan->client_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($loan->principle, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900">{{ $loan->interest }}%</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($loan->total_interest_scheduled, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 font-medium">{{ number_format($loan->total_interest_paid, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600 font-bold">{{ number_format($loan->total_interest_receivable, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium
                                    {{ $loan->overdue_interest > 0 ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ number_format($loan->overdue_interest, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($loan->overdue_interest > 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                    <button wire:click="viewLoanDetails('{{ $loan->loan_id }}')"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    No interest receivable records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($interestReceivables->hasPages())
                <div class="mt-4">
                    {{ $interestReceivables->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- DAILY ACCRUALS TAB --}}
    @if($activeTab === 'accruals')
        {{-- Accrual Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Loans Processed</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($accrualLoansCount) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Total Accrued</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($totalAccruedInterest, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Performing Interest</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($performingInterest, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Suspended (NPL)</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($suspendedInterest, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">NPL Loans</p>
                <p class="text-2xl font-bold text-orange-600">{{ number_format($nplLoansCount) }}</p>
            </div>
        </div>

        {{-- Date Selection and Actions --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700">Accrual Date:</label>
                    <input type="date" wire:model="accrualDate" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="runDailyAccrual" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Run Accrual
                    </button>
                </div>
            </div>
        </div>

        {{-- Daily Accruals Table --}}
        <div class="w-full p-4 bg-white rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Interest Accruals for {{ \Carbon\Carbon::parse($accrualDate)->format('M d, Y') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Daily Interest</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cumulative</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Classification</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">GL Posted</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dailyAccruals as $accrual)
                            <tr class="hover:bg-gray-50 {{ $accrual->is_suspended ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $accrual->loan_id }}</div>
                                    <div class="text-xs text-gray-500">{{ $accrual->loan_account_number ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ ($accrual->first_name ?? '') . ' ' . ($accrual->last_name ?? '') }}</div>
                                    <div class="text-xs text-gray-500">{{ $accrual->client_number ?? $accrual->member_number }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($accrual->opening_balance, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-900">{{ number_format($accrual->annual_rate, 2) }}%</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium {{ $accrual->is_suspended ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($accrual->daily_interest, 4) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">{{ number_format($accrual->cumulative_accrued, 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $accrual->loan_classification === 'PERFORMING' ? 'bg-green-100 text-green-800' : 
                                           ($accrual->loan_classification === 'WATCH' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $accrual->loan_classification ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($accrual->posted_to_gl)
                                        <span class="text-green-600"><svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg></span>
                                    @elseif($accrual->is_suspended)
                                        <span class="text-orange-500" title="Suspended - Not posted to GL">
                                            <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </span>
                                    @else
                                        <span class="text-gray-400"><svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No accrual records for this date. Click "Run Accrual" to process.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($dailyAccruals instanceof \Illuminate\Pagination\LengthAwarePaginator && $dailyAccruals->hasPages())
                <div class="mt-4">
                    {{ $dailyAccruals->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- INCOME REPORT TAB --}}
    @if($activeTab === 'income-report')
        {{-- Income Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Total Income</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Interest Income</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($interestIncome, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $totalIncome > 0 ? number_format(($interestIncome / $totalIncome) * 100, 1) : 0 }}% of total</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Fee Income</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($feeIncome, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $totalIncome > 0 ? number_format(($feeIncome / $totalIncome) * 100, 1) : 0 }}% of total</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Other Income</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($otherIncome, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $totalIncome > 0 ? number_format(($otherIncome / $totalIncome) * 100, 1) : 0 }}% of total</p>
            </div>
        </div>

        {{-- Period Selection --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700">Period:</label>
                    <select wire:model="incomeReportPeriod" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500">
                        <option value="day">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="exportIncomeReport" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Report
                    </button>
                </div>
            </div>
        </div>

        {{-- Income Breakdown Table --}}
        <div class="w-full p-4 bg-white rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Breakdown by Category</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Period Income</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">YTD Balance</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $totalPeriod = collect($incomeBreakdown)->sum('period_income'); @endphp
                        @forelse($incomeBreakdown as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600">{{ $item['account_number'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['account_name'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 font-medium">{{ number_format($item['period_income'], 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">{{ number_format($item['ytd_balance'], 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ $totalPeriod > 0 ? number_format(($item['period_income'] / $totalPeriod) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No income data available for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">TOTAL</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-green-700">{{ number_format($totalPeriod, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-red-700">{{ number_format(collect($incomeBreakdown)->sum('ytd_balance'), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-gray-700">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Income Composition Chart --}}
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Composition</h3>
            <div class="flex items-center space-x-8">
                {{-- Simple bar representation --}}
                <div class="flex-1">
                    <div class="h-8 bg-gray-200 rounded-full overflow-hidden flex">
                        @if($totalIncome > 0)
                            <div class="h-full bg-red-500" style="width: {{ ($interestIncome / $totalIncome) * 100 }}%" title="Interest Income"></div>
                            <div class="h-full bg-green-500" style="width: {{ ($feeIncome / $totalIncome) * 100 }}%" title="Fee Income"></div>
                            <div class="h-full bg-purple-500" style="width: {{ ($otherIncome / $totalIncome) * 100 }}%" title="Other Income"></div>
                        @endif
                    </div>
                </div>
                <div class="flex space-x-4 text-sm">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span>Interest</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span>Fees</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                        <span>Other</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- AUDIT TRAIL TAB --}}
    @if($activeTab === 'audit-trail')
        {{-- Audit Trail Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Total Records</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($auditStats->total_records ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Created</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($auditStats->creates ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Updated</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($auditStats->updates ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm font-medium text-gray-600">Reversed</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($auditStats->reversals ?? 0) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">From:</label>
                    <input type="date" wire:model="auditTrailDateFrom" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">To:</label>
                    <input type="date" wire:model="auditTrailDateTo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Action:</label>
                    <select wire:model="auditTrailAction" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500">
                        <option value="">All Actions</option>
                        <option value="CREATE">Created</option>
                        <option value="UPDATE">Updated</option>
                        <option value="REVERSE">Reversed</option>
                    </select>
                </div>
                <div class="flex-1">
                    <div class="relative">
                        <input type="text"
                               wire:model.debounce.300ms="auditTrailSearch"
                               placeholder="Search by income code, source, user..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Income Audit Trail Table --}}
        <div class="w-full p-4 bg-white rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Transaction Audit Trail</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Income Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed By</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($incomeAuditTrail as $audit)
                            <tr class="hover:bg-gray-50 {{ $audit->action === 'REVERSE' ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($audit->created_at)->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($audit->created_at)->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $audit->income_code ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $audit->income_source ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $audit->action === 'CREATE' ? 'bg-green-100 text-green-800' :
                                           ($audit->action === 'UPDATE' ? 'bg-red-100 text-red-800' :
                                           ($audit->action === 'REVERSE' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $audit->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $audit->description }}">
                                        {{ $audit->description ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                    {{ number_format($audit->amount ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $audit->user_name ?? 'System' }}</div>
                                    <div class="text-xs text-gray-500">{{ $audit->ip_address ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button wire:click="viewAuditDetail({{ $audit->id }})"
                                            class="text-red-600 hover:text-red-900 font-medium">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No audit trail records found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($incomeAuditTrail instanceof \Illuminate\Pagination\LengthAwarePaginator && $incomeAuditTrail->hasPages())
                <div class="mt-4">
                    {{ $incomeAuditTrail->links() }}
                </div>
            @endif
        </div>

        {{-- GL Income Transactions --}}
        <div class="w-full p-4 bg-white rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">GL Income Transactions</h3>
            <p class="text-sm text-gray-500 mb-4">Recent general ledger entries for income accounts (Category 4000)</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Narration</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($glIncomeTransactions as $gl)
                            <tr class="hover:bg-gray-50 {{ ($gl->debit ?? 0) > 0 ? 'bg-yellow-50' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($gl->created_at)->format('M d, Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ $gl->reference_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-600">{{ $gl->account_number }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($gl->account_name, 30) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $gl->narration }}">
                                        {{ $gl->narration ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ ($gl->debit ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                    {{ ($gl->debit ?? 0) > 0 ? number_format($gl->debit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ ($gl->credit ?? 0) > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    {{ ($gl->credit ?? 0) > 0 ? number_format($gl->credit, 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if(($gl->debit ?? 0) > 0)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800" title="Reversal/Correction">
                                            REV
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $gl->trans_status ?? 'OK' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No GL income transactions found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Loan Detail Modal --}}
    @if($showDetailModal && $selectedLoan)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Loan Interest Details - {{ $selectedLoan['loan_account_number'] }}</h3>
                            <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-600">Client Number</p>
                                <p class="text-base font-medium text-gray-900">{{ $selectedLoan['client_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Principal Amount</p>
                                <p class="text-base font-medium text-gray-900">{{ number_format($selectedLoan['principal'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Interest Rate</p>
                                <p class="text-base font-medium text-gray-900">{{ $selectedLoan['interest_rate'] }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Interest Receivable</p>
                                <p class="text-base font-bold text-red-600">{{ number_format($selectedLoan['total_interest_receivable'], 2) }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest Due</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest Paid</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Receivable</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedLoan['schedule_details'] as $schedule)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $schedule['installment_date'] }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-gray-900">{{ number_format($schedule['interest_due'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-green-600">{{ number_format($schedule['interest_paid'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-red-600 font-medium">{{ number_format($schedule['interest_receivable'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-center">
                                                <span class="px-2 py-1 text-xs rounded-full
                                                    {{ $schedule['status'] == 'PAID' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $schedule['status'] ?? 'PENDING' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeDetailModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Audit Detail Modal --}}
    @if($showAuditDetailModal && $selectedAuditDetail)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeAuditDetailModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Audit Trail Details</h3>
                                <p class="text-sm text-gray-500">{{ $selectedAuditDetail->income_code ?? 'Income Transaction' }}</p>
                            </div>
                            <button wire:click="closeAuditDetailModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Action Badge --}}
                        <div class="mb-4">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full
                                {{ $selectedAuditDetail->action === 'CREATE' ? 'bg-green-100 text-green-800' :
                                   ($selectedAuditDetail->action === 'UPDATE' ? 'bg-red-100 text-red-800' :
                                   ($selectedAuditDetail->action === 'REVERSE' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $selectedAuditDetail->action }}
                            </span>
                        </div>

                        {{-- Basic Info --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Date/Time</p>
                                <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedAuditDetail->created_at)->format('M d, Y H:i:s') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Performed By</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAuditDetail->user_name ?? 'System' }}</p>
                                <p class="text-xs text-gray-500">{{ $selectedAuditDetail->user_email ?? '' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">IP Address</p>
                                <p class="text-sm font-mono text-gray-900">{{ $selectedAuditDetail->ip_address ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Current Status</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAuditDetail->current_status ?? 'N/A' }}</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($selectedAuditDetail->description)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $selectedAuditDetail->description }}</p>
                            </div>
                        @endif

                        {{-- Changes Comparison --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Old Values --}}
                            @if($selectedAuditDetail->old_values_decoded)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <span class="w-3 h-3 bg-red-400 rounded-full mr-2"></span>
                                        Previous Values
                                    </h4>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 max-h-64 overflow-y-auto">
                                        <dl class="text-sm space-y-1">
                                            @foreach($selectedAuditDetail->old_values_decoded as $key => $value)
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</dt>
                                                    <dd class="font-mono text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </div>
                                </div>
                            @endif

                            {{-- New Values --}}
                            @if($selectedAuditDetail->new_values_decoded)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                        <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                                        New Values
                                    </h4>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 max-h-64 overflow-y-auto">
                                        <dl class="text-sm space-y-1">
                                            @foreach($selectedAuditDetail->new_values_decoded as $key => $value)
                                                <div class="flex justify-between">
                                                    <dt class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</dt>
                                                    <dd class="font-mono text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- User Agent --}}
                        @if($selectedAuditDetail->user_agent)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h4 class="text-xs font-medium text-gray-500 uppercase mb-1">User Agent</h4>
                                <p class="text-xs text-gray-600 font-mono break-all">{{ $selectedAuditDetail->user_agent }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeAuditDetailModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
