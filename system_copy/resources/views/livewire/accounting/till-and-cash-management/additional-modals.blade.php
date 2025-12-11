{{-- Assign Till Modal --}}
@if($showAssignModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Assign Till to User
                        </h3>
                        <div class="mt-4">
                            <form wire:submit.prevent="assignTill">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Till</label>
                                        <select wire:model="selectedTillId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" disabled>
                                            <option value="">Select Till</option>
                                            @foreach($tills as $till)
                                                <option value="{{ $till->id }}">{{ $till->name ?? 'Till #' . $till->id }} ({{ $till->teller->name ?? 'Unassigned' }})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedTillId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Assign to User</label>
                                        <select wire:model="assignUserId" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                                            <option value="">Select User</option>
                                            @foreach(DB::table('users')->get() ?? [] as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                        @error('assignUserId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="assignTill" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Assign Till
                </button>
                <button wire:click="closeAssignModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Till Details Modal --}}
@if($showTillDetailsModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Till Details
                        </h3>
                        <div class="mt-4">
                            @if($selectedTillId && $tills->find($selectedTillId))
                                @php $till = $tills->find($selectedTillId); @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Basic Information --}}
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-gray-700 border-b pb-2">Basic Information</h4>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Till Name</label>
                                            <p class="text-sm text-gray-900">{{ $till->name ?? 'Till #' . $till->id }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Till Number</label>
                                            <p class="text-sm text-gray-900">{{ $till->till_number ?? 'TIL' . str_pad($till->id, 3, '0', STR_PAD_LEFT) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Status</label>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $till->status === 'open' ? 'bg-green-100 text-green-800' : 
                                                   ($till->status === 'closed' ? 'bg-gray-100 text-gray-800' : 
                                                   ($till->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ ucfirst($till->status) }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Branch</label>
                                            <p class="text-sm text-gray-900">{{ $till->branch->name ?? 'Unknown Branch' }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Financial Information --}}
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-medium text-gray-700 border-b pb-2">Financial Information</h4>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Current Balance</label>
                                            <p class="text-sm font-semibold text-gray-900">${{ number_format($till->current_balance ?? 0, 2) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Opening Balance</label>
                                            <p class="text-sm text-gray-900">${{ number_format($till->opening_balance ?? 0, 2) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Minimum Limit</label>
                                            <p class="text-sm text-gray-900">${{ number_format($till->minimum_limit ?? 0, 2) }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Maximum Limit</label>
                                            <p class="text-sm text-gray-900">${{ number_format($till->maximum_limit ?? 0, 2) }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Assignment Information --}}
                                    <div class="space-y-4 md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 border-b pb-2">Assignment Information</h4>
                                        @if($till->teller)
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0 h-12 w-12">
                                                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $till->teller->user->name ?? 'Unknown' }}</p>
                                                    <p class="text-sm text-gray-500">{{ $till->teller->user->email ?? '' }}</p>
                                                    <p class="text-xs text-gray-400">Assigned at {{ $till->teller->assigned_at ? $till->teller->assigned_at->format('M d, Y H:i') : 'Unknown' }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-500">No user assigned to this till</p>
                                                @if($isSupervisor || $isAdmin)
                                                    <button wire:click="showAssignTillModal({{ $till->id }})" class="mt-2 inline-flex items-center px-3 py-1 bg-purple-600 text-white text-xs rounded-md hover:bg-purple-700 transition-colors">
                                                        Assign User
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Activity Information --}}
                                    <div class="space-y-4 md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 border-b pb-2">Activity Information</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500">Created</label>
                                                <p class="text-sm text-gray-900">{{ $till->created_at ? $till->created_at->format('M d, Y H:i') : 'Unknown' }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500">Last Updated</label>
                                                <p class="text-sm text-gray-900">{{ $till->updated_at ? $till->updated_at->format('M d, Y H:i') : 'Unknown' }}</p>
                                            </div>
                                            @if($till->opened_at)
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500">Opened At</label>
                                                <p class="text-sm text-gray-900">{{ $till->opened_at->format('M d, Y H:i') }}</p>
                                            </div>
                                            @endif
                                            @if($till->closed_at)
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500">Closed At</label>
                                                <p class="text-sm text-gray-900">{{ $till->closed_at->format('M d, Y H:i') }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-500">Till not found</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="closeTillDetailsModal" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif 