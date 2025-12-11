<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Liquid Assets Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Computation of liquid assets for regulatory compliance</p>
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

    {{-- Liquidity Summary --}}
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
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($liquidityRatio, 2) }}%</h4>
                    <p class="text-sm text-gray-500">Liquidity Ratio</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalLiquidAssets, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Liquid Assets</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalLiabilities, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Short-term Liabilities</p>
                </div>
            </div>
        </div>

        <div class="bg-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-50 p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $complianceStatus === 'COMPLIANT' ? 'green' : 'red' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($complianceStatus === 'COMPLIANT')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            @endif
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $complianceStatus }}</h4>
                    <p class="text-sm text-gray-500">BOT Compliance</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Liquid Assets Breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Cash and Cash Equivalents --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Cash and Cash Equivalents</h3>
                <p class="text-sm text-gray-500">Immediately available funds</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Cash on Hand</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($cashBalances['cash_on_hand'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Cash in Vault</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($cashBalances['cash_in_vault'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Cash in Transit</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($cashBalances['cash_in_transit'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Petty Cash</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($cashBalances['petty_cash'], 2) }} TZS</span>
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Cash</span>
                            <span class="text-base font-bold text-blue-900">{{ number_format($liquidAssets['cash_and_equivalents'], 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bank Deposits --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Bank Deposits</h3>
                <p class="text-sm text-gray-500">Deposits with financial institutions</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Demand Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['demand_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Savings Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['savings_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Time Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['time_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Call Deposits</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($bankDeposits['call_deposits'], 2) }} TZS</span>
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Bank Deposits</span>
                            <span class="text-base font-bold text-green-900">{{ number_format($liquidAssets['bank_deposits'], 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liquid Investments --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Liquid Investments</h3>
                <p class="text-sm text-gray-500">Marketable securities and instruments</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Treasury Bills</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($investments['treasury_bills'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Government Bonds</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($investments['government_bonds'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Bank Certificates</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($investments['bank_certificates'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Money Market Instruments</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($investments['money_market_instruments'], 2) }} TZS</span>
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Investments</span>
                            <span class="text-base font-bold text-purple-900">{{ number_format($liquidAssets['liquid_investments'], 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Short-term Liabilities --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Short-term Liabilities</h3>
            <p class="text-sm text-gray-500">Obligations due within 30 days</p>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($liabilities['member_deposits'], 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Member Deposits</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($liabilities['short_term_borrowings'], 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Short-term Borrowings</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($liabilities['accrued_expenses'], 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Accrued Expenses</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($liabilities['payables'], 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Payables</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-semibold text-gray-900">{{ number_format($liabilities['provisions'], 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Provisions</div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalLiabilities, 2) }} TZS</div>
                    <div class="text-sm text-gray-500">Total Short-term Liabilities</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Regulatory Compliance Summary --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Regulatory Compliance Summary</h3>
            <p class="text-sm text-gray-500">BOT Liquidity Requirements</p>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($liquidityRatio, 2) }}%</div>
                    <div class="text-sm text-gray-500">Current Liquidity Ratio</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $minimumLiquidityRatio }}%</div>
                    <div class="text-sm text-gray-500">BOT Minimum Requirement</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $excessLiquidity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($excessLiquidity, 2) }} TZS
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $excessLiquidity >= 0 ? 'Excess Liquidity' : 'Liquidity Shortfall' }}
                    </div>
                </div>
            </div>
            
            <div class="mt-6 p-4 rounded-lg {{ $complianceStatus === 'COMPLIANT' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($complianceStatus === 'COMPLIANT')
                            <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium {{ $complianceStatus === 'COMPLIANT' ? 'text-green-800' : 'text-red-800' }}">
                            {{ $complianceStatus === 'COMPLIANT' ? 'Compliant with BOT Liquidity Requirements' : 'Non-Compliant with BOT Liquidity Requirements' }}
                        </h3>
                        <div class="mt-2 text-sm {{ $complianceStatus === 'COMPLIANT' ? 'text-green-700' : 'text-red-700' }}">
                            @if($complianceStatus === 'COMPLIANT')
                                The institution meets the minimum liquidity ratio requirement of {{ $minimumLiquidityRatio }}% as set by the Bank of Tanzania.
                            @else
                                The institution does not meet the minimum liquidity ratio requirement of {{ $minimumLiquidityRatio }}%. Immediate action is required to improve liquidity position.
                            @endif
                        </div>
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
