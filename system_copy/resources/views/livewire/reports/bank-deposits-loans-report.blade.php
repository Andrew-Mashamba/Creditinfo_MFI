<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Bank Deposits & Loans Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Deposits and loans in banks and financial institutions</p>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            BOT Required
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Monthly Report
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div>
                        <label for="reportDate" class="block text-sm font-medium text-gray-700 mb-2">
                            Report Date
                        </label>
                        <input type="date" 
                               wire:model.live="reportDate" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button wire:click="exportReport('pdf')" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </button>
                    <button wire:click="exportReport('excel')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalDeposits, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Bank Deposits</p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalLoans, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Bank Loans</p>
                </div>
            </div>
        </div>

        <div class="bg-{{ $netPosition >= 0 ? 'green' : 'orange' }}-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-{{ $netPosition >= 0 ? 'green' : 'orange' }}-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $netPosition >= 0 ? 'green' : 'orange' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($netPosition, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">{{ $netPosition >= 0 ? 'Net Credit Position' : 'Net Debit Position' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ count($bankRelationships) }}</h4>
                    <p class="text-sm text-gray-500">Bank Relationships</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Deposits Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Deposits by Institution Type --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bank Deposits by Institution Type</h3>
                <p class="text-sm text-gray-500">Deposits with different financial institutions</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Central Bank</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['central_bank'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Commercial Banks</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['commercial_banks'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Development Banks</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['development_banks'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Microfinance Banks</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['microfinance_banks'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">SACCOS</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['savings_credit_cooperatives'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Other Financial Institutions</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['other_financial_institutions'], 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bank Loans by Institution Type --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bank Loans by Institution Type</h3>
                <p class="text-sm text-gray-500">Loans from different financial institutions</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Central Bank Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['central_bank_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Commercial Bank Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['commercial_bank_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Development Bank Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['development_bank_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Microfinance Bank Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['microfinance_bank_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">SACCOS Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['saccos_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Other FI Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankLoans['other_fi_loans'], 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bank Relationships Table --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Bank Relationships</h3>
            <p class="text-sm text-gray-500">Detailed breakdown by individual banks</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Name</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deposits (TZS)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Loans (TZS)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Position (TZS)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Relationship Type</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bankRelationships as $relationship)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $relationship['bank_name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($relationship['deposits'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($relationship['loans'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                <span class="font-medium {{ $relationship['net_position'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($relationship['net_position'], 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($relationship['deposits'] > 0 && $relationship['loans'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Both
                                    </span>
                                @elseif($relationship['deposits'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Deposits Only
                                    </span>
                                @elseif($relationship['loans'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Loans Only
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        No Activity
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No bank relationships found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Deposit and Loan Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Deposit Types --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Deposit Types</h3>
                <p class="text-sm text-gray-500">Breakdown by deposit instrument</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Demand Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($depositBreakdown['demand_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Time Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($depositBreakdown['time_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Savings Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($depositBreakdown['savings_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Call Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($depositBreakdown['call_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Certificates of Deposit</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($depositBreakdown['certificates_of_deposit'], 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loan Types --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Loan Types</h3>
                <p class="text-sm text-gray-500">Breakdown by loan instrument</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Short-term Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($loanBreakdown['short_term_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Medium-term Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($loanBreakdown['medium_term_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Long-term Loans</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($loanBreakdown['long_term_loans'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Overdraft Facilities</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($loanBreakdown['overdraft_facilities'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Credit Lines</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($loanBreakdown['credit_lines'], 2) }} TZS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session()->has('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif
</div>
