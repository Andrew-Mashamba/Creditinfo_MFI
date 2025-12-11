<div>
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
            <button wire:click="$set('message', null)" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
            <button wire:click="$set('error', null)" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Header Section with Stats -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Budget Items Management</h2>
                <p class="text-gray-600 mt-1">Create, manage, and track budget items and allocations</p>
            </div>
            <button wire:click="menuItemClick" 
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-600 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Budget Item
            </button>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                <div class="flex items-center">
                    <div class="p-2 bg-red-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-red-600">Total Budget Items</p>
                        <p class="text-2xl font-bold text-red-900">{{ App\Models\BudgetManagement::count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="flex items-center">
                    <div class="p-2 bg-green-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">Approved</p>
                        <p class="text-2xl font-bold text-green-900">{{ App\Models\BudgetManagement::where('approval_status', 'APPROVED')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-600">Pending</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ App\Models\BudgetManagement::where('approval_status', 'PENDING')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600">Total Budget</p>
                        @php
                            $totals = App\Models\BudgetManagement::selectRaw("COALESCE(SUM(CAST(revenue as numeric)),0) as total_rev, COALESCE(SUM(CAST(capital_expenditure as numeric)),0) as total_cap")->first();
                            $totalBudget = ($totals->total_rev ?? 0) + ($totals->total_cap ?? 0);
                        @endphp
                        <p class="text-2xl font-bold text-purple-900">{{ number_format($totalBudget, 2) }} TZS</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Budget Items Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table Header with Filters -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Budget Items</h3>
                    <p class="text-sm text-gray-600">Manage and track all budget items</p>
                </div>
                
                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.debounce.300ms="search" 
                               type="text" 
                               placeholder="Search budget items..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    </div>

                    <!-- Status Filter -->
                    <select wire:model="statusFilter" class="block w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="PENDING">Pending</option>
                        <option value="APPROVED">Approved</option>
                        <option value="REJECTED">Rejected</option>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>

                    <!-- Export Button -->
                    <button wire:click="exportToExcel" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th wire:click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                ID
                                @if($sortField === 'id')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('budget_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Budget Name
                                @if($sortField === 'budget_name')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('revenue')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Annual Budget
                                @if($sortField === 'revenue')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('capital_expenditure')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Monthly Allocation
                                @if($sortField === 'capital_expenditure')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Budget Utilization
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Expense Account
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Budget Period
                        </th>
                        <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Status
                                @if($sortField === 'status')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('approval_status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Approval Status
                                @if($sortField === 'approval_status')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            <div class="flex items-center">
                                Created Date
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($budgetItems as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $item->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $item->budget_name ?? 'N/A' }}</div>
                                    @if($item->notes)
                                        <div class="text-sm text-gray-500">{{ Str::limit($item->notes, 50) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->revenue ?? 0, 2) }} TZS
                                <div class="text-xs text-gray-500">Annual</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->capital_expenditure ?? 0, 2) }} TZS
                                <div class="text-xs text-gray-500">Monthly</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $totalBudget = ($item->revenue ?? 0) + ($item->capital_expenditure ?? 0);
                                    $spentAmount = $item->spent_amount ?? 0;
                                    $utilization = $totalBudget > 0 ? round(($spentAmount / $totalBudget) * 100, 1) : 0;
                                    $available = $totalBudget - $spentAmount;
                                    
                                    $colorClass = 'bg-green-500';
                                    if ($utilization > 100) $colorClass = 'bg-red-500';
                                    elseif ($utilization >= 90) $colorClass = 'bg-orange-500';
                                    elseif ($utilization >= 80) $colorClass = 'bg-yellow-500';
                                    elseif ($utilization >= 50) $colorClass = 'bg-red-500';
                                @endphp
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-xs font-medium text-gray-700">{{ $utilization }}%</span>
                                        <span class="text-xs text-gray-500">{{ number_format($spentAmount, 0) }} / {{ number_format($totalBudget, 0) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $colorClass }} h-2 rounded-full transition-all duration-300" style="width: {{ min($utilization, 100) }}%"></div>
                                    </div>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-xs text-gray-500">Available:</span>
                                        <span class="text-xs font-medium {{ $available < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($available, 0) }} TZS</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($item->expenseAccount)
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->expenseAccount->account_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->expenseAccount->account_number }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-400">Not assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($item->start_date && $item->end_date)
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($item->start_date)->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">to {{ \Carbon\Carbon::parse($item->end_date)->format('M d, Y') }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($item->status === 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($item->status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($item->status === 'INACTIVE') bg-gray-100 text-gray-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $item->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($item->approval_status === 'APPROVED') bg-green-100 text-green-800
                                    @elseif($item->approval_status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @elseif($item->approval_status === 'REJECTED') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $item->approval_status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->created_at ? $item->created_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button wire:click="viewItem({{ $item->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                            title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    <button wire:click="requestEdit({{ $item->id }})" 
                                            @if($item->is_locked) disabled @endif
                                            class="@if($item->is_locked) text-gray-400 cursor-not-allowed @else text-green-600 hover:text-green-900 @endif transition-colors duration-200"
                                            title="@if($item->is_locked) Locked: {{ $item->locked_reason }} @else Request Edit @endif">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="requestDelete({{ $item->id }})" 
                                            @if($item->is_locked) disabled @endif
                                            class="@if($item->is_locked) text-gray-400 cursor-not-allowed @else text-red-600 hover:text-red-900 @endif transition-colors duration-200"
                                            title="@if($item->is_locked) Locked: {{ $item->locked_reason }} @else Request Delete @endif">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No budget items found</h3>
                                    <p class="text-gray-500 mb-4">Get started by creating your first budget item.</p>
                                    <button wire:click="menuItemClick" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Create Budget Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($budgetItems->hasPages())
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ $budgetItems->firstItem() }} to {{ $budgetItems->lastItem() }} of {{ $budgetItems->total() }} results
                    </div>
                    <div>
                        {{ $budgetItems->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- New Budget Item Modal -->
    @if($this->new_budget_item)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Create New Budget Item
                                </h3>
                                <div class="mt-4 space-y-4">
                                    @if (session()->has('message'))
                                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                            <span class="block sm:inline">{{ session('message') }}</span>
                                        </div>
                                    @endif

                                    <div>
                                        <label for="budget_name" class="block text-sm font-medium text-gray-700">Budget Name</label>
                                        <input type="text" wire:model="budget_name" id="budget_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Enter budget name" readonly>
                                        @error('budget_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700">Budget Start Date *</label>
                                            <input type="date" wire:model="start_date" id="start_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" onchange="adjustToFirstDay(this)">
                                            <p class="mt-1 text-sm text-gray-500">Must be the 1st day of the month</p>
                                            @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700">Budget End Date *</label>
                                            <input type="date" wire:model="end_date" id="end_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" onchange="adjustToLastDay(this)">
                                            <p class="mt-1 text-sm text-gray-500">Must be the last day of the month</p>
                                            @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="annual_budget" class="block text-sm font-medium text-gray-700">Annual Budget Amount (TZS) *</label>
                                        <input type="number" step="0.01" wire:model="annual_budget" id="annual_budget" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="0.00">
                                        <p class="mt-1 text-sm text-gray-500">Total budget allocated for this expense item for the entire year</p>
                                        @error('annual_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="monthly_allocation" class="block text-sm font-medium text-gray-700">Monthly Allocation (TZS)</label>
                                        <input type="number" step="0.01" wire:model="monthly_allocation" id="monthly_allocation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="0.00" readonly>
                                        <p class="mt-1 text-sm text-gray-500">Automatically calculated: Annual Budget ÷ 12 months</p>
                                        @error('monthly_allocation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="expense_account_id" class="block text-sm font-medium text-gray-700">Expense Account *</label>
                                        <select wire:model="expense_account_id" id="expense_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            <option value="">Select an expense account *</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                            @endforeach
                                        </select>
                                        @error('expense_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Enter any additional notes..."></textarea>
                                        @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create Budget Item
                        </button>
                        <button type="button" wire:click="$toggle('new_budget_item')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View Budget Item Modal -->
    @if($viewModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeViewModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Budget Item Details
                                </h3>
                                @if($selectedItem)
                                    <div class="mt-4 space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Budget Name</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->budget_name ?? 'N/A' }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1
                                                    @if($selectedItem->status === 'ACTIVE') bg-green-100 text-green-800
                                                    @elseif($selectedItem->status === 'PENDING') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $selectedItem->status ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Annual Budget (TZS)</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ number_format($selectedItem->revenue ?? 0, 2) }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Monthly Allocation (TZS)</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ number_format($selectedItem->capital_expenditure ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->start_date ? \Carbon\Carbon::parse($selectedItem->start_date)->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->end_date ? \Carbon\Carbon::parse($selectedItem->end_date)->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Expense Account</label>
                                            @if($selectedItem->expenseAccount)
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->expenseAccount->account_name }} ({{ $selectedItem->expenseAccount->account_number }})</p>
                                            @else
                                                <p class="mt-1 text-sm text-gray-500">Not assigned</p>
                                            @endif
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Approval Status</label>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1
                                                @if($selectedItem->approval_status === 'APPROVED') bg-green-100 text-green-800
                                                @elseif($selectedItem->approval_status === 'PENDING') bg-yellow-100 text-yellow-800
                                                @elseif($selectedItem->approval_status === 'REJECTED') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $selectedItem->approval_status ?? 'N/A' }}
                                            </span>
                                        </div>
                                        
                                        @if($selectedItem->notes)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->notes }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Created Date</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->created_at ? $selectedItem->created_at->format('M d, Y H:i') : 'N/A' }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $selectedItem->updated_at ? $selectedItem->updated_at->format('M d, Y H:i') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeViewModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Request Modal -->
    @if($editRequestModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Request Budget Edit
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="edit_budget_name" class="block text-sm font-medium text-gray-700">Budget Name</label>
                                        <input type="text" wire:model="edit_budget_name" id="edit_budget_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Enter budget name" readonly>
                                        @error('edit_budget_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="edit_start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                            <input type="date" wire:model="edit_start_date" id="edit_start_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" onchange="adjustToFirstDay(this)">
                                            <p class="mt-1 text-sm text-gray-500">Must be the 1st day of the month</p>
                                            @error('edit_start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="edit_end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                                            <input type="date" wire:model="edit_end_date" id="edit_end_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" onchange="adjustToLastDay(this)">
                                            <p class="mt-1 text-sm text-gray-500">Must be the last day of the month</p>
                                            @error('edit_end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="edit_annual_budget" class="block text-sm font-medium text-gray-700">Annual Budget Amount (TZS) *</label>
                                        <input type="number" step="0.01" wire:model="edit_annual_budget" id="edit_annual_budget" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="0.00">
                                        <p class="mt-1 text-sm text-gray-500">Total budget allocated for this expense item for the entire year</p>
                                        @error('edit_annual_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_monthly_allocation" class="block text-sm font-medium text-gray-700">Monthly Allocation (TZS)</label>
                                        <input type="number" step="0.01" wire:model="edit_monthly_allocation" id="edit_monthly_allocation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm bg-gray-100" placeholder="0.00" readonly>
                                        <p class="mt-1 text-sm text-gray-500">Automatically calculated: Annual Budget ÷ 12 months</p>
                                        @error('edit_monthly_allocation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_expense_account_id" class="block text-sm font-medium text-gray-700">Expense Account *</label>
                                        <select wire:model="edit_expense_account_id" id="edit_expense_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                            <option value="">Select an expense account</option>
                                            @foreach($expenseAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                            @endforeach
                                        </select>
                                        @error('edit_expense_account_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea wire:model="edit_notes" id="edit_notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Enter any additional notes..."></textarea>
                                        @error('edit_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="edit_justification" class="block text-sm font-medium text-gray-700">Justification for Edit *</label>
                                        <textarea wire:model="edit_justification" id="edit_justification" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Please provide a justification for this edit request..." required></textarea>
                                        @error('edit_justification') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        <p class="mt-1 text-sm text-gray-500">This justification will be reviewed during the approval process</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="submitEditRequest" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit Edit Request
                        </button>
                        <button type="button" wire:click="closeEditRequestModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Request Modal -->
    @if($deleteRequestModal)
        <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Request Budget Deletion
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please provide a justification for deleting this budget item. This request will be sent for approval.
                                    </p>
                                    @if($selectedItem)
                                        <div class="mt-3 bg-gray-50 rounded-lg p-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $selectedItem->budget_name }}</p>
                                            <p class="text-sm text-gray-500">Annual Budget: {{ number_format($selectedItem->revenue ?? 0, 2) }} TZS</p>
                                        </div>
                                    @endif
                                    <div class="mt-4">
                                        <label for="delete_justification" class="block text-sm font-medium text-gray-700">Justification for Deletion *</label>
                                        <textarea wire:model="delete_justification" id="delete_justification" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Please explain why this budget item should be deleted..." required></textarea>
                                        @error('delete_justification') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        <p class="mt-1 text-sm text-gray-500">This justification will be reviewed during the approval process</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="submitDeleteRequest" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit Delete Request
                        </button>
                        <button type="button" wire:click="closeDeleteRequestModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Because she competes with no one, no one can compete with her. --}}
</div>

<script nonce="{{ csp_nonce() }}">
function adjustToFirstDay(input) {
    if (input.value) {
        const date = new Date(input.value);
        date.setDate(1); // Set to first day of the month
        input.value = date.toISOString().split('T')[0];
        
        // Trigger Livewire update
        if (input.id === 'start_date') {
            @this.set('start_date', input.value);
        } else if (input.id === 'edit_start_date') {
            @this.set('edit_start_date', input.value);
        }
    }
}

function adjustToLastDay(input) {
    if (input.value) {
        const date = new Date(input.value);
        // Move to next month, then back one day to get last day of current month
        date.setMonth(date.getMonth() + 1, 0);
        input.value = date.toISOString().split('T')[0];
        
        // Trigger Livewire update
        if (input.id === 'end_date') {
            @this.set('end_date', input.value);
        } else if (input.id === 'edit_end_date') {
            @this.set('edit_end_date', input.value);
        }
    }
}
</script>
