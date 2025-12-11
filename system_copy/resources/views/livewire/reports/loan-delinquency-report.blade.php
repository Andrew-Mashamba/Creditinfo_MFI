<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Loan Delinquency Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Analysis of overdue loans and delinquency patterns</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                        Risk
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

    <!-- Report Content -->
    @if($reportData)
        <div class="bg-white shadow rounded-lg">
            <!-- Report Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900">LOAN DELINQUENCY REPORT</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        As at {{ $reportData['period']['end_date'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">(All amounts in Tanzanian Shillings)</p>
                </div>
            </div>

            <!-- Delinquency Summary -->
            <div class="px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Delinquency Summary</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-red-600">Total Delinquent Amount</div>
                        <div class="text-2xl font-bold text-red-900">{{ number_format($reportData['delinquency_summary']['total_delinquent_amount'], 2) }}</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-blue-600">Total Loan Portfolio</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($reportData['delinquency_summary']['total_loan_portfolio'], 2) }}</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-orange-600">Delinquency Rate</div>
                        <div class="text-2xl font-bold text-orange-900">{{ number_format($reportData['delinquency_summary']['delinquency_rate'], 2) }}%</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-purple-600">Delinquent Loans</div>
                        <div class="text-2xl font-bold text-purple-900">{{ number_format($reportData['delinquency_summary']['number_of_delinquent_loans']) }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-green-600">Current Loans</div>
                        <div class="text-2xl font-bold text-green-900">{{ number_format($reportData['delinquency_summary']['current_loans']) }}</div>
                    </div>
                </div>

                <!-- Delinquency by Age -->
                @if(!empty($reportData['delinquency_by_age']))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Delinquency by Age</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['delinquency_by_age'] as $age => $amount)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $age }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $reportData['delinquency_summary']['total_delinquent_amount'] > 0 ? number_format(($amount / $reportData['delinquency_summary']['total_delinquent_amount']) * 100, 1) : 0 }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($age == '1-30 days')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Low Risk</span>
                                                @elseif($age == '31-60 days')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Medium Risk</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High Risk</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Detailed Delinquent Loans -->
                @if(!empty($reportData['delinquent_loans']))
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Delinquent Loans</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Information</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financial Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delinquency Info</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Security & Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['delinquent_loans'] as $loan)
                                        <tr class="hover:bg-gray-50">
                                            <!-- Loan Details Column -->
                                            <td class="px-6 py-4 text-sm">
                                                <div class="space-y-1">
                                                    <div class="font-medium text-gray-900">{{ $loan['loan_id'] }}</div>
                                                    <div class="text-gray-600">{{ $loan['product_name'] ?? 'N/A' }}</div>
                                                    <div class="text-gray-500 text-xs">
                                                        Original: {{ number_format($loan['original_loan_amount'], 2) }} TZS
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        Rate: {{ $loan['interest_rate'] ?? 'N/A' }}% | Term: {{ $loan['loan_term'] ?? 'N/A' }} months
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        Disbursed: {{ $loan['disbursement_date'] ? \Carbon\Carbon::parse($loan['disbursement_date'])->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        Officer: {{ $loan['loan_officer'] ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Client Information Column -->
                                            <td class="px-6 py-4 text-sm">
                                                <div class="space-y-1">
                                                    <div class="font-medium text-gray-900">{{ $loan['client_name'] ?: $loan['business_name'] }}</div>
                                                    <div class="text-gray-600">{{ $loan['client_number'] }}</div>
                                                    @if($loan['client_phone'])
                                                        <div class="text-gray-500 text-xs">
                                                            <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                                            </svg>
                                                            {{ $loan['client_phone'] }}
                                                        </div>
                                                    @endif
                                                    @if($loan['client_email'])
                                                        <div class="text-gray-500 text-xs">
                                                            <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                            </svg>
                                                            {{ $loan['client_email'] }}
                                                        </div>
                                                    @endif
                                                    @if($loan['client_address'])
                                                        <div class="text-gray-500 text-xs">
                                                            <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ Str::limit($loan['client_address'], 30) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <!-- Financial Details Column -->
                                            <td class="px-6 py-4 text-sm">
                                                <div class="space-y-1">
                                                    <div class="text-gray-900">
                                                        Outstanding: <span class="font-medium">{{ number_format($loan['outstanding_balance'], 2) }} TZS</span>
                                                    </div>
                                                    <div class="text-red-600">
                                                        Overdue: <span class="font-medium">{{ number_format($loan['overdue_amount'], 2) }} TZS</span>
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        Installments: {{ $loan['overdue_installments'] }} overdue
                                                    </div>
                                                    @if($loan['last_payment_date'])
                                                        <div class="text-gray-500 text-xs">
                                                            Last Payment: {{ \Carbon\Carbon::parse($loan['last_payment_date'])->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-gray-500 text-xs">
                                                            Amount: {{ number_format($loan['last_payment_amount'], 2) }} TZS
                                                        </div>
                                                    @else
                                                        <div class="text-red-500 text-xs">No payment history</div>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <!-- Delinquency Info Column -->
                                            <td class="px-6 py-4 text-sm">
                                                <div class="space-y-1">
                                                    <div class="text-gray-900">
                                                        Due Date: {{ \Carbon\Carbon::parse($loan['last_due_date'])->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-gray-600">
                                                        Days Past Due: <span class="font-medium">{{ $loan['days_past_due'] }}</span>
                                                    </div>
                                                    <div class="mb-2">
                                                @if($loan['days_past_due'] <= 30)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $loan['delinquency_status'] }}</span>
                                                @elseif($loan['days_past_due'] <= 60)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">{{ $loan['delinquency_status'] }}</span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $loan['delinquency_status'] }}</span>
                                                @endif
                                                    </div>
                                                    <div class="text-gray-500 text-xs">
                                                        <strong>Reason:</strong> {{ $loan['delinquency_reason'] }}
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Security & Actions Column -->
                                            <td class="px-6 py-4 text-sm">
                                                <div class="space-y-1">
                                                    @if($loan['guarantor_name'])
                                                        <div class="text-gray-600">
                                                            <strong>Guarantor:</strong> {{ $loan['guarantor_name'] }}
                                                        </div>
                                                        @if($loan['guarantor_phone'])
                                                            <div class="text-gray-500 text-xs">{{ $loan['guarantor_phone'] }}</div>
                                                        @endif
                                                    @endif
                                                    
                                                    @if($loan['collateral_type'])
                                                        <div class="text-gray-600">
                                                            <strong>Collateral:</strong> {{ $loan['collateral_type'] }}
                                                        </div>
                                                        @if($loan['collateral_value'])
                                                            <div class="text-gray-500 text-xs">{{ number_format($loan['collateral_value'], 2) }} TZS</div>
                                                        @endif
                                                    @endif
                                                    
                                                    @if(!empty($loan['collection_actions']))
                                                        <div class="mt-2">
                                                            <div class="text-gray-600 text-xs font-medium">Recent Actions:</div>
                                                            @foreach(array_slice($loan['collection_actions'], 0, 2) as $action)
                                                                <div class="text-gray-500 text-xs">
                                                                    {{ $action['type'] }} - {{ \Carbon\Carbon::parse($action['date'])->format('M d') }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Delinquent Loans</h3>
                        <p class="mt-1 text-sm text-gray-500">All loans are current as of the report date.</p>
                    </div>
                @endif
            </div>

            <!-- Export Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <div>Generated on {{ $reportData['generated_at'] ? \Carbon\Carbon::parse($reportData['generated_at'])->format('F d, Y \a\t g:i A') : now()->format('F d, Y \a\t g:i A') }}</div>
                        <div>Generated by: {{ $reportData['generated_by'] ?? auth()->user()->name ?? 'System' }}</div>
                        <div>Report Period: {{ $reportData['period']['end_date'] }}</div>
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
                            <span wire:loading wire:target="exportToPDF">Exporting PDF...</span>
                        </button>
                        <button wire:click="exportToExcel" 
                                wire:loading.attr="disabled"
                                wire:target="exportToExcel"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                            <span wire:loading wire:target="exportToExcel">Exporting Excel...</span>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Report Generated</h3>
                <p class="mt-1 text-sm text-gray-500">Select a date and click "Generate Report" to create the Loan Delinquency Report.</p>
            </div>
        </div>
    @endif
</div>