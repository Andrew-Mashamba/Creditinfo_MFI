<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Loan Portfolio Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive analysis of loan portfolio performance and risk</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        Operational
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">Report Date</label>
                    <input type="date" 
                           wire:model="reportEndDate" 
                           id="endDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button wire:click="generateReport" 
                            wire:loading.attr="disabled"
                            wire:target="generateReport"
                            class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                        <span wire:loading wire:target="generateReport">Generating...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error/Success Messages -->
    @if($errorMessage)
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ $errorMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button wire:click="$set('errorMessage', '')" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($successMessage)
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">{{ $successMessage }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button wire:click="$set('successMessage', '')" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Report Content -->
    @if($reportData)
        <div class="bg-white shadow rounded-lg">
            <!-- Report Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900">LOAN PORTFOLIO REPORT</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        As at {{ $reportData['period']['end_date'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">(All amounts in Tanzanian Shillings)</p>
                </div>
            </div>

            <!-- Portfolio Summary -->
            <div class="px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio Summary</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-blue-600">Total Portfolio</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($reportData['portfolio_summary']['total_portfolio'], 2) }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-green-600">Number of Loans</div>
                        <div class="text-2xl font-bold text-green-900">{{ number_format($reportData['portfolio_summary']['number_of_loans']) }}</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-purple-600">Average Loan Size</div>
                        <div class="text-2xl font-bold text-purple-900">{{ number_format($reportData['portfolio_summary']['average_loan_size'], 2) }}</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-orange-600">Largest Loan</div>
                        <div class="text-2xl font-bold text-orange-900">{{ number_format($reportData['portfolio_summary']['largest_loan'], 2) }}</div>
                    </div>
                </div>

                <!-- Financial Metrics -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Metrics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-indigo-600">Interest Income</div>
                            <div class="text-2xl font-bold text-indigo-900">{{ number_format($reportData['financial_metrics']['total_interest_income'], 2) }}</div>
                        </div>
                        <div class="bg-teal-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-teal-600">Portfolio Yield</div>
                            <div class="text-2xl font-bold text-teal-900">{{ number_format($reportData['financial_metrics']['portfolio_yield'], 2) }}%</div>
                        </div>
                        <div class="bg-cyan-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-cyan-600">Avg Interest Rate</div>
                            <div class="text-2xl font-bold text-cyan-900">{{ number_format($reportData['financial_metrics']['average_interest_rate'], 2) }}%</div>
                        </div>
                        <div class="bg-amber-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-amber-600">Provision for Losses</div>
                            <div class="text-2xl font-bold text-amber-900">{{ number_format($reportData['financial_metrics']['provision_for_losses'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Risk Analysis -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Analysis</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Portfolio at Risk -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Portfolio at Risk</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Total Portfolio at Risk:</span>
                                    <span class="font-semibold text-red-600">{{ number_format($reportData['risk_analysis']['portfolio_at_risk'], 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">PAR Ratio:</span>
                                    <span class="font-semibold text-red-600">{{ number_format($reportData['risk_analysis']['portfolio_at_risk_ratio'], 2) }}%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">NPL Ratio:</span>
                                    <span class="font-semibold text-red-600">{{ number_format($reportData['risk_analysis']['non_performing_loan_ratio'], 2) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Risk Distribution -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Risk Distribution</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-green-600">Low Risk:</span>
                                    <span class="font-semibold">{{ number_format($reportData['risk_analysis']['risk_distribution']['low_risk']['amount'], 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-yellow-600">Medium Risk:</span>
                                    <span class="font-semibold">{{ number_format($reportData['risk_analysis']['risk_distribution']['medium_risk']['amount'], 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-orange-600">High Risk:</span>
                                    <span class="font-semibold">{{ number_format($reportData['risk_analysis']['risk_distribution']['high_risk']['amount'], 2) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-red-600">Critical Risk:</span>
                                    <span class="font-semibold">{{ number_format($reportData['risk_analysis']['risk_distribution']['critical_risk']['amount'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delinquency Analysis -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Delinquency Analysis</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($reportData['risk_analysis']['delinquency_buckets'] as $category => $data)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @if($category === 'current')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Current
                                                </span>
                                            @elseif($category === '1-30_days')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    1-30 Days
                                                </span>
                                            @elseif($category === '31-60_days')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    31-60 Days
                                                </span>
                                            @elseif($category === '61-90_days')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    61-90 Days
                                                </span>
                                            @elseif($category === '91-180_days')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-900">
                                                    91-180 Days
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-300 text-red-900">
                                                    Over 180 Days
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['amount'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['count']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reportData['portfolio_summary']['total_portfolio'] > 0 ? number_format(($data['amount'] / $reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Trend Analysis -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Trend Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-blue-600">Month-over-Month Growth</div>
                            <div class="text-2xl font-bold {{ $reportData['trend_analysis']['month_over_month_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $reportData['trend_analysis']['month_over_month_growth'] >= 0 ? '+' : '' }}{{ number_format($reportData['trend_analysis']['month_over_month_growth'], 2) }}%
                            </div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-green-600">Year-over-Year Growth</div>
                            <div class="text-2xl font-bold {{ $reportData['trend_analysis']['year_over_year_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $reportData['trend_analysis']['year_over_year_growth'] >= 0 ? '+' : '' }}{{ number_format($reportData['trend_analysis']['year_over_year_growth'], 2) }}%
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-purple-600">Current Portfolio</div>
                            <div class="text-2xl font-bold text-purple-900">{{ number_format($reportData['trend_analysis']['current_portfolio'], 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Portfolio by Type -->
                @if(!empty($reportData['portfolio_by_type']))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio by Loan Type</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['portfolio_by_type'] as $type => $amount)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $type }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $reportData['portfolio_summary']['total_portfolio'] > 0 ? number_format(($amount / $reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0 }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Detailed Loan List -->
                @if(!empty($reportData['loan_details']))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Loan Portfolio</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Past Due</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['loan_details'] as $loan)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loan['loan_id'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['client_number'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['business_name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $loan['category'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="text-sm font-medium">{{ number_format($loan['outstanding_balance'], 2) }}</div>
                                                <div class="text-xs text-gray-500">P: {{ number_format($loan['outstanding_principal'], 2) }}</div>
                                                <div class="text-xs text-gray-500">I: {{ number_format($loan['outstanding_interest'], 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($loan['days_past_due'] > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {{ $loan['days_past_due'] }} days
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Current
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($loan['risk_level'] === 'Low Risk')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Low Risk
                                                    </span>
                                                @elseif($loan['risk_level'] === 'Medium Risk')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Medium Risk
                                                    </span>
                                                @elseif($loan['risk_level'] === 'High Risk')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        High Risk
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Critical Risk
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan['interest_rate'], 2) }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $loan['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Export Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="exportToPDF" 
                                wire:loading.attr="disabled"
                                wire:target="exportToPDF"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportToPDF">Export PDF</span>
                            <span wire:loading wire:target="exportToPDF">                                
                                Exporting PDF...
                            </span>
                        </button>
                        <button wire:click="exportToExcel" 
                                wire:loading.attr="disabled"
                                wire:target="exportToExcel"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">        
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                                <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                            <span wire:loading wire:target="exportToExcel">                                
                                Exporting Excel...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Report Generated</h3>
                <p class="mt-1 text-sm text-gray-500">Select a date and click "Generate Report" to create the Loan Portfolio Report.</p>
            </div>
        </div>
    @endif
</div>
