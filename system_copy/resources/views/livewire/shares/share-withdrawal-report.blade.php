<div>
    {{-- Share Withdrawal Report Modal --}}
    @if($showShareWithdrawalReport)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-4/5">
                {{-- Modal Header --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">
                            Share Withdrawal Report
                        </h2>
                        <button wire:click="hideShareWithdrawalReport" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="space-y-6">
                        {{-- Summary Statistics --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h3 class="text-sm font-medium text-gray-500">Total Withdrawals</h3>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalWithdrawals ?? 0 }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h3 class="text-sm font-medium text-gray-500">Total Shares Withdrawn</h3>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totalSharesWithdrawn ?? 0) }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h3 class="text-sm font-medium text-gray-500">Total Value</h3>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">TZS {{ number_format($totalWithdrawalValue ?? 0) }}</p>
                            </div>
                        </div>

                        {{-- Filters --}}
                        <div class="bg-white p-4 rounded-lg shadow">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="dateFrom" class="block text-sm font-medium text-gray-700">From Date</label>
                                    <input type="date" wire:model.live="dateFrom" id="dateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="dateTo" class="block text-sm font-medium text-gray-700">To Date</label>
                                    <input type="date" wire:model.live="dateTo" id="dateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                    <select wire:model.live="withdrawalStatus" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All Status</option>
                                        <option value="PENDING">Pending</option>
                                        <option value="APPROVED">Approved</option>
                                        <option value="COMPLETED">Completed</option>
                                        <option value="REJECTED">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Withdrawals Table --}}
                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($withdrawals['data'] as $withdrawal)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($withdrawal['created_at'])->format('Y-m-d') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if(isset($withdrawal['member']) && $withdrawal['member'])
                                                            {{ $withdrawal['member']['first_name'] ?? '' }} {{ $withdrawal['member']['last_name'] ?? '' }}
                                                        @else
                                                            Member Not Found
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $withdrawal['client_number'] ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $withdrawal['product_name'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ number_format($withdrawal['withdrawal_amount']) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    TZS {{ number_format($withdrawal['total_value']) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($withdrawal['status'] === 'COMPLETED') bg-green-100 text-green-800
                                                        @elseif($withdrawal['status'] === 'PENDING') bg-yellow-100 text-yellow-800
                                                        @elseif($withdrawal['status'] === 'REJECTED') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800
                                                        @endif">
                                                        {{ $withdrawal['status'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <button wire:click="viewWithdrawalDetails({{ $withdrawal['id'] }})" class="text-indigo-600 hover:text-indigo-900">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                    No withdrawals found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 flex justify-between sm:hidden">
                                        <button wire:click="previousPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Previous
                                        </button>
                                        <button wire:click="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Next
                                        </button>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Showing
                                                <span class="font-medium">{{ ($withdrawals['current_page'] - 1) * $withdrawals['per_page'] + 1 }}</span>
                                                to
                                                <span class="font-medium">{{ min($withdrawals['current_page'] * $withdrawals['per_page'], $withdrawals['total']) }}</span>
                                                of
                                                <span class="font-medium">{{ $withdrawals['total'] }}</span>
                                                results
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    <span class="sr-only">Previous</span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                @for($i = 1; $i <= $withdrawals['last_page']; $i++)
                                                    <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 {{ $i === $withdrawals['current_page'] ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                                        {{ $i }}
                                                    </button>
                                                @endfor
                                                <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    <span class="sr-only">Next</span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="exportWithdrawalReport" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Export Report
                    </button>
                    <button wire:click="hideShareWithdrawalReport" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Withdrawal Details Modal --}}
    @if($showWithdrawalDetails && $selectedWithdrawal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-1/2 sm:my-8">
                {{-- Modal Header --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">
                            Withdrawal Details
                        </h2>
                        <button wire:click="hideWithdrawalDetails" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Member Details</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedWithdrawal->member->first_name }} {{ $selectedWithdrawal->member->last_name }}</p>
                                <p class="text-sm text-gray-500">Client #: {{ $selectedWithdrawal->client_number }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Product Details</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedWithdrawal->product_name }}</p>
                                <p class="text-sm text-gray-500">Price per Share: TZS {{ number_format($selectedWithdrawal->nominal_price) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Withdrawal Details</h3>
                                <p class="mt-1 text-sm text-gray-900">Shares: {{ number_format($selectedWithdrawal->withdrawal_amount) }}</p>
                                <p class="text-sm text-gray-900">Total Value: TZS {{ number_format($selectedWithdrawal->total_value) }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Account Details</h3>
                                <p class="mt-1 text-sm text-gray-900">Receiving Account: {{ $selectedWithdrawal->receiving_account_number }}</p>
                                <p class="text-sm text-gray-500">Source Account: {{ $selectedWithdrawal->source_account_number }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Reason</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $selectedWithdrawal->reason }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedWithdrawal->status }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Processed By</h3>
                                <p class="mt-1 text-sm text-gray-900">{{ $selectedWithdrawal->approver->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $selectedWithdrawal->approved_at ? $selectedWithdrawal->approved_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="hideWithdrawalDetails" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
