<div>
    <!-- Search and Filters Section -->
    <div class="mb-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Input -->
            <div class="md:col-span-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.debounce.500ms="search"
                        id="search"
                        placeholder="Search by loan number, client name, amount..."
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                    wire:model="statusFilter"
                    id="statusFilter"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Loan Type Filter -->
            <div>
                <label for="loanTypeFilter" class="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
                <select
                    wire:model="loanTypeFilter"
                    id="loanTypeFilter"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Types</option>
                    @foreach($loanTypes as $type)
                        @if($type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Results Count -->
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold">{{ $loans->firstItem() ?? 0 }}</span> to
                <span class="font-semibold">{{ $loans->lastItem() ?? 0 }}</span> of
                <span class="font-semibold">{{ $loans->total() }}</span> results
            </p>

            @if($search || $statusFilter || $loanTypeFilter)
                <button
                    wire:click="$set('search', ''); $set('statusFilter', ''); $set('loanTypeFilter', '')"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Clear Filters
                </button>
            @endif
        </div>
    </div>

    <!-- Loading State -->
    <div wire:loading class="mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-700 font-medium">Searching...</span>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden" wire:loading.remove>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Loan Account
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Client
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Loan Product
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Principal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Outstanding
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pending Installments
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $loan->loan_account_number }}</div>
                                <div class="text-xs text-gray-500">{{ $loan->client_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $loan->client_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $loan->loan_product_name ?? 'N/A' }}</div>
                                @if($loan->loan_type_2)
                                    <div class="text-xs text-gray-500">{{ $loan->loan_type_2 }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ number_format($loan->principle, 2) }} TZS
                                </div>
                                @if($loan->interest)
                                    <div class="text-xs text-gray-500">Interest: {{ number_format($loan->interest, 2) }}%</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-orange-600">
                                    {{ number_format($loan->outstanding_balance, 2) }} TZS
                                </div>
                                @if($loan->outstanding_interest > 0)
                                    <div class="text-xs text-gray-500">Int: {{ number_format($loan->outstanding_interest, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($loan->status == 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($loan->status == 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($loan->status == 'REJECTED') bg-red-100 text-red-800
                                    @elseif($loan->status == 'CLOSED') bg-gray-100 text-gray-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ $loan->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $loan->pending_installments }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button
                                    wire:click="viewSchedule({{ $loan->id }})"
                                    class="text-blue-600 hover:text-blue-900 font-medium"
                                >
                                    View Schedule
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">No loans found</p>
                                    <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($loans->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

    <!-- Schedule Modal -->
    @if($showScheduleModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeScheduleModal"></div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white" id="modal-title">
                                    Loan Repayment Schedule
                                </h3>
                                @if($selectedLoan)
                                    <p class="text-sm text-blue-100 mt-1">
                                        {{ $selectedLoan->loan_account_number }} - {{ $selectedLoan->client->first_name ?? '' }} {{ $selectedLoan->client->last_name ?? '' }}
                                    </p>
                                @endif
                            </div>
                            <button wire:click="closeScheduleModal" class="text-white hover:text-gray-200">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white px-6 py-4 max-h-[600px] overflow-y-auto">
                        @if($loanSchedules->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Installment</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Principal</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Interest</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Penalty</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($loanSchedules as $index => $schedule)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    {{ $schedule->installment_date ? \Carbon\Carbon::parse($schedule->installment_date)->format('d M Y') : 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($schedule->principle ?? 0, 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($schedule->interest ?? 0, 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-right text-red-600">{{ number_format($schedule->penalties ?? 0, 2) }}</td>
                                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                                                    {{ number_format(($schedule->principle ?? 0) + ($schedule->interest ?? 0) + ($schedule->penalties ?? 0), 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                        @if($schedule->completion_status == 'PAID') bg-green-100 text-green-800
                                                        @elseif($schedule->completion_status == 'ACTIVE') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ $schedule->completion_status ?? 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50 font-semibold">
                                        <tr>
                                            <td colspan="2" class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($loanSchedules->sum('principle'), 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($loanSchedules->sum('interest'), 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-red-600">{{ number_format($loanSchedules->sum('penalties'), 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900">
                                                {{ number_format($loanSchedules->sum('principle') + $loanSchedules->sum('interest') + $loanSchedules->sum('penalties'), 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500">No schedule information available for this loan.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4">
                        <button
                            wire:click="closeScheduleModal"
                            class="w-full sm:w-auto px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
