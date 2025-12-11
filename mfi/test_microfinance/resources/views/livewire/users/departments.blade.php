<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Department Management</h1>
                        <p class="text-gray-600 mt-1">Manage departments and their organizational structure</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Departments</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($departments->count() ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($departments->where('status', true)->count() ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button wire:click="$set('showCreateDepartment', true)" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                        <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Department
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Search & Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Search departments..." class="pl-10 block w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                </div>
                <div>
                    <select wire:model="statusFilter" class="block w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        <option value="">All Status</option>
                        <option value="true">ACTIVE</option>
                        <option value="false">INACTIVE</option>
                    </select>
                </div>
                <div>
                    <select wire:model="branchFilter" class="block w-full rounded-lg border-gray-300 focus:border-red-400 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
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

        <!-- Departments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">  
            @forelse($departments as $department)            
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow">
                    <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-red-50 to-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $department->department_name }}</h3>
                                <p class="text-sm text-gray-600 font-mono">{{ $department->department_code }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $department->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $department->status ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </div>
                        
                        <!-- Department Info -->
                        <div class="space-y-3">
                            <div class="flex items-center text-sm">
                                <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-gray-600">Branch: <span class="font-medium text-gray-900">{{ $department->branch->name ?? 'N/A' }}</span></span>
                            </div>
                            @if($department->description)
                                <div class="flex items-start text-sm">
                                    <svg class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    <span class="text-gray-600">{{ $department->description }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Statistics -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $department->users_count ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Users</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $department->roles_count ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Roles</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex space-x-2">
                                <button wire:click="editDepartment({{ $department->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                <button wire:click="deleteDepartment({{ $department->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                            <div class="text-xs text-gray-400">
                                ID: {{ $department->id }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new department.</p>
                        <div class="mt-6">
                            <button wire:click="$set('showCreateDepartment', true)" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Department
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(isset($paginatedDepartments) && $paginatedDepartments->hasPages())
            <div class="mt-8">            
                {{ $paginatedDepartments->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Department Modal -->
    <div class="modal" style="display: {{ $showCreateDepartment ? 'block' : 'none' }}">
        <div class="modal-content">
            <div class="modal-header bg-red-50 border-b border-red-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ $editingDepartment !== null ? 'Edit Department' : 'Create New Department' }}</h2>
            </div>

            <div class="modal-body">
                <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto pr-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Department Name') }}</label>
                        <input id="name" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="name" required />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Department Code') }}</label>
                        <input id="code" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="code" required />
                        @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="branch" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Branch') }}</label>
                        <select id="branch" class="form-select w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="branch" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea id="description" class="form-textarea w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="description" rows="3"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }}</label>
                        <select id="status" class="form-select w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Dashboard Type Selector --}}
                    <div>
                        <label for="dashboard_type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dashboard Type') }}</label>
                        <select id="dashboard_type" class="form-select w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" wire:model.defer="selectedDashboardType" required>
                            <option value="">Select Dashboard Type</option>
                            <option value="1">CEO Dashboard</option>
                            <option value="2">Accountant Dashboard</option>
                            <option value="3">Loan Officer Dashboard</option>
                            <option value="4">Branch Manager Dashboard</option>
                            <option value="5">Teller Dashboard</option>
                            <option value="6">Member Dashboard</option>
                            <option value="7">Procurement Dashboard</option>
                            <option value="8">HR Dashboard</option>
                        </select>
                        @error('selectedDashboardType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" wire:click="$set('showCreateDepartment', false)">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" wire:click="saveDepartment">
                        {{ $editingDepartment !== null ? 'Update Department' : 'Create Department' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

