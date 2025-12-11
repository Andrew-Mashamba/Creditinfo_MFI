<div class="min-h-screen py-8 bg-white shadow-md rounded-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Toast Notifications --}}
        <div
            x-data="{ show: @entangle('showToast'), type: @entangle('toastType'), message: @entangle('toastMessage') }"
            x-show="show"
            x-transition
            class="fixed top-6 right-6 z-[9999] max-w-xs w-full pointer-events-auto"
            x-init="if(show) setTimeout(() => show = false, 3500)"
            aria-live="polite"
            role="alert"
            style="display: none;"
        >
            <div :class="type === 'success' ? 'bg-green-100 border-green-400 text-green-800' : 'bg-red-100 border-red-400 text-red-800'" class="border-l-4 rounded-lg shadow-lg px-4 py-3 flex items-center gap-2">
                <svg x-show="type === 'success'" class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <svg x-show="type === 'error'" class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span class="font-semibold" x-text="message"></span>
            </div>
        </div>

        {{-- Delete User Confirmation Modal --}}
        <div class="fixed z-50 inset-0 overflow-y-auto" style="display: {{ $showDeleteUser ? 'block' : 'none' }}" aria-modal="true" role="dialog">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900 opacity-70"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative animate-fade-in">
                    <!-- Close Button -->
                    <button wire:click="$set('showDeleteUser', false)" type="button" aria-label="Close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <!-- Loading Overlay -->
                    <div wire:loading.flex wire:target="confirmDeleteUser" class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-20">
                        <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </div>
                    <div class="bg-white px-6 pt-7 pb-6 sm:p-8 sm:pb-6">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-2xl leading-7 font-bold text-gray-900 mb-2 flex items-center gap-2">
                                    <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Delete User
                                </h3>
                                <div class="text-gray-500 text-sm mb-4">Are you sure you want to delete this user? This action cannot be undone.</div>
                                <hr class="mb-4">
                                <div class="mt-4 text-sm text-gray-700">
                                    <p><strong>Name:</strong> {{ $editingUser->name ?? '' }}</p>
                                    <p><strong>Email:</strong> {{ $editingUser->email ?? '' }}</p>
                                    <p><strong>Employee ID:</strong> {{ $editingUser->employeeId ?? '' }}</p>
                                </div>
                                <div class="mt-6 flex flex-col sm:flex-row gap-2 justify-end">
                                    <button wire:click="confirmDeleteUser" type="button" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                                        Delete User
                                    </button>
                                    <button wire:click="$set('showDeleteUser', false)" type="button" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                                <div class="mt-4 text-xs text-gray-400">
                                    <span>Tip: Press <kbd>Esc</kbd> to close. Use <kbd>Tab</kbd> to navigate.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">User Management</h1>
                <p class="mt-1 text-base text-gray-600">Manage system users and their access levels</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Bulk Actions -->
                @if($selectedUsers && count($selectedUsers) > 0)
                    <div class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-lg">
                        <span class="text-sm text-blue-700">{{ count($selectedUsers) }} selected</span>
                        <button wire:click="bulkActivate" class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                            Activate
                        </button>
                        <button wire:click="bulkDeactivate" class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                            Deactivate
                        </button>
                        <button wire:click="bulkDelete" class="text-xs bg-red-800 text-white px-3 py-1 rounded hover:bg-red-900">
                            Delete
                        </button>
                        <button wire:click="clearSelection" class="text-xs bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700">
                            Clear
                        </button>
                    </div>
                @endif
                <button wire:click="$set('showCreateUser', true)" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-blue-900 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New User
                </button>
            </div>
        </div>

        <!-- Enhanced Filters Section -->
        <div class="bg-white/90 rounded-2xl shadow-md p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Search users..." class="pl-10 block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <select wire:model="department_code" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_code }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model="roleFilter" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Roles</option>
                        @foreach($allRoles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model="statusFilter" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalUsers }}</div>
                    <div class="text-sm text-gray-600">Total Users</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $activeUsers }}</div>
                    <div class="text-sm text-gray-600">Active</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $inactiveUsers }}</div>
                    <div class="text-sm text-gray-600">Inactive</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $totalDepartments }}</div>
                    <div class="text-sm text-gray-600">Departments</div>
                </div>
            </div>
        </div>

        <!-- Enhanced Users Table -->
        <div class="bg-white/95 rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
         
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-100 sticky top-0 shadow z-10">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" wire:model="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                <div class="flex items-center">
                                    User
                                    @if($sortField === 'name')
                                        <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Department & Branch</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Roles & Permissions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status & Activity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        
                        @forelse($users ?? [] as $user)
                            <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }} hover:bg-indigo-50 transition">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if($user->profile_photo_url)
                                                <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-200" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                            @else
                                                <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg border-2 border-indigo-200">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            @if($user->employeeId)
                                                <div class="text-xs text-gray-400">ID: {{ $user->employeeId }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        @if($user->department)
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                                <span class="text-sm font-medium text-gray-900">{{ $user->department->department_name }}</span>
                                            </div>
                                        @endif
                                        @if($user->employee && $user->employee->branch)
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                <span class="text-sm text-gray-600">{{ $user->employee->branch->branch_name ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                        @if($user->employee && $user->employee->job_title)
                                            <div class="text-xs text-gray-500 italic">{{ $user->employee->job_title }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <!-- Roles -->
                                        <div>
                                            <div class="text-xs font-medium text-gray-700 mb-1">Roles:</div>
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($user->roles ?? [] as $role)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $role->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-500">No roles assigned</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        <!-- Permissions Summary -->
                                        <div>
                                            <div class="text-xs font-medium text-gray-700 mb-1">Key Permissions:</div>
                                            <div class="flex flex-wrap gap-1">
                                                @php
                                                    $permissions = $user->roles->flatMap->permissions->unique('name')->take(3);
                                                @endphp
                                                @forelse($permissions as $permission)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">
                                                        {{ Str::limit($permission->name, 15) }}
                                                    </span>
                                                @empty
                                                    <span class="text-xs text-gray-500">No permissions</span>
                                                @endforelse
                                                @if($user->roles->flatMap->permissions->count() > 3)
                                                    <span class="text-xs text-gray-500">+{{ $user->roles->flatMap->permissions->count() - 3 }} more</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-2">
                                        <!-- Status -->
                                        @php
                                            $status = $user->status;
                                            $statusLabel = $status === 'ACTIVE' ? 'Active' : ($status === 'INACTIVE' ? 'Inactive' : 'Pending');
                                            $statusClasses = $status === 'ACTIVE' ? 'bg-green-100 text-green-800' : ($status === 'INACTIVE' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                        
                                        <!-- Last Activity -->
                                        <div class="text-xs text-gray-500">
                                            @if(isset($user->last_active_at) && $user->last_active_at)
                                                <div class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $user->last_active_at->diffForHumans() }}
                                                </div>
                                            @else
                                                <span class="text-gray-400">Never active</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Login Status -->
                                        @if(isset($user->last_login_at) && $user->last_login_at)
                                            <div class="text-xs text-gray-500">
                                                Last login: {{ $user->last_login_at->format('M j, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <!-- View Details -->
                                        <button wire:click="viewUser({{ $user->id }})" class="text-blue-600 hover:text-blue-900" title="View Details">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Edit User -->
                                        <button wire:click="editUser({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900" title="Edit User">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Manage Permissions -->
                                        <button wire:click="managePermissions({{ $user->id }})" class="text-purple-600 hover:text-purple-900" title="Manage Permissions">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Toggle Status -->
                                        <button wire:click="toggleUserStatus({{ $user->id }})" class="{{ $user->status === 'ACTIVE' ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }}" title="{{ $user->status === 'ACTIVE' ? 'Deactivate' : 'Activate' }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($user->status === 'ACTIVE')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                @endif
                                            </svg>
                                        </button>
                                        
                                        <!-- Delete User -->
                                        <button wire:click="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900" title="Delete User">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="mt-2">No users found</p>
                                        <button wire:click="$set('showCreateUser', true)" class="mt-2 text-indigo-600 hover:text-indigo-900">
                                            Add your first user
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enhanced Pagination -->
        <div class="mt-6">
            @if($users && $users->hasPages())
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div class="fixed z-50 inset-0 overflow-y-auto" style="display: {{ $showCreateUser ? 'block' : 'none' }}" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 w-1/2 mx-auto">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full relative animate-fade-in">
                <!-- Close Button -->
                <button wire:click="$set('showCreateUser', false)" type="button" aria-label="Close" class="absolute top-6 right-6 text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full transition z-10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Content -->
                <div class="px-8 pt-8 pb-8">
                    <!-- Sticky Stepper Header -->
                    <div class="sticky top-0 bg-white z-10 pb-6 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3 ">
                                @php
                                    $totalSteps = $userType === 'governance' ? 5 : 8;
                                    $stepTitles = $userType === 'governance' 
                                        ? ['User Type', 'Personal Info', 'Governance Role', 'System Access', 'Review']
                                        : ['User Type', 'Personal Info', 'Emergency Contact', 'Employment', 'Payroll', 'Files', 'System Access', 'Review'];
                                @endphp
                                @for($i = 0; $i < $totalSteps; $i++)
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 flex items-center justify-center rounded-full font-bold text-lg transition-all duration-200 {{ $currentStep == $i ? 'bg-blue-900 text-white shadow-lg' : ($currentStep > $i ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400') }}">
                                            @if($currentStep > $i)
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @else
                                                {{ $i + 1 }}
                                            @endif
                                        </div>
                                        @if($i < $totalSteps - 1)
                                            <div class="w-8 h-0.5 mx-2 {{ $currentStep > $i ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Step {{ $currentStep+1 }} of {{ $totalSteps }}</div>
                                <div class="text-lg font-semibold text-gray-800">{{ $stepTitles[$currentStep] ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Validation Error Summary --}}
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="text-red-800 font-semibold">Please fix the following errors:</h4>
                            </div>
                            <div class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <div class="flex items-center text-sm text-red-700">
                                        <svg class="w-3 h-3 text-red-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4a1 1 0 112 0 1 1 0 01-2 0zm1-8a1 1 0 00-1 1v4a1 1 0 002 0V7a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $error }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="saveUser" class="space-y-8">
                        <!-- Navigation Buttons -->
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                            <div class="flex gap-3">
                                <button type="button" wire:click="$set('showCreateUser', false)" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    Cancel
                                </button>
                                <button type="button" wire:click="previousStep" @if($currentStep === 0) disabled @endif class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Previous
                                </button>
                            </div>
                            <div class="flex gap-3">
                                @if($currentStep < ($userType === 'governance' ? 4 : 7))
                                    <button type="button" wire:click="nextStep" class="px-6 py-3 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        Next
                                        <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button type="submit" class="px-8 py-3 text-sm font-medium text-white bg-green-600 border border-transparent rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $editingUser ? 'Update User' : 'Create User' }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 1: User Type Selection -->
                        @if($currentStep === 0)
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Select User Type</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Choose the type of user you want to register. This will determine the information we collect and the roles available.</p>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Governance Card -->
                                <div class="group relative bg-white border-2 border-gray-200 rounded-2xl p-8 cursor-pointer hover:border-blue-900 hover:shadow-xl transition-all duration-300 {{ $userType === 'governance' ? 'border-blue-900 bg-blue-900 shadow-xl' : '' }}" wire:click="$set('userType', 'governance')">
                                    <div class="absolute top-4 right-4">
                                        <div class="w-6 h-6 rounded-full border-2 {{ $userType === 'governance' ? 'border-blue-900 bg-blue-900' : 'border-gray-300' }} flex items-center justify-center transition-all duration-200">
                                            @if($userType === 'governance')
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="w-20 h-20 bg-blue-900 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <h4 class="text-2xl font-bold text-gray-900 mb-3">Board Member & Oversight</h4>
                                        <p class="text-gray-600 mb-6 leading-relaxed">For board directors, supervisory committee members, and governance roles with strategic oversight responsibilities.</p>
                                        <div class="space-y-3 text-left">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-blue-900 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Elected positions
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-blue-900 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Governance oversight
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-blue-900 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Policy approval
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-blue-900 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Strategic direction
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Operational Card -->
                                <div class="group relative bg-white border-2 border-gray-200 rounded-2xl p-8 cursor-pointer hover:border-green-500 hover:shadow-xl transition-all duration-300 {{ $userType === 'operational' ? 'border-green-500 bg-blue-900 shadow-xl' : '' }}" wire:click="$set('userType', 'operational')">
                                    <div class="absolute top-4 right-4">
                                        <div class="w-6 h-6 rounded-full border-2 {{ $userType === 'operational' ? 'border-green-500 bg-green-500' : 'border-gray-300' }} flex items-center justify-center transition-all duration-200">
                                            @if($userType === 'operational')
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="w-20 h-20 bg-blue-900 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6" />
                                            </svg>
                                        </div>
                                        <h4 class="text-2xl font-bold text-gray-900 mb-3">Staff Member</h4>
                                        <p class="text-gray-600 mb-6 leading-relaxed">For employees, managers, and operational staff with day-to-day responsibilities and service delivery roles.</p>
                                        <div class="space-y-3 text-left">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Employment positions
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Operational roles
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Department staff
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Service delivery
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @error('userType') 
                                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-red-700 font-medium">{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror
                        </div>
                        @endif

                        <!-- Step 2: Personal Info -->
                        @if($currentStep === 1)
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">
                                    {{ $userType === 'governance' ? 'Board Member Information' : 'Staff Member Information' }}
                                </h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                                    {{ $userType === 'governance' ? 'Please provide the basic information for this governance role.' : 'Please provide complete information for this employment role.' }}
                                </p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="firstName" placeholder="Enter first name" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200 placeholder-gray-400">
                                    @error('firstName') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Middle Name</label>
                                    <input type="text" wire:model.defer="middleName" placeholder="Enter middle name (optional)" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200 placeholder-gray-400">
                                    @error('middleName') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="lastName" placeholder="Enter last name" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200 placeholder-gray-400">
                                    @error('lastName') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model.defer="dob" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200">
                                    @error('dob') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Gender <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="gender" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('gender') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Marital Status <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="maritalStatus" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-900 focus:border-blue-900 transition-all duration-200">
                                        <option value="">Select Status</option>
                                        <option value="single">Single</option>
                                        <option value="married">Married</option>
                                        <option value="divorced">Divorced</option>
                                        <option value="widowed">Widowed</option>
                                    </select>
                                    @error('maritalStatus') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nationality <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="nationality" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('nationality') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" wire:model.defer="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                                    <input type="tel" wire:model.defer="phone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NIDA Number <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="nida" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('nida') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">TIN Number <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="tin" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('tin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Physical Address <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="physicalAddress" placeholder="Street Address" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('physicalAddress') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City/Town <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="city" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Region <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="region" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('region') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 3: Governance Role (Board Only) -->
                        @if($currentStep === 2 && $userType === 'governance')
                        <div class="space-y-6">
                            <div class="text-center mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Governance Role Assignment</h3>
                                <p class="text-sm text-gray-600">Select the governance role and department</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department <span class="text-red-500">*</span></label>
                                    <select wire:model="department_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Department</option>
                                        <optgroup label="Governance & Oversight">
                                            @foreach($departments ?? [] as $department)
                                                @if($department->level === 1 && $department->department_code === 'GOV')
                                                    <option value="{{ $department->id }}" class="font-semibold">{{ $department->department_name }}</option>
                                                    @foreach($departments as $childDept)
                                                        @if($childDept->parent_department_id === $department->id)
                                                            <option value="{{ $childDept->id }}" class="ml-4">├─ {{ $childDept->department_name }}</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="role_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Role</option>
                                        @php
                                            $rolesByDepartment = $availableRoles->groupBy('department_id');
                                            $systemRoles = $availableRoles->where('is_system_role', true);
                                        @endphp
                                        {{-- System Roles --}}
                                        @if($systemRoles->count() > 0)
                                            <optgroup label="System Roles">
                                                @foreach($systemRoles as $role)
                                                    <option value="{{ $role->id }}" class="font-semibold">
                                                        {{ $role->name }}
                                                        @if($role->description)
                                                            - {{ Str::limit($role->description, 50) }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        {{-- Department Roles --}}
                                        @foreach($rolesByDepartment as $deptId => $roles)
                                            @php
                                                $dept = $departments->where('id', $deptId)->first();
                                                $departmentRoles = $roles->where('is_system_role', false);
                                            @endphp
                                            @if($dept && $departmentRoles->count() > 0)
                                                <optgroup label="{{ $dept->department_name }}">
                                                    @foreach($departmentRoles as $role)
                                                        <option value="{{ $role->id }}">
                                                            {{ $role->name }}
                                                            @if($role->description)
                                                                - {{ Str::limit($role->description, 40) }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('role_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Position Title <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="job_title" placeholder="e.g., Board Chair, Committee Member" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                    @error('job_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Term Start Date <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model.defer="start_date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                    @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 3: Emergency Contact (Staff Only) -->
                        @if($currentStep === 2 && $userType === 'operational')
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Emergency Contact Information</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Please provide emergency contact details for this staff member. This information will be used in case of emergencies.</p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">Emergency Contact Name <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="emergencyContactName" placeholder="Enter full name" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 placeholder-gray-400">
                                        @error('emergencyContactName') 
                                            <div class="flex items-center text-red-500 text-sm mt-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">Relationship <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="emergencyContactRelationship" placeholder="e.g., Spouse, Parent, Sibling" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 placeholder-gray-400">
                                        @error('emergencyContactRelationship') 
                                            <div class="flex items-center text-red-500 text-sm mt-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">Emergency Contact Phone <span class="text-red-500">*</span></label>
                                        <input type="tel" wire:model.defer="emergencyContactPhone" placeholder="Enter phone number" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 placeholder-gray-400">
                                        @error('emergencyContactPhone') 
                                            <div class="flex items-center text-red-500 text-sm mt-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-sm font-semibold text-gray-700">Emergency Contact Email</label>
                                        <input type="email" wire:model.defer="emergencyContactEmail" placeholder="Enter email address (optional)" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 placeholder-gray-400">
                                        @error('emergencyContactEmail') 
                                            <div class="flex items-center text-red-500 text-sm mt-1">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 4: Employment (Staff Only) -->
                        @if($currentStep === 3 && $userType === 'operational')
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Employment Information</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Please provide employment details including department, role, and position information for this staff member.</p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Department <span class="text-red-500">*</span></label>
                                    <select wire:model="department_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                        <option value="">Select Department</option>
                                        <optgroup label="Core Management & Operations">
                                            @foreach($departments ?? [] as $department)
                                                @if($department->level === 1 && $department->department_code === 'CMO')
                                                    <option value="{{ $department->id }}" class="font-semibold">{{ $department->department_name }}</option>
                                                    @foreach($departments as $childDept)
                                                        @if($childDept->parent_department_id === $department->id)
                                                            <option value="{{ $childDept->id }}" class="ml-4">├─ {{ $childDept->department_name }}</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @error('department_id') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700">Branch <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="branch_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                        <option value="">Select Branch</option>
                                        @foreach($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') 
                                        <div class="flex items-center text-red-500 text-sm mt-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="role_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Role</option>
                                        @php
                                            $rolesByDepartment = $availableRoles->groupBy('department_id');
                                        @endphp
                                        @foreach($rolesByDepartment as $deptId => $roles)
                                            @php
                                                $dept = $departments->where('id', $deptId)->first();
                                            @endphp
                                            @if($dept)
                                                <optgroup label="{{ $dept->department_name }}">
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('role_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reporting Manager</label>
                                    <select wire:model.defer="reporting_manager_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Manager</option>
                                        @foreach($managers ?? [] as $manager)
                                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('reporting_manager_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employment Type <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="employment_type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Type</option>
                                        <option value="full_time">Full Time</option>
                                        <option value="part_time">Part Time</option>
                                        <option value="contract">Contract</option>
                                        <option value="internship">Internship</option>
                                    </select>
                                    @error('employment_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model.defer="start_date" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                    @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Job Title <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="job_title" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                    @error('job_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Employee ID <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="employeeId" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                    @error('employeeId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 5: Payroll (Staff Only) -->
                        @if($currentStep === 4 && $userType === 'operational')
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Payroll Information</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Please provide salary, benefits, and tax information for payroll processing and compliance.</p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Basic Salary <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.defer="basicSalary" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0.00" step="0.01" min="0">
                                    @error('basicSalary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Payment Frequency <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="paymentFrequency" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Frequency</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="biweekly">Bi-weekly</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                    @error('paymentFrequency') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NSSF Number <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="nssfNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="NSSF Number">
                                    @error('nssfNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NSSF Contribution Rate <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.defer="nssfRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0-100" step="0.01" min="0" max="100">
                                    @error('nssfRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NHIF Number <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.defer="nhifNumber" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="NHIF Number">
                                    @error('nhifNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NHIF Contribution Rate <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.defer="nhifRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0-100" step="0.01" min="0" max="100">
                                    @error('nhifRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Workers Compensation Insurance</label>
                                    <input type="number" wire:model.defer="workersCompensation" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0.00" step="0.01" min="0">
                                    @error('workersCompensation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Life Insurance</label>
                                    <input type="number" wire:model.defer="lifeInsurance" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0.00" step="0.01" min="0">
                                    @error('lifeInsurance') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tax Category <span class="text-red-500">*</span></label>
                                    <select wire:model.defer="taxCategory" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                                        <option value="">Select Category</option>
                                        <option value="A">Category A</option>
                                        <option value="B">Category B</option>
                                        <option value="C">Category C</option>
                                    </select>
                                    @error('taxCategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">PAYE Rate <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model.defer="payeRate" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-900 focus:border-blue-900" placeholder="0-100" step="0.01" min="0" max="100">
                                    @error('payeRate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 6: Files (Staff Only) -->
                        @if($currentStep === 5 && $userType === 'operational')
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Document Upload</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Please upload relevant documents and files for this staff member. All documents will be securely stored.</p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Profile Photo Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Profile Photo
                                            <span class="ml-2 text-xs text-gray-500">(Optional)</span>
                                        </div>
                                    </label>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-teal-400 transition-colors duration-200 {{ $profilePhoto ? 'border-teal-400 bg-teal-50' : '' }}">
                                            <div wire:loading wire:target="profilePhoto" >
                                                <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-xl">
                                                <div class="flex items-center text-teal-600">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Uploading...
                                                </div>
                                                </div>
                                            </div>
                                            @if($profilePhoto)
                                                <div class="flex items-center justify-center mb-4">
                                                    <img src="{{ $profilePhoto->temporaryUrl() }}" alt="Profile Preview" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                                </div>
                                                <p class="text-sm text-teal-600 font-medium">{{ $profilePhoto->getClientOriginalName() }}</p>
                                                <button type="button" wire:click="$set('profilePhoto', null)" class="mt-2 text-xs text-red-500 hover:text-red-700">Remove</button>
                                            @else
                                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
                                            @endif
                                            <input type="file" wire:model="profilePhoto" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*">
                                        </div>
                                    </div>
                                    @error('profilePhoto') 
                                        <div class="flex items-center text-red-500 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- CV/Resume Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            CV/Resume
                                            <span class="ml-2 text-xs text-gray-500">(Optional)</span>
                                        </div>
                                    </label>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-teal-400 transition-colors duration-200 {{ $cv ? 'border-teal-400 bg-teal-50' : '' }}">
                                            <div wire:loading wire:target="cv" >
                                                <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-xl">
                                                <div class="flex items-center text-teal-600">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Uploading...
                                                </div>
                                                </div>
                                            </div>
                                            @if($cv)
                                                <div class="flex items-center justify-center mb-4">
                                                    <div class="w-16 h-16 bg-teal-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-teal-600 font-medium">{{ $cv->getClientOriginalName() }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($cv->getSize() / 1024, 1) }} KB</p>
                                                <button type="button" wire:click="$set('cv', null)" class="mt-2 text-xs text-red-500 hover:text-red-700">Remove</button>
                                            @else
                                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                                            @endif
                                            <input type="file" wire:model="cv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx">
                                        </div>
                                    </div>
                                    @error('cv') 
                                        <div class="flex items-center text-red-500 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- National ID Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                            </svg>
                                            National ID
                                            <span class="ml-2 text-xs text-gray-500">(Optional)</span>
                                        </div>
                                    </label>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-teal-400 transition-colors duration-200 {{ $nationalId ? 'border-teal-400 bg-teal-50' : '' }}">
                                            <div wire:loading wire:target="nationalId" >
                                                <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-xl">
                                                <div class="flex items-center text-teal-600">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Uploading...
                                                </div>
                                                </div>
                                            </div>
                                            @if($nationalId)
                                                <div class="flex items-center justify-center mb-4">
                                                    <div class="w-16 h-16 bg-teal-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-teal-600 font-medium">{{ $nationalId->getClientOriginalName() }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($nationalId->getSize() / 1024, 1) }} KB</p>
                                                <button type="button" wire:click="$set('nationalId', null)" class="mt-2 text-xs text-red-500 hover:text-red-700">Remove</button>
                                            @else
                                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                <p class="text-xs text-gray-500">PDF, JPG, JPEG, PNG up to 2MB</p>
                                            @endif
                                            <input type="file" wire:model="nationalId" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                    @error('nationalId') 
                                        <div class="flex items-center text-red-500 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Employment Contract Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                            </svg>
                                            Employment Contract
                                            <span class="ml-2 text-xs text-gray-500">(Optional)</span>
                                        </div>
                                    </label>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-teal-400 transition-colors duration-200 {{ $employmentContract ? 'border-teal-400 bg-teal-50' : '' }}">
                                            <div wire:loading wire:target="employmentContract" >
                                                <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-xl">
                                                <div class="flex items-center text-teal-600">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Uploading...
                                                </div>
                                                </div>
                                            </div>
                                            @if($employmentContract)
                                                <div class="flex items-center justify-center mb-4">
                                                    <div class="w-16 h-16 bg-teal-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-teal-600 font-medium">{{ $employmentContract->getClientOriginalName() }}</p>
                                                <p class="text-xs text-gray-500">{{ number_format($employmentContract->getSize() / 1024, 1) }} KB</p>
                                                <button type="button" wire:click="$set('employmentContract', null)" class="mt-2 text-xs text-red-500 hover:text-red-700">Remove</button>
                                            @else
                                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                <p class="text-xs text-gray-500">PDF up to 2MB</p>
                                            @endif
                                            <input type="file" wire:model="employmentContract" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf">
                                        </div>
                                    </div>
                                    @error('employmentContract') 
                                        <div class="flex items-center text-red-500 text-sm">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 7: System Access (Staff Only) / Step 4: System Access (Board Only) -->
                        @if($currentStep === ($userType === 'governance' ? 3 : 6))
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">System Access</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Configure user roles, permissions, and system access credentials for this user.</p>
                            </div>
                            
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
               
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                                    <input type="password" wire:model.defer="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                                    <input type="password" wire:model.defer="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" wire:model.defer="sendCredentials" id="sendCredentials" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="sendCredentials" class="ml-2 block text-sm text-gray-700">Send credentials email to user</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 8: Review (Staff Only) / Step 5: Review (Board Only) -->
                        @if($currentStep === ($userType === 'governance' ? 4 : 7))
                        <div class="max-w-4xl mx-auto">
                            <div class="text-center mb-8">
                                <div class="w-20 h-20 bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-3xl font-bold text-gray-900 mb-3">Review Information</h3>
                                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Please review all the information before creating the user account. You can go back to edit any step.</p>
                            </div>
                            
                            <div class="bg-blue-100 border border-blue-200 rounded-2xl p-8 shadow-lg">
                                <h4 class="font-semibold text-blue-900 mb-2">Review Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <div><span class="font-medium">Name:</span> {{ $firstName }} {{ $middleName }} {{ $lastName }}</div>
                                        <div><span class="font-medium">Email:</span> {{ $email }}</div>
                                        <div><span class="font-medium">Phone:</span> {{ $phone }}</div>
                                        <div><span class="font-medium">DOB:</span> {{ $dob }}</div>
                                        <div><span class="font-medium">Gender:</span> {{ $gender }}</div>
                                        <div><span class="font-medium">Marital Status:</span> {{ $maritalStatus }}</div>
                                        <div><span class="font-medium">Nationality:</span> {{ $nationality }}</div>
                                        <div><span class="font-medium">NIDA:</span> {{ $nida }}</div>
                                        <div><span class="font-medium">TIN:</span> {{ $tin }}</div>
                                        <div><span class="font-medium">Address:</span> {{ $physicalAddress }}</div>
                                        <div><span class="font-medium">City:</span> {{ $city }}</div>
                                        <div><span class="font-medium">Region:</span> {{ $region }}</div>
                                    </div>
                                    <div>
                                        <div><span class="font-medium">User Type:</span> 
                                            @if($userType === 'governance')
                                                <span class="text-blue-600 font-semibold">Board Member & Oversight</span>
                                            @elseif($userType === 'operational')
                                                <span class="text-green-600 font-semibold">Staff Member</span>
                                            @else
                                                <span class="text-gray-500">Not specified</span>
                                            @endif
                                        </div>
                                        <div><span class="font-medium">Department:</span> 
                                            @php
                                                $dept = $departments->where('id', $department_id)->first();
                                                $parentDept = $dept ? $departments->where('id', $dept->parent_department_id)->first() : null;
                                            @endphp
                                            @if($dept)
                                                @if($parentDept)
                                                    {{ $parentDept->department_name }} → {{ $dept->department_name }}
                                                @else
                                                    {{ $dept->department_name }}
                                                @endif
                                            @else
                                                <span class="text-gray-500">Not selected</span>
                                            @endif
                                        </div>
                                        @if($userType === 'operational')
                                            <div><span class="font-medium">Branch:</span> {{ optional($branches->where('id', $branch_id)->first())->name }}</div>
                                            <div><span class="font-medium">Manager:</span> {{ optional($managers->where('id', $reporting_manager_id)->first())->name }}</div>
                                            <div><span class="font-medium">Employment Type:</span> {{ $employment_type }}</div>
                                            <div><span class="font-medium">Employee ID:</span> {{ $employeeId }}</div>
                                            <div><span class="font-medium">Salary:</span> {{ $basicSalary }}</div>
                                            <div><span class="font-medium">Payment Frequency:</span> {{ $paymentFrequency }}</div>
                                        @endif
                                        <div><span class="font-medium">Role:</span> {{ optional($availableRoles->where('id', $role_id)->first())->name }}</div>
                                        <div><span class="font-medium">Start Date:</span> {{ $start_date }}</div>
                                        <div><span class="font-medium">Job Title:</span> {{ $job_title }}</div>
                                        <div><span class="font-medium">Status:</span> {{ $status === 1 ? 'Active' : 'Inactive' }}</div>
                                    </div>
                                </div>
                                <div class="mt-4 text-xs text-gray-500">Please review all information before submitting. You can go back to edit any step.</div>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div class="fixed z-50 inset-0 overflow-y-auto" style="display: {{ $showViewUser ? 'block' : 'none' }}" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full relative">
                <!-- Close Button -->
                <button wire:click="$set('showViewUser', false)" type="button" aria-label="Close" class="absolute top-6 right-6 text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full transition z-10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Content -->
                <div class="px-8 pt-8 pb-8">
                    @if($viewingUser)
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">User Details</h3>
                            <p class="text-gray-600">Complete information about {{ $viewingUser->name }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- User Information -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Name:</div>
                                            <div class="text-sm text-gray-900">{{ $viewingUser->name }}</div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Email:</div>
                                            <div class="text-sm text-gray-900">{{ $viewingUser->email }}</div>
                                        </div>
                                        @if($viewingUser->employeeId)
                                            <div class="flex items-center">
                                                <div class="w-32 text-sm font-medium text-gray-500">Employee ID:</div>
                                                <div class="text-sm text-gray-900">{{ $viewingUser->employeeId }}</div>
                                            </div>
                                        @endif
                                        @if($viewingUser->employee)
                                            <div class="flex items-center">
                                                <div class="w-32 text-sm font-medium text-gray-500">Phone:</div>
                                                <div class="text-sm text-gray-900">{{ $viewingUser->employee->phone ?? 'N/A' }}</div>
                                            </div>
                                            <div class="flex items-center">
                                                <div class="w-32 text-sm font-medium text-gray-500">Job Title:</div>
                                                <div class="text-sm text-gray-900">{{ $viewingUser->employee->job_title ?? 'N/A' }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Department & Branch</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Department:</div>
                                            <div class="text-sm text-gray-900">{{ $viewingUser->department ? $viewingUser->department->department_name : 'N/A' }}</div>
                                        </div>
                                        @if($viewingUser->employee && $viewingUser->employee->branch)
                                            <div class="flex items-center">
                                                <div class="w-32 text-sm font-medium text-gray-500">Branch:</div>
                                                <div class="text-sm text-gray-900">{{ $viewingUser->employee->branch->branch_name ?? 'N/A' }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Roles & Permissions -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Roles & Permissions</h4>
                                    <div class="space-y-4">
                                        <!-- Roles -->
                                        <div>
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Assigned Roles:</h5>
                                            <div class="flex flex-wrap gap-2">
                                                @forelse($viewingUser->roles ?? [] as $role)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $role->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-sm text-gray-500">No roles assigned</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        
                                        <!-- Permissions -->
                                        <div>
                                            <h5 class="text-sm font-medium text-gray-700 mb-2">Permissions:</h5>
                                            <div class="max-h-32 overflow-y-auto">
                                                <div class="flex flex-wrap gap-1">
                                                    @php
                                                        $permissions = $viewingUser->roles->flatMap->permissions->unique('name');
                                                    @endphp
                                                    @forelse($permissions as $permission)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                                                            {{ $permission->name }}
                                                        </span>
                                                    @empty
                                                        <span class="text-sm text-gray-500">No permissions</span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Activity & Status</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Status:</div>
                                            @php
                                                $status = $viewingUser->status;
                                                $statusLabel = $status === 1 ? 'Active' : ($status === 0 ? 'Inactive' : 'Pending');
                                                $statusClasses = $status === 1 ? 'bg-green-100 text-green-800' : ($status === 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Last Active:</div>
                                            <div class="text-sm text-gray-900">
                                                {{ isset($viewingUser->last_active_at) && $viewingUser->last_active_at ? $viewingUser->last_active_at->diffForHumans() : 'Never' }}
                                            </div>
                                        </div>
                                        @if(isset($viewingUser->last_login_at) && $viewingUser->last_login_at)
                                            <div class="flex items-center">
                                                <div class="w-32 text-sm font-medium text-gray-500">Last Login:</div>
                                                <div class="text-sm text-gray-900">{{ $viewingUser->last_login_at->format('M j, Y g:i A') }}</div>
                                            </div>
                                        @endif
                                        <div class="flex items-center">
                                            <div class="w-32 text-sm font-medium text-gray-500">Created:</div>
                                            <div class="text-sm text-gray-900">{{ $viewingUser->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <button wire:click="editUser({{ $viewingUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Edit User
                            </button>
                            <button wire:click="managePermissions({{ $viewingUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Manage Permissions
                            </button>
                            <button wire:click="$set('showViewUser', false)" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Close
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Permissions Modal -->
    <div class="fixed z-50 inset-0 overflow-y-auto" style="display: {{ $showManagePermissions ? 'block' : 'none' }}" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full relative">
                <!-- Close Button -->
                <button wire:click="$set('showManagePermissions', false)" type="button" aria-label="Close" class="absolute top-6 right-6 text-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full transition z-10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Content -->
                <div class="px-8 pt-8 pb-8">
                    @if($editingUser)
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Manage Permissions</h3>
                            <p class="text-gray-600">Update roles and permissions for {{ $editingUser->name }}</p>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Current User Info -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">User Information</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Name</div>
                                        <div class="text-sm text-gray-900">{{ $editingUser->name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Email</div>
                                        <div class="text-sm text-gray-900">{{ $editingUser->email }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Department</div>
                                        <div class="text-sm text-gray-900">{{ $editingUser->department ? $editingUser->department->department_name : 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500">Status</div>
                                        <div class="text-sm text-gray-900">{{ $editingUser->status ? 'Active' : 'Inactive' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Assign Roles</h4>
                                <div class="space-y-3">
                                    @foreach($allRoles as $role)
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $role->description ?? 'No description' }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedRoles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Current Permissions Preview -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4">Permissions Preview</h4>
                                <div class="max-h-32 overflow-y-auto">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $selectedRoleIds = collect($selectedRoles);
                                            $previewPermissions = $allRoles->whereIn('id', $selectedRoleIds)->flatMap->permissions->unique('name');
                                        @endphp
                                        @forelse($previewPermissions as $permission)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-800">
                                                {{ $permission->name }}
                                            </span>
                                        @empty
                                            <span class="text-sm text-gray-500">No permissions will be assigned</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <button wire:click="$set('showManagePermissions', false)" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button wire:click="updateUserPermissions" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Permissions
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
