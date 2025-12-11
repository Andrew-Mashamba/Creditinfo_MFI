<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <!-- Toast Notifications -->
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
        <div class="flex items-center gap-3 rounded-lg shadow-lg px-4 py-3"
            :class="{
                'bg-green-50 border border-green-200': type === 'success',
                'bg-red-50 border border-red-200': type === 'error',
                'bg-yellow-50 border border-yellow-200': type === 'warning',
            }"
        >
            <svg x-show="type === 'success'" class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg x-show="type === 'error'" class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg x-show="type === 'warning'" class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" />
            </svg>
            <span class="font-semibold" x-text="message"></span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Enhanced Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="p-4 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 15v2m0 4h.01M6.938 19h10.124c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Role Management</h1>
                    <p class="text-gray-600 mt-1">Manage system roles and their permissions</p>
                </div>
            </div>
            <div class="flex gap-4">
                <button wire:click="toggleViewMode" class="inline-flex items-center px-4 py-2 rounded-lg shadow-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all border border-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $viewMode === 'grid' ? 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' : 'M4 6h16M4 10h16M4 14h16M4 18h16' }}" />
                    </svg>
                    {{ $viewMode === 'grid' ? 'Table View' : 'Grid View' }}
                </button>
                <button wire:click="toggleBulkActions" class="inline-flex items-center px-4 py-2 rounded-lg shadow-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all border border-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Bulk Actions
                </button>
                <button wire:click="$set('showCreateRole', true)" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Role
                </button>
                <button wire:click="$set('showCreateSubRole', true)" class="inline-flex items-center px-6 py-3 rounded-xl shadow-lg text-base font-semibold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                    <svg class="-ml-1 mr-2 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Sub-Role
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        @if($showStatistics)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Roles</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['active'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Inactive</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['inactive'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">System</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['system'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Department</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['department'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">With Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $statistics['with_users'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif


        <!-- Bulk Actions Section -->
        @if($showBulkActions)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:click="selectAllRoles" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Select All</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="deselectAllRoles" class="text-sm text-gray-600 hover:text-gray-800">Deselect All</button>
                        <span class="text-sm text-gray-500">({{ count($selectedRoles) }} selected)</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select wire:model="bulkAction" class="rounded-lg border-gray-300 text-sm focus:border-indigo-400 focus:ring focus:ring-indigo-200">
                        <option value="">Choose Action</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button wire:click="performBulkAction" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Apply Action
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Enhanced Filters Section -->
        <div class="bg-white/90 rounded-2xl shadow-md p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.debounce.300ms="search" type="text" placeholder="Search roles by name or description..." class="pl-10 block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 shadow-sm">
                </div>
                <div class="flex-1">
                    <select wire:model="departmentFilter" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 shadow-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <select wire:model="typeFilter" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 shadow-sm">
                        <option value="">All Types</option>
                        <option value="role">Main Roles</option>
                        <option value="subrole">Sub-Roles</option>
                    </select>
                </div>
                <div class="flex-1">
                    <select wire:model="statusFilter" class="block w-full rounded-lg border-gray-300 focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 shadow-sm">
                        <option value="">All Status</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button wire:click="$set('search', '')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Clear
                    </button>
                    <button wire:click="toggleStatistics" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{ $showStatistics ? 'Hide' : 'Show' }} Stats
                    </button>
                </div>
            </div>
        </div>

        <!-- Roles Display -->
        @if($viewMode === 'table')
        <!-- Table View -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                                <div class="flex items-center">
                                    Role Name
                                    @if($sortField === 'name')
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($filteredRoles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($showBulkActions)
                                        <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                        @if($role->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($role->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $role->department->department_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->parent_role_id ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $role->parent_role_id ? 'Sub-Role' : 'Main Role' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $role->users_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($role->status ?? 'ACTIVE') === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $role->status ?? 'ACTIVE' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button wire:click="showPermissionsModal({{ $role->id }}, '{{ $role->parent_role_id ? 'subrole' : 'role' }}')" class="text-green-600 hover:text-green-900" title="Manage Permissions">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                    <button wire:click="editRole({{ $role->id }})" class="text-indigo-600 hover:text-indigo-900" title="Edit Role">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button wire:click="deleteRole({{ $role->id }})" class="text-red-600 hover:text-red-900" title="Delete Role">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                <div class="flex flex-col items-center py-8">
                                    <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">No roles found</h3>
                                    <p class="text-gray-500">Get started by creating a new role.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <!-- Grid View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($departments as $department)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h2a4 4 0 014 4v2" /></svg>
                                {{ $department->department_name }}
                            </h3>
                            <span class="px-2 py-1 text-xs font-bold rounded-full {{ $department->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $department->status ? 'ACTIVE' : 'INACTIVE' }}
                            </span>
                        </div>
                    </div>

                    <!-- Main Roles -->
                    <div class="p-4 border-b border-gray-100">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Main Roles</h4>
                        <div class="space-y-2">
                            @forelse($department->roles()->whereNull('parent_role_id')->get() as $role)
                                <div wire:click="showPermissionsModal({{ $role->id }}, 'role')" class="flex items-center justify-between bg-blue-50 rounded-md p-3 hover:bg-blue-100 transition-colors duration-150">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900">{{ $role->name }}</span>
                                            <span class="ml-2 text-xs text-gray-500">({{ $role->users_count ?? 0 }} users)</span>
                                        </div>
                                        @if($role->description)
                                            <p class="mt-1 text-xs text-gray-500">{{ $role->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button wire:click="showPermissionsModal({{ $role->id }}, 'role')" class="text-green-600 hover:text-green-900" title="Manage Permissions">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </button>
                                        <button wire:click="editRole({{ $role->id }})" class="text-indigo-600 hover:text-indigo-900" title="Edit Role">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="deleteRole({{ $role->id }})" class="text-red-600 hover:text-red-900" title="Delete Role">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No main roles defined</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Sub-Roles -->
                    <div class="p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Sub-Roles</h4>
                        <div class="space-y-2"> 
                            @forelse($department->roles()->whereNotNull('parent_role_id')->get() as $subRole)
                                <div wire:click="showPermissionsModal({{ $subRole->id }}, 'subrole')" class="flex items-center justify-between bg-gray-50 rounded-md p-3 hover:bg-gray-100 transition-colors duration-150">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900">{{ $subRole->name }}</span>
                                            <span class="ml-2 text-xs text-gray-500">({{ $subRole->users_count ?? 0 }} users)</span>
                                        </div>
                                        <div class="flex items-center mt-1">
                                            <span class="text-xs text-gray-500">Parent: {{ $subRole->parentRole->name ?? 'Unknown' }}</span>
                                        </div>
                                        @if($subRole->description)
                                            <p class="mt-1 text-xs text-gray-500">{{ $subRole->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button wire:click="showPermissionsModal({{ $subRole->id }}, 'subrole')" class="text-green-600 hover:text-green-900" title="Manage Permissions">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </button>
                                        <button wire:click="editSubRole({{ $subRole->id }})" class="text-indigo-600 hover:text-indigo-900" title="Edit Sub-Role">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button wire:click="deleteSubRole({{ $subRole->id }})" class="text-red-600 hover:text-red-900" title="Delete Sub-Role">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No sub-roles defined</p>
                            @endforelse
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
                    </div>
                </div>
            @endforelse
        </div>
        @endif
    </div>

    <!-- Create/Edit Role Modal -->
    <div class="modal" style="display: {{ $showCreateRole ? 'block' : 'none' }}" aria-modal="true" role="dialog" aria-labelledby="role-modal-title" x-data="{ focusTrap: false }" x-init="focusTrap = true" @keydown.escape="$wire.set('showCreateRole', false)">
        <div class="modal-content relative">
            <!-- Close Button -->
            <button type="button" @click="$wire.set('showCreateRole', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="modal-header bg-indigo-50 border-b flex items-center gap-2 rounded-t-lg">
                <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 15v2m0 4h.01M6.938 19h10.124c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <h2 id="role-modal-title" class="text-lg font-semibold text-gray-900">{{ $editingRole ? 'Edit Role' : 'Create New Role' }}</h2>
            </div>

            <!-- Loading Overlay -->
            <div wire:loading wire:target="saveRole" class="absolute inset-0 bg-white/70 flex items-center justify-center z-20 rounded-lg">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>

            <div class="modal-body">
                <div class="space-y-4">
                    <div>
                        <label for="roleName">{{ __('Role Name') }}</label>
                        <input id="roleName" type="text" class="form-input" wire:model.defer="roleName" required />
                        @error('roleName') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="roleDepartment">{{ __('Department') }}</label>
                        <select id="roleDepartment" class="form-select" wire:model.defer="roleDepartment" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                        @error('roleDepartment') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="roleDescription">{{ __('Description') }}</label>
                        <textarea id="roleDescription" class="form-textarea" wire:model.defer="roleDescription" rows="3"></textarea>
                        @error('roleDescription') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    {{--<div>
                        <label>{{ __('Approval Permissions') }}</label>
                        <div class="mt-2 space-y-2">
                            <label class="checkbox-label">
                                <input type="checkbox" wire:model.defer="canApprove" class="form-checkbox">
                                <span>Can Approve Requests</span>
                            </label>
                            <div class="ml-6 mt-2 space-y-2">
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canFirstCheck" class="form-checkbox">
                                    <span>First Checker</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canSecondCheck" class="form-checkbox">
                                    <span>Second Checker</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canFinalApprove" class="form-checkbox">
                                    <span>Final Approver</span>
                                </label>
                            </div>
                        </div>
                    </div>--}}
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showCreateRole', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </button>

                <button type="button" class="btn btn-primary" wire:click="saveRole" wire:loading.attr="disabled">
                    {{ $editingRole ? 'Update Role' : 'Create Role' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Create/Edit Sub-Role Modal -->
    <div class="modal" style="display: {{ $showCreateSubRole ? 'block' : 'none' }}" aria-modal="true" role="dialog" aria-labelledby="subrole-modal-title" x-data="{ focusTrap: false }" x-init="focusTrap = true" @keydown.escape="$wire.set('showCreateSubRole', false)">
        <div class="modal-content relative">
            <!-- Close Button -->
            <button type="button" @click="$wire.set('showCreateSubRole', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="modal-header bg-green-50 border-b flex items-center gap-2 rounded-t-lg">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 15v2m0 4h.01M6.938 19h10.124c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <h2 id="subrole-modal-title" class="text-lg font-semibold text-gray-900">{{ $editingSubRole ? 'Edit Sub-Role' : 'Create New Sub-Role' }}</h2>
            </div>

            <!-- Loading Overlay -->
            <div wire:loading wire:target="saveSubRole" class="absolute inset-0 bg-white/70 flex items-center justify-center z-20 rounded-lg">
                <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>

            <div class="modal-body">
                <div class="space-y-4">
                    <div>
                        <label for="subRoleName">{{ __('Sub-Role Name') }}</label>
                        <input id="subRoleName" type="text" class="form-input" wire:model.defer="subRoleName" required />
                        @error('subRoleName') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="subRoleParent">{{ __('Parent Role') }}</label>
                        <select id="subRoleParent" class="form-select" wire:model.defer="subRoleParent" required>
                            <option value="">Select Parent Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('subRoleParent') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="subRoleDescription">{{ __('Description') }}</label>
                        <textarea id="subRoleDescription" class="form-textarea" wire:model.defer="subRoleDescription" rows="3"></textarea>
                        @error('subRoleDescription') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label>{{ __('Permissions') }}</label>
                        <div class="mt-2 space-y-2">                            
                            {{--@foreach($menuActions as $menuAction)
                             
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="selectedPermissions" value="{{ $menuAction->id }}" class="form-checkbox">
                                    <span>{{ $menuAction->name }}</span>
                                </label>
                            @endforeach--}}
                        </div>
                        @error('selectedPermissions') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    {{--<div>
                        <label>{{ __('Approval Permissions') }}</label>
                        <div class="mt-2 space-y-2">
                            <label class="checkbox-label">
                                <input type="checkbox" wire:model.defer="canApprove" class="form-checkbox">
                                <span>Can Approve Requests</span>
                            </label>
                            <div class="ml-6 mt-2 space-y-2">
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canFirstCheck" class="form-checkbox">
                                    <span>First Checker</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canSecondCheck" class="form-checkbox">
                                    <span>Second Checker</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" wire:model.defer="canFinalApprove" class="form-checkbox">
                                    <span>Final Approver</span>
                                </label>
                            </div>
                        </div>
                    </div>--}}
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showCreateSubRole', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </button>

                <button type="button" class="btn btn-primary" wire:click="saveSubRole" wire:loading.attr="disabled">
                    {{ $editingSubRole ? 'Update Sub-Role' : 'Create Sub-Role' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" style="display: {{ $showDeleteModal ? 'block' : 'none' }}" aria-modal="true" role="dialog">
        <div class="modal-content relative">
            <!-- Close Button -->
            <button type="button" @click="$wire.set('showDeleteModal', false)" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="modal-header bg-red-50 border-b flex items-center gap-2 rounded-t-lg">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <h2 class="text-lg font-semibold text-gray-900">Delete {{ $deletingType === 'role' ? 'Role' : 'Sub-Role' }}</h2>
            </div>

            <div class="modal-body">
                <p class="text-gray-700">Are you sure you want to delete this {{ $deletingType === 'role' ? 'role' : 'sub-role' }}? This action cannot be undone.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled">
                    Cancel
                </button>

                <button type="button" class="btn btn-danger" wire:click="confirmDelete" wire:loading.attr="disabled">
                    Delete {{ $deletingType === 'role' ? 'Role' : 'Sub-Role' }}
                </button>
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

    <!-- Permissions Modal -->
    <div class="modal" style="display: {{ $showPermissionsModal ? 'block' : 'none' }}" aria-modal="true" role="dialog" aria-labelledby="permissions-modal-title" x-data="{ focusTrap: false }" x-init="focusTrap = true" @keydown.escape="$wire.closePermissionsModal()">
        <div class="modal-content relative" style="max-width: 80%; max-height: 90vh; overflow-y: auto;">
            <!-- Close Button -->
            <button type="button" @click="$wire.closePermissionsModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none z-10" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <div class="modal-header bg-indigo-50 border-b flex items-center gap-2 rounded-t-lg">
                <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h2 id="permissions-modal-title" class="text-lg font-semibold text-gray-900">
                    Manage Permissions - {{ $selectedRoleType === 'subrole' ? 'Sub-Role' : 'Role' }}
                </h2>
            </div>

            <!-- Loading Overlay -->
            <div wire:loading wire:target="savePermissions" >
                <div class="absolute inset-0 bg-white/70 flex items-center justify-center z-20 rounded-lg">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                </div>
            </div>

            <div class="modal-body">
                <div class="space-y-6">
                    <!-- Search and Filter -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" placeholder="Search permissions..." class="form-input" wire:model.debounce.300ms="search">
                        </div>
                        <div class="flex-1">
                            <select class="form-select" wire:model="departmentFilter">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Permissions by Menu -->
                    <div class="space-y-4">
                        @foreach($allMenus as $menu)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        {{ $menu->menu_name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $menu->menu_description }}</p>
                                </div>
                                
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @php
                                            $actions = ['view', 'create', 'edit', 'delete', 'approve', 'reject', 'manage', 'configure', 'audit'];
                                        @endphp
                                        @foreach($actions as $action)
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                                <input type="checkbox" 
                                                       wire:model="selectedPermissions" 
                                                       value="{{ $menu->id }}_{{ $action }}" 
                                                       class="form-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <div class="ml-3 flex-1">
                                                    <span class="text-sm font-medium text-gray-900 capitalize">{{ $action }}</span>
                                                    <p class="text-xs text-gray-500 mt-1">{{ ucfirst($action) }} {{ $menu->menu_name }}</p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Summary -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 class="font-medium text-blue-900">Selected Permissions</h4>
                        </div>
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">{{ count($selectedPermissions) }}</span> permission(s) selected
                        </p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closePermissionsModal" wire:loading.attr="disabled">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" wire:click="savePermissions" wire:loading.attr="disabled">
                    Save Permissions
                </button>
            </div>
        </div>
    </div>
</div>
