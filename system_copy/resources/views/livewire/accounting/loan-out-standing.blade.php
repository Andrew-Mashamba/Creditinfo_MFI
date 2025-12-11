<div class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Loan Outstanding Report</h1>
            <p class="mt-2 text-sm text-gray-600">Comprehensive view of all outstanding loan balances as of {{ now()->format('F d, Y') }}</p>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Outstanding Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Outstanding</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($summary['total_outstanding'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">TZS</p>
                    </div>
                    <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Loans Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Loans</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_loans'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active Accounts</p>
                    </div>
                    <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Amount Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Overdue Amount</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($summary['total_overdue'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $summary['overdue_loans_count'] ?? 0 }} Loans</p>
                    </div>
                    <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Outstanding Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Outstanding</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($summary['average_outstanding'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Per Loan</p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Principal vs Interest Breakdown -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Outstanding Breakdown</h3>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Principal Outstanding:</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($summary['total_principal_outstanding'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Interest Outstanding:</span>
                        <span class="text-sm font-semibold text-gray-900">{{ number_format($summary['total_interest_outstanding'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700">Total:</span>
                            <span class="text-sm font-bold text-blue-600">{{ number_format($summary['total_outstanding'] ?? 0, 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aged Outstanding Analysis -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Loan Classification by Aging</h3>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Standard</span>
                            <span class="text-xs text-gray-500 ml-1">(0-30 days)</span>
                        </div>
                        <span class="text-sm font-semibold text-green-600">{{ number_format($agedOutstanding['standard'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Watch</span>
                            <span class="text-xs text-gray-500 ml-1">(31-90 days)</span>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">{{ number_format($agedOutstanding['watch'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Substandard</span>
                            <span class="text-xs text-gray-500 ml-1">(91-180 days)</span>
                        </div>
                        <span class="text-sm font-semibold text-yellow-600">{{ number_format($agedOutstanding['substandard'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Doubtful</span>
                            <span class="text-xs text-gray-500 ml-1">(181-365 days)</span>
                        </div>
                        <span class="text-sm font-semibold text-orange-600">{{ number_format($agedOutstanding['doubtful'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Loss</span>
                            <span class="text-xs text-gray-500 ml-1">(&gt;365 days)</span>
                        </div>
                        <span class="text-sm font-semibold text-red-700">{{ number_format($agedOutstanding['loss'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-700">Total Overdue:</span>
                            <span class="text-sm font-bold text-red-600">{{ number_format($agedOutstanding['total'] ?? 0, 2) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" wire:model.debounce.300ms="search"
                           placeholder="Search by loan account, client number, or name..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model="filterStatus"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Loans</option>
                        <option value="performing">Performing</option>
                        <option value="overdue">Overdue</option>
                        <option value="high_risk">High Risk (>90 days)</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Show</label>
                    <select wire:model="perPage"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 flex justify-end space-x-2">
                <button wire:click="exportToExcel"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="inline-block h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to Excel
                </button>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('loan_account_number')">
                                Loan Account
                                @if($sortField === 'loan_account_number')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('client_number')">
                                Client Number
                                @if($sortField === 'client_number')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Client Name
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('principal_outstanding')">
                                Principal O/S
                                @if($sortField === 'principal_outstanding')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('interest_outstanding')">
                                Interest O/S
                                @if($sortField === 'interest_outstanding')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('total_outstanding')">
                                Total O/S
                                @if($sortField === 'total_outstanding')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('overdue_amount')">
                                Overdue
                                @if($sortField === 'overdue_amount')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" wire:click="sortBy('days_in_arrears')">
                                Days Overdue
                                @if($sortField === 'days_in_arrears')
                                    <span class="text-blue-600">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($loanOutstandings as $loan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $loan->loan_account_number }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $loan->client_number }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $loan->client_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($loan->principal_outstanding, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                    {{ number_format($loan->interest_outstanding, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-blue-600">
                                    {{ number_format($loan->total_outstanding, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ $loan->overdue_amount > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    {{ number_format($loan->overdue_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    @if($loan->days_in_arrears > 0)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $loan->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : ($loan->days_in_arrears > 30 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $loan->days_in_arrears }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    @if($loan->is_overdue)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Current
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <button wire:click="viewLoanDetails('{{ $loan->loan_id }}')"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    No loans found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $loanOutstandings->links() }}
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedLoan)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white" wire:click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Loan Outstanding Details - {{ $selectedLoan['loan_account_number'] }}
                    </h3>
                    <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-4">
                    <!-- Loan Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Client Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $selectedLoan['client_number'] }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Principal Amount</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($selectedLoan['principal_amount'], 2) }} TZS</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Interest Rate</p>
                            <p class="text-lg font-semibold text-gray-900">{{ number_format($selectedLoan['interest_rate'], 2) }}%</p>
                        </div>
                    </div>

                    <!-- Outstanding Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-600">Principal Outstanding</p>
                            <p class="text-xl font-bold text-blue-700">{{ number_format($selectedLoan['principal_outstanding'], 2) }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-green-600">Interest Outstanding</p>
                            <p class="text-xl font-bold text-green-700">{{ number_format($selectedLoan['interest_outstanding'], 2) }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-purple-600">Total Outstanding</p>
                            <p class="text-xl font-bold text-purple-700">{{ number_format($selectedLoan['total_outstanding'], 2) }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm text-red-600">Total Overdue</p>
                            <p class="text-xl font-bold text-red-700">{{ number_format($selectedLoan['total_overdue'], 2) }}</p>
                        </div>
                    </div>

                    <!-- Schedule Details Table -->
                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Installment Schedule</h4>
                    <div class="overflow-x-auto max-h-96">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Principal Due</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Principal Paid</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Principal O/S</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest Due</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest Paid</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest O/S</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total O/S</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedLoan['schedule_details'] as $schedule)
                                    <tr class="{{ $schedule['is_overdue'] ? 'bg-red-50' : '' }}">
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900">{{ $schedule['installment_date'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">{{ number_format($schedule['principal_due'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-green-600">{{ number_format($schedule['principal_paid'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-gray-900">{{ number_format($schedule['principal_outstanding'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-gray-700">{{ number_format($schedule['interest_due'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-green-600">{{ number_format($schedule['interest_paid'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-gray-900">{{ number_format($schedule['interest_outstanding'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-blue-600">{{ number_format($schedule['total_outstanding'], 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $schedule['status'] === 'PAID' ? 'bg-green-100 text-green-800' : ($schedule['is_overdue'] ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $schedule['status'] }}
                                                @if($schedule['days_in_arrears'] > 0)
                                                    ({{ $schedule['days_in_arrears'] }}d)
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                    <button wire:click="closeDetailModal"
                            class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
