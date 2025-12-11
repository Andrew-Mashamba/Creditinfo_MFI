<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Loan Applications</h2>
            <p class="mt-1 text-sm text-gray-600">Manage and track all loan applications in the system</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <!-- Export Buttons -->
            <div class="flex items-center space-x-2">
                <button wire:click="exportToExcel" class="inline-flex items-center px-3 py-2 border border-green-300 rounded-lg shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Excel
                </button>
                <button wire:click="exportToPdf" class="inline-flex items-center px-3 py-2 border border-red-300 rounded-lg shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    PDF
                </button>
            </div>
            
            <!-- Search Box -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input wire:model.debounce.300ms="search" type="text" placeholder="Search by loan ID, client name, status, amount..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                @endif
            </div>
            
            <!-- Filter Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filters
                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <!-- Filter Dropdown Menu -->
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                <option value="">All Statuses</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                            <select wire:model="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loan Officer</label>
                            <select wire:model="loanOfficerFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}">{{ $officer->first_name }} {{ $officer->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                            <input wire:model="dateFilter" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                        </div>
                        
                        <div class="flex space-x-2">
                            <button wire:click="clearFilters" class="flex-1 px-3 py-2 text-sm text-gray-600 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                                Clear All
                            </button>
                            <button @click="open = false" class="flex-1 px-3 py-2 text-sm text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            // Get approval stage configuration
            $loanProcessConfig = DB::table('process_code_configs')
                ->where('process_code', 'LOAN_APP')
                ->where('is_active', true)
                ->first();
            
            // Get all role names for approval stages
            $allRoleIds = [];
            if ($loanProcessConfig) {
                $firstCheckerRoles = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
                $secondCheckerRoles = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
                $approverRoles = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
                
                $allRoleIds = array_merge($allRoleIds, $firstCheckerRoles, $secondCheckerRoles, $approverRoles);
            }
            
            $allRoleIds = array_unique(array_filter($allRoleIds));
            $roleNames = [];
            if (!empty($allRoleIds)) {
                $roleNames = DB::table('roles')
                    ->whereIn('id', $allRoleIds)
                    ->pluck('name', 'id')
                    ->toArray();
            }
        @endphp
    </div>

    <!-- Enhanced Loans Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" wire:loading.class="opacity-50">
    
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    Recent Applications
                    @if($search)
                        <span class="text-sm font-normal text-red-600">(Searching for "{{ $search }}")</span>
                    @endif
                </h3>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span>Showing {{ $loans->count() }} of {{ $loans->total() }} applications</span>
                    @if($search && $loans->count() == 0)
                        <span class="text-red-600">(No results found)</span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">
                            <button wire:click="sortBy('loan_account_number')" class="flex items-center space-x-1 hover:text-red-600 focus:outline-none">
                                <span>Loan ID</span>
                                @if($sortField === 'loan_account_number')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Product</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Client Name</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Client ID</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Loan Type</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Amount (TZS)</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Term (Months)</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Current Stage</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-r border-gray-200">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 hover:text-red-600 focus:outline-none">
                                <span>Created Date</span>
                                @if($sortField === 'created_at')
                                    @if($sortDirection === 'asc')
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @else
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($loans as $index => $loan)
                        <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-red-50 focus-within:bg-red-100 transition-all duration-200 group">
                            <!-- Loan ID -->
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 border-r border-gray-200">
                                <div class="flex items-center">
                                   
                                    <span class="font-semibold break-all">#{{ $loan->loan_account_number ?? 'N/A' }}</span>
                                </div>
                            </td>
                            
                            <!-- Product -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <div class="font-medium break-words max-w-xs">{{ $loan->loanProduct->sub_product_name ?? 'Unknown Product' }}</div>
                        
                            </td>
                            
                            <!-- Client Name -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium truncate">
                                            @if($loan->client)
                                                {{ $loan->client->first_name ?? 'Unknown' }} {{ $loan->client->last_name ?? 'Client' }}
                                            @else
                                                Unknown Client
                                            @endif
                                        </div>
                                       
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Client ID -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <span class="font-mono text-sm break-all">{{ $loan->client_number ?? 'N/A' }}</span>
                            </td>
                            
                            <!-- Loan Type -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                @php
                                    $loanType = $loan->loan_type_2 ?? 'New';
                                    $typeConfig = [
                                        'New' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200'],
                                        'TopUp' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200'],
                                        'Restructure' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-200'],
                                        'TakeOver' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-200'],
                                    ];
                                    $config = $typeConfig[$loanType] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200'];
                                @endphp
                                {{ strtoupper($loanType) }}
                            </td>
                            
                            <!-- Amount -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <div class="font-bold text-gray-900">
                                    {{ number_format($loan->principle ?? 0, 0) }}
                                </div>
                               
                            </td>
                            
                            <!-- Term -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <span class="font-medium">{{ $loan->tenure ?? 'N/A' }}</span>
                                <div class="text-xs text-gray-500">months</div>
                            </td>
                            
                            <!-- Current Stage -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                @php
                                    $currentStage = $loan->approval_stage ?? 'Not Set';
                                    $stageRoleNames = $loan->approval_stage_role_name ?? null;
                                    
                                    // Map approval stages to display names and colors
                                    // Support both formats: with spaces and with underscores
                                    $stageConfig = [
                                        'Exception' => ['name' => 'Exception', 'color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'Inputter' => ['name' => 'Inputter', 'color' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                        'First Checker' => ['name' => 'First Checker', 'color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                        'first_checker' => ['name' => 'First Checker', 'color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                        'Second Checker' => ['name' => 'Second Checker', 'color' => 'bg-purple-100 text-purple-800 border-purple-200', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                        'second_checker' => ['name' => 'Second Checker', 'color' => 'bg-purple-100 text-purple-800 border-purple-200', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                        'Approver' => ['name' => 'Approver', 'color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'approver' => ['name' => 'Approver', 'color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'FINANCE' => ['name' => 'Finance', 'color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'approved' => ['name' => 'Approved', 'color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'rejected' => ['name' => 'Rejected', 'color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ];
                                    
                                    $config = $stageConfig[$currentStage] ?? ['name' => $currentStage, 'color' => 'bg-gray-100 text-gray-800 border-gray-200', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                                @endphp
                                
                               
                                
                                @if($stageRoleNames)
                                    <div class="text-sm text-gray-900 mt-1 font-bold break-words max-w-xs">
                                        @php
                                            $roleNamesArray = explode(', ', $stageRoleNames);
                                            $roleCount = count(array_filter($roleNamesArray));
                                        @endphp
                                        @if($roleCount > 1)
                                            <span class="cursor-help" title="{{ $stageRoleNames }}">
                                                LOAN COMMITTEE
                                            </span>
                                        @else
                                            {{ strtoupper($stageRoleNames) }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                            
                            <!-- Status -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                @php
                                    $statusConfig = [
                                        'PENDING' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'APPROVED' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'REJECTED' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'UNDER_REVIEW' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                                        'ACTIVE' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'PENDING_APPROVAL' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'PENDING-EXCEPTIONS' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'PENDING-WITH-EXCEPTIONS' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'PENDING_EXCEPTION_APPROVAL' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    ];
                                    $status = $loan->status ?? 'UNKNOWN';
                                    $config = $statusConfig[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                                    
                                    // Display status in capital letters and map PENDING-EXCEPTIONS to EXCEPTION QUEUE
                                    $displayStatus = $status;
                                    if ($status === 'PENDING-EXCEPTIONS' || $status === 'PENDING-WITH-EXCEPTIONS' || $status === 'PENDING_EXCEPTION_APPROVAL') {
                                        $displayStatus = 'EXCEPTION QUEUE';
                                    } else {
                                        $displayStatus = strtoupper(str_replace(['_', '-'], ' ', $status));
                                    }
                                @endphp
                                {{ $displayStatus }}
                            </td>
                            
                            <!-- Created Date -->
                            <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-200">
                                <div class="font-medium text-gray-900">
                                    {{ $loan->created_at ? $loan->created_at->format('M d, Y') : 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $loan->created_at ? $loan->created_at->diffForHumans() : '' }}
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-4 py-3 text-sm font-medium">
                                <div class="flex items-center space-x-1 ">
                                    <button wire:click="viewLoan({{ $loan->id }})" 
                                            class="cursor-pointer p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200" 
                                            title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                  
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No loan applications found</h3>
                                    <p class="text-gray-500 mb-4">Get started by creating a new loan application.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $loans->firstItem() ?? 0 }}</span> to <span class="font-medium">{{ $loans->lastItem() ?? 0 }}</span> of <span class="font-medium">{{ $loans->total() }}</span> results
                </div>
                <div class="flex items-center space-x-2">
                    {{ $loans->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

