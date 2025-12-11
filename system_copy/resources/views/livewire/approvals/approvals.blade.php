{{-- Enhanced Approvals Manager with Modern UI --}}


<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">

<style nonce="{{ csp_nonce() }}">
    /* Custom pagination styling */
    .pagination {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .pagination .page-item {
        list-style: none;
    }
    
    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
    }
    
    .pagination .page-link:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
        color: #111827;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: #ffffff;
    }
    
    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f9fafb;
    }
    
    .pagination .page-item.disabled .page-link:hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
        color: #374151;
    }
</style>
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-900 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Approval Management</h1>
                        <p class="text-gray-600 mt-1">Manage and process approval requests across all departments</p>
                    </div>
                </div>
                
                <!-- Enhanced Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Pending Count -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $approvals->where('process_status', 'PENDING')->count() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Urgent/Overdue -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Urgent</p>
                                @php
                                    $urgentCount = $approvals->where('process_status', 'PENDING')
                                        ->filter(function($approval) {
                                            return $approval->created_at && $approval->created_at->diffInDays(now()) > 3;
                                        })->count();
                                @endphp
                                <p class="text-lg font-semibold text-red-600">{{ $urgentCount }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- This Week's Approved -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">This Week</p>
                                @php
                                    $thisWeekApproved = $approvals->where('process_status', 'APPROVED')
                                        ->filter(function($approval) {
                                            return $approval->updated_at && $approval->updated_at->isCurrentWeek();
                                        })->count();
                                @endphp
                                <p class="text-lg font-semibold text-green-600">{{ $thisWeekApproved }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Average Time -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Avg. Time</p>
                                @php
                                    $approvedApprovals = $approvals->where('process_status', 'APPROVED');
                                    $avgTime = $approvedApprovals->isNotEmpty() 
                                        ? $approvedApprovals->avg(function($approval) {
                                            if ($approval->created_at && $approval->updated_at) {
                                                return $approval->created_at->diffInDays($approval->updated_at);
                                            }
                                            return 0;
                                        }) : 0;
                                @endphp
                                <p class="text-lg font-semibold text-blue-600">{{ number_format($avgTime, 1) }}d</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="mb-6 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Breakdown by Category</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $categoryBreakdown = $approvals->groupBy('process_code')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'pending' => $group->where('process_status', 'PENDING')->count(),
                            'name' => $group->first()->process_name ?? 'Unknown'
                        ];
                    });
                    
                    // Define professional color schemes for different categories
                    $categoryColors = [
                        'LOAN_APP' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'icon' => 'text-blue-600'],
                        'LOAN_DISB' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-blue-900', 'icon' => 'text-indigo-600'],
                        'MEMBER_REG' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'icon' => 'text-green-600'],
                        'BRANCH_CREATE' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'icon' => 'text-purple-600'],
                        'LARGE_WD' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'icon' => 'text-orange-600'],
                        'FIXED_DEP' => ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'text' => 'text-teal-700', 'icon' => 'text-teal-600'],
                        'FUND_TRANS' => ['bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-700', 'icon' => 'text-cyan-600'],
                        'SHARE_WD' => ['bg' => 'bg-pink-50', 'border' => 'border-pink-200', 'text' => 'text-pink-700', 'icon' => 'text-pink-600'],
                        'default' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'icon' => 'text-gray-600']
                    ];
                    
                    // Define icons for different categories
                    $categoryIcons = [
                        'LOAN_APP' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'LOAN_DISB' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                        'MEMBER_REG' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                        'BRANCH_CREATE' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                        'LARGE_WD' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'default' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                    ];
                @endphp
                @foreach($categoryBreakdown->take(4) as $code => $data)
                @php
                    $colors = $categoryColors[$code] ?? $categoryColors['default'];
                    $iconPath = $categoryIcons[$code] ?? $categoryIcons['default'];
                @endphp
                <div class="relative {{ $colors['bg'] }} {{ $colors['border'] }} border-2 rounded-xl p-4 hover:shadow-md transition-all duration-200 group">
                    <!-- Icon -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 bg-white rounded-lg {{ $colors['border'] }} border">
                            <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                            </svg>
                        </div>
                        @if($data['pending'] > 0)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1 animate-pulse"></div>
                                {{ $data['pending'] }} pending
                            </span>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="space-y-1">
                        <div class="text-sm font-medium {{ $colors['text'] }} leading-tight">{{ $data['name'] }}</div>
                        <div class="flex items-baseline justify-between">
                            <div class="text-2xl font-bold text-gray-900">{{ $data['count'] }}</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide">{{ $code }}</div>
                        </div>
                    </div>
                    
                    <!-- Hover effect indicator -->
                    <div class="absolute inset-0 rounded-xl border-2 border-transparent group-hover:{{ $colors['border'] }} group-hover:border-opacity-50 transition-all duration-200"></div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Search Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="searchTerm" 
                            placeholder="Search approvals..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search approvals"
                        />
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Filters</h3>
                    <div class="space-y-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model.live="filterStatus" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">All Status</option>
                                <option value="PENDING">Pending</option>
                                <option value="APPROVED">Approved</option>
                                <option value="REJECTED">Rejected</option>
                            </select>
                        </div>

                        <!-- Process Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Process Type</label>
                            <select wire:model.live="filterProcess" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="all">All Processes</option>
                                @foreach($processCodes as $process)
                                    <option value="{{ $process->process_code }}">{{ $process->process_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                <input type="date" wire:model.live="filterDateFrom" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                                <input type="date" wire:model.live="filterDateTo" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Quick Actions -->
                <div class="p-4 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click.stop="resetFilters" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Filters
                        </button>
                        <button wire:click.stop="toggleFilters" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            {{ $showFilters ? 'Hide' : 'Show' }} Advanced
                        </button>
                        <button wire:click.stop="$set('filterStatus', 'PENDING')" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Show Pending Only
                        </button>
                        <button onclick="window.print()" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print/Export
                        </button>
                    </div>
                </div>

                <!-- Performance Stats -->
                <div class="p-4 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Performance</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">This Month</span>
                                                         @php
                                 $thisMonthApproved = $approvals->where('process_status', 'APPROVED')
                                     ->filter(function($approval) {
                                         return $approval->updated_at && $approval->updated_at->isCurrentMonth();
                                     })->count();
                             @endphp
                            <span class="text-sm font-semibold text-green-600">{{ $thisMonthApproved }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">This Month Rejected</span>
                                                         @php
                                 $thisMonthRejected = $approvals->where('process_status', 'REJECTED')
                                     ->filter(function($approval) {
                                         return $approval->updated_at && $approval->updated_at->isCurrentMonth();
                                     })->count();
                             @endphp
                            <span class="text-sm font-semibold text-red-600">{{ $thisMonthRejected }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">SLA Performance</span>
                                                         @php
                                 $onTimeApprovals = $approvals->where('process_status', 'APPROVED')
                                     ->filter(function($approval) {
                                         return $approval->created_at && $approval->updated_at && 
                                                $approval->created_at->diffInDays($approval->updated_at) <= 3;
                                     })->count();
                                 $totalApproved = $approvals->where('process_status', 'APPROVED')->count();
                                 $slaPerformance = $totalApproved > 0 ? round(($onTimeApprovals / $totalApproved) * 100) : 0;
                             @endphp
                            <span class="text-sm font-semibold {{ $slaPerformance >= 80 ? 'text-green-600' : ($slaPerformance >= 60 ? 'text-yellow-600' : 'text-red-600') }}">{{ $slaPerformance }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Content Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Approval Requests</h2>
                                <p class="text-gray-600 mt-1">Manage and process approval requests</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <select wire:model.live="perPage" class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Approvals Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th wire:click.stop="sortBy('process_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center space-x-1">
                                            <span>Process</span>
                                            @if($sortField === 'process_name')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    </th>
                                    <th wire:click.stop="sortBy('process_description')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center space-x-1">
                                            <span>Description</span>
                                            @if($sortField === 'process_description')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Checker</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Second Checker</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                                    <th wire:click.stop="sortBy('process_status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center space-x-1">
                                            <span>Status</span>
                                            @if($sortField === 'process_status')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @endif
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($approvals as $approval)
                                    @php
                                        $config = $approval->processConfig;
                                        if (!$config) {
                                            Log::error('Process config not found for approval ID: ' . $approval->id);
                                            continue;
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ $approval->process_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $approval->process_code }}</div>
                                                    @php
                                                        $daysSinceSubmitted = $approval->created_at ? $approval->created_at->diffInDays(now()) : 0;
                                                    @endphp
                                                    @if($daysSinceSubmitted > 3 && $approval->process_status == 'PENDING')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ $daysSinceSubmitted }}d overdue
                                                        </span>
                                                    @elseif($daysSinceSubmitted > 1 && $approval->process_status == 'PENDING')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ $daysSinceSubmitted }}d old
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $approval->process_description }}</div>
                                            <div class="text-sm text-gray-500">{{ $approval->approval_process_description }}</div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Submitted: {{ $approval->created_at ? $approval->created_at->format('M j, Y') : 'Date not available' }}
                                                @if($approval->user)
                                                    by {{ $approval->user->name }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($config->requires_first_checker)
                                                @if($approval->first_checker_id)
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->first_checker_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-900">{{ $approval->firstChecker->name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $approval->first_checker_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $approval->first_checker_status }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->first_checker_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <span class="text-sm text-gray-500">Pending</span>
                                                    @php
                                                        $userRoles = auth()->user()->roles()->select('roles.id')->pluck('id')->toArray();
                                                        $approverRoles = $config->approver_roles ?? [];
                                                        $userRoleIds = array_map('intval', $userRoles);
                                                        $approverRoleIds = array_map('intval', $approverRoles);
                                                        $hasRequiredRole = !empty(array_intersect($userRoleIds, $approverRoleIds));
                                                    @endphp
                                                    @if($hasRequiredRole)
                                                        <div class="mt-2">
                                                            <div class="inline-flex rounded-md shadow-xs" role="group">
                                                                @if($permissions['canView'] ?? false)
                                                                <button wire:click.stop="showViewChangeDetailsModal('{{ $approval->process_code }}', '{{ $approval->process_id }}')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    View Details
                                                                </button>
                                                                @endif
                                                                @if($permissions['canApprove'] ?? false)
                                                                <button wire:click.stop="showApproveConfirmationModal({{ $approval->id }},'1')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Approve
                                                                </button>
                                                                @endif
                                                                @if($permissions['canReject'] ?? false)
                                                                <button wire:click.stop="showRejectAndCommentsConfirmationModal({{ $approval->id }})" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Reject
                                                                </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($config->requires_second_checker)
                                                @if($approval->second_checker_id)
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->second_checker_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-900">{{ $approval->secondChecker->name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $approval->second_checker_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $approval->second_checker_status }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->second_checker_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <span class="text-sm text-gray-500">Pending</span>
                                                    @php
                                                        $userRoles = auth()->user()->roles()->select('roles.id')->pluck('id')->toArray();
                                                        $approverRoles = $config->approver_roles ?? [];
                                                        $userRoleIds = array_map('intval', $userRoles);
                                                        $approverRoleIds = array_map('intval', $approverRoles);
                                                        $hasRequiredRole = !empty(array_intersect($userRoleIds, $approverRoleIds));
                                                    @endphp
                                                    @if($hasRequiredRole)
                                                        <div class="mt-2">
                                                            <div class="inline-flex rounded-md shadow-xs" role="group">
                                                                @if($permissions['canView'] ?? false)
                                                                <button wire:click.stop="showViewChangeDetailsModal('{{ $approval->process_code }}', '{{ $approval->process_id }}')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    View Details
                                                                </button>
                                                                @endif
                                                                @if($permissions['canApprove'] ?? false)
                                                                <button wire:click.stop="showApproveConfirmationModal({{ $approval->id }},'2')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Approve
                                                                </button>
                                                                @endif
                                                                @if($permissions['canReject'] ?? false)
                                                                <button wire:click.stop="showRejectAndCommentsConfirmationModal({{ $approval->id }})" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Reject
                                                                </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($config->requires_approver)
                                                @if($approval->approver_id)
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->approver_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <div class="text-sm text-gray-900">{{ $approval->approver->name ?? 'N/A' }}</div>
                                                    <div class="text-sm text-gray-500">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $approval->approval_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $approval->approval_status }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-900">
                                                        Role : {{ implode(', ', $config->approver_role_names) ?: 'N/A' }}
                                                    </div>
                                                    <span class="text-sm text-gray-500">Pending</span>
                                                    @php
                                                        $userRoles = auth()->user()->roles()->select('roles.id')->pluck('id')->toArray();
                                                        $approverRoles = $config->approver_roles ?? [];
                                                        $userRoleIds = array_map('intval', $userRoles);
                                                        $approverRoleIds = array_map('intval', $approverRoles);
                                                        $hasRequiredRole = !empty(array_intersect($userRoleIds, $approverRoleIds));
                                                    @endphp
                                                    @if($hasRequiredRole)
                                                        <div class="mt-2">
                                                            <div class="inline-flex rounded-md shadow-xs" role="group">
                                                                <button wire:click.stop="showViewChangeDetailsModal('{{ $approval->process_code }}', '{{ $approval->process_id }}')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    View Details
                                                                </button>
                                                                <button wire:click.stop="showApproveConfirmationModal({{ $approval->id }},'3')" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Approve
                                                                </button>
                                                                <button wire:click.stop="showRejectAndCommentsConfirmationModal({{ $approval->id }})" type="button" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-blue-500 dark:focus:text-white">
                                                                    Reject
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $approval->process_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                   ($approval->process_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $approval->process_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            <div class="text-center py-8">
                                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="text-gray-500">No approval requests found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Enhanced Pagination -->
                    <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button wire:click.stop="previousPage" 
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $approvals->onFirstPage() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $approvals->onFirstPage() ? 'disabled' : '' }}>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                            <button wire:click.stop="nextPage" 
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $approvals->hasMorePages() ? '' : 'opacity-50 cursor-not-allowed' }}"
                                {{ $approvals->hasMorePages() ? '' : 'disabled' }}>
                                Next
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ $approvals->firstItem() ?? 0 }}</span>
                                    to
                                    <span class="font-medium">{{ $approvals->lastItem() ?? 0 }}</span>
                                    of
                                    <span class="font-medium">{{ $approvals->total() }}</span>
                                    results
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                {{ $approvals->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- View Details Modal -->
    @if($showViewDetailsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all w-4/5 max-w-6xl">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900" id="modal-title">
                                    Approval Details
                                </h3>
                                <p class="text-gray-500 text-sm">Comprehensive view of approval request information</p>
                            </div>
                        </div>
                        <button wire:click.stop="closeViewDetailsModal" type="button" class="text-gray-900 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 rounded-md p-1 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="bg-gray-50 p-6 max-h-[70vh] overflow-y-auto">
                    @if($selectedApprovalId)
                        @php
                            $approval = $approvals->firstWhere('id', $selectedApprovalId);
                        @endphp
                        @if($approval)
                            <div class="space-y-6">
                                <!-- Basic Information Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Process Information Card -->
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                                        <div class="flex items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-lg font-semibold text-gray-900">Process Information</h4>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Process Code:</span>
                                                <span class="text-sm font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded">{{ $approval->process_code }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Process Name:</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $approval->process_name }}</span>
                                            </div>
                                            <div class="py-2">
                                                <span class="text-sm font-medium text-gray-600 block mb-1">Description:</span>
                                                <span class="text-sm text-gray-700">{{ $approval->process_description }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Requester Information Card -->
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                                        <div class="flex items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-lg font-semibold text-gray-900">Requester</h4>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Name:</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $approval->user->name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Email:</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $approval->user->email ?? 'N/A' }}</span>
                                            </div>
                                            <div class="py-2">
                                                <span class="text-sm font-medium text-gray-600 block mb-1">Submitted:</span>
                                                <span class="text-sm text-gray-700">{{ $approval->created_at ? $approval->created_at->format('M j, Y \a\t g:i A') : 'Date not available' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Information Card -->
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                                        <div class="flex items-center mb-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-lg font-semibold text-gray-900">Status</h4>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Current Status:</span>
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $approval->process_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 
                                                       ($approval->process_status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $approval->process_status }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                                <span class="text-sm font-medium text-gray-600">Last Updated:</span>
                                                <span class="text-sm font-semibold text-gray-900">{{ $approval->updated_at ? $approval->updated_at->format('M j, Y \a\t g:i A') : 'N/A' }}</span>
                                            </div>
                                            @php
                                                $daysSinceSubmitted = $approval->created_at ? $approval->created_at->diffInDays(now()) : 0;
                                            @endphp
                                            <div class="py-2">
                                                <span class="text-sm font-medium text-gray-600 block mb-1">Age:</span>
                                                <span class="text-sm font-semibold {{ $daysSinceSubmitted > 3 ? 'text-red-600' : ($daysSinceSubmitted > 1 ? 'text-yellow-600' : 'text-green-600') }}">
                                                    {{ $daysSinceSubmitted }} day{{ $daysSinceSubmitted != 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Related Data Section -->
                                @if($approval->process_code && $approval->process_id)
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-lg font-semibold text-gray-900">Related Data</h4>
                                                    <p class="text-sm text-gray-600">Associated data for this approval request</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            @php
                                                $tableData = null;
                                                switch($approval->process_code) {
                                                    case 'MEMBER_REG':
                                                        $tableData = \App\Models\ClientsModel::find($approval->process_id);
                                                        break;
                                                    case 'BRANCH_CREATE':
                                                    case 'BRANCH_EDIT':
                                                    case 'BRANCH_DEACTIVATE':
                                                        $tableData = \App\Models\Branch::find($approval->process_id);
                                                        break;
                                                    case 'LOAN_APP':
                                                    case 'LOAN_DISB':
                                                    case 'LOAN_REST':
                                                    case 'LOAN_WOFF':
                                                        $tableData = \App\Models\Loan::find($approval->process_id);
                                                        break;
                                                    case 'LARGE_WD':
                                                    case 'FIXED_DEP':
                                                    case 'FUND_TRANS':
                                                    case 'BLOCK_SHARE_ACC':
                                                        $tableData = \App\Models\ShareRegister::select('status', 'share_account_number', 'product_name', 'current_share_balance', 'total_share_value')->find($approval->process_id);                                                        
                                                        break;
                                                    case 'ACTIVATE_SHARE_ACC':
                                                        $tableData = \App\Models\ShareRegister::select('status', 'share_account_number', 'product_name', 'current_share_balance', 'total_share_value')->find($approval->process_id);                                                        
                                                        break;
                                                    case 'SHARE_WD':
                                                    case 'PETTY_CASH':
                                                    case 'OP_EXP':
                                                    case 'CAP_EXP':
                                                    case 'ASSET_PUR':
                                                    case 'INT_RATE':
                                                        $tableData = \App\Models\Transaction::find($approval->process_id);
                                                        break;
                                                    case 'ASSET_DISP':
                                                        $tableData = \App\Models\PPE::find($approval->process_id);
                                                        break;
                                                    case 'PROD_EDIT':
                                                    $tableData = \App\Models\sub_products::find($approval->process_id);
                                                    break;
                                                    case 'PRODUCT_CRE':
                                                    $tableData = \App\Models\sub_products::find($approval->process_id);
                                                    break;
                                                    case 'PROD_DEACTIVATE':
                                                    $tableData = \App\Models\sub_products::find($approval->process_id);
                                                    break;
                                                }
                                            @endphp                                            
                                            @if($tableData)                                            
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach($tableData->toArray() as $key => $value)                                                    
                                                        @if(!in_array($key, ['created_at', 'updated_at', 'deleted_at']))
                                                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                                                                <div class="text-sm font-semibold text-gray-700 mb-2 capitalize">
                                                                    {{ str_replace('_', ' ', $key) }}
                                                                </div>
                                                                <div class="text-sm text-gray-900 break-words">
                                                                    @if(is_array($value))
                                                                        <div class="bg-white rounded border p-2 max-h-32 overflow-y-auto">
                                                                            <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                        </div>
                                                                    @elseif(is_bool($value))
                                                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                            {{ $value ? 'Yes' : 'No' }}
                                                                        </span>
                                                                    @elseif(is_null($value))
                                                                        <span class="text-gray-400 italic">Not set</span>
                                                                    @else
                                                                        <div class="bg-white rounded border p-2">
                                                                            {{ $value }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-8">
                                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                        </svg>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">No related data found</p>
                                                    <p class="text-gray-400 text-sm">This approval request doesn't have associated data</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Changes Section -->
                                @if($approval->edit_package)
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-6 py-4 border-b border-yellow-200">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-lg font-semibold text-gray-900">Changes Made</h4>
                                                    <p class="text-sm text-gray-600">Track modifications in this approval request</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Field</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Previous Value</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">New Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @php
                                                        $editPackage = is_string($approval->edit_package) ? json_decode($approval->edit_package, true) : $approval->edit_package;
                                                    @endphp
                                                    @if(!empty($editPackage) && is_array($editPackage))
                                                        @foreach($editPackage as $field => $values)
                                                            @if(isset($values['old']) && isset($values['new']))
                                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                                        <div class="text-sm font-semibold text-gray-900 capitalize">
                                                                            {{ str_replace('_', ' ', $field) }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-6 py-4">
                                                                        <div class="text-sm text-gray-700">
                                                                            @if(is_array($values['old']))
                                                                                <div class="bg-red-50 border border-red-200 rounded p-2 max-h-24 overflow-y-auto">
                                                                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($values['old'], JSON_PRETTY_PRINT) }}</pre>
                                                                                </div>
                                                                            @elseif(is_bool($values['old']))
                                                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                                    {{ $values['old'] ? 'Yes' : 'No' }}
                                                                                </span>
                                                                            @elseif(is_null($values['old']))
                                                                                <span class="text-gray-400 italic">Not set</span>
                                                                            @else
                                                                                <div class="bg-red-50 border border-red-200 rounded p-2">
                                                                                    {{ $values['old'] }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td class="px-6 py-4">
                                                                        <div class="text-sm text-gray-700">
                                                                            @if(is_array($values['new']))
                                                                                <div class="bg-green-50 border border-green-200 rounded p-2 max-h-24 overflow-y-auto">
                                                                                    <pre class="whitespace-pre-wrap text-xs">{{ json_encode($values['new'], JSON_PRETTY_PRINT) }}</pre>
                                                                                </div>
                                                                            @elseif(is_bool($values['new']))
                                                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                                    {{ $values['new'] ? 'Yes' : 'No' }}
                                                                                </span>
                                                                            @elseif(is_null($values['new']))
                                                                                <span class="text-gray-400 italic">Not set</span>
                                                                            @else
                                                                                <div class="bg-green-50 border border-green-200 rounded p-2">
                                                                                    {{ $values['new'] }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="3" class="px-6 py-8 text-center">
                                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                    </svg>
                                                                </div>
                                                                <p class="text-gray-500 font-medium">No changes to display</p>
                                                                <p class="text-gray-400 text-sm">This approval request has no modifications</p>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">Approval details not found</p>
                                <p class="text-gray-400 text-sm">The requested approval information could not be retrieved</p>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-end space-x-3">
                        <button wire:click.stop="closeViewDetailsModal" type="button" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 font-medium">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

   <!-- Approval Modal -->
    @if($showApproveModal)
        <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white backdrop-blur-md rounded-2xl shadow-2xl p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Confirm Approval</h3>
                <p class="text-sm text-gray-700 mb-6">
                    Are you sure you want to approve this request? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click.stop="closeApproveModal"
                        class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 text-sm font-medium">
                        Cancel
                    </button>
                    <button wire:click.stop="approve"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                        Approve
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white backdrop-blur-md rounded-2xl shadow-2xl p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Reject Request</h3>

                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for
                        Rejection</label>
                    <textarea wire:model="rejection_reason" id="rejection_reason" rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                    @error('rejection_reason')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click.stop="closeRejectModal"
                        class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 text-sm font-medium">
                        Cancel
                    </button>
                    <button wire:click.stop="reject"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                        Reject
                    </button>
                </div>
            </div>
        </div>
    @endif


    <!-- Comment Modal -->
    @if($showCommentModal)
        <div class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
            style="width: 70%;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Add Comment
                                </h3>
                                <div class="mt-4">
                                    <textarea wire:model="comment" rows="4"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter your comment here..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click.stop="$set('showCommentModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Include Enhanced Loan Assessment Modal -->
    @include('livewire.approvals.approvals-loan-modal')

    <!-- Old Loan Assessment Modal (Deprecated - kept for reference) -->
    @if(false)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4 aria-labelledby="
            modal-title" role="dialog" aria-modal="true">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[95vh] ">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-black" id="modal-title">
                                        Comprehensive Loan Assessment Review
                                    </h3>
                                    <p class="text-sm text-blue-400">
                                        Detailed assessment for approval decision - {{ $loanData->client_number ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button wire:click.stop="closeLoanAssessmentModal" type="button"
                                class="text-white hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-md p-1">
                                <svg class="h-6 w-6" fill="none" stroke="red" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-6 max-h-96 overflow-y-auto">
                        @if($loanData)
                                    <div class="space-y-6">
                                        <!-- Warning Alert for Missing Assessment Data -->
                                        @if(empty($assessmentData))
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-sm font-medium text-yellow-800">
                                                            Assessment Data Not Available
                                                        </h3>
                                                        <div class="mt-2 text-sm text-yellow-700">
                                                            <p>
                                                                This loan was sent for approval without completing the full assessment
                                                                process.
                                                                The information shown below is based on basic loan data only.
                                                                For a complete assessment review, please ensure the loan assessment is
                                                                completed first.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Executive Summary Card -->
                                        <div
                                            class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-xl p-6 shadow-sm">
                                            <div class="flex items-center mb-4">
                                                <div class="flex-shrink-0">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <h4 class="text-lg font-semibold text-gray-900">Executive Summary</h4>
                                                    <p class="text-sm text-gray-600">Key loan details and assessment overview</p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                                                    <div class="text-sm font-medium text-gray-600 mb-1">Approved Amount</div>
                                                    <div class="text-2xl font-bold text-green-600">
                                                        {{ number_format($loanAmountLimits['approved_amount'] ?? 0, 2) }} TZS
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                                                    <div class="text-sm font-medium text-gray-600 mb-1">Monthly Payment</div>
                                                    <div class="text-2xl font-bold text-blue-600">
                                                        {{ number_format($loanStatistics['monthly_installment'] ?? 0, 2) }} TZS
                                                    </div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 border border-indigo-200">
                                                    <div class="text-sm font-medium text-gray-600 mb-1">Assessment Score</div>
                                                    <div
                                                        class="text-2xl font-bold 
                                                                        {{ ($assessmentSummary['overall_score'] ?? 0) >= 80 ? 'text-green-600' :
                            (($assessmentSummary['overall_score'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ number_format($assessmentSummary['overall_score'] ?? 0, 1) }}%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                        <!-- Top-up/Restructure Information -->
                                        @if(!empty($topUpData) && $topUpData['selected_loan'])
                                            <div
                                                class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-6 shadow-sm">
                                                <div class="flex items-center mb-4">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h4 class="text-lg font-semibold text-gray-900">Top-up Loan Information</h4>
                                                        <p class="text-sm text-gray-600">Details of the existing loan being topped up</p>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-3">
                                                        <div class="flex justify-between items-center py-2 border-b border-yellow-200">
                                                            <span class="text-sm font-medium text-gray-600">Existing Loan ID:</span>
                                                            <span
                                                                class="text-sm font-semibold text-gray-900">{{ $topUpData['selected_loan'] }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center py-2 border-b border-yellow-200">
                                                            <span class="text-sm font-medium text-gray-600">Top-up Amount:</span>
                                                            <span
                                                                class="text-sm font-semibold text-yellow-600">{{ number_format($topUpData['top_up_amount'] ?? 0, 2) }}
                                                                TZS</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if(!empty($restructureData) && $restructureData['restructured_loan'])
                                            <div
                                                class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6 shadow-sm">
                                                <div class="flex items-center mb-4">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h4 class="text-lg font-semibold text-gray-900">Loan Restructuring</h4>
                                                        <p class="text-sm text-gray-600">Details of the loan being restructured</p>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="space-y-3">
                                                        <div class="flex justify-between items-center py-2 border-b border-purple-200">
                                                            <span class="text-sm font-medium text-gray-600">Restructured Loan ID:</span>
                                                            <span
                                                                class="text-sm font-semibold text-gray-900">{{ $restructureData['restructured_loan'] }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center py-2 border-b border-purple-200">
                                                            <span class="text-sm font-medium text-gray-600">Restructure Amount:</span>
                                                            <span
                                                                class="text-sm font-semibold text-purple-600">{{ number_format($restructureData['restructure_amount'] ?? 0, 2) }}
                                                                TZS</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif


                                    </div>
                        @else
                            <div class="flex items-center justify-center py-12">
                                <div class="text-center">
                                    <div
                                        class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-600">Loading assessment data...</p>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Notification System -->
    @if (session()->has('notification'))
    <div class="fixed bottom-0 right-0 m-6 z-50 w-96"> <!-- Increased width to 384px (w-96) -->
        <div class="bg-white shadow-xl rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transition-all duration-300
            {{ session('notification.type') === 'success' ? 'bg-green-50 border-l-4 border-green-400' : 
               (session('notification.type') === 'error' ? 'bg-red-50 border-l-4 border-red-400' : 
               (session('notification.type') === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-400' : 'bg-blue-50 border-l-4 border-blue-400')) }}">
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icon Section -->
                    <div class="flex-shrink-0">
                        @if(session('notification.type') === 'success')
                            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif(session('notification.type') === 'error')
                            <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif(session('notification.type') === 'warning')
                            <svg class="h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>

                        <!-- Message Section -->
                        <div class="ml-3 flex-1 pt-0.5">
                            <p class="text-sm font-medium leading-5
                                    {{ session('notification.type') === 'success' ? 'text-green-800' :
            (session('notification.type') === 'error' ? 'text-red-800' :
                (session('notification.type') === 'warning' ? 'text-yellow-800' : 'text-blue-800')) }}">
                                {{ session('notification.message') }}
                            </p>
                        </div>

                    <!-- Close Button -->
                    <div class="ml-4 flex-shrink-0">
                        <button 
                            wire:click.stop="$set('showNotification', false)" 
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (session()->has('message'))
    <div class="fixed bottom-0 right-0 m-6">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    </div>
    @endif





</div>

