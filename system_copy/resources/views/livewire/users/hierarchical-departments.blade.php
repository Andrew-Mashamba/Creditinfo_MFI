<div class="p-6">
    <!-- Header with Statistics -->
    <div class=" rounded-xl p-6 mb-6 ">
        <h2 class="text-3xl font-bold mb-4">Department Management</h2>
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['total'] }}</div>
                <div class="text-sm opacity-90">Total Departments</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['active'] }}</div>
                <div class="text-sm opacity-90">Active</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['with_roles'] }}</div>
                <div class="text-sm opacity-90">With Roles</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['with_users'] }}</div>
                <div class="text-sm opacity-90">With Users</div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-4">
            <input type="text" 
                   wire:model.debounce.300ms="search" 
                   placeholder="Search departments..."
                   class="px-4 py-2 border rounded-lg w-64">
            
            <select wire:model="branchFilter" class="px-4 py-2 border rounded-lg">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            
            @if($search || $branchFilter)
                <button wire:click="clearFilters" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Clear Filters
                </button>
            @endif
        </div>
        
        <button wire:click="create" class="bg-blue-900 text-white px-6 py-2 rounded-lg hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Department
        </button>
    </div>

    <!-- Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($departments as $dept)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                <!-- Department Header -->
                <div class=" p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold">{{ $dept->department_name }}</h3>
                            <p class="text-sm opacity-90">Code: {{ $dept->department_code ?? 'N/A' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $dept->status ? 'bg-green-500' : 'bg-red-500' }}">
                            {{ $dept->status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                <!-- Department Body -->
                <div class="p-4">
                    <div class="text-sm text-gray-600 mb-3">
                        <p><strong>Branch:</strong> {{ $dept->branch->name ?? 'N/A' }}</p>
                        <p><strong>Roles:</strong> {{ $dept->roles_count ?? 0 }} ({{ $dept->active_roles_count ?? 0 }} active)</p>
                        @if($dept->description)
                            <p class="mt-2 text-gray-500">{{ Str::limit($dept->description, 100) }}</p>
                        @endif
                    </div>
                    
                    <!-- Roles Preview -->
                    @if($dept->roles && $dept->roles->count() > 0)
                        <div class="border-t pt-3 mb-3">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Department Roles:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($dept->roles->take(3) as $role)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                                @if($dept->roles->count() > 3)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                        +{{ $dept->roles->count() - 3 }} more
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="flex justify-between items-center">
                        <button wire:click="viewDetails({{ $dept->id }})" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Details
                        </button>
                        <div class="flex space-x-2">
                            <button wire:click="toggleStatus({{ $dept->id }})" 
                                    class="p-1 text-gray-600 hover:text-gray-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                            <button wire:click="edit({{ $dept->id }})" 
                                    class="p-1 text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button wire:click="delete({{ $dept->id }})" 
                                    onclick="return confirm('Are you sure you want to delete this department?')"
                                    class="p-1 text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $departments->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h3 class="text-xl font-bold mb-4">
                    {{ $editingId ? 'Edit Department' : 'Create New Department' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Name *</label>
                        <input type="text" wire:model="department_name" 
                               class="w-full px-3 py-2 border rounded-lg @error('department_name') border-red-500 @enderror"
                               placeholder="e.g., Loans Department">
                        @error('department_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department Code *</label>
                        <input type="text" wire:model="department_code" 
                               class="w-full px-3 py-2 border rounded-lg @error('department_code') border-red-500 @enderror"
                               placeholder="e.g., LNS"
                               maxlength="10">
                        @error('department_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <select wire:model="branch_id" 
                                class="w-full px-3 py-2 border rounded-lg @error('branch_id') border-red-500 @enderror">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg @error('description') border-red-500 @enderror"
                                  placeholder="Brief description of the department"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="status" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeModal" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="save" 
                            class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700">
                        {{ $editingId ? 'Update' : 'Create' }} Department
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Details Modal -->
    @if($showDetailsModal && $viewingDepartment)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
                <h3 class="text-xl font-bold mb-4">Department Details: {{ $viewingDepartment->department_name }}</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Code</p>
                        <p class="font-semibold">{{ $viewingDepartment->department_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Branch</p>
                        <p class="font-semibold">{{ $viewingDepartment->branch->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-semibold">{{ $viewingDepartment->status ? 'Active' : 'Inactive' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Roles</p>
                        <p class="font-semibold">{{ $viewingDepartment->roles->count() }}</p>
                    </div>
                </div>
                
                @if($viewingDepartment->roles->count() > 0)
                    <div class="border-t pt-4">
                        <h4 class="font-semibold mb-3">Department Roles & Sub-Roles</h4>
                        <div class="space-y-3">
                            @foreach($viewingDepartment->roles as $role)
                                <div class="border rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <h5 class="font-medium">{{ $role->name }}</h5>
                                        <span class="text-sm text-gray-500">{{ $role->users->count() }} users</span>
                                    </div>
                                    @if($role->subRoles->count() > 0)
                                        <div class="mt-2 ml-4">
                                            @foreach($role->subRoles as $subRole)
                                                <div class="text-sm text-gray-600 py-1">
                                                    └─ {{ $subRole->name }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex justify-end mt-6">
                    <button wire:click="closeModal" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>