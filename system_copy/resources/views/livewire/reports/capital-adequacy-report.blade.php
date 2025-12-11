<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Capital Adequacy Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Computation of capital adequacy ratio for regulatory compliance</p>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            BOT Required
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Monthly Report
                        </span>
                    </div>
                </div>
                <div class="flex items-end space-x-4">
                    <div class="flex-shrink-0">
                        <label for="reportDate" class="block text-sm font-medium text-gray-700 mb-2">
                            Report Date
                        </label>
                        <input type="date" 
                               wire:model.live="reportDate" 
                               class="block w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <button wire:click="exportReport('pdf')" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </button>
                    <button wire:click="exportReport('excel')" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Capital Adequacy Summary --}}
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
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($capitalAdequacyRatio, 2) }}%</h4>
                    <p class="text-sm text-gray-500">Capital Adequacy Ratio</p>
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
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalCapital, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Total Capital</p>
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
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalRiskWeightedAssets, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Risk-Weighted Assets</p>
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

    {{-- Capital Structure Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Tier 1 Capital --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tier 1 Capital (Core Capital)</h3>
                <p class="text-sm text-gray-500">Primary capital components</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Paid-up Capital</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['paid_up_capital'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Share Premium</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['share_premium'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Retained Earnings</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['retained_earnings'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">General Reserves</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['general_reserves'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Statutory Reserves</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['statutory_reserves'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Revaluation Reserves</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['revaluation_reserves'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Other Reserves</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier1Capital['other_reserves'], 2) }} TZS</span>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Tier 1 Capital</span>
                            <span class="text-base font-bold text-blue-900">{{ number_format($totalTier1Capital, 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tier 2 Capital --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Tier 2 Capital (Supplementary Capital)</h3>
                <p class="text-sm text-gray-500">Secondary capital components</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Subordinated Debt</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier2Capital['subordinated_debt'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Hybrid Instruments</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier2Capital['hybrid_instruments'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Loan Loss Provisions</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier2Capital['loan_loss_provisions'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Revaluation Reserves (Tier 2)</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier2Capital['revaluation_reserves_tier2'], 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-700">Undisclosed Reserves</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($tier2Capital['undisclosed_reserves'], 2) }} TZS</span>
                    </div>
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Total Tier 2 Capital</span>
                            <span class="text-base font-bold text-green-900">{{ number_format($totalTier2Capital, 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Risk-Weighted Assets --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Risk-Weighted Assets</h3>
            <p class="text-sm text-gray-500">Assets weighted according to their risk profile</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (TZS)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Weight (%)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Weighted Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($riskWeightedAssets as $category => $data)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucwords(str_replace('_', ' ', $category)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($data['amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($data['risk_weight'] == 0) bg-green-100 text-green-800
                                    @elseif($data['risk_weight'] <= 50) bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $data['risk_weight'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                {{ number_format($data['weighted_amount'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Total Risk-Weighted Assets</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                            {{ number_format(array_sum(array_column($riskWeightedAssets, 'amount')), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-center">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                            {{ number_format($totalRiskWeightedAssets, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Regulatory Compliance Summary --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Regulatory Compliance Summary</h3>
            <p class="text-sm text-gray-500">BOT Capital Adequacy Requirements</p>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($capitalAdequacyRatio, 2) }}%</div>
                    <div class="text-sm text-gray-500">Current CAR</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $minimumRequiredRatio }}%</div>
                    <div class="text-sm text-gray-500">BOT Minimum Requirement</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $excessCapital >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($excessCapital, 2) }} TZS
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $excessCapital >= 0 ? 'Excess Capital' : 'Capital Shortfall' }}
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
                            {{ $complianceStatus === 'COMPLIANT' ? 'Compliant with BOT Requirements' : 'Non-Compliant with BOT Requirements' }}
                        </h3>
                        <div class="mt-2 text-sm {{ $complianceStatus === 'COMPLIANT' ? 'text-green-700' : 'text-red-700' }}">
                            @if($complianceStatus === 'COMPLIANT')
                                The institution meets the minimum capital adequacy ratio requirement of {{ $minimumRequiredRatio }}% as set by the Bank of Tanzania.
                            @else
                                The institution does not meet the minimum capital adequacy ratio requirement of {{ $minimumRequiredRatio }}%. Immediate action is required to improve capital position.
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
