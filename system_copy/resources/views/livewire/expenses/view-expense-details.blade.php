<div>
    @if($showModal && $expense)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Expense Details #{{ $expense->id }}
                            </h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Basic Information -->
                                <div class="col-span-2 border-b pb-3">
                                    <h4 class="font-semibold text-gray-700 mb-2">Basic Information</h4>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Description</label>
                                    <p class="font-medium">{{ $expense->description }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Amount</label>
                                    <p class="font-medium">{{ number_format($expense->amount, 2) }} TZS</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Expense Account</label>
                                    <p class="font-medium">{{ $expense->account->account_name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Payment Type</label>
                                    <p class="font-medium">{{ ucwords(str_replace('_', ' ', $expense->payment_type)) }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Status</label>
                                    <p>
                                        @php
                                            $badgeClass = match($expense->status) {
                                                'PENDING_APPROVAL' => 'bg-yellow-100 text-yellow-800',
                                                'APPROVED' => 'bg-blue-100 text-blue-800',
                                                'PAID' => 'bg-green-100 text-green-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="{{ $badgeClass }} text-xs font-medium px-2.5 py-0.5 rounded">
                                            {{ str_replace('_', ' ', $expense->status) }}
                                        </span>
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Submitted By</label>
                                    <p class="font-medium">{{ $expense->user->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Submission Date</label>
                                    <p class="font-medium">{{ $expense->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Expense Month</label>
                                    <p class="font-medium">{{ $expense->expense_month ? \Carbon\Carbon::parse($expense->expense_month)->format('F Y') : 'N/A' }}</p>
                                </div>
                                
                                <!-- Budget Information -->
                                @if($expense->budget_item_id || $expense->budget_allocation_id)
                                <div class="col-span-2 border-b pb-3 mt-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Budget Information</h4>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Budget Status</label>
                                    <p class="font-medium">{{ $expense->budget_status ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Budget Utilization</label>
                                    <p class="font-medium">{{ $expense->budget_utilization_percentage ? number_format($expense->budget_utilization_percentage, 1) . '%' : 'N/A' }}</p>
                                </div>
                                
                                @if($expense->budget_resolution && $expense->budget_resolution !== 'NONE')
                                <div>
                                    <label class="text-sm text-gray-500">Budget Resolution</label>
                                    <p class="font-medium">{{ str_replace('_', ' ', $expense->budget_resolution) }}</p>
                                </div>
                                @endif
                                
                                @if($expense->budget_notes)
                                <div class="col-span-2">
                                    <label class="text-sm text-gray-500">Budget Notes</label>
                                    <p class="font-medium">{{ $expense->budget_notes }}</p>
                                </div>
                                @endif
                                @endif
                                
                                <!-- Payment Information -->
                                @if($expense->status === 'PAID')
                                <div class="col-span-2 border-b pb-3 mt-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Payment Date</label>
                                    <p class="font-medium">{{ $expense->payment_date ? \Carbon\Carbon::parse($expense->payment_date)->format('d/m/Y H:i') : 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Payment Reference</label>
                                    <p class="font-medium">{{ $expense->payment_reference ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Paid By</label>
                                    <p class="font-medium">{{ $expense->paidByUser->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Transaction ID</label>
                                    <p class="font-medium">{{ $expense->payment_transaction_id ?? 'N/A' }}</p>
                                </div>
                                @endif
                                
                                <!-- Approval Information -->
                                @if($expense->approval)
                                <div class="col-span-2 border-b pb-3 mt-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Approval Information</h4>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Approval Status</label>
                                    <p class="font-medium">{{ $expense->approval->approval_status ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm text-gray-500">Process Code</label>
                                    <p class="font-medium">{{ $expense->approval->process_code ?? 'N/A' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="closeModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>