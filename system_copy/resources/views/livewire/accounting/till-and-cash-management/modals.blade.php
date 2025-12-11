{{-- Create Till Modal --}}
@if($showCreateTillModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Create New Till
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveCreateTill">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Basic Information --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Basic Information</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till Name *</label>
                                        <input type="text" wire:model="newTill.name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Main Till, Counter 1">
                                        @error('newTill.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till Number *</label>
                                        <input type="text" wire:model="newTill.till_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., TIL001, CT001">
                                        @error('newTill.till_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Branch *</label>
                                        <select wire:model="newTill.branch_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Branch</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newTill.branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Institution *</label>
                                        <select wire:model="newTill.institution_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Institution</option>
                                            @foreach($institutions ?? [] as $institution)
                                                <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newTill.institution_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Financial Limits --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Financial Limits</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Maximum Limit</label>
                                        <input type="number" wire:model="newTill.maximum_limit" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="500000.00">
                                        <p class="text-xs text-gray-500 mt-1">Maximum cash the till can hold</p>
                                        @error('newTill.maximum_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Minimum Limit</label>
                                        <input type="number" wire:model="newTill.minimum_limit" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="10000.00">
                                        <p class="text-xs text-gray-500 mt-1">Minimum cash required</p>
                                        @error('newTill.minimum_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Settings --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Settings</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Initial Status</label>
                                        <select wire:model="newTill.status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="closed">Closed</option>
                                            <option value="open">Open</option>
                                            <option value="suspended">Suspended</option>
                                            <option value="maintenance">Maintenance</option>
                                        </select>
                                        @error('newTill.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>



                                    <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="parent_account">
                            Parent Account *
                            </label>
                            <select wire:model="newTill.parent_account" id="parent_account"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Parent Account</option>
                                @foreach($parentAccounts ?? [] as $account)
                                    <option value="{{ $account->account_number }}">
                                        {{ $account->account_name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('newTill.parent_account') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>


                                    
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="newTill.requires_supervisor_approval" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">Requires Supervisor Approval</span>
                                        </label>
                                        @error('newTill.requires_supervisor_approval') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Description --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea wire:model="newTill.description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter till description, location, or special instructions..."></textarea>
                                        @error('newTill.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveCreateTill" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Create Till
                </button>
                <button wire:click="closeCreateTillModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Till Operations Modal --}}
@if($showTillModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $tillModalTitle }}
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveTillOperation">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="selectedTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} ({{ $till->teller->name ?? 'Unassigned' }})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Operation Type</label>
                                        <select wire:model="tillOperationType" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Operation</option>
                                            <option value="opening">Till Opening</option>
                                            <option value="closing">Till Closing</option>
                                            <option value="deposit">Cash Deposit</option>
                                            <option value="withdrawal">Cash Withdrawal</option>
                                            <option value="transfer">Transfer to Strongroom</option>
                                        </select>
                                        @error('tillOperationType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" wire:model="tillOperationAmount" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                                        @error('tillOperationAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference/Notes</label>
                                        <textarea wire:model="tillOperationNotes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter reference or notes..."></textarea>
                                        @error('tillOperationNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveTillOperation" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Operation
                </button>
                <button wire:click="closeTillModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Strongroom Movement Modal --}}
@if($showStrongroomModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Strongroom Cash Movement
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveStrongroomMovement">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Movement Type</label>
                                        <select wire:model="strongroomMovementType" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select Type</option>
                                            <option value="deposit">Deposit to Strongroom</option>
                                            <option value="withdrawal">Withdrawal from Strongroom</option>
                                            <option value="transfer">Transfer Between Tills</option>
                                        </select>
                                        @error('strongroomMovementType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" wire:model="strongroomMovementAmount" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="0.00">
                                        @error('strongroomMovementAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Source Till (if applicable)</label>
                                        <select wire:model="sourceTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select Source Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} ({{ $till->teller->name ?? 'Unassigned' }})</option>
                                            @endforeach
                                        </select>
                                        @error('sourceTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destination Till (if applicable)</label>
                                        <select wire:model="destinationTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select Destination Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} ({{ $till->teller->name ?? 'Unassigned' }})</option>
                                            @endforeach
                                        </select>
                                        @error('destinationTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Purpose/Reference</label>
                                        <textarea wire:model="strongroomMovementPurpose" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="Enter purpose or reference..."></textarea>
                                        @error('strongroomMovementPurpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveStrongroomMovement" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Movement
                </button>
                <button wire:click="closeStrongroomModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Reconciliation Modal --}}
@if($showReconciliationModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Till Reconciliation
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveReconciliation">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="reconciliationTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} ({{ $till->teller->name ?? 'Unassigned' }})</option>
                                            @endforeach
                                        </select>
                                        @error('reconciliationTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Expected Amount</label>
                                        <input type="number" wire:model="expectedAmount" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="0.00">
                                        @error('expectedAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Actual Amount</label>
                                        <input type="number" wire:model="actualAmount" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="0.00">
                                        @error('actualAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Variance Notes</label>
                                        <textarea wire:model="varianceNotes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="Explain any variance..."></textarea>
                                        @error('varianceNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveReconciliation" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Reconciliation
                </button>
                <button wire:click="closeReconciliationModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Approval Modal --}}
@if($showApprovalModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Request Approval
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveApproval">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Approval Type</label>
                                        <select wire:model="approvalRequestType" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                            <option value="">Select Type</option>
                                            <option value="till_opening">Till Opening</option>
                                            <option value="till_closing">Till Closing</option>
                                            <option value="cash_movement">Cash Movement</option>
                                            <option value="strongroom_access">Strongroom Access</option>
                                            <option value="reconciliation">Reconciliation</option>
                                        </select>
                                        @error('approvalRequestType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount (if applicable)</label>
                                        <input type="number" wire:model="approvalRequestAmount" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500" placeholder="0.00">
                                        @error('approvalRequestAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason/Justification</label>
                                        <textarea wire:model="approvalRequestReason" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500" placeholder="Explain the reason for this request..."></textarea>
                                        @error('approvalRequestReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Urgency</label>
                                        <select wire:model="approvalRequestUrgency" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                                            <option value="normal">Normal</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                        @error('approvalRequestUrgency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveApproval" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Request
                </button>
                <button wire:click="closeApprovalModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Confirmation Modal --}}
@if($showConfirmationModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $confirmationTitle }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                {{ $confirmationMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="confirmAction" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ $confirmationButtonText }}
                </button>
                <button wire:click="closeConfirmationModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Replenishment Request Modal --}}
@if($showReplenishmentModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Request Till Replenishment
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="submitReplenishmentRequest">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="replenishmentTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                            <option value="">Select Till</option>
                                            @foreach($userTills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }}</option>
                                            @endforeach
                                        </select>
                                        @error('replenishmentTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount Required</label>
                                        <input type="number" wire:model="replenishmentAmount" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" placeholder="0.00">
                                        @error('replenishmentAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason for Replenishment</label>
                                        <select wire:model="replenishmentReason" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                            <option value="">Select Reason</option>
                                            <option value="low_balance">Low Balance</option>
                                            <option value="high_demand">High Customer Demand</option>
                                            <option value="end_of_day">End of Day Preparation</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('replenishmentReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                        <textarea wire:model="replenishmentNotes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" placeholder="Provide additional details..."></textarea>
                                        @error('replenishmentNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Urgency</label>
                                        <select wire:model="replenishmentUrgency" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                            <option value="normal">Normal</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                        @error('replenishmentUrgency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitReplenishmentRequest" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Request
                </button>
                <button wire:click="closeReplenishmentModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Deposit to Vault Modal --}}
@if($showDepositModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Deposit Excess Cash to Vault
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="submitDepositRequest">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="depositTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Till</option>
                                            @foreach($userTills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} (Balance: ${{ number_format($till->current_balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                        @error('depositTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount to Deposit</label>
                                        <input type="number" wire:model="depositAmount" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                                        @error('depositAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason for Deposit</label>
                                        <select wire:model="depositReason" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Reason</option>
                                            <option value="excess_cash">Excess Cash</option>
                                            <option value="end_of_day">End of Day</option>
                                            <option value="security">Security Reasons</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('depositReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                        <textarea wire:model="depositNotes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Provide additional details..."></textarea>
                                        @error('depositNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitDepositRequest" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Request
                </button>
                <button wire:click="closeDepositModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Direct Transfer Modal (Supervisor Only) --}}
@if($showDirectTransferModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Direct Vault Transfer
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="submitDirectTransfer">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transfer Direction</label>
                                        <select wire:model="directTransferDirection" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Direction</option>
                                            <option value="vault_to_till">Vault → Till</option>
                                            <option value="till_to_vault">Till → Vault</option>
                                        </select>
                                        @error('directTransferDirection') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="directTransferTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} (Balance: ${{ number_format($till->current_balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                        @error('directTransferTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" wire:model="directTransferAmount" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="0.00">
                                        @error('directTransferAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Purpose</label>
                                        <textarea wire:model="directTransferPurpose" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" placeholder="Explain the purpose of this transfer..."></textarea>
                                        @error('directTransferPurpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Second Authorizer</label>
                                        <select wire:model="secondAuthorizerId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Second Authorizer</option>
                                            @foreach($supervisors as $supervisor)
                                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('secondAuthorizerId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitDirectTransfer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Transfer
                </button>
                <button wire:click="closeDirectTransferModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Two-Person Verification Modal --}}
@if($showTwoPersonVerificationModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Two-Person Verification Required
                        </h3>
                        <div class="mt-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <p class="text-sm text-yellow-800">
                                        This transfer requires verification from a second authorized person for security purposes.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Second Authorizer</label>
                                    <select wire:model="verificationAuthorizerId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                                        <option value="">Select Authorizer</option>
                                        @foreach($availableAuthorizers as $authorizer)
                                            <option value="{{ $authorizer->id }}">{{ $authorizer->name }} ({{ $authorizer->role }})</option>
                                        @endforeach
                                    </select>
                                    @error('verificationAuthorizerId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Verification Code</label>
                                    <input type="text" wire:model="verificationCode" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" placeholder="Enter verification code">
                                    @error('verificationCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Verification Notes</label>
                                    <textarea wire:model="verificationNotes" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" placeholder="Any additional verification notes..."></textarea>
                                    @error('verificationNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="confirmTwoPersonVerification" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Verification
                </button>
                <button wire:click="closeTwoPersonVerificationModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Create/Edit Vault Modal --}}
@if($showCreateVaultModal || $showEditVaultModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ $showCreateVaultModal ? 'Create New Vault' : 'Edit Vault' }}
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="saveVault">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Basic Information --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Basic Information</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vault Name *</label>
                                        <input type="text" wire:model="vaultForm.name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Main Vault, Branch Vault A">
                                        @error('vaultForm.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vault Code *</label>
                                        <input type="text" wire:model="vaultForm.code" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="VLT001, MAIN-VLT">
                                        @error('vaultForm.code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Institution *</label>
                                        <select wire:model="vaultForm.institution_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Institution</option>
                                            @foreach($institutions ?? [] as $institution)
                                                <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vaultForm.institution_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                                                        <div>
                                        <label class="block text-sm font-medium text-gray-700">Branch *</label>
                                        <select wire:model="vaultForm.branch_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Branch</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('vaultForm.branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Parent Account *</label>
                                        <select wire:model="vaultForm.parent_account" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Parent Account</option>
                                            @foreach($parentAccounts ?? [] as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_name }} - {{ $account->account_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vaultForm.parent_account') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Financial Limits --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Financial Limits & Settings</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Maximum Limit *</label>
                                        <input type="number" wire:model="vaultForm.limit" step="0.01" min="0" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="1000000.00">
                                        <p class="text-xs text-gray-500 mt-1">Maximum cash the vault can hold before bank transfer</p>
                                        @error('vaultForm.limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Warning Threshold (%)</label>
                                        <input type="number" wire:model="vaultForm.warning_threshold" min="50" max="95" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="80">
                                        <p class="text-xs text-gray-500 mt-1">Warning when vault reaches this percentage of limit</p>
                                        @error('vaultForm.warning_threshold') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Account Number</label>
                                        <input type="text" wire:model="vaultForm.bank_account_number" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="1234567890">
                                        <p class="text-xs text-gray-500 mt-1">Account for automatic bank transfers</p>
                                        @error('vaultForm.bank_account_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                        <input type="text" wire:model="vaultForm.bank_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="National Bank">
                                        @error('vaultForm.bank_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Settings --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Automation Settings</h4>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <div class="space-y-3">
                                            <label class="flex items-center">
                                                <input type="checkbox" wire:model="vaultForm.auto_bank_transfer" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-700">Enable Automatic Bank Transfer</span>
                                            </label>
                                            <p class="text-xs text-gray-500 ml-6">Automatically transfer excess cash to bank when limit is exceeded</p>
                                            
                                            <label class="flex items-center">
                                                <input type="checkbox" wire:model="vaultForm.requires_dual_approval" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-700">Requires Dual Approval</span>
                                            </label>
                                            <p class="text-xs text-gray-500 ml-6">Require two supervisors to approve large transactions</p>
                                            
                                            <label class="flex items-center">
                                                <input type="checkbox" wire:model="vaultForm.send_alerts" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-700">Send Limit Alerts</span>
                                            </label>
                                            <p class="text-xs text-gray-500 ml-6">Send notifications when approaching limits</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <select wire:model="vaultForm.status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="maintenance">Maintenance</option>
                                        </select>
                                        @error('vaultForm.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Description --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea wire:model="vaultForm.description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter vault description, location, or special instructions..."></textarea>
                                        @error('vaultForm.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="saveVault" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ $showCreateVaultModal ? 'Create Vault' : 'Update Vault' }}
                </button>
                <button wire:click="closeVaultModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Vault Details Modal --}}
@if($showVaultDetailsModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Vault Details: {{ $selectedVault->name ?? 'Unknown' }}
                    </h3>
                    <button wire:click="closeVaultDetailsModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                @if($selectedVault)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Vault Information --}}
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Vault Information</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Name:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Code:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->code }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Branch:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->branch->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Institution:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->branch->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <span class="text-sm font-medium {{ $selectedVault->status === 'active' ? 'text-green-600' : 'text-gray-600' }}">
                                        {{ ucfirst($selectedVault->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Financial Information --}}
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Financial Information</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Current Balance:</span>
                                    <span class="text-lg font-bold {{ $selectedVault->current_balance > $selectedVault->limit ? 'text-red-600' : 'text-green-600' }}">
                                        ${{ number_format($selectedVault->current_balance, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Limit:</span>
                                    <span class="text-sm font-medium text-gray-900">${{ number_format($selectedVault->limit, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Utilization:</span>
                                    @php
                                        $utilization = $selectedVault->limit > 0 ? ($selectedVault->current_balance / $selectedVault->limit) * 100 : 0;
                                    @endphp
                                    <span class="text-sm font-medium {{ $utilization > 100 ? 'text-red-600' : ($utilization > 80 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ number_format($utilization, 1) }}%
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Warning Threshold:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->warning_threshold }}%</span>
                                </div>
                                @if($selectedVault->current_balance > $selectedVault->limit)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Excess Amount:</span>
                                        <span class="text-sm font-medium text-red-600">
                                            ${{ number_format($selectedVault->current_balance - $selectedVault->limit, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Bank Information --}}
                        <div class="bg-green-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Bank Information</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Bank Name:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->bank_name ?? 'Not Set' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Account Number:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $selectedVault->bank_account_number ?? 'Not Set' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Auto Transfer:</span>
                                    <span class="text-sm font-medium {{ $selectedVault->auto_bank_transfer ? 'text-green-600' : 'text-gray-600' }}">
                                        {{ $selectedVault->auto_bank_transfer ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Dual Approval:</span>
                                    <span class="text-sm font-medium {{ $selectedVault->requires_dual_approval ? 'text-blue-600' : 'text-gray-600' }}">
                                        {{ $selectedVault->requires_dual_approval ? 'Required' : 'Not Required' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Alerts:</span>
                                    <span class="text-sm font-medium {{ $selectedVault->send_alerts ? 'text-blue-600' : 'text-gray-600' }}">
                                        {{ $selectedVault->send_alerts ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Recent Activity --}}
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($selectedVault->recentActivity ?? [] as $activity)
                                    <div class="flex items-center justify-between p-2 bg-white rounded">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $activity->description }}</p>
                                            <p class="text-xs text-gray-500">{{ $activity->created_at->format('M d, H:i') }}</p>
                                        </div>
                                        <span class="text-sm font-medium {{ $activity->type === 'deposit' ? 'text-green-600' : 'text-blue-600' }}">
                                            {{ $activity->type === 'deposit' ? '+' : '-' }}${{ number_format($activity->amount, 2) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No recent activity</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="mt-6 flex items-center justify-end space-x-3">
                        {{-- Bank to Vault Transfer Button --}}
                        @if($isAdmin || $isSupervisor)
                            <button wire:click="showBankToVaultModal({{ $selectedVault->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Bank to Vault
                            </button>
                            
                            <button wire:click="showVaultToBankModal({{ $selectedVault->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" transform="rotate(180)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Vault to Bank
                            </button>
                        @endif
                        
                        @if($selectedVault->current_balance > $selectedVault->limit && $selectedVault->auto_bank_transfer)
                            <button wire:click="initiateBankTransfer({{ $selectedVault->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Transfer to Bank
                            </button>
                        @endif
                        
                        @if($isAdmin || $isSupervisor)
                            <button wire:click="editVault({{ $selectedVault->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Vault
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bank Transfer Confirmation Modal --}}
@if($showBankTransferModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirm Bank Transfer
                        </h3>
                        <div class="mt-4">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-red-800">Vault Over Limit</h4>
                                        <p class="text-sm text-red-700 mt-1">
                                            The vault has exceeded its maximum limit. Transfer excess cash to the bank.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($selectedVault)
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Vault</label>
                                            <p class="text-sm text-gray-900">{{ $selectedVault->name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Current Balance</label>
                                            <p class="text-sm font-medium text-red-600">${{ number_format($selectedVault->current_balance, 2) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Vault Limit</label>
                                            <p class="text-sm text-gray-900">${{ number_format($selectedVault->limit, 2) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Excess Amount</label>
                                            <p class="text-sm font-medium text-red-600">${{ number_format($selectedVault->current_balance - $selectedVault->limit, 2) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transfer Amount</label>
                                        <input type="number" wire:model="bankTransferAmount" step="0.01" min="0" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" 
                                               placeholder="0.00">
                                        @error('bankTransferAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destination Bank</label>
                                        <p class="text-sm text-gray-900">{{ $selectedVault->bank_name ?? 'Default Bank' }}</p>
                                        <p class="text-xs text-gray-500">Account: {{ $selectedVault->bank_account_number ?? 'Not Set' }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transfer Reference</label>
                                        <input type="text" wire:model="bankTransferReference" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" 
                                               placeholder="Auto-generated reference">
                                        @error('bankTransferReference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="confirmBankTransfer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Transfer
                </button>
                <button wire:click="closeBankTransferModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bank to Vault Transfer Modal --}}
@if($showBankToVaultModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Bank to Vault Transfer
                        </h3>
                        <div class="mt-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-800">Transfer Funds to Vault</h4>
                                        <p class="text-sm text-blue-700 mt-1">
                                            Transfer funds from a bank account to increase vault cash reserves. This will update both account balances and create proper accounting entries.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form wire:submit.prevent="processBankToVaultTransfer">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Vault Selection --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Transfer Details</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destination Vault *</label>
                                        <select wire:model="bankToVaultTransfer.vault_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Vault</option>
                                            @foreach($vaults ?? [] as $vault)
                                                <option value="{{ $vault->id }}">
                                                    {{ $vault->name }} - {{ $vault->code }} (Balance: ${{ number_format($vault->current_balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bankToVaultTransfer.vault_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Source Bank Account *</label>
                                        <select wire:model="bankToVaultTransfer.bank_account_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Bank Account</option>
                                            @foreach($bankAccounts ?? [] as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->bank_name }} - {{ $account->account_name }} ({{ $account->account_number }})
                                                    <br>Available: ${{ number_format($account->current_balance, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bankToVaultTransfer.bank_account_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transfer Amount *</label>
                                        <input type="number" wire:model="bankToVaultTransfer.amount" step="0.01" min="0.01" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="0.00">
                                        @error('bankToVaultTransfer.amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="bankToVaultTransfer.reference" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="Auto-generated if empty">
                                        @error('bankToVaultTransfer.reference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Transfer Description *</label>
                                        <textarea wire:model="bankToVaultTransfer.description" rows="3" 
                                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                                  placeholder="Purpose of transfer, source of funds, etc."></textarea>
                                        @error('bankToVaultTransfer.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Security Transport Details --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Security Transport Details</h4>
                                        <p class="text-xs text-gray-500 mb-3">Professional security transport information for cash transfer</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transport Company</label>
                                        <input type="text" wire:model="bankToVaultTransfer.transport_company_name" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="e.g., SecureGuard Transport Ltd">
                                        @error('bankToVaultTransfer.transport_company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vehicle Registration</label>
                                        <input type="text" wire:model="bankToVaultTransfer.vehicle_registration" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="e.g., T123ABC">
                                        @error('bankToVaultTransfer.vehicle_registration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Team Leader Name</label>
                                        <input type="text" wire:model="bankToVaultTransfer.team_leader_name" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="Security team leader">
                                        @error('bankToVaultTransfer.team_leader_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Team Leader Contact</label>
                                        <input type="text" wire:model="bankToVaultTransfer.team_leader_contact" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                               placeholder="+255 XXX XXX XXX">
                                        @error('bankToVaultTransfer.team_leader_contact') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Scheduled Pickup Time</label>
                                        <input type="datetime-local" wire:model="bankToVaultTransfer.scheduled_pickup_time" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @error('bankToVaultTransfer.scheduled_pickup_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Scheduled Delivery Time</label>
                                        <input type="datetime-local" wire:model="bankToVaultTransfer.scheduled_delivery_time" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        @error('bankToVaultTransfer.scheduled_delivery_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    {{-- Authorization --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Authorization</h4>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Authorizing Supervisor *</label>
                                        <select wire:model="bankToVaultTransfer.authorizer_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Authorizer</option>
                                            @foreach($availableAuthorizers ?? [] as $authorizer)
                                                <option value="{{ $authorizer->id }}">{{ $authorizer->name }} ({{ $authorizer->role }})</option>
                                            @endforeach
                                        </select>
                                        @error('bankToVaultTransfer.authorizer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="processBankToVaultTransfer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Process Transfer
                </button>
                <button wire:click="closeBankToVaultModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Vault to Bank Transfer Modal --}}
@if($showVaultToBankModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Vault to Bank Transfer
                        </h3>
                        <div class="mt-4">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-purple-800">Transfer Funds to Bank</h4>
                                        <p class="text-sm text-purple-700 mt-1">
                                            Transfer excess funds from vault to bank account. This will reduce vault cash reserves and update both balances with proper accounting entries.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form wire:submit.prevent="processVaultToBankTransfer">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Transfer Details --}}
                                    <div class="md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Transfer Details</h4>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Source Vault *</label>
                                        <select wire:model="vaultToBankTransfer.vault_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Vault</option>
                                            @foreach($vaults ?? [] as $vault)
                                                <option value="{{ $vault->id }}">
                                                    {{ $vault->name }} - {{ $vault->code }} (Balance: ${{ number_format($vault->current_balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vaultToBankTransfer.vault_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destination Bank Account *</label>
                                        <select wire:model="vaultToBankTransfer.bank_account_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select Bank Account</option>
                                            @foreach($bankAccounts ?? [] as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->bank_name }} - {{ $account->account_name }} ({{ $account->account_number }})
                                                    <br>Current: ${{ number_format($account->current_balance, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vaultToBankTransfer.bank_account_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Transfer Amount *</label>
                                        <input type="number" wire:model="vaultToBankTransfer.amount" step="0.01" min="0.01" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                               placeholder="0.00">
                                        @error('vaultToBankTransfer.amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                                        <input type="text" wire:model="vaultToBankTransfer.reference" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                               placeholder="Auto-generated if empty">
                                        @error('vaultToBankTransfer.reference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Transfer Description *</label>
                                        <textarea wire:model="vaultToBankTransfer.description" rows="3" 
                                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                                  placeholder="Reason for transfer, e.g., excess cash deposit, end-of-day transfer, etc."></textarea>
                                        @error('vaultToBankTransfer.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                                                         {{-- Security Transport Details --}}
                                     <div class="md:col-span-2">
                                         <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Security Transport Details</h4>
                                         <p class="text-xs text-gray-500 mb-3">Professional security transport information for cash transfer</p>
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Transport Company</label>
                                         <input type="text" wire:model="vaultToBankTransfer.transport_company_name" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                                placeholder="e.g., SecureGuard Transport Ltd">
                                         @error('vaultToBankTransfer.transport_company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Vehicle Registration</label>
                                         <input type="text" wire:model="vaultToBankTransfer.vehicle_registration" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                                placeholder="e.g., T123ABC">
                                         @error('vaultToBankTransfer.vehicle_registration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Team Leader Name</label>
                                         <input type="text" wire:model="vaultToBankTransfer.team_leader_name" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                                placeholder="Security team leader">
                                         @error('vaultToBankTransfer.team_leader_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Team Leader Contact</label>
                                         <input type="text" wire:model="vaultToBankTransfer.team_leader_contact" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" 
                                                placeholder="+255 XXX XXX XXX">
                                         @error('vaultToBankTransfer.team_leader_contact') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Scheduled Pickup Time</label>
                                         <input type="datetime-local" wire:model="vaultToBankTransfer.scheduled_pickup_time" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                         @error('vaultToBankTransfer.scheduled_pickup_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     <div>
                                         <label class="block text-sm font-medium text-gray-700">Scheduled Delivery Time</label>
                                         <input type="datetime-local" wire:model="vaultToBankTransfer.scheduled_delivery_time" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                         @error('vaultToBankTransfer.scheduled_delivery_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                     
                                     {{-- Authorization --}}
                                     <div class="md:col-span-2">
                                         <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">Authorization</h4>
                                     </div>
                                     
                                     <div class="md:col-span-2">
                                         <label class="block text-sm font-medium text-gray-700">Authorizing Supervisor *</label>
                                         <select wire:model="vaultToBankTransfer.authorizer_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                             <option value="">Select Authorizer</option>
                                             @foreach($availableAuthorizers ?? [] as $authorizer)
                                                 <option value="{{ $authorizer->id }}">{{ $authorizer->name }} ({{ $authorizer->role }})</option>
                                             @endforeach
                                         </select>
                                         @error('vaultToBankTransfer.authorizer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                     </div>
                                    
                                    {{-- Transfer Impact Warning --}}
                                    <div class="md:col-span-2">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-yellow-800">Transfer Impact</p>
                                                    <p class="text-xs text-yellow-700 mt-1">
                                                        This will reduce vault cash reserves. Ensure sufficient cash remains for daily operations.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="processVaultToBankTransfer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Process Transfer
                </button>
                <button wire:click="closeVaultToBankModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@include('livewire.accounting.till-and-cash-management.additional-modals')