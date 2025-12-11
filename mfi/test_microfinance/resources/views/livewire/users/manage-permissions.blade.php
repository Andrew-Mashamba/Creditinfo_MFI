<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-purple-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Permission Management</h1>
                        <p class="text-gray-600 mt-1">Manage system permissions and their assignments</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Permissions</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($permissions->count() ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">System</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($permissions->where('is_system', true)->count() ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button wire:click="createPermission" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all">
                        <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Permission
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
                    <input type="text" wire:model.debounce.300ms="search" id="search" class="pl-10 block w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" placeholder="Search permissions...">
                </div>
                <div>
                    <select wire:model="moduleFilter" id="moduleFilter" class="block w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}">{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model="actionFilter" id="actionFilter" class="block w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}">{{ ucfirst($action) }}</option>
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

        <!-- Permissions Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Permissions List</h3>
                    <div class="flex items-center space-x-2">
                        <select wire:model="perPage" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                Name
                                @if($sortField === 'name')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('module')">
                                Module
                                @if($sortField === 'module')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('action')">
                                Action
                                @if($sortField === 'action')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('is_system')">
                                Type
                                @if($sortField === 'is_system')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($permissions as $permission)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ ucfirst($permission->module) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst($permission->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $permission->description ?? 'No description available' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $permission->is_system ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $permission->is_system ? 'System' : 'Custom' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="editPermission({{ $permission->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button wire:click="deletePermission({{ $permission->id }})" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No permissions found</h3>
                                        <p class="mt-1 text-sm text-gray-500">Create your first permission to get started.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $permissions->links() }}
            </div>
        </div>

        <!-- Enhanced Assignment Section -->
        <div class="mt-8 bg-white rounded-xl border border-purple-200">
            <div class="px-6 py-5 border-b border-purple-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-purple-900">Advanced Assignment</h3>
                <div class="flex gap-2">
                    <button wire:click="$set('assignmentMode', 'individual')" class="px-3 py-1 rounded-lg text-sm font-semibold focus:outline-none {{ $assignmentMode === 'individual' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700' }}">Individual</button>
                    <button wire:click="$set('assignmentMode', 'bulk')" class="px-3 py-1 rounded-lg text-sm font-semibold focus:outline-none {{ $assignmentMode === 'bulk' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700' }}">Bulk</button>
                    <button wire:click="$set('assignmentMode', 'template')" class="px-3 py-1 rounded-lg text-sm font-semibold focus:outline-none {{ $assignmentMode === 'template' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700' }}">Template</button>
                </div>
            </div>
            <div class="p-6">
                @if($assignmentMode === 'bulk')
                    <div class="mb-4 flex gap-2">
                        <button wire:click="selectAllPermissions" class="btn btn-secondary">Select All Permissions</button>
                        <button wire:click="selectAllRoles" class="btn btn-secondary">Select All Roles</button>
                        <button wire:click="selectAllSubRoles" class="btn btn-secondary">Select All Sub-Roles</button>
                        <button wire:click="selectAllDepartments" class="btn btn-secondary">Select All Departments</button>
                        <button wire:click="clearBulkSelection" class="btn btn-danger">Clear All</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                            <select wire:model="bulkPermissions" multiple class="form-select">
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Type</label>
                            <select wire:model="bulkAssignmentType" class="form-select">
                                <option value="roles">Roles</option>
                                <option value="sub_roles">Sub-Roles</option>
                                <option value="users">Users</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        @if($bulkAssignmentType === 'roles')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                                <select wire:model="bulkRoles" multiple class="form-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($bulkAssignmentType === 'sub_roles')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sub-Roles</label>
                                <select wire:model="bulkSubRoles" multiple class="form-select">
                                    @foreach($subRoles as $subRole)
                                        <option value="{{ $subRole->id }}">{{ $subRole->name }} ({{ $subRole->role->name ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($bulkAssignmentType === 'users')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Users</label>
                                <select wire:model="selectedUsers" multiple class="form-select">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departments</label>
                            <select wire:model="bulkDepartments" multiple class="form-select">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="bulkInheritFromParent" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700">Inherit to child roles</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conditions (JSON)</label>
                        <input type="text" wire:model="bulkConditions" class="form-input" placeholder='{"max_amount":10000}'>
                    </div>
                    <button wire:click="assignBulkPermissions" class="btn btn-primary">Assign Bulk Permissions</button>
                @elseif($assignmentMode === 'template')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Template</label>
                        <select wire:model="selectedTemplate" class="form-select">
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $key => $template)
                                <option value="{{ $key }}">{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="applyTemplate" class="btn btn-primary mb-4">Apply Template</button>
                    @if($selectedTemplate)
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-800">Template Details</h4>
                            <pre class="bg-gray-100 rounded p-2 text-xs">@json($templates[$selectedTemplate])</pre>
                        </div>
                    @endif
                    <button wire:click="assignBulkPermissions" class="btn btn-primary">Assign Template Permissions</button>
                @elseif($assignmentMode === 'individual')
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800">Assign to Sub-Roles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permission</label>
                                <select wire:model="selectedPermission" class="form-select">
                                    <option value="">Select Permission</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sub-Roles</label>
                                <select wire:model="selectedSubRoles" multiple class="form-select">
                                    @foreach($subRoles as $subRole)
                                        <option value="{{ $subRole->id }}">{{ $subRole->name }} ({{ $subRole->role->name ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departments</label>
                                <select wire:model="selectedDepartments" multiple class="form-select">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button wire:click="assignPermissionToSubRoles" class="btn btn-primary mt-4">Assign to Sub-Roles</button>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800">Assign to Roles</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permission</label>
                                <select wire:model="selectedPermission" class="form-select">
                                    <option value="">Select Permission</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                                <select wire:model="selectedRoles" multiple class="form-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departments</label>
                                <select wire:model="selectedDepartments" multiple class="form-select">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button wire:click="assignPermissionToRoles" class="btn btn-primary mt-4">Assign to Roles</button>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold text-gray-800">Assign to Users</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permission</label>
                                <select wire:model="selectedPermissionUser" class="form-select">
                                    <option value="">Select Permission</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Users</label>
                                <select wire:model="selectedUsers" multiple class="form-select">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departments</label>
                                <select wire:model="selectedDepartments" multiple class="form-select">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button wire:click="assignPermissionToUsers" class="btn btn-primary mt-4">Assign to Users</button>
                    </div>
                @endif
                @if($showRoleHierarchy)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-800 mb-2">Role Hierarchy</h4>
                        <ul class="ml-4">
                            @foreach($roleHierarchy as $rootRole)
                                <li>
                                    <span class="font-bold">{{ $rootRole->name }}</span>
                                    @if($rootRole->children->count())
                                        <ul class="ml-4">
                                            @foreach($rootRole->children as $child)
                                                <li>{{ $child->name }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

    <!-- Create/Edit Permission Modal -->
    <div class="modal" style="display: {{ $showCreatePermission ? 'block' : 'none' }}">
        <div class="modal-content">
            <div class="modal-header bg-purple-50 border-b border-purple-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ $editingPermission ? 'Edit Permission' : 'Create New Permission' }}</h2>
            </div>

            <div class="modal-body">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                        <input id="name" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" wire:model.defer="name" required />
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea id="description" class="form-textarea w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" wire:model.defer="description" rows="3"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="module" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Module') }}</label>
                        <input id="module" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" wire:model.defer="module" required />
                        @error('module') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="action" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Action') }}</label>
                        <input id="action" type="text" class="form-input w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500" wire:model.defer="action" required />
                        @error('action') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.defer="is_system" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">{{ __('System Permission') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-gray-50 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" wire:click="$set('showCreatePermission', false)" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" wire:click="savePermission" wire:loading.attr="disabled">
                        {{ $editingPermission ? 'Update Permission' : 'Create Permission' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Permission Modal -->
    <div class="modal" style="display: {{ $showDeletePermission ? 'block' : 'none' }}">
        <div class="modal-content">
            <div class="modal-header bg-red-50 border-b border-red-200">
                <h2 class="text-lg font-semibold text-gray-900">Delete Permission</h2>
            </div>

            <div class="modal-body">
                <p class="text-gray-700">Are you sure you want to delete this permission? This action cannot be undone.</p>
                @if($deletingPermission && $deletingPermission->users_count > 0)
                    <p class="text-red-600 text-sm mt-2">
                        Warning: This permission is assigned to {{ $deletingPermission->users_count }} users.
                    </p>
                @endif
            </div>

            <div class="modal-footer bg-gray-50 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" wire:click="$set('showDeletePermission', false)" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </button>

                    <button type="button" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" wire:click="confirmDeletePermission" wire:loading.attr="disabled">
                        {{ __('Delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style nonce="{{ csp_nonce() }}">
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 50;
}

.modal-content {
    position: relative;
    background-color: #fff;
    margin: 1.5rem auto;
    padding: 0;
    border-radius: 0.5rem;
    max-width: 32rem;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    background-color: #f3f4f6;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    padding: 0.5rem;
    margin-top: 0.25rem;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: #6366f1;
    outline: none;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.checkbox-label {
    display: inline-flex;
    align-items: center;
}

.form-checkbox {
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
    color: #6366f1;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background-color: #6366f1;
    color: white;
    border: none;
}

.btn-primary:hover:not(:disabled) {
    background-color: #4f46e5;
}

.btn-secondary {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
    background-color: #e5e7eb;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
    border: none;
}

.btn-danger:hover:not(:disabled) {
    background-color: #dc2626;
}

.error {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.text-danger {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
</style>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener('livewire:load', function () {
        Livewire.on('notify', function(data) {
            // You can use your preferred notification library here
            alert(data.message);
        });
    });
</script>
@endpush
