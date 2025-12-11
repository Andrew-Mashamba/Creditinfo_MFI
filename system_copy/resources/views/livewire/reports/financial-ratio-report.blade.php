<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Financial Ratio Report</h2>
                <p class="text-gray-600">Financial ratio analysis and performance indicators</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="exportToExcel" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to Excel
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                <select wire:model="reportPeriod" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <select wire:model="selectedMonth" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select wire:model="selectedYear" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select wire:model="selectedBranch" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch['id'] }}">{{ $branch['branch_name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Overall Score --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Overall Financial Health Score</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-4xl font-bold text-blue-600">{{ $overallScore }}</div>
                <div class="text-sm text-gray-500">Overall Score</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $riskLevel }}</div>
                <div class="text-sm text-gray-500">Risk Level</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $performanceGrade }}</div>
                <div class="text-sm text-gray-500">Performance Grade</div>
            </div>
        </div>
    </div>

    {{-- Liquidity Ratios --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Liquidity Ratios</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-medium text-blue-900">Current Ratio</h4>
                <p class="text-2xl font-bold text-blue-600">{{ $liquidityRatios['current_ratio']['value'] ?? 0 }}</p>
                <p class="text-sm text-blue-700">Benchmark: {{ $liquidityRatios['current_ratio']['benchmark'] ?? 0 }}</p>
                <p class="text-xs text-blue-600">{{ $liquidityRatios['current_ratio']['status'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-medium text-green-900">Quick Ratio</h4>
                <p class="text-2xl font-bold text-green-600">{{ $liquidityRatios['quick_ratio']['value'] ?? 0 }}</p>
                <p class="text-sm text-green-700">Benchmark: {{ $liquidityRatios['quick_ratio']['benchmark'] ?? 0 }}</p>
                <p class="text-xs text-green-600">{{ $liquidityRatios['quick_ratio']['status'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-medium text-yellow-900">Cash Ratio</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ $liquidityRatios['cash_ratio']['value'] ?? 0 }}</p>
                <p class="text-sm text-yellow-700">Benchmark: {{ $liquidityRatios['cash_ratio']['benchmark'] ?? 0 }}</p>
                <p class="text-xs text-yellow-600">{{ $liquidityRatios['cash_ratio']['status'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h4 class="font-medium text-purple-900">Operating Cash Flow</h4>
                <p class="text-2xl font-bold text-purple-600">{{ $liquidityRatios['operating_cash_flow_ratio']['value'] ?? 0 }}</p>
                <p class="text-sm text-purple-700">Benchmark: {{ $liquidityRatios['operating_cash_flow_ratio']['benchmark'] ?? 0 }}</p>
                <p class="text-xs text-purple-600">{{ $liquidityRatios['operating_cash_flow_ratio']['status'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Profitability Ratios --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Profitability Ratios</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-medium text-blue-900">Gross Profit Margin</h4>
                <p class="text-2xl font-bold text-blue-600">{{ $profitabilityRatios['gross_profit_margin']['value'] ?? 0 }}%</p>
                <p class="text-sm text-blue-700">Benchmark: {{ $profitabilityRatios['gross_profit_margin']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-medium text-green-900">Net Profit Margin</h4>
                <p class="text-2xl font-bold text-green-600">{{ $profitabilityRatios['net_profit_margin']['value'] ?? 0 }}%</p>
                <p class="text-sm text-green-700">Benchmark: {{ $profitabilityRatios['net_profit_margin']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-medium text-yellow-900">Return on Assets</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ $profitabilityRatios['return_on_assets']['value'] ?? 0 }}%</p>
                <p class="text-sm text-yellow-700">Benchmark: {{ $profitabilityRatios['return_on_assets']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h4 class="font-medium text-purple-900">Return on Equity</h4>
                <p class="text-2xl font-bold text-purple-600">{{ $profitabilityRatios['return_on_equity']['value'] ?? 0 }}%</p>
                <p class="text-sm text-purple-700">Benchmark: {{ $profitabilityRatios['return_on_equity']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-indigo-50 p-4 rounded-lg">
                <h4 class="font-medium text-indigo-900">Return on Investment</h4>
                <p class="text-2xl font-bold text-indigo-600">{{ $profitabilityRatios['return_on_investment']['value'] ?? 0 }}%</p>
                <p class="text-sm text-indigo-700">Benchmark: {{ $profitabilityRatios['return_on_investment']['benchmark'] ?? 0 }}%</p>
            </div>
        </div>
    </div>

    {{-- Risk Ratios --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Ratios</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-red-50 p-4 rounded-lg">
                <h4 class="font-medium text-red-900">Portfolio at Risk</h4>
                <p class="text-2xl font-bold text-red-600">{{ $riskRatios['portfolio_at_risk']['value'] ?? 0 }}%</p>
                <p class="text-sm text-red-700">Benchmark: {{ $riskRatios['portfolio_at_risk']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <h4 class="font-medium text-orange-900">Provision Coverage</h4>
                <p class="text-2xl font-bold text-orange-600">{{ $riskRatios['provision_coverage']['value'] ?? 0 }}%</p>
                <p class="text-sm text-orange-700">Benchmark: {{ $riskRatios['provision_coverage']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-medium text-yellow-900">Capital Adequacy</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ $riskRatios['capital_adequacy_ratio']['value'] ?? 0 }}%</p>
                <p class="text-sm text-yellow-700">Benchmark: {{ $riskRatios['capital_adequacy_ratio']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-medium text-green-900">Loan Loss Provision</h4>
                <p class="text-2xl font-bold text-green-600">{{ $riskRatios['loan_loss_provision']['value'] ?? 0 }}%</p>
                <p class="text-sm text-green-700">Benchmark: {{ $riskRatios['loan_loss_provision']['benchmark'] ?? 0 }}%</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-medium text-blue-900">Concentration Risk</h4>
                <p class="text-2xl font-bold text-blue-600">{{ $riskRatios['concentration_risk']['value'] ?? 0 }}%</p>
                <p class="text-sm text-blue-700">Benchmark: {{ $riskRatios['concentration_risk']['benchmark'] ?? 0 }}%</p>
            </div>
        </div>
    </div>

    {{-- Recommendations --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendations</h3>
        <div class="space-y-4">
            @forelse($recommendations as $recommendation)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $recommendation['category'] ?? 'N/A' }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $recommendation['recommendation'] ?? 'No recommendation available' }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $recommendation['impact'] ?? 'N/A' }}</p>
                        </div>
                        <div class="ml-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $recommendation['priority'] === 'High' ? 'bg-red-100 text-red-800' : 
                                   ($recommendation['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $recommendation['priority'] ?? 'Low' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center">No recommendations available</p>
            @endforelse
        </div>
    </div>
</div>
