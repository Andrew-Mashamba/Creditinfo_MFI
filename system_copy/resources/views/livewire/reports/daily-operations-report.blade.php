<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Daily Operations Report</h2>
                <p class="text-gray-600">Daily operational summary and transaction reports</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="exportToExcel" 
                        wire:loading.attr="disabled"
                        wire:target="exportToExcel"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">

                    {{-- Export icon --}}
                    <svg wire:loading.remove wire:target="exportToExcel" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    
                    {{-- Button text --}}
                    <span wire:loading wire:target="exportToExcel">Exporting...</span>
                    <span wire:loading.remove wire:target="exportToExcel">Export to Excel</span>
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Date</label>
                <input type="date" wire:model="reportDate" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            <div class="flex items-end">
                <button wire:click="loadDailyOperationsData" 
                        class="w-full px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Refresh Data
                </button>
            </div>
        </div>
    </div>

    {{-- Daily Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $dailySummary['total_clients_served'] ?? 0 }}</h4>
                    <p class="text-sm text-gray-500">Clients Served</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalTransactions }}</h4>
                    <p class="text-sm text-gray-500">Total Transactions</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalTransactionValue, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Transaction Value</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalNewLoans }}</h4>
                    <p class="text-sm text-gray-500">New Loans</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Summary --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-medium text-blue-900">Deposits</h4>
                <p class="text-2xl font-bold text-blue-600">{{ $transactionSummary['deposits']['count'] ?? 0 }}</p>
                <p class="text-sm text-blue-700">{{ number_format($transactionSummary['deposits']['total_amount'] ?? 0, 2) }} TZS</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <h4 class="font-medium text-red-900">Withdrawals</h4>
                <p class="text-2xl font-bold text-red-600">{{ $transactionSummary['withdrawals']['count'] ?? 0 }}</p>
                <p class="text-sm text-red-700">{{ number_format($transactionSummary['withdrawals']['total_amount'] ?? 0, 2) }} TZS</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h4 class="font-medium text-green-900">Transfers</h4>
                <p class="text-2xl font-bold text-green-600">{{ $transactionSummary['transfers']['count'] ?? 0 }}</p>
                <p class="text-sm text-green-700">{{ number_format($transactionSummary['transfers']['total_amount'] ?? 0, 2) }} TZS</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-medium text-yellow-900">Loan Payments</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ $transactionSummary['loan_payments']['count'] ?? 0 }}</p>
                <p class="text-sm text-yellow-700">{{ number_format($transactionSummary['loan_payments']['total_amount'] ?? 0, 2) }} TZS</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900">Other</h4>
                <p class="text-2xl font-bold text-gray-600">{{ $transactionSummary['other_transactions']['count'] ?? 0 }}</p>
                <p class="text-sm text-gray-700">{{ number_format($transactionSummary['other_transactions']['total_amount'] ?? 0, 2) }} TZS</p>
            </div>
        </div>
    </div>

    {{-- Loan Operations --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Operations</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($loanOperations as $loan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loan['loan_id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['client_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan['loan_amount'], 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['loan_type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['officer'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $loan['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 
                                       ($loan['status'] === 'Under Review' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $loan['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan['timestamp'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No loan operations found for this date</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- New Loans and Disbursements --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- New Loans --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">New Loans Approved</h3>
            <div class="space-y-4">
                @forelse($newLoans as $loan)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $loan['client_name'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $loan['loan_type'] }}</p>
                                <p class="text-sm text-gray-500">Officer: {{ $loan['officer'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ number_format($loan['loan_amount'], 2) }} TZS</p>
                                <p class="text-sm text-gray-600">{{ $loan['interest_rate'] }}% for {{ $loan['term_months'] }} months</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center">No new loans approved today</p>
                @endforelse
            </div>
        </div>

        {{-- Loan Disbursements --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Disbursements</h3>
            <div class="space-y-4">
                @forelse($loanDisbursements as $disbursement)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $disbursement['client_name'] }}</h4>
                                <p class="text-sm text-gray-600">Method: {{ $disbursement['method'] }}</p>
                                <p class="text-sm text-gray-500">Officer: {{ $disbursement['officer'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ number_format($disbursement['disbursed_amount'], 2) }} TZS</p>
                                <p class="text-sm text-gray-600">{{ $disbursement['disbursement_date'] }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center">No loan disbursements today</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Staff Activities --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Staff Activities</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clients Served</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($staffActivities as $staff)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $staff['staff_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $staff['position'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $staff['department'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $staff['activities_completed'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $staff['clients_served'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $staff['transactions_processed'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $staff['efficiency_rating'] === 'Excellent' ? 'bg-green-100 text-green-800' : 
                                       ($staff['efficiency_rating'] === 'Good' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $staff['efficiency_rating'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No staff activities recorded</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Summary Details --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Summary Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Operational Metrics</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Day of Week:</span>
                        <span class="font-medium">{{ $dailySummary['day_of_week'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Staff on Duty:</span>
                        <span class="font-medium">{{ $dailySummary['staff_on_duty'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">System Uptime:</span>
                        <span class="font-medium">{{ $dailySummary['system_uptime'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Performance Metrics</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Avg Transaction Time:</span>
                        <span class="font-medium">{{ $dailySummary['average_transaction_time'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Peak Hours:</span>
                        <span class="font-medium">{{ $dailySummary['peak_hours'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Avg Transaction Value:</span>
                        <span class="font-medium">{{ number_format($averageTransactionValue, 2) }} TZS</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Activity Summary</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">New Clients:</span>
                        <span class="font-medium">{{ $totalNewClients }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Loan Disbursements:</span>
                        <span class="font-medium">{{ $totalLoanDisbursements }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Loan Repayments:</span>
                        <span class="font-medium">{{ $totalLoanRepayments }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
