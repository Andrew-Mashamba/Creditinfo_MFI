<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Loan Repayment Schedule Report</h2>
                <p class="text-gray-600">Detailed loan repayment schedules and payment plans</p>
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

    {{-- Summary Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $totalScheduledPayments }}</h4>
                    <p class="text-sm text-gray-500">Scheduled Payments</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalOverdueAmount, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Overdue Amount</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ number_format($totalUpcomingAmount, 2) }} TZS</h4>
                    <p class="text-sm text-gray-500">Upcoming Amount</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $collectionRate }}%</h4>
                    <p class="text-sm text-gray-500">Collection Rate</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Repayment Schedules --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Repayment Schedules</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($repaymentSchedules as $schedule)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $schedule['loan_id'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule['client_name'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($schedule['outstanding_balance'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($schedule['monthly_payment'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule['next_payment_date'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $schedule['remaining_payments'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $schedule['payment_status'] === 'Current' ? 'bg-green-100 text-green-800' : 
                                       ($schedule['payment_status'] === 'Overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $schedule['payment_status'] ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No repayment schedules available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Overdue Payments --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Overdue Payments</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penalty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($overduePayments as $overdue)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $overdue['loan_id'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $overdue['client_name'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($overdue['overdue_amount'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $overdue['days_overdue'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($overdue['penalty_amount'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($overdue['total_due'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $overdue['risk_level'] === 'Low' ? 'bg-green-100 text-green-800' : 
                                       ($overdue['risk_level'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($overdue['risk_level'] === 'High' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $overdue['risk_level'] ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $overdue['collection_status'] ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No overdue payments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Upcoming Payments --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Payments</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Until Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($upcomingPayments as $upcoming)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $upcoming['loan_id'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $upcoming['client_name'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $upcoming['due_date'] ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($upcoming['due_amount'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($upcoming['principal_amount'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($upcoming['interest_amount'] ?? 0, 2) }} TZS</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $upcoming['days_until_due'] ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $upcoming['risk_level'] === 'Low' ? 'bg-green-100 text-green-800' : 
                                       ($upcoming['risk_level'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $upcoming['risk_level'] ?? 'N/A' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No upcoming payments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment Patterns --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Patterns</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Payment Timing</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">On Time:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_timing']['on_time'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Early:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_timing']['early'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Late (1-7 days):</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_timing']['late_1_7_days'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Late (8-30 days):</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_timing']['late_8_30_days'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Payment Methods</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Bank Transfer:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_methods']['bank_transfer'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Mobile Money:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_methods']['mobile_money'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cash:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_methods']['cash'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cheque:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_methods']['cheque'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-gray-900">Payment Frequency</h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monthly:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_frequency']['monthly'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Bi-weekly:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_frequency']['bi_weekly'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Quarterly:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_frequency']['quarterly'] ?? 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Weekly:</span>
                        <span class="font-medium">{{ $paymentPatterns['payment_frequency']['weekly'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Collection Efficiency --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Collection Efficiency</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $collectionEfficiency['overall_collection_rate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-500">Overall Collection Rate</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ $collectionEfficiency['on_time_collection_rate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-500">On-Time Collection Rate</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $collectionEfficiency['late_collection_rate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-500">Late Collection Rate</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-red-600">{{ $collectionEfficiency['write_off_rate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-500">Write-off Rate</div>
            </div>
        </div>
    </div>
</div>
