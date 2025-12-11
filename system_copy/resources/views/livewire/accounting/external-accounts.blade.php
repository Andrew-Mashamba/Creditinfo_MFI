<div class="bg-white rounded-lg shadow-lg p-6 space-y-6">
    {{-- Header Section --}}
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">External Bank Accounts</h2>
        <button wire:click="showAddBankAccountModal" type="button" 
            class="inline-flex items-center px-4 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Add Bank Account
        </button>
    </div>

    {{-- Search and Filters Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50 p-4 rounded-lg">
        <div class="w-full sm:w-96">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.debounce.300ms="search" 
                    type="text" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200"
                    placeholder="Search bank accounts...">
                <div wire:loading wire:target="search" class="absolute right-0 top-0 bottom-0 flex items-center pr-3">
                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="w-full sm:w-auto">
            <select wire:model="perPage" 
                class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
     

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach(['bank_name', 'account_name', 'account_number', 'account_type', 'branch_name', 'swift_code', 'currency', 'current_balance', 'internal_mirror_account_number', 'status'] as $field)
                            <th wire:click="sortBy('{{ $field }}')" 
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                                {{ str_replace('_', ' ', ucfirst($field)) }}
                                @if($sortField === $field)
                                    <svg class="inline-block h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                    </svg>
                                @endif
                            </th>
                        @endforeach
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bankAccounts as $account)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $account->bank_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $account->account_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $account->account_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $account->account_type === 'main_operations' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $account->account_type === 'main_operations' ? 'Main Operations' : 'Branch Account' }}
                                </span>
                                @if($account->account_type === 'branch' && $account->branch_id)
                                    <div class="text-xs text-gray-500 mt-1">
                                        @php
                                            $branchName = $branches->where('id', $account->branch_id)->first()->name ?? 'Unknown Branch';
                                        @endphp
                                        {{ $branchName }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $account->account_type === 'branch' ? $account->branch_name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $account->swift_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $account->currency }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="flex items-center space-x-2">
                                    <span>{{ number_format($account->current_balance, 2) }}</span>
                                    <button 
                                        wire:click="refreshAccountBalance({{ $account->id }})" 
                                        wire:loading.attr="disabled"
                                        wire:target="refreshAccountBalance({{ $account->id }})"
                                        class="inline-flex items-center p-1 text-gray-400 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded transition-colors duration-200"
                                        title="Refresh balance from external API">
                                        <svg wire:loading.remove wire:target="refreshAccountBalance({{ $account->id }})" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <svg wire:loading wire:target="refreshAccountBalance({{ $account->id }})" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $account->internal_mirror_account_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $account->status === 'active' 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @livewire('accounting.cash-accounts-table-actions', 
                                    ['accountId' => $account->id, 'status' => $account->status], 
                                    key('actions-'.$account->id))
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="h-12 w-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-lg font-medium">
                                        @if($search)
                                            No bank accounts found matching "{{ $search }}"
                                        @else
                                            No bank accounts found
                                        @endif
                                    </p>
                                    <p class="text-sm mt-1">Try adjusting your search or add a new bank account</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $bankAccounts->links() }}
    </div>

    {{-- Add Bank Account Modal --}}
    @if($showAddModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add New Bank Account
                            </h3>
                            <form wire:submit.prevent="createBankAccount" class="mt-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                                        <input type="text" wire:model.defer="newBankAccount.bank_name" id="bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                                        <input type="text" wire:model.defer="newBankAccount.account_name" id="account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    {{-- Account Type Selection --}}
                                    <div class="col-span-2">
                                        <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                                        <select wire:model="newBankAccount.account_type" id="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="main_operations">Main Operations</option>
                                            <option value="branch">Branch Account</option>
                                        </select>
                                        @error('newBankAccount.account_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    {{-- Branch Selection (only show when account type is branch) --}}
                                    @if($newBankAccount['account_type'] === 'branch')
                                    <div class="col-span-2">
                                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Select Branch <span class="text-red-500">*</span></label>
                                        <select wire:model.defer="newBankAccount.branch_id" id="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">Choose a branch...</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('newBankAccount.branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                    <div>
                                        <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                                        <input type="text" wire:model.defer="newBankAccount.account_number" id="account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @if($newBankAccount['account_type'] === 'branch')
                                    <div>
                                        <label for="branch_name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                                        <input type="text" wire:model.defer="newBankAccount.branch_name" id="branch_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.branch_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @else
                                    <div>
                                        {{-- Empty div for grid layout when branch name is hidden --}}
                                    </div>
                                    @endif
                                    <div>
                                        <label for="swift_code" class="block text-sm font-medium text-gray-700">Swift Code</label>
                                        <input type="text" wire:model.defer="newBankAccount.swift_code" id="swift_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.swift_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                        <input type="text" wire:model.defer="newBankAccount.currency" id="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="opening_balance" class="block text-sm font-medium text-gray-700">Opening Balance</label>
                                        <input type="number" step="0.01" wire:model.defer="newBankAccount.opening_balance" id="opening_balance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('newBankAccount.opening_balance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="internal_mirror_account_number" class="block text-sm font-medium text-gray-700">Internal Mirror Account</label>
                                        <select wire:model.defer="newBankAccount.internal_mirror_account_number" id="internal_mirror_account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">Select Mirror Account...</option>
                                            @foreach($mirrorAccounts as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_name }} - {{ $account->account_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('newBankAccount.internal_mirror_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea wire:model.defer="newBankAccount.description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                    @error('newBankAccount.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Create Account
                                    </button>
                                    <button type="button" wire:click="closeAddModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- View Bank Account Modal --}}
    @if($showViewModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Bank Account Details
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->bank_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Account Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->account_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Account Type</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $selectedBankAccount->account_type === 'main_operations' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $selectedBankAccount->account_type === 'main_operations' ? 'Main Operations' : 'Branch Account' }}
                                            </span>
                                        </p>
                                    </div>
                                    @if($selectedBankAccount->account_type === 'branch' && $selectedBankAccount->branch_id)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Assigned Branch</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @php
                                                $branchName = $branches->where('id', $selectedBankAccount->branch_id)->first()->name ?? 'Unknown Branch';
                                            @endphp
                                            {{ $branchName }}
                                        </p>
                                    </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Account Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->account_number }}</p>
                                    </div>
                                    @if($selectedBankAccount->account_type === 'branch')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Branch Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->branch_name }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Swift Code</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->swift_code }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->currency }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Current Balance</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ number_format($selectedBankAccount->current_balance, 2) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Internal Mirror Account</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            @php
                                                $mirrorAccount = $mirrorAccounts->where('account_number', $selectedBankAccount->internal_mirror_account_number)->first();
                                                $mirrorAccountName = $mirrorAccount ? $mirrorAccount->account_name : 'Unknown Account';
                                            @endphp
                                            {{ $mirrorAccountName }} - {{ $selectedBankAccount->internal_mirror_account_number }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->status }}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $selectedBankAccount->description }}</p>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="button" wire:click="closeViewModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Bank Account Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-100 bg-opacity-10 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-centerx sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Bank Account
                            </h3>
                            <form wire:submit.prevent="updateBankAccount" class="mt-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                                        <input type="text" wire:model.defer="editing.bank_name" id="edit_bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="edit_account_name" class="block text-sm font-medium text-gray-700">Account Name</label>
                                        <input type="text" wire:model.defer="editing.account_name" id="edit_account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    {{-- Account Type Selection --}}
                                    <div class="col-span-2">
                                        <label for="edit_account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                                        <select wire:model="editing.account_type" id="edit_account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="main_operations">Main Operations</option>
                                            <option value="branch">Branch Account</option>
                                        </select>
                                        @error('editing.account_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    {{-- Branch Selection (only show when account type is branch) --}}
                                    @if($editing['account_type'] === 'branch')
                                    <div class="col-span-2">
                                        <label for="edit_branch_id" class="block text-sm font-medium text-gray-700">Select Branch <span class="text-red-500">*</span></label>
                                        <select wire:model.defer="editing.branch_id" id="edit_branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">Choose a branch...</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('editing.branch_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                    <div>
                                        <label for="edit_account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                                        <input type="text" wire:model.defer="editing.account_number" id="edit_account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @if($editing['account_type'] === 'branch')
                                    <div>
                                        <label for="edit_branch_name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                                        <input type="text" wire:model.defer="editing.branch_name" id="edit_branch_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.branch_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    @else
                                    <div>
                                        {{-- Empty div for grid layout when branch name is hidden --}}
                                    </div>
                                    @endif
                                    <div>
                                        <label for="edit_swift_code" class="block text-sm font-medium text-gray-700">Swift Code</label>
                                        <input type="text" wire:model.defer="editing.swift_code" id="edit_swift_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.swift_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="edit_currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                        <input type="text" wire:model.defer="editing.currency" id="edit_currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        @error('editing.currency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="edit_internal_mirror_account_number" class="block text-sm font-medium text-gray-700">Internal Mirror Account</label>
                                        <select wire:model.defer="editing.internal_mirror_account_number" id="edit_internal_mirror_account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            <option value="">Select Mirror Account...</option>
                                            @foreach($mirrorAccounts as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_name }} - {{ $account->account_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('editing.internal_mirror_account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea wire:model.defer="editing.description" id="edit_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                    @error('editing.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-900 text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Update Account
                                    </button>
                                    <button type="button" wire:click="closeEditModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Bank Account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this bank account? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Disable Confirmation Modal --}}
    @if($showDisableModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Disable Bank Account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to disable this bank account? Please provide a reason for disabling.
                                </p>
                                <div class="mt-4">
                                    <label for="disable_reason" class="block text-sm font-medium text-gray-700">Reason for Disabling</label>
                                    <textarea wire:model.defer="disableReason" id="disable_reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                    @error('disableReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="disable" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Disable
                    </button>
                    <button type="button" wire:click="$set('showDisableModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Enable Confirmation Modal --}}
    @if($showEnableModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Enable Bank Account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to enable this bank account?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="enable" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Enable
                    </button>
                    <button type="button" wire:click="$set('showEnableModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

