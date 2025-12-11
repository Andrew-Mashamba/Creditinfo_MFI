<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Statement of Cash Flow</h1>
                    <p class="mt-1 text-sm text-gray-500">Cash flow statement showing cash inflows and outflows from operating, investing, and financing activities</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        BOT, IFRS
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Regulatory
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Selection -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" 
                           wire:model="reportStartDate" 
                           id="startDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" 
                           wire:model="reportEndDate" 
                           id="endDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button wire:click="generateStatement" 
                            wire:loading.attr="disabled"
                            wire:target="generateStatement"
                            class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="generateStatement">Generate Report</span>
                        <span wire:loading wire:target="generateStatement">Generating...</span>
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
            </div>
        </div>
    @endif

    <!-- Statement Content -->
    @if($statementData)
        <div class="bg-white shadow rounded-lg">
            <!-- Statement Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900">STATEMENT OF CASH FLOW</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        For the period from {{ $statementData['period']['start_date'] }} to {{ $statementData['period']['end_date'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">(All amounts in Tanzanian Shillings)</p>
                </div>
            </div>


            <!-- Cash Flow Statement Content -->
            <div class="px-6 py-4">
                <!-- Operating Activities -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">CASH FLOWS FROM OPERATING ACTIVITIES</h3>
                    
                    <!-- Income -->
                    @if(!empty($statementData['operating_activities']['income_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Inflows:</h4>
                            @foreach($statementData['operating_activities']['income_details'] as $income)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $income['account_name'] }}</span>
                                    <span class="text-sm font-medium text-green-600">{{ number_format($income['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Expenses -->
                    @if(!empty($statementData['operating_activities']['expense_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Outflows:</h4>
                            @foreach($statementData['operating_activities']['expense_details'] as $expense)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $expense['account_name'] }}</span>
                                    <span class="text-sm font-medium text-red-600">{{ number_format($expense['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Net Operating Cash Flow -->
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-md font-semibold text-gray-900">Net Cash from Operating Activities</span>
                            <span class="text-md font-bold {{ $statementData['operating_activities']['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['operating_activities']['net_cash_flow'] >= 0 ? number_format($statementData['operating_activities']['net_cash_flow'], 2) : '(' . number_format(abs($statementData['operating_activities']['net_cash_flow']), 2) . ')' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Investing Activities -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">CASH FLOWS FROM INVESTING ACTIVITIES</h3>
                    
                    <!-- Asset Sales -->
                    @if(!empty($statementData['investing_activities']['sale_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Inflows:</h4>
                            @foreach($statementData['investing_activities']['sale_details'] as $sale)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">Sale of {{ $sale['account_name'] }}</span>
                                    <span class="text-sm font-medium text-green-600">{{ number_format($sale['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Asset Purchases -->
                    @if(!empty($statementData['investing_activities']['purchase_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Outflows:</h4>
                            @foreach($statementData['investing_activities']['purchase_details'] as $purchase)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">Purchase of {{ $purchase['account_name'] }}</span>
                                    <span class="text-sm font-medium text-red-600">{{ number_format($purchase['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Net Investing Cash Flow -->
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-md font-semibold text-gray-900">Net Cash from Investing Activities</span>
                            <span class="text-md font-bold {{ $statementData['investing_activities']['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['investing_activities']['net_cash_flow'] >= 0 ? number_format($statementData['investing_activities']['net_cash_flow'], 2) : '(' . number_format(abs($statementData['investing_activities']['net_cash_flow']), 2) . ')' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Financing Activities -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">CASH FLOWS FROM FINANCING ACTIVITIES</h3>
                    
                    <!-- Loan Proceeds -->
                    @if(!empty($statementData['financing_activities']['loan_proceed_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Inflows:</h4>
                            @foreach($statementData['financing_activities']['loan_proceed_details'] as $proceed)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $proceed['account_name'] }} Proceeds</span>
                                    <span class="text-sm font-medium text-green-600">{{ number_format($proceed['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Capital Contributions -->
                    @if(!empty($statementData['financing_activities']['capital_contribution_details']))
                        <div class="ml-4 mb-3">
                            @foreach($statementData['financing_activities']['capital_contribution_details'] as $contribution)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $contribution['account_name'] }} Contribution</span>
                                    <span class="text-sm font-medium text-green-600">{{ number_format($contribution['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Loan Repayments -->
                    @if(!empty($statementData['financing_activities']['loan_repayment_details']))
                        <div class="ml-4 mb-3">
                            <h4 class="text-md font-medium text-gray-800 mb-2">Cash Outflows:</h4>
                            @foreach($statementData['financing_activities']['loan_repayment_details'] as $repayment)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $repayment['account_name'] }} Repayment</span>
                                    <span class="text-sm font-medium text-red-600">{{ number_format($repayment['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Capital Withdrawals -->
                    @if(!empty($statementData['financing_activities']['capital_withdrawal_details']))
                        <div class="ml-4 mb-3">
                            @foreach($statementData['financing_activities']['capital_withdrawal_details'] as $withdrawal)
                                <div class="flex justify-between items-center py-1">
                                    <span class="text-sm text-gray-700 ml-4">{{ $withdrawal['account_name'] }} Withdrawal</span>
                                    <span class="text-sm font-medium text-red-600">{{ number_format($withdrawal['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Net Financing Cash Flow -->
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-md font-semibold text-gray-900">Net Cash from Financing Activities</span>
                            <span class="text-md font-bold {{ $statementData['financing_activities']['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['financing_activities']['net_cash_flow'] >= 0 ? number_format($statementData['financing_activities']['net_cash_flow'], 2) : '(' . number_format(abs($statementData['financing_activities']['net_cash_flow']), 2) . ')' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Summary -->
                <div class="border-t-2 border-gray-300 pt-6">
                    <div class="space-y-3">
                        <!-- Net Cash Flow -->
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900">Net Increase (Decrease) in Cash</span>
                            <span class="text-lg font-bold {{ $statementData['cash_flow_summary']['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['cash_flow_summary']['net_cash_flow'] >= 0 ? number_format($statementData['cash_flow_summary']['net_cash_flow'], 2) : '(' . number_format(abs($statementData['cash_flow_summary']['net_cash_flow']), 2) . ')' }}
                            </span>
                        </div>

                        <!-- Beginning Cash -->
                        <div class="flex justify-between items-center">
                            <span class="text-md font-medium text-gray-800">Cash at Beginning of Period</span>
                            <span class="text-md font-medium text-gray-700">{{ number_format($statementData['cash_flow_summary']['beginning_cash'], 2) }}</span>
                        </div>

                        <!-- Ending Cash -->
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3">
                            <span class="text-lg font-bold text-gray-900">Cash at End of Period</span>
                            <span class="text-lg font-bold text-gray-900">{{ number_format($statementData['cash_flow_summary']['ending_cash'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cash Flow Analysis -->
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Cash Flow Analysis</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="text-center">
                            <div class="font-medium text-gray-700">Operating Activities</div>
                            <div class="text-lg font-bold {{ $statementData['cash_flow_summary']['net_operating_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['cash_flow_summary']['net_operating_cash_flow'] >= 0 ? '+' : '' }}{{ number_format($statementData['cash_flow_summary']['net_operating_cash_flow'], 2) }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-medium text-gray-700">Investing Activities</div>
                            <div class="text-lg font-bold {{ $statementData['cash_flow_summary']['net_investing_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['cash_flow_summary']['net_investing_cash_flow'] >= 0 ? '+' : '' }}{{ number_format($statementData['cash_flow_summary']['net_investing_cash_flow'], 2) }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="font-medium text-gray-700">Financing Activities</div>
                            <div class="text-lg font-bold {{ $statementData['cash_flow_summary']['net_financing_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $statementData['cash_flow_summary']['net_financing_cash_flow'] >= 0 ? '+' : '' }}{{ number_format($statementData['cash_flow_summary']['net_financing_cash_flow'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="exportPdf" 
                                wire:loading.attr="disabled"
                                wire:target="exportPdf"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                            <span wire:loading wire:target="exportPdf">Exporting PDF...</span>
                        </button>
                        <button wire:click="exportExcel" 
                                wire:loading.attr="disabled"
                                wire:target="exportExcel"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                            <span wire:loading wire:target="exportExcel">Exporting Excel...</span>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Statement Generated</h3>
                <p class="mt-1 text-sm text-gray-500">Select a date range and click "Generate Report" to create the Statement of Cash Flow.</p>
            </div>
        </div>
    @endif
</div>