<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Approvers Manager</h1>
                        <p class="text-gray-600 mt-1">Configure, manage, and analyze approval flows and roles</p>
                    </div>
                </div>
                <!-- Dashboard Cards -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Flows</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $configs->where('is_active', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingCount ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Flows</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $configs->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-6">
            <!-- Sidebar Navigation -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Search -->
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search approval flows..." class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white" />
                    </div>
                </div>
                <!-- Navigation -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Sections</h3>
                    @php
                        $sections = [
                            ['id' => 1, 'label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                            ['id' => 2, 'label' => 'Approval Flows', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                            ['id' => 3, 'label' => 'Pending', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['id' => 4, 'label' => 'Roles & Permissions', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                            ['id' => 5, 'label' => 'Reports', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ];
                    @endphp
                    <nav class="space-y-2">
                        @foreach ($sections as $section)
                            <button wire:click="$set('selectedSection', {{ $section['id'] }})" class="relative w-full group transition-all duration-200">
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200 @if ($selectedSection == $section['id']) bg-blue-900 text-white shadow-lg @else bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 @endif">
                                    <div class="mr-3">
                                        <svg class="w-5 h-5 @if ($selectedSection == $section['id']) text-white @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                    </div>
                                    @if ($section['id'] == 3 && ($pendingCount ?? 0) > 0)
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px] h-5">
                                                {{ ($pendingCount ?? 0) > 99 ? '99+' : ($pendingCount ?? 0) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>
                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="create" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Approval Flow
                        </button>
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Export Flows
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                @switch($selectedSection)
                    @case(1)
                        {{-- Dashboard Section --}}
                        <div class="p-6 space-y-6">
                            <!-- Statistics Cards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <!-- Total Flows Card -->
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-gray-500 text-sm font-medium">Total Flows</p>
                                            <p class="text-3xl font-bold">{{ $totalFlows }}</p>
                                            <p class="text-gray-500 text-xs mt-1">{{ $activeFlows }} active, {{ $inactiveFlows }} inactive</p>
                                        </div>
                                        <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Approvals Card -->
                                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-yellow-100 text-sm font-medium">Pending Approvals</p>
                                            <p class="text-3xl font-bold">{{ $pendingCount }}</p>
                                            <p class="text-yellow-100 text-xs mt-1">Requires attention</p>
                                        </div>
                                        <div class="p-3 bg-yellow-400 bg-opacity-30 rounded-full">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved Today Card -->
                                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-green-100 text-sm font-medium">Approved Today</p>
                                            <p class="text-3xl font-bold">{{ $approvedToday }}</p>
                                            <p class="text-green-100 text-xs mt-1">{{ $rejectedToday }} rejected</p>
                                        </div>
                                        <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Users Card -->
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-purple-100 text-sm font-medium">Active Roles</p>
                                            <p class="text-3xl font-bold">{{ $roles->count() }}</p>
                                            <p class="text-purple-100 text-xs mt-1">Configured roles</p>
                                        </div>
                                        <div class="p-3 bg-purple-400 bg-opacity-30 rounded-full">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Dashboard Content -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Recent Activity -->
                                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Recent Approval Activity</h3>
                                    </div>
                                    <div class="p-6">
                                        @if(count($recentApprovals) > 0)
                                            <div class="space-y-4">
                                                @foreach($recentApprovals as $approval)
                                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-2 h-2 bg-{{ $approval->process_status === 'APPROVED' ? 'green' : ($approval->process_status === 'PENDING' ? 'yellow' : 'red') }}-500 rounded-full"></div>
                                                            </div>
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    {{ $approval->processConfig->process_name ?? $approval->process_code }}
                                                                </p>
                                                                <p class="text-xs text-gray-500">
                                                                    by {{ $approval->user->name ?? 'Unknown' }} • {{ $approval->created_at->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                                            @if($approval->process_status === 'APPROVED')
                                                                bg-green-100 text-green-800
                                                            @elseif($approval->process_status === 'PENDING')
                                                                bg-yellow-100 text-yellow-800
                                                            @else
                                                                bg-red-100 text-red-800
                                                            @endif">
                                                            {{ ucfirst(strtolower($approval->process_status)) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <p class="text-gray-500">No recent approval activity</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Category Distribution -->
                                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Flow Categories</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="space-y-4">
                                            @foreach($categoryStats as $category => $count)
                                                @if($count > 0)
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="w-3 h-3 rounded-full 
                                                                @if($category === 'loan') bg-blue-500
                                                                @elseif($category === 'financial') bg-green-500
                                                                @elseif($category === 'member') bg-purple-500
                                                                @elseif($category === 'expense') bg-yellow-500
                                                                @elseif($category === 'asset') bg-red-500
                                                                @elseif($category === 'hr') bg-indigo-500
                                                                @else bg-gray-500
                                                                @endif"></div>
                                                            <span class="text-sm text-gray-700 capitalize">{{ $category }}</span>
                                                        </div>
                                                        <span class="text-sm font-semibold text-gray-900">{{ $count }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Trends and Top Performers -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Approval Trends -->
                                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">7-Day Approval Trends</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="space-y-3">
                                            @foreach($approvalTrends as $trend)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-600 w-16">{{ $trend['date'] }}</span>
                                                    <div class="flex-1 mx-4">
                                                        <div class="flex items-center space-x-1">
                                                            @if($trend['approved'] > 0)
                                                                <div class="bg-green-200 text-green-800 px-2 py-1 rounded text-xs">{{ $trend['approved'] }}</div>
                                                            @endif
                                                            @if($trend['pending'] > 0)
                                                                <div class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs">{{ $trend['pending'] }}</div>
                                                            @endif
                                                            @if($trend['rejected'] > 0)
                                                                <div class="bg-red-200 text-red-800 px-2 py-1 rounded text-xs">{{ $trend['rejected'] }}</div>
                                                            @endif
                                                            @if($trend['approved'] == 0 && $trend['pending'] == 0 && $trend['rejected'] == 0)
                                                                <div class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs">0</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Top Approvers -->
                                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <h3 class="text-lg font-semibold text-gray-900">Top Approvers This Month</h3>
                                    </div>
                                    <div class="p-6">
                                        @if(count($topApprovers) > 0)
                                            <div class="space-y-4">
                                                @foreach($topApprovers as $approver)
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                                <span class="text-xs font-medium text-blue-600">{{ substr($approver->approver->name ?? 'U', 0, 1) }}</span>
                                                            </div>
                                                            <span class="text-sm text-gray-900">{{ $approver->approver->name ?? 'Unknown' }}</span>
                                                        </div>
                                                        <span class="text-sm font-semibold text-blue-600">{{ $approver->approval_count }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-8">
                                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                                </svg>
                                                <p class="text-gray-500">No approval data this month</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <button wire:click="create" class="flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-blue-600 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Add Flow</span>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="$set('selectedSection', 3)" class="flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200 hover:border-yellow-300 hover:bg-yellow-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-yellow-600 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-600">View Pending</span>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="$set('selectedSection', 5)" class="flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-purple-600 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">Reports</span>
                                        </div>
                                    </button>
                                    
                                    <button wire:click="exportConfigs" class="flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-all duration-200 group">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-green-600 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700 group-hover:text-green-600">Export</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @break
                    @case(2)
                        {{-- Approval Flows Table --}}
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Approval Flows</h3>
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.debounce.300ms="searchTerm" 
                                               placeholder="Search process codes..." 
                                               class="w-64 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <button wire:click="create" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition-colors">
                                        Add Flow
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Filter Controls --}}
                            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                                <div class="flex flex-wrap items-center gap-4">
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm font-medium text-gray-700">Category:</label>
                                        <select wire:model="selectedCategory" class="border border-gray-300 rounded-md text-sm px-3 py-1 focus:ring-blue-500 focus:border-blue-500">
                                            @foreach($this->categories as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm font-medium text-gray-700">Status:</label>
                                        <select wire:model="filterStatus" class="border border-gray-300 rounded-md text-sm px-3 py-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="all">All</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm font-medium text-gray-700">Role:</label>
                                        <select wire:model="filterRole" class="border border-gray-300 rounded-md text-sm px-3 py-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="all">All Roles</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    @if($searchTerm || $selectedCategory !== 'all' || $filterStatus !== 'all' || $filterRole !== 'all')
                                        <button wire:click="clearFilters" class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                            Clear Filters
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Process Code</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Process Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Range</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checkers</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($configs as $config)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $config->process_code }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $config->process_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($config->min_amount || $config->max_amount)
                                                        {{ number_format($config->min_amount ?? 0, 2) }} - {{ $config->max_amount ? number_format($config->max_amount, 2) : '∞' }}
                                                    @else
                                                        No limit
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($config->requires_first_checker)
                                                        <div class="mb-1">
                                                            <span class="font-medium text-gray-700">First Checker:</span>
                                                            @if($config->first_checker_roles)
                                                                <div class="mt-1 flex flex-wrap gap-1">
                                                                    @foreach($config->first_checker_roles as $roleId)
                                                                        @php
                                                                            $role = $roles->firstWhere('id', $roleId);
                                                                        @endphp
                                                                        @if($role)
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                                                {{ $role->name }}
                                                                            </span>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $config->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $config->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button wire:click="edit({{ $config->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                                    <button wire:click="delete({{ $config->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                    No approval flows found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination Controls --}}
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-2">
                                        <label class="text-sm text-gray-700">Show:</label>
                                        <select wire:model="perPage" class="border border-gray-300 rounded-md text-sm px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                            <option value="500">Show All</option>
                                        </select>
                                        <span class="text-sm text-gray-700">per page</span>
                                    </div>
                                    <div class="text-sm text-gray-700">
                                        Showing {{ $configs->firstItem() }} to {{ $configs->lastItem() }} of {{ $configs->total() }} results
                                    </div>
                                </div>
                                
                                {{-- Pagination Links --}}
                                <div class="flex-1 flex justify-end">
                                    {{ $configs->links() }}
                                </div>
                            </div>
                        </div>
                        @break
                    @case(3)
                        {{-- Pending Approvals Section --}}
                        <div class="p-6">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-yellow-900 mb-4">Pending Approvals</h3>
                                <div class="space-y-4">
                                    @if(count($pendingApprovals ?? []))
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Process</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($pendingApprovals as $approval)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm">{{ $approval->processConfig->process_name ?? $approval->process_code }}</td>
                                                            <td class="px-4 py-2 text-sm">{{ $approval->user->name ?? 'N/A' }}</td>
                                                            <td class="px-4 py-2 text-sm">{{ $approval->created_at ? $approval->created_at->format('Y-m-d H:i') : '' }}</td>
                                                            <td class="px-4 py-2 text-sm">
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                                    Pending
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <svg class="w-16 h-16 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-yellow-700">No pending approvals to process</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @break
                    @case(4)
                        {{-- Roles & Permissions Section --}}
                        <div class="p-6">
                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Roles & Permissions</h3>
                                <div class="h-32 flex items-center justify-center bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">Roles and permissions management coming soon.</p>
                                </div>
                            </div>
                        </div>
                        @break
                    @case(5)
                        {{-- Reports Section --}}
                        <div class="p-6">
                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Approval Reports</h3>
                                <div class="h-32 flex items-center justify-center bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">Report generation coming soon.</p>
                                </div>
                            </div>
                        </div>
                        @break
                    @default
                        <div class="p-6">
                            <div class="text-center text-gray-500">Select a section to get started.</div>
                        </div>
                @endswitch
                
                {{-- Modal Form --}}
                @if($showForm)
                    @include('livewire.approvals._approval-flow-form')
                @endif
            </div>
        </div>
    </div>
</div>

