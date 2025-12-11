<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-pink-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Audit Logs</h1>
                        <p class="text-gray-600 mt-1">Track and monitor system activities and user actions</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Activities</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalActivities ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Users</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($activeUsers ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Today's Activities</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($todayActivities ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Advanced Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select wire:model="userFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Users</option>
                        @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Action Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                    <select wire:model="actionFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Actions</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="view">View</option>
                    </select>
                </div>
                
                <!-- Module Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                    <select wire:model="moduleFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Modules</option>
                        <option value="users">Users</option>
                        <option value="roles">Roles</option>
                        <option value="departments">Departments</option>
                        <option value="permissions">Permissions</option>
                        <option value="settings">Settings</option>
                    </select>
                </div>
                
                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" wire:model="dateFrom" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" wire:model="dateTo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                        <option value="warning">Warning</option>
                    </select>
                </div>
                
                <!-- Search Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Search activities..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Activity Logs</h3>
                    <div class="flex items-center space-x-2">
                        <select wire:model="perPage" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                        <button wire:click="exportLogs" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                Timestamp
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('user_id')">
                                User
                                @if($sortField === 'user_id')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('action')">
                                Action
                                @if($sortField === 'action')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('module')">
                                Module
                                @if($sortField === 'module')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                Status
                                @if($sortField === 'status')
                                    @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($auditLogs ?? [] as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>
                                        <div class="font-medium">{{ $log->created_at->format('M d, Y') }}</div>
                                        <div class="text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            @if($log->user && $log->user->profile_photo_url)
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ $log->user->profile_photo_url }}" alt="{{ $log->user->name }}">
                                            @else
                                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-600 font-medium text-sm">
                                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : '?' }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $log->user ? $log->user->name : 'System' }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->user ? $log->user->email : 'system@nbc.com' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($log->action === 'login') bg-green-100 text-green-800
                                        @elseif($log->action === 'logout') bg-gray-100 text-gray-800
                                        @elseif($log->action === 'create') bg-blue-100 text-blue-800
                                        @elseif($log->action === 'update') bg-yellow-100 text-yellow-800
                                        @elseif($log->action === 'delete') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucfirst($log->module ?? 'System') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $log->description ?? 'No description available' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($log->status === 'success') bg-green-100 text-green-800
                                        @elseif($log->status === 'failed') bg-red-100 text-red-800
                                        @elseif($log->status === 'warning') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($log->status ?? 'unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->ip_address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button wire:click="viewLogDetails({{ $log->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No audit logs found</h3>
                                        <p class="mt-1 text-sm text-gray-500">No activities have been recorded yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $auditLogs->links() ?? '' }}
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
@if($showLogModal && $selectedLog)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Audit Log Details</h3>
                <button wire:click="$set('showLogModal', false)" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Activity Information</h4>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedLog->created_at->format('F d, Y \a\t H:i:s') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedLog->user ? $selectedLog->user->name : 'System' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($selectedLog->action) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Module</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($selectedLog->module ?? 'System') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedLog->status === 'success') bg-green-100 text-green-800
                                    @elseif($selectedLog->status === 'failed') bg-red-100 text-red-800
                                    @elseif($selectedLog->status === 'warning') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($selectedLog->status ?? 'unknown') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $selectedLog->ip_address ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Description -->
                @if($selectedLog->description)
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Description</h4>
                    <p class="text-sm text-gray-900">{{ $selectedLog->description }}</p>
                </div>
                @endif

                <!-- Additional Data -->
                @if($selectedLog->additional_data)
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">Additional Data</h4>
                    <pre class="bg-gray-50 p-4 rounded-lg text-sm text-gray-900 overflow-x-auto">{{ json_encode(json_decode($selectedLog->additional_data), JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif

                <!-- User Agent -->
                @if($selectedLog->user_agent)
                <div>
                    <h4 class="text-md font-semibold text-gray-900 mb-3">User Agent</h4>
                    <p class="text-sm text-gray-900 break-all">{{ $selectedLog->user_agent }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endif 