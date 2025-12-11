<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Report Header --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Member Loan Account Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Individual member loan account statements and balances</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="exportToPdf" 
                            wire:loading.attr="disabled"
                            wire:target="exportToPdf"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="exportToPdf" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <svg wire:loading wire:target="exportToPdf" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportToPdf">Export PDF</span>
                        <span wire:loading wire:target="exportToPdf">Generating PDF...</span>
                    </button>
                    <button wire:click="exportToExcel" 
                            wire:loading.attr="disabled"
                            wire:target="exportToExcel"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="exportToExcel" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <svg wire:loading wire:target="exportToExcel" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="exportToExcel">Export Excel</span>
                        <span wire:loading wire:target="exportToExcel">Generating Excel...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Client Selection --}}
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Member Selection</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="selectedClient" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Member
                    </label>
                    <select wire:model.live="selectedClient" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Choose a member...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->full_name }} ({{ $client->client_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="clientNumber" class="block text-sm font-medium text-gray-700 mb-2">
                        Or Enter Member Number
                    </label>
                    <input type="text" 
                           wire:model.live="clientNumber" 
                           placeholder="Enter member number..."
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Client Loans Overview --}}
    @if($clientLoans->count() > 0)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Member Loans Overview</h3>
                <p class="text-sm text-gray-500">All loans for the selected member</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($clientLoans as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->loan_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->product_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->principle, 2) }} TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($loan->outstanding_balance, 2) }} TZS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loan->interest }}%</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($loan->status === 'ACTIVE') bg-green-100 text-green-800
                                        @elseif($loan->status === 'PENDING') bg-yellow-100 text-yellow-800
                                        @elseif($loan->status === 'COMPLETED') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $loan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($loan->days_in_arrears > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $loan->days_in_arrears }} days
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($loan->next_payment_date)
                                        <div class="flex flex-col">
                                            <span>{{ date('Y-m-d', strtotime($loan->next_payment_date)) }}</span>
                                            <span class="text-xs text-gray-500">{{ number_format($loan->next_payment_amount, 2) }} TZS</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="viewLoanDetails({{ $loan->id }})" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Loan Details Modal --}}
    @if($showLoanDetails && $selectedLoan)
        <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeLoanDetails">
            <div class="relative top-10 mx-auto p-4 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Loan Account Details</h3>
                        <button wire:click="closeLoanDetails" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Loan Summary Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                        <div class="bg-red-50 p-3 rounded-lg">
                            <h4 class="text-xs font-medium text-gray-500">Total Loan Amount</h4>
                            <p class="text-lg font-bold text-red-600">{{ number_format($totalLoanAmount, 2) }} TZS</p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <h4 class="text-xs font-medium text-gray-500">Total Paid</h4>
                            <p class="text-lg font-bold text-green-600">{{ number_format($totalPaidAmount, 2) }} TZS</p>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-lg">
                            <h4 class="text-xs font-medium text-gray-500">Outstanding Balance</h4>
                            <p class="text-lg font-bold text-orange-600">{{ number_format($outstandingBalance, 2) }} TZS</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg">
                            <h4 class="text-xs font-medium text-gray-500">Days in Arrears</h4>
                            <p class="text-lg font-bold text-red-600">{{ $daysInArrears }} days</p>
                        </div>
                    </div>

                    {{-- Loan Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Loan Information</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Loan ID:</span>
                                    <span class="font-medium">{{ $selectedLoan->loan_id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Product:</span>
                                    <span class="font-medium">{{ $loanAccountDetails['product_name'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Interest Rate:</span>
                                    <span class="font-medium">{{ $selectedLoan->interest }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium">{{ $selectedLoan->status }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-3 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Member Information</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">{{ $loanAccountDetails['client_name'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium">{{ $loanAccountDetails['client_phone'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ $loanAccountDetails['client_email'] ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Branch:</span>
                                    <span class="font-medium">{{ $loanAccountDetails['branch_name'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Next Payment Information --}}
                    @if($nextPaymentDate)
                        <div class="bg-yellow-50 p-3 rounded-lg mb-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Next Payment Due</h4>
                            <div class="flex justify-between items-center">
                                <div class="text-sm">
                                    <p class="text-gray-600">Payment Date: <span class="font-medium">{{ date('Y-m-d', strtotime($nextPaymentDate)) }}</span></p>
                                    <p class="text-gray-600">Amount: <span class="font-medium">{{ number_format($nextPaymentAmount, 2) }} TZS</span></p>
                                </div>
                                @if($daysInArrears > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Overdue by {{ $daysInArrears }} days
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Current
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Payment Schedule --}}
                    @if($loanSchedules && $loanSchedules->count() > 0)
                        <div class="bg-white border rounded-lg max-h-80 overflow-y-auto">
                            <div class="px-4 py-3 border-b border-gray-200 sticky top-0 bg-white">
                                <h4 class="text-sm font-semibold text-gray-900">Payment Schedule</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-12">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S/N</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Due</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($loanSchedules as $index => $schedule)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $index + 1 }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ date('Y-m-d', strtotime($schedule->installment_date)) }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ number_format($schedule->installment, 2) }} TZS</td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ number_format($schedule->payment ?? 0, 2) }} TZS</td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                        @if($schedule->payment_status === 'Paid') bg-green-100 text-green-800
                                                        @else bg-yellow-100 text-yellow-800 @endif">
                                                        {{ $schedule->payment_status }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                                    @if($schedule->days_in_arrears > 0)
                                                        <span class="text-red-600 font-medium">{{ $schedule->days_in_arrears }} days</span>
                                                    @else
                                                        <span class="text-green-600">Current</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

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
