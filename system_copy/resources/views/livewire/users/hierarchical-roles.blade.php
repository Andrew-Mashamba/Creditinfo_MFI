<div class="p-6">
    <!-- Header with Statistics -->
    <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-xl p-6 mb-6 text-white">
        <h2 class="text-3xl font-bold mb-4">Roles & Sub-Roles Management</h2>
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['total_roles'] }}</div>
                <div class="text-sm opacity-90">Total Roles</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['total_subroles'] }}</div>
                <div class="text-sm opacity-90">Total Sub-Roles</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['active_roles'] }}</div>
                <div class="text-sm opacity-90">Active Roles</div>
            </div>
            <div class="bg-white/20 rounded-lg p-3">
                <div class="text-3xl font-bold">{{ $stats['roles_with_users'] }}</div>
                <div class="text-sm opacity-90">With Users</div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-4">
            <input type="text" 
                   wire:model.debounce.300ms="search" 
                   placeholder="Search roles..."
                   class="px-4 py-2 border rounded-lg w-64">
            
            <select wire:model="departmentFilter" class="px-4 py-2 border rounded-lg">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                @endforeach
            </select>
            
            <button wire:click="toggleViewMode" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                View: {{ ucfirst($viewMode) }}
            </button>
            
            @if($search || $departmentFilter)
                <button wire:click="clearFilters" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Clear Filters
                </button>
            @endif
        </div>
        
        <button wire:click="createRole" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Role
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

    <!-- Roles Display -->
    @if($viewMode === 'tree')
        <!-- Tree View -->
        <div class="space-y-6">
            @foreach($departments as $dept)
                @php $deptRoles = $roles->get($dept->id, collect()) @endphp
                @if($deptRoles->count() > 0)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gray-100 px-4 py-3 font-semibold text-gray-700">
                            {{ $dept->department_name }}
                        </div>
                        <div class="p-4">
                            @foreach($deptRoles as $role)
                                <div class="mb-4 border-l-4 border-green-500 pl-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-lg font-semibold">{{ $role->name }}</h4>
                                        <div class="flex space-x-2">
                                            <button wire:click="createSubRole({{ $role->id }})" 
                                                    class="text-green-600 hover:text-green-800 text-sm">
                                                + Add Sub-Role
                                            </button>
                                            <button wire:click="editRole({{ $role->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                Edit
                                            </button>
                                        </div>
                                    </div>
                                    @if($role->subRoles->count() > 0)
                                        <div class="ml-4 space-y-2">
                                            @foreach($role->subRoles as $subRole)
                                                <div class="flex justify-between items-center bg-gray-50 rounded p-2">
                                                    <span class="text-sm">└─ {{ $subRole->name }}</span>
                                                    <div class="flex space-x-2">
                                                        <button wire:click="editSubRole({{ $subRole->id }})" 
                                                                class="text-blue-600 hover:text-blue-800 text-xs">
                                                            Edit
                                                        </button>
                                                        <button wire:click="deleteSubRole({{ $subRole->id }})" 
                                                                onclick="return confirm('Delete this sub-role?')"
                                                                class="text-red-600 hover:text-red-800 text-xs">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <!-- Grid View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($roles as $role)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <!-- Role Header -->
                    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold">{{ $role->name }}</h3>
                                <p class="text-sm opacity-90">{{ $role->department->department_name ?? 'N/A' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $role->status === 'ACTIVE' ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ $role->status }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Role Body -->
                    <div class="p-4">
                        <div class="text-sm text-gray-600 mb-3">
                            <p><strong>Users:</strong> {{ $role->users_count ?? 0 }}</p>
                            <p><strong>Sub-Roles:</strong> {{ $role->sub_roles_count ?? 0 }}</p>
                            @if($role->description)
                                <p class="mt-2 text-gray-500">{{ Str::limit($role->description, 100) }}</p>
                            @endif
                        </div>
                        
                        <!-- Sub-Roles Preview -->
                        @if($role->subRoles->count() > 0)
                            <div class="border-t pt-3 mb-3">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Sub-Roles:</p>
                                <div class="space-y-1">
                                    @foreach($role->subRoles->take(3) as $subRole)
                                        <div class="text-xs text-gray-600 flex justify-between">
                                            <span>• {{ $subRole->name }}</span>
                                            <button wire:click="editSubRole({{ $subRole->id }})" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                Edit
                                            </button>
                                        </div>
                                    @endforeach
                                    @if($role->subRoles->count() > 3)
                                        <div class="text-xs text-gray-500">
                                            +{{ $role->subRoles->count() - 3 }} more
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="flex justify-between items-center">
                            <button wire:click="viewRoleDetails({{ $role->id }})" 
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                View Details
                            </button>
                            <div class="flex space-x-2">
                                <button wire:click="createSubRole({{ $role->id }})" 
                                        class="p-1 text-green-600 hover:text-green-800" title="Add Sub-Role">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                                <button wire:click="toggleRoleStatus({{ $role->id }})" 
                                        class="p-1 text-gray-600 hover:text-gray-800" title="Toggle Status">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                                <button wire:click="editRole({{ $role->id }})" 
                                        class="p-1 text-blue-600 hover:text-blue-800" title="Edit Role">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="deleteRole({{ $role->id }})" 
                                        onclick="return confirm('Are you sure you want to delete this role?')"
                                        class="p-1 text-red-600 hover:text-red-800" title="Delete Role">
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
        
        <!-- Pagination for Grid View -->
        @if($viewMode === 'grid')
            <div class="mt-6">
                {{ $roles->links() }}
            </div>
        @endif
    @endif

    <!-- Create/Edit Role Modal -->
    @if($showCreateRoleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h3 class="text-xl font-bold mb-4">
                    {{ $editingRoleId ? 'Edit Role' : 'Create New Role' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                        <input type="text" wire:model="role_name" 
                               class="w-full px-3 py-2 border rounded-lg @error('role_name') border-red-500 @enderror"
                               placeholder="e.g., Credit Manager">
                        @error('role_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department *</label>
                        <select wire:model="role_department_id" 
                                class="w-full px-3 py-2 border rounded-lg @error('role_department_id') border-red-500 @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                        @error('role_department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="role_description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg @error('role_description') border-red-500 @enderror"
                                  placeholder="Brief description of the role"></textarea>
                        @error('role_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeRoleModal" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="saveRole" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        {{ $editingRoleId ? 'Update' : 'Create' }} Role
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Create/Edit Sub-Role Modal -->
    @if($showCreateSubRoleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h3 class="text-xl font-bold mb-4">
                    {{ $editingSubRoleId ? 'Edit Sub-Role' : 'Create New Sub-Role' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sub-Role Name *</label>
                        <input type="text" wire:model="subrole_name" 
                               class="w-full px-3 py-2 border rounded-lg @error('subrole_name') border-red-500 @enderror"
                               placeholder="e.g., Junior Credit Officer">
                        @error('subrole_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Parent Role *</label>
                        <select wire:model="subrole_parent_id" 
                                class="w-full px-3 py-2 border rounded-lg @error('subrole_parent_id') border-red-500 @enderror">
                            <option value="">Select Parent Role</option>
                            @foreach(\App\Models\Role::with('department')->get() as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }} ({{ $role->department->department_name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('subrole_parent_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="subrole_description" rows="3"
                                  class="w-full px-3 py-2 border rounded-lg @error('subrole_description') border-red-500 @enderror"
                                  placeholder="Brief description of the sub-role"></textarea>
                        @error('subrole_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="inherit_permissions" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Inherit permissions from parent role</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeSubRoleModal" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="saveSubRole" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        {{ $editingSubRoleId ? 'Update' : 'Create' }} Sub-Role
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Role Details Modal -->
    @if($showDetailsModal && $viewingRole)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
                <h3 class="text-xl font-bold mb-4">Role Details: {{ $viewingRole->name }}</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600">Department</p>
                        <p class="font-semibold">{{ $viewingRole->department->department_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-semibold">{{ $viewingRole->status }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Users</p>
                        <p class="font-semibold">{{ $viewingRole->users->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Sub-Roles</p>
                        <p class="font-semibold">{{ $viewingRole->subRoles->count() }}</p>
                    </div>
                </div>
                
                @if($viewingRole->subRoles->count() > 0)
                    <div class="border-t pt-4">
                        <h4 class="font-semibold mb-3">Sub-Roles Hierarchy</h4>
                        <div class="space-y-2">
                            @foreach($viewingRole->subRoles as $subRole)
                                <div class="border rounded-lg p-3">
                                    <div class="flex justify-between items-center">
                                        <h5 class="font-medium">{{ $subRole->name }}</h5>
                                        <span class="text-sm text-gray-500">{{ $subRole->users->count() ?? 0 }} users</span>
                                    </div>
                                    @if($subRole->description)
                                        <p class="text-sm text-gray-600 mt-1">{{ $subRole->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex justify-end mt-6">
                    <button wire:click="closeDetailsModal" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>