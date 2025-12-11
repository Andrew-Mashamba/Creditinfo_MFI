<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-orange-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Loan Committee</h1>
                        <p class="text-gray-600 mt-1">Manage loan approval committees and members</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Committees</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($committees->count() ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Members</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalMembers ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button wire:click="$set('showCreateCommittee', true)" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all">
                        <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Committee
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Search & Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Search committees..." class="pl-10 block w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                </div>
                <div>
                    <select wire:model="departmentFilter" class="block w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button wire:click="clearFilters" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Committees Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($committees as $committee)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $committee->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $committee->description ?? 'No description' }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $committee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $committee->is_active ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </div>
                        
                        <!-- Committee Info -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-gray-600">Department: <span class="font-medium text-gray-900">{{ $committee->department->name ?? 'N/A' }}</span></span>
                            </div>
                            <div class="flex items-center text-sm">
                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="text-gray-600">Members: <span class="font-medium text-gray-900">{{ $committee->members_count ?? 0 }}</span></span>
                            </div>
                            <div class="flex items-center text-sm">
                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-600">Approval Limit: <span class="font-medium text-gray-900">{{ number_format($committee->approval_limit ?? 0) }}</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Members List -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Committee Members</h4>
                            <div class="space-y-2">
                                @forelse($committee->members as $member)
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                @if($member->profile_photo_url)
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $member->profile_photo_url }}" alt="{{ $member->name }}">
                                                @else
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-600 font-medium text-sm">
                                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $member->pivot->is_primary_approver ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $member->pivot->is_primary_approver ? 'Primary' : 'Member' }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No members assigned</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex space-x-2">
                                <button wire:click="editCommittee({{ $committee->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                <button wire:click="deleteCommittee({{ $committee->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                            <div class="text-xs text-gray-400">
                                ID: {{ $committee->id }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No loan committees found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new loan committee.</p>
                        <div class="mt-6">
                            <button wire:click="$set('showCreateCommittee', true)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Committee
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(isset($committees) && $committees->hasPages())
            <div class="mt-8">            
                {{ $committees->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Committee Modal -->
    <div class="modal" style="display: {{ $showCreateCommittee ? 'block' : 'none' }}">
        <div class="modal-content">
            <div class="modal-header bg-orange-50 border-b border-orange-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ $editingCommittee ? 'Edit Loan Committee' : 'Create New Loan Committee' }}</h2>
            </div>

            <div class="modal-body">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Committee Name') }}</label>
                        <input id="name" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="name" required />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea id="description" class="form-textarea w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="description" rows="3"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Department') }}</label>
                        <select id="department" class="form-select w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="department_id" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="approvalLimit" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Approval Limit') }}</label>
                        <input type="number" id="approvalLimit" wire:model.defer="approval_limit" min="0" step="0.01" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <p class="text-sm text-gray-500 mt-1">Maximum loan amount this committee can approve</p>
                        @error('approval_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="members" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Committee Members') }}</label>
                        <select id="members" multiple class="form-select w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="selectedMembers">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('selectedMembers') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="primaryApprover" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Primary Approver') }}</label>
                        <select id="primaryApprover" class="form-select w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="primaryApprover">
                            <option value="">Select Primary Approver</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">The primary approver for this committee</p>
                        @error('primaryApprover') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select id="is_active" class="form-select w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500" wire:model.defer="is_active" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('is_active') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" wire:click="$set('showCreateCommittee', false)">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" wire:click="saveCommittee">
                        {{ $editingCommittee ? 'Update Committee' : 'Create Committee' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
