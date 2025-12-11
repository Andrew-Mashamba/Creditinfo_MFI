<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-600">Total Accrued</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($summaryStats['total_accrued'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-600">Pending Realization</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($summaryStats['pending_amount'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $summaryStats['pending_count'] }} expenses</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-600">Realized</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($summaryStats['realized_amount'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $summaryStats['realized_count'] }} expenses</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-600">Overdue</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($summaryStats['overdue_amount'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $summaryStats['overdue_count'] }} expenses</p>
        </div>
    </div>

    {{-- Filters and Actions --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">From:</label>
                <input type="date" wire:model="dateFrom" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">To:</label>
                <input type="date" wire:model="dateTo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Status:</label>
                <select wire:model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="realized">Realized</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="flex-1">
                <div class="relative">
                    <input type="text"
                           wire:model.debounce.300ms="search"
                           placeholder="Search by description, account..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button wire:click="openCreateModal" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Accrual
            </button>
        </div>
    </div>

    {{-- Accrued Expenses Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accrual Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected Payment</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accruedExpenses as $expense)
                        @php
                            $isOverdue = !$expense->realization_date && $expense->expected_payment_date && \Carbon\Carbon::parse($expense->expected_payment_date)->isPast();
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $expense->accrual_date ? \Carbon\Carbon::parse($expense->accrual_date)->format('M d, Y') : '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $expense->accrual_date ? \Carbon\Carbon::parse($expense->accrual_date)->format('H:i') : '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $expense->description }}">
                                    {{ $expense->description }}
                                </div>
                                <div class="text-xs text-gray-500">By: {{ $expense->created_by_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $expense->account_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $expense->account_number ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                {{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($expense->expected_payment_date)
                                    <div class="text-sm {{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                        {{ \Carbon\Carbon::parse($expense->expected_payment_date)->format('M d, Y') }}
                                    </div>
                                    @if($isOverdue)
                                        <div class="text-xs text-red-500">{{ \Carbon\Carbon::parse($expense->expected_payment_date)->diffForHumans() }}</div>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($expense->realization_date)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Realized
                                    </span>
                                @elseif($isOverdue)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Overdue
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="viewDetails({{ $expense->id }})" class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    @if(!$expense->realization_date)
                                        <button wire:click="openRealizeModal({{ $expense->id }})" class="text-green-600 hover:text-green-900" title="Realize Accrual">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>No accrued expenses found for the selected period.</p>
                                <button wire:click="openCreateModal" class="mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                    Create your first accrual
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($accruedExpenses->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $accruedExpenses->links() }}
            </div>
        @endif
    </div>

    {{-- Create Accrual Modal --}}
    @if($showCreateModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeCreateModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createAccrual">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Create Accrued Expense</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Account *</label>
                                    <select wire:model="accrualAccountId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Account</option>
                                        @foreach($expenseAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('accrualAccountId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                                    <textarea wire:model="accrualDescription" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the accrued expense..."></textarea>
                                    @error('accrualDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (TZS) *</label>
                                    <input type="number" step="0.01" wire:model="accrualAmount" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                                    @error('accrualAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected Payment Date *</label>
                                    <input type="date" wire:model="accrualExpectedPaymentDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('accrualExpectedPaymentDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                    <textarea wire:model="accrualNotes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Additional notes..."></textarea>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-blue-700">
                                    <strong>GL Entry:</strong> This will create the following journal entry:<br>
                                    DR: Selected Expense Account<br>
                                    CR: Accrued Expenses Liability (0101200029002920)
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Create Accrual
                            </button>
                            <button type="button" wire:click="closeCreateModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Realize Accrual Modal --}}
    @if($showRealizeModal)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeRealizeModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <form wire:submit.prevent="realizeAccrual">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Realize Accrued Expense</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Realization Date *</label>
                                    <input type="date" wire:model="realizationDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('realizationDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                    <textarea wire:model="realizationNotes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Notes about realization..."></textarea>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-green-50 rounded-lg">
                                <p class="text-xs text-green-700">
                                    <strong>What happens:</strong><br>
                                    1. The accrual liability will be reversed<br>
                                    2. The expense will move to "Pending Approval" status<br>
                                    3. Once approved, it can be paid
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Realize Expense
                            </button>
                            <button type="button" wire:click="closeRealizeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if($showDetailModal && $selectedAccrual)
        <div class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="closeDetailModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Accrued Expense Details</h3>
                            <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Status Badge --}}
                        <div class="mb-4">
                            @if($selectedAccrual->realization_date)
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Realized</span>
                            @elseif($selectedAccrual->expected_payment_date && \Carbon\Carbon::parse($selectedAccrual->expected_payment_date)->isPast())
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                            @else
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Accrual Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAccrual->accrual_date ? \Carbon\Carbon::parse($selectedAccrual->accrual_date)->format('M d, Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Expected Payment Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAccrual->expected_payment_date ? \Carbon\Carbon::parse($selectedAccrual->expected_payment_date)->format('M d, Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Amount</p>
                                <p class="text-lg font-bold text-gray-900">TZS {{ number_format($selectedAccrual->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Status</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAccrual->status }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Expense Account</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAccrual->account_name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $selectedAccrual->account_number }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Description</p>
                                <p class="text-sm text-gray-900">{{ $selectedAccrual->description }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Created By</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedAccrual->created_by_name ?? 'N/A' }}</p>
                            </div>
                            @if($selectedAccrual->realization_date)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Realization Date</p>
                                    <p class="text-sm font-medium text-green-600">{{ \Carbon\Carbon::parse($selectedAccrual->realization_date)->format('M d, Y H:i') }}</p>
                                </div>
                            @endif
                            @if($selectedAccrual->budget_notes)
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500 uppercase">Notes</p>
                                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $selectedAccrual->budget_notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @if(!$selectedAccrual->realization_date)
                            <button wire:click="openRealizeModal({{ $selectedAccrual->id }})" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Realize Expense
                            </button>
                        @endif
                        <button type="button" wire:click="closeDetailModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
