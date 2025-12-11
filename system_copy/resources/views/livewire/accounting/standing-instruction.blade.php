<div class="p-4">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
        <div class="bg-blue-900 text-white px-4 py-2 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-lg font-semibold">Standing Instructions Management</h2>
                </div>
                <button wire:click="openModal" class="bg-white text-blue-900 px-3 py-1 text-sm rounded-md hover:bg-blue-50 transition-colors flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>New Standing Instruction</span>
                </button>
            </div>
        </div>
        
        {{-- Quick Stats --}}
        <div class="grid grid-cols-4 gap-4 p-4">
            <div class="bg-blue-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Active Instructions</p>
                        <p class="text-xl font-bold text-blue-900">{{ $activeCount ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-600 font-medium">Executed Today</p>
                        <p class="text-xl font-bold text-green-900">{{ $executedToday ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-yellow-600 font-medium">Pending</p>
                        <p class="text-xl font-bold text-yellow-900">{{ $pendingCount ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-red-600 font-medium">Failed</p>
                        <p class="text-xl font-bold text-red-900">{{ $failedCount ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Standing Instructions Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="px-4 py-2 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Standing Instructions List</h3>
                <div class="flex items-center space-x-2">
                    <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search instructions..." 
                           class="px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <select wire:model="statusFilter" class="px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left">Reference</th>
                        <th class="px-3 py-2 text-left">Source Account</th>
                        <th class="px-3 py-2 text-left">Destination</th>
                        <th class="px-3 py-2 text-right">Amount</th>
                        <th class="px-3 py-2 text-center">Frequency</th>
                        <th class="px-3 py-2 text-center">Next Run</th>
                        <th class="px-3 py-2 text-center">Status</th>
                        <th class="px-3 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($instructions ?? [] as $instruction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium text-blue-600">{{ $instruction->reference_number }}</td>
                        <td class="px-3 py-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $instruction->source_account_name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $instruction->source_account_number }}</p>
                            </div>
                        </td>
                        <td class="px-3 py-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $instruction->destination_account_name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $instruction->destination_account_number }}</p>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-right font-medium">{{ number_format($instruction->amount, 2) }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($instruction->frequency == 'daily') bg-purple-100 text-purple-700
                                @elseif($instruction->frequency == 'weekly') bg-indigo-100 text-indigo-700
                                @elseif($instruction->frequency == 'monthly') bg-blue-100 text-blue-700
                                @elseif($instruction->frequency == 'quarterly') bg-cyan-100 text-cyan-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                {{ ucfirst($instruction->frequency) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-xs">{{ $instruction->next_run_date ? \Carbon\Carbon::parse($instruction->next_run_date)->format('d M Y') : 'N/A' }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($instruction->status == 'active') bg-green-100 text-green-700
                                @elseif($instruction->status == 'paused') bg-yellow-100 text-yellow-700
                                @elseif($instruction->status == 'completed') bg-gray-100 text-gray-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($instruction->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-center space-x-1">
                                <button wire:click="viewInstruction({{ $instruction->id }})" 
                                        class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                @if($instruction->status == 'active')
                                <button wire:click="pauseInstruction({{ $instruction->id }})" 
                                        class="p-1 text-yellow-600 hover:bg-yellow-50 rounded" title="Pause">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                @elseif($instruction->status == 'paused')
                                <button wire:click="resumeInstruction({{ $instruction->id }})" 
                                        class="p-1 text-green-600 hover:bg-green-50 rounded" title="Resume">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                @endif
                                <button wire:click="confirmDelete({{ $instruction->id }})" 
                                        class="p-1 text-red-600 hover:bg-red-50 rounded" title="Cancel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-sm font-medium">No standing instructions found</p>
                                <p class="text-xs text-gray-400 mt-1">Create your first standing instruction to get started</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(isset($instructions) && $instructions->hasPages())
        <div class="px-4 py-2 border-t border-gray-200">
            {{ $instructions->links() }}
        </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            
            <div class="relative bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="bg-blue-900 text-white px-4 py-3 rounded-t-lg">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $editMode ? 'Edit' : 'Create New' }} Standing Instruction</h3>
                        <button wire:click="closeModal" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <form wire:submit.prevent="saveInstruction">
                    <div class="p-4">
                        {{-- Progress Steps --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full {{ $currentStep >= 1 ? 'bg-blue-900 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-xs font-bold">1</div>
                                    <span class="ml-2 text-xs {{ $currentStep >= 1 ? 'text-blue-900 font-semibold' : 'text-gray-500' }}">Source</span>
                                </div>
                                <div class="flex-1 h-0.5 mx-2 {{ $currentStep >= 2 ? 'bg-blue-900' : 'bg-gray-300' }}"></div>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full {{ $currentStep >= 2 ? 'bg-blue-900 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-xs font-bold">2</div>
                                    <span class="ml-2 text-xs {{ $currentStep >= 2 ? 'text-blue-900 font-semibold' : 'text-gray-500' }}">Destination</span>
                                </div>
                                <div class="flex-1 h-0.5 mx-2 {{ $currentStep >= 3 ? 'bg-blue-900' : 'bg-gray-300' }}"></div>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full {{ $currentStep >= 3 ? 'bg-blue-900 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-xs font-bold">3</div>
                                    <span class="ml-2 text-xs {{ $currentStep >= 3 ? 'text-blue-900 font-semibold' : 'text-gray-500' }}">Schedule</span>
                                </div>
                                <div class="flex-1 h-0.5 mx-2 {{ $currentStep >= 4 ? 'bg-blue-900' : 'bg-gray-300' }}"></div>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full {{ $currentStep >= 4 ? 'bg-blue-900 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-xs font-bold">4</div>
                                    <span class="ml-2 text-xs {{ $currentStep >= 4 ? 'text-blue-900 font-semibold' : 'text-gray-500' }}">Review</span>
                                </div>
                            </div>
                        </div>

                        {{-- Step 1: Source Account --}}
                        @if($currentStep == 1)
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 border-b pb-2">Source Account Details</h4>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Account Type</label>
                                    <select wire:model="source_account_type" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Type</option>
                                        <option value="member">Member Account</option>
                                        <option value="internal">Internal Account</option>
                                        <option value="external">External Bank Account</option>
                                    </select>
                                    @error('source_account_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                @if($source_account_type == 'member')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Select Member</label>
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.debounce.300ms="member_search"
                                               wire:focus="$set('showMemberDropdown', true)"
                                               placeholder="Search member..."
                                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        
                                        @if($showMemberDropdown && count($members ?? []) > 0)
                                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-auto">
                                            @foreach($members as $member)
                                            <div wire:click="selectMember({{ $member->id }})" 
                                                 class="px-3 py-2 text-sm hover:bg-blue-50 cursor-pointer">
                                                <p class="font-medium">{{ $member->first_name }} {{ $member->last_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $member->client_number }}</p>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @error('member_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                                
                                @if($member_id || $source_account_type == 'internal')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Source Account</label>
                                    <select wire:model="source_account_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Account</option>
                                        @foreach($sourceAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->account_name }} ({{ $account->account_number }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('source_account_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                                
                                @if($source_account_type == 'external')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Bank</label>
                                    <select wire:model="source_bank_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Bank</option>
                                        @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('source_bank_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Account Number</label>
                                    <input type="text" wire:model="source_account_number" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter account number">
                                    @error('source_account_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                            
                            @if($source_account_id)
                            <div class="bg-blue-50 rounded-lg p-3">
                                <h5 class="text-xs font-semibold text-blue-900 mb-2">Selected Account Details</h5>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <p class="text-gray-600">Account Name:</p>
                                        <p class="font-medium">{{ $sourceAccount->account_name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Account Number:</p>
                                        <p class="font-medium">{{ $sourceAccount->account_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Available Balance:</p>
                                        <p class="font-medium text-green-600">{{ number_format($sourceAccount->balance ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Step 2: Destination Account --}}
                        @if($currentStep == 2)
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 border-b pb-2">Destination Account Details</h4>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Destination Type</label>
                                    <select wire:model="destination_type" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Type</option>
                                        <option value="member">Member Account</option>
                                        <option value="internal">Internal Account</option>
                                        <option value="loan">Loan Repayment</option>
                                        <option value="savings">Savings Account</option>
                                        <option value="shares">Share Purchase</option>
                                    </select>
                                    @error('destination_type') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                @if($destination_type)
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Destination Account</label>
                                    <select wire:model="destination_account_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Account</option>
                                        @foreach($destinationAccounts ?? [] as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->account_name }} ({{ $account->account_number }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('destination_account_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                            
                            @if($destination_account_id)
                            <div class="bg-green-50 rounded-lg p-3">
                                <h5 class="text-xs font-semibold text-green-900 mb-2">Destination Account Details</h5>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <p class="text-gray-600">Account Name:</p>
                                        <p class="font-medium">{{ $destinationAccount->account_name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Account Number:</p>
                                        <p class="font-medium">{{ $destinationAccount->account_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Account Type:</p>
                                        <p class="font-medium">{{ ucfirst($destination_type) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Step 3: Schedule & Amount --}}
                        @if($currentStep == 3)
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 border-b pb-2">Schedule & Amount Details</h4>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount</label>
                                    <div class="relative">
                                        <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TZS</span>
                                        <input type="number" wire:model="amount" step="0.01"
                                               class="w-full pl-12 pr-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0.00">
                                    </div>
                                    @error('amount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Frequency</label>
                                    <select wire:model="frequency" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Frequency</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="bi-weekly">Bi-Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="annually">Annually</option>
                                    </select>
                                    @error('frequency') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                @if($frequency == 'weekly' || $frequency == 'bi-weekly')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Day of Week</label>
                                    <select wire:model="day_of_week" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Day</option>
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                        <option value="0">Sunday</option>
                                    </select>
                                    @error('day_of_week') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                                
                                @if($frequency == 'monthly' || $frequency == 'quarterly' || $frequency == 'annually')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Day of Month</label>
                                    <select wire:model="day_of_month" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Day</option>
                                        @for($i = 1; $i <= 28; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                        <option value="last">Last Day</option>
                                    </select>
                                    @error('day_of_month') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                @endif
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                    <input type="date" wire:model="start_date" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    @error('start_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">End Date (Optional)</label>
                                    <input type="date" wire:model="end_date" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="text-xs text-gray-500 mt-1">Leave empty for indefinite</p>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Maximum Executions (Optional)</label>
                                    <input type="number" wire:model="max_executions" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Leave empty for unlimited">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Narration/Description</label>
                                <textarea wire:model="description" rows="2"
                                          class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Enter description for this standing instruction"></textarea>
                                @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            
                            {{-- Schedule Preview --}}
                            @if($frequency && $startDate)
                            <div class="bg-yellow-50 rounded-lg p-3">
                                <h5 class="text-xs font-semibold text-yellow-900 mb-2">Execution Schedule Preview</h5>
                                <div class="text-xs space-y-1">
                                    <p>First execution: <span class="font-medium">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</span></p>
                                    <p>Frequency: <span class="font-medium">{{ ucfirst($frequency) }}</span></p>
                                    @if($endDate)
                                    <p>Last execution: <span class="font-medium">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span></p>
                                    @endif
                                    @if($maxExecutions)
                                    <p>Total executions: <span class="font-medium">{{ $maxExecutions }}</span></p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Step 4: Review & Confirm --}}
                        @if($currentStep == 4)
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700 border-b pb-2">Review Standing Instruction</h4>
                            
                            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Source Account</p>
                                        <p class="font-medium">{{ $sourceAccount->account_name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $sourceAccount->account_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Destination Account</p>
                                        <p class="font-medium">{{ $destinationAccount->account_name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $destinationAccount->account_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Amount</p>
                                        <p class="font-medium text-lg text-blue-900">TZS {{ number_format($amount, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Frequency</p>
                                        <p class="font-medium">{{ ucfirst($frequency) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">Start Date</p>
                                        <p class="font-medium">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600 mb-1">End Date</p>
                                        <p class="font-medium">{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Until Further Notice' }}</p>
                                    </div>
                                </div>
                                
                                @if($narration)
                                <div>
                                    <p class="text-xs text-gray-600 mb-1">Description</p>
                                    <p class="text-sm">{{ $narration }}</p>
                                </div>
                                @endif
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-xs">
                                        <p class="font-semibold text-blue-900 mb-1">Important Information</p>
                                        <ul class="space-y-1 text-blue-700">
                                            <li>• This instruction will execute automatically on the scheduled dates</li>
                                            <li>• Ensure sufficient balance in the source account</li>
                                            <li>• You can pause or cancel this instruction at any time</li>
                                            <li>• Email notifications will be sent for each execution</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between rounded-b-lg">
                        <div>
                            @if($currentStep > 1)
                            <button type="button" wire:click="previousStep" 
                                    class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Previous
                            </button>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button type="button" wire:click="closeModal" 
                                    class="px-3 py-1.5 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </button>
                            
                            @if($currentStep < 4)
                            <button type="button" wire:click="nextStep" 
                                    class="px-3 py-1.5 text-sm text-white bg-blue-900 rounded-md hover:bg-blue-800">
                                Next
                            </button>
                            @else
                            <button type="submit" 
                                    class="px-4 py-1.5 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Create Standing Instruction</span>
                            </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Success/Error Messages --}}
    @if(session()->has('success'))
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session()->has('error'))
    <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif
</div>