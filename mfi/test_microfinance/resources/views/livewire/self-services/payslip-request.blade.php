<div class="min-h-screen bg-gray-50 p-6">
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Payslips & Tax Documents</h2>
            <p class="text-gray-600 mt-1">View and download your payslips, tax certificates, and payment history</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'current')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'current' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Current Payslip
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Payslip History
                </button>
                <button wire:click="$set('selectedTab', 'tax')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'tax' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Tax Documents
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('current')
                    <!-- Current Payslip -->
                    <div class="p-6">
                        @if($currentPayslip)
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $currentPayslip['month'] }} Payslip</h3>
                            <button wire:click="downloadCurrentPayslip" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition duration-150 ease-in-out flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download PDF
                            </button>
                        </div>

                        <!-- Employee Info -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Employee ID</p>
                                    <p class="font-medium">{{ $currentPayslip['employee_id'] }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Pay Period</p>
                                    <p class="font-medium">{{ $currentPayslip['month'] }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Payment Date</p>
                                    <p class="font-medium">{{ \Carbon\Carbon::parse($currentPayslip['payment_date'])->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Payment Method</p>
                                    <p class="font-medium">{{ $currentPayslip['payment_method'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings and Deductions -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Earnings -->
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Earnings</h4>
                                <div class="space-y-2">
                                    @foreach($currentPayslip['earnings'] as $earning)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $earning['description'] }}</span>
                                        <span class="font-medium">TSH {{ number_format($earning['amount']) }}</span>
                                    </div>
                                    @endforeach
                                    <div class="pt-2 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Gross Pay</span>
                                            <span class="font-semibold">TSH {{ number_format($currentPayslip['gross_pay']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions -->
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Deductions</h4>
                                <div class="space-y-2">
                                    @foreach($currentPayslip['deductions'] as $deduction)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $deduction['description'] }}</span>
                                        <span class="font-medium text-red-600">TSH {{ number_format($deduction['amount']) }}</span>
                                    </div>
                                    @endforeach
                                    <div class="pt-2 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <span class="font-semibold">Total Deductions</span>
                                            <span class="font-semibold text-red-600">TSH {{ number_format($currentPayslip['total_deductions']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Net Pay -->
                        <div class="mt-6 bg-red-50 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-red-600 font-medium">Net Pay</p>
                                    <p class="text-2xl font-bold text-red-900">TSH {{ number_format($currentPayslip['net_pay']) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-red-600">Bank Account</p>
                                    <p class="font-medium text-red-900">{{ $currentPayslip['bank_account'] }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Year to Date Summary -->
                        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600">Gross YTD</p>
                                <p class="font-semibold">TSH {{ number_format($yearToDateEarnings['gross_ytd']) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600">Net YTD</p>
                                <p class="font-semibold">TSH {{ number_format($yearToDateEarnings['net_ytd']) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600">Tax YTD</p>
                                <p class="font-semibold">TSH {{ number_format($yearToDateEarnings['tax_ytd']) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-600">Months Worked</p>
                                <p class="font-semibold">{{ $yearToDateEarnings['months_worked'] }}</p>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No Current Payslip</h3>
                            <p class="mt-1 text-sm text-gray-500">Your current month's payslip is not yet available.</p>
                        </div>
                        @endif
                    </div>
                    @break

                @case('history')
                    <!-- Payslip History -->
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Payslip History</h3>
                            <select wire:model="selectedYear" wire:change="filterByYear" 
                                    class="rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 text-sm">
                                @for($year = \Carbon\Carbon::now()->year; $year >= \Carbon\Carbon::now()->year - 5; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Month
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Gross Pay
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Net Pay
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($payslips as $payslip)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $payslip['month'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($payslip['payment_date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            TSH {{ number_format($payslip['gross_pay']) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            TSH {{ number_format($payslip['net_pay']) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($payslip['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button wire:click="downloadPayslip({{ $payslip['id'] }})" 
                                                    class="text-red-600 hover:text-red-900 text-sm font-medium flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                </svg>
                                                Download
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            No payslip history found for {{ $selectedYear }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('tax')
                    <!-- Tax Documents -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tax Information - {{ $taxSummary['year'] }}</h3>
                        
                        <!-- Tax Summary Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-red-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-red-600 font-medium">Gross Income</p>
                                        <p class="text-xl font-bold text-red-900">TSH {{ number_format($taxSummary['gross_income']) }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-green-600 font-medium">PAYE Paid</p>
                                        <p class="text-xl font-bold text-green-900">TSH {{ number_format($taxSummary['paye_paid']) }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-purple-50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-purple-600 font-medium">NSSF Paid</p>
                                        <p class="text-xl font-bold text-purple-900">TSH {{ number_format($taxSummary['nssf_paid']) }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Details -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h4 class="font-semibold text-gray-900 mb-4">Tax Calculation Details</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Gross Annual Income</span>
                                    <span class="font-medium">TSH {{ number_format($taxSummary['gross_income']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Taxable Income</span>
                                    <span class="font-medium">TSH {{ number_format($taxSummary['taxable_income']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax Rate</span>
                                    <span class="font-medium">{{ $taxSummary['tax_rate'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax Bracket</span>
                                    <span class="font-medium">{{ $taxSummary['tax_bracket'] }}</span>
                                </div>
                                <div class="pt-3 border-t border-gray-300">
                                    <div class="flex justify-between">
                                        <span class="font-semibold text-gray-900">Total Tax Paid</span>
                                        <span class="font-semibold text-gray-900">TSH {{ number_format($taxSummary['paye_paid']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Download Tax Certificate -->
                        <div class="bg-red-50 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-red-900">Annual Tax Certificate</h4>
                                    <p class="text-sm text-red-700 mt-1">Download your {{ $taxSummary['year'] }} tax certificate for filing returns</p>
                                </div>
                                <button wire:click="downloadTaxCertificate" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition duration-150 ease-in-out flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download Certificate
                                </button>
                            </div>
                        </div>

                        <!-- Tax Filing Note -->
                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Tax Filing Reminder</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Please ensure to file your annual tax returns before the deadline. Contact HR if you need assistance with tax-related queries.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>