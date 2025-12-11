<div class="p-6 bg-white rounded-lg shadow-lg">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Deposits Full Report</h1>
                <p class="text-gray-600 mt-1">Comprehensive deposits analysis and reporting</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="refreshReport" 
                        class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-900 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                <button wire:click="exportReport('excel')" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
                <button wire:click="exportReport('pdf')" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if($successMessage)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button wire:click="$set('successMessage', '')" class="text-green-500 hover:text-green-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errorMessage)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ $errorMessage }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button wire:click="$set('errorMessage', '')" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loading Indicator -->
        @if($isLoading || $isExporting)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-spinner fa-spin text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">
                            @if($isExporting)
                                Generating statement...
                            @else
                                Loading data...
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>


     <!-- Summary Cards -->
     @if(isset($summaryStatistics) && $summaryStatistics !== null)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-900 rounded-full">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">Total Members</p>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ number_format($summaryStatistics->member_count ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <div class="flex items-center">
                    <div class="p-3 bg-green-600 rounded-full">
                        <i class="fas fa-wallet text-white"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">Total Balance</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ number_format($summaryStatistics->total_balance ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-600 rounded-full">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600">Average Balance</p>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ number_format($summaryStatistics->avg_balance ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-orange-50 p-6 rounded-lg border border-orange-200">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-600 rounded-full">
                        <i class="fas fa-credit-card text-white"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-orange-600">Total Accounts</p>
                        <p class="text-2xl font-bold text-orange-900">
                            {{ number_format($summaryStatistics->account_count ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif


    

    <!-- Filters Section -->
    <div class="bg-gray-50 p-6 rounded-lg mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Report Filters</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Branch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select wire:model="selectedBranch" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->branch_number }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Member Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Member</label>
                <select wire:model="selectedMember" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Members</option>
                    @foreach($members as $member)
                        <option value="{{ $member->client_number }}">
                            @if($member->business_name)
                                {{ $member->business_name }} ({{ $member->client_number }})
                            @else
                                {{ $member->first_name }} {{ $member->last_name }} ({{ $member->client_number }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Account Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account</label>
                <select wire:model="selectedAccount" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->account_number }}">
                            {{ $account->account_name }} - {{ $account->client_number }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Product Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                <select wire:model="selectedProduct" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->sub_product_id }}">
                            {{ $product->product_name }} - {{ $product->sub_product_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" wire:model="dateFrom" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" wire:model="dateTo" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select wire:model="reportType" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="summary">Summary Report</option>
                    <option value="detailed">Detailed Report</option>
                    <option value="comparative">Comparative Report</option>
                </select>
            </div>

            <!-- Group By -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                <select wire:model="groupBy" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="member">By Member</option>
                    <option value="account">By Account</option>
                    <option value="branch">By Branch</option>
                    <option value="product">By Product</option>
                    <option value="date">By Date</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Time Frame -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Time Frame</label>
                <select wire:model="timeFrame" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="day">Daily</option>
                    <option value="week">Weekly</option>
                    <option value="month">Monthly</option>
                    <option value="quarter">Quarterly</option>
                    <option value="year">Yearly</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model="statusFilter" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Status</option>
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                    <option value="PENDING">Pending</option>
                    <option value="BLOCKED">Blocked</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                <select wire:model="perPage" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <button wire:click="resetFilters" 
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-undo mr-2"></i>Reset Filters
            </button>

            @if($isLoading)
                <div class="flex items-center text-blue-600">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Loading report...
                </div>
            @endif
        </div>
    </div>

   
 

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">
                @switch($reportType)
                    @case('summary')
                        Summary Report
                        @break
                    @case('detailed')
                        Detailed Report
                        @break
                    @case('comparative')
                        Comparative Report
                        @break
                @endswitch
            </h3>
        </div>

        @if($isLoading)
            <div class="p-8 text-center">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Loading report data...</p>
            </div>
        @elseif(isset($summaryData) && $summaryData !== null && (is_object($summaryData) && method_exists($summaryData, 'count') ? $summaryData->count() : (is_array($summaryData) ? count($summaryData) : 0)) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @switch($groupBy)
                                @case('member')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('client_number')" class="hover:text-gray-700">
                                            Member Number
                                            @if($sortBy === 'client_number')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('account_count')" class="hover:text-gray-700">
                                            Accounts
                                            @if($sortBy === 'account_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('total_balance')" class="hover:text-gray-700">
                                            Total Balance
                                            @if($sortBy === 'total_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('avg_balance')" class="hover:text-gray-700">
                                            Average Balance
                                            @if($sortBy === 'avg_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    @break

                                @case('account')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('account_number')" class="hover:text-gray-700">
                                            Account Number
                                            @if($sortBy === 'account_number')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('balance')" class="hover:text-gray-700">
                                            Balance
                                            @if($sortBy === 'balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    @break

                                @case('branch')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('account_count')" class="hover:text-gray-700">
                                            Accounts
                                            @if($sortBy === 'account_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('member_count')" class="hover:text-gray-700">
                                            Members
                                            @if($sortBy === 'member_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('total_balance')" class="hover:text-gray-700">
                                            Total Balance
                                            @if($sortBy === 'total_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('avg_balance')" class="hover:text-gray-700">
                                            Average Balance
                                            @if($sortBy === 'avg_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    @break

                                @case('product')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('account_count')" class="hover:text-gray-700">
                                            Accounts
                                            @if($sortBy === 'account_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('member_count')" class="hover:text-gray-700">
                                            Members
                                            @if($sortBy === 'member_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('total_balance')" class="hover:text-gray-700">
                                            Total Balance
                                            @if($sortBy === 'total_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('avg_balance')" class="hover:text-gray-700">
                                            Average Balance
                                            @if($sortBy === 'avg_balance')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    @break

                                @case('date')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('transaction_count')" class="hover:text-gray-700">
                                            Transactions
                                            @if($sortBy === 'transaction_count')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('total_credits')" class="hover:text-gray-700">
                                            Credits
                                            @if($sortBy === 'total_credits')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('total_debits')" class="hover:text-gray-700">
                                            Debits
                                            @if($sortBy === 'total_debits')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortBy('net_change')" class="hover:text-gray-700">
                                            Net Change
                                            @if($sortBy === 'net_change')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @endif
                                        </button>
                                    </th>
                                    @break
                            @endswitch
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($summaryData as $item)
                            <tr class="hover:bg-gray-50">
                                @switch($groupBy)
                                    @case('member')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->client_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $member = collect($members)->firstWhere('client_number', $item->client_number);
                                            @endphp
                                            {{ $member ? $member->first_name . ' ' . $member->last_name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->account_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->total_balance, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->avg_balance, 2) }}
                                        </td>
                                        @break

                                    @case('account')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->account_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->account_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->client_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->balance, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $item->status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 
                                                   ($item->status === 'INACTIVE' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        @break

                                    @case('branch')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @php
                                                $branch = $branches->firstWhere('branch_number', $item->branch_number);
                                            @endphp
                                            {{ $branch ? $branch->name : $item->branch_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->account_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->member_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->total_balance, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->avg_balance, 2) }}
                                        </td>
                                        @break

                                    @case('product')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->product_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->account_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->member_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->total_balance, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->avg_balance, 2) }}
                                        </td>
                                        @break

                                    @case('date')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $item->date_period }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ number_format($item->transaction_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                            {{ number_format($item->total_credits, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                            {{ number_format($item->total_debits, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                            {{ $item->net_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($item->net_change, 2) }}
                                        </td>
                                        @break
                                @endswitch
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                @if(isset($summaryData) && $summaryData !== null && is_object($summaryData) && method_exists($summaryData, 'links'))
                    {{ $summaryData->links() }}
                @endif
            </div>
        @elseif($reportType === 'detailed' && isset($detailedData) && $detailedData !== null && (is_object($detailedData) && method_exists($detailedData, 'count') ? $detailedData->count() : (is_array($detailedData) ? count($detailedData) : 0)) > 0)
            <!-- Detailed Report Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('account_number')" class="hover:text-gray-700">
                                    Account Number
                                    @if($sortBy === 'account_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('account_name')" class="hover:text-gray-700">
                                    Account Name
                                    @if($sortBy === 'account_name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('balance')" class="hover:text-gray-700">
                                    Balance
                                    @if($sortBy === 'balance')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('status')" class="hover:text-gray-700">
                                    Status
                                    @if($sortBy === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('created_at')" class="hover:text-gray-700">
                                    Created Date
                                    @if($sortBy === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($detailedData as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-piggy-bank text-blue-600 text-xs"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->account_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $item->account_name }}">
                                        {{ $item->account_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($item->client)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-green-600 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item->client->first_name }} {{ $item->client->last_name }}
                                                </div>
                                                @if($item->client->business_name)
                                                    <div class="text-sm text-gray-500">{{ $item->client->business_name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->client_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($item->share_product)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-6 w-6">
                                                <div class="h-6 w-6 rounded bg-purple-100 flex items-center justify-center">
                                                    <i class="fas fa-tag text-purple-600 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->share_product->product_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->share_product->sub_product_name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php
                                        $branch = collect($branches)->firstWhere('branch_number', $item->branch_number);
                                    @endphp
                                    @if($branch)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-6 w-6">
                                                <div class="h-6 w-6 rounded bg-orange-100 flex items-center justify-center">
                                                    <i class="fas fa-building text-orange-600 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900">{{ $branch->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->branch_number }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ $item->branch_number }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ number_format($item->balance, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">TZS</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'ACTIVE' => 'bg-green-100 text-green-800',
                                            'INACTIVE' => 'bg-red-100 text-red-800',
                                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                                            'BLOCKED' => 'bg-gray-100 text-gray-800',
                                            'SUSPENDED' => 'bg-orange-100 text-orange-800'
                                        ];
                                        $statusColor = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->created_at ? $item->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button wire:click="viewAccountDetails('{{ $item->account_number }}')" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button wire:click="viewAccountTransactions('{{ $item->account_number }}')" 
                                                class="text-green-600 hover:text-green-900 transition-colors duration-200" 
                                                title="View Transactions">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button wire:click="downloadAccountStatement('{{ $item->account_number }}')" 
                                                class="text-purple-600 hover:text-purple-900 transition-colors duration-200" 
                                                title="Download Statement"
                                                @if($isExporting) disabled @endif>
                                            <i class="fas fa-download @if($isExporting) animate-pulse @endif"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Detailed Report -->
            <div class="px-6 py-4 border-t border-gray-200">
                @if(isset($detailedData) && $detailedData !== null && is_object($detailedData) && method_exists($detailedData, 'links'))
                    {{ $detailedData->links() }}
                @endif
            </div>
        @else
            <div class="p-8 text-center">
                <i class="fas fa-chart-bar text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">No data available for the selected filters.</p>
            </div>
        @endif
    </div>

    <!-- Comparative Report Section -->
    @if($reportType === 'comparative' && isset($comparativeData))
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Comparative Analysis</h3>
            
            @if(isset($comparativeData['current_period']) && isset($comparativeData['previous_period']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Current Period -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-blue-800 mb-3">Current Period</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-blue-600">Accounts:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['current_period']->account_count) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Members:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['current_period']->member_count) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Total Balance:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['current_period']->total_balance, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Average Balance:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['current_period']->avg_balance, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Period -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">Previous Period</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Accounts:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['previous_period']->account_count) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Members:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['previous_period']->member_count) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Balance:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['previous_period']->total_balance, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Average Balance:</span>
                                <span class="font-semibold">{{ number_format($comparativeData['previous_period']->avg_balance, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changes -->
                @if(isset($comparativeData['changes']))
                    <div class="mt-6 bg-green-50 p-4 rounded-lg">
                        <h4 class="text-lg font-semibold text-green-800 mb-3">Percentage Changes</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <p class="text-sm text-green-600">Account Count</p>
                                <p class="text-lg font-bold {{ $comparativeData['changes']['account_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $comparativeData['changes']['account_count_change'] }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-green-600">Member Count</p>
                                <p class="text-lg font-bold {{ $comparativeData['changes']['member_count_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $comparativeData['changes']['member_count_change'] }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-green-600">Total Balance</p>
                                <p class="text-lg font-bold {{ $comparativeData['changes']['total_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $comparativeData['changes']['total_balance_change'] }}%
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-green-600">Average Balance</p>
                                <p class="text-lg font-bold {{ $comparativeData['changes']['avg_balance_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $comparativeData['changes']['avg_balance_change'] }}%
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>

<!-- Account Details Modal -->
@if($showAccountDetailsModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Account Details</h3>
                <button wire:click="closeAccountDetailsModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Account Information -->
            <div class="space-y-6">
                <!-- Account Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-blue-900 flex items-center justify-center">
                                <i class="fas fa-piggy-bank text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-2xl font-bold text-gray-900">{{ $selectedAccountData->account_name }}</h4>
                            <p class="text-lg text-gray-600">{{ $selectedAccountData->account_number }}</p>
                            <div class="mt-2">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                    @if($selectedAccountData->status === 'ACTIVE') bg-green-100 text-green-800
                                    @elseif($selectedAccountData->status === 'INACTIVE') bg-red-100 text-red-800
                                    @elseif($selectedAccountData->status === 'PENDING') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $selectedAccountData->status }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Current Balance</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($selectedAccountData->balance, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Account Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Member Information -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user text-blue-600 mr-2"></i>
                            Member Information
                        </h5>
                        @if($selectedAccountData->client)
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Member Name:</span>
                                <span class="font-medium">{{ $selectedAccountData->client->first_name }} {{ $selectedAccountData->client->last_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Member Number:</span>
                                <span class="font-medium">{{ $selectedAccountData->client_number }}</span>
                            </div>
                            @if($selectedAccountData->client->business_name)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Business Name:</span>
                                <span class="font-medium">{{ $selectedAccountData->client->business_name }}</span>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="text-gray-500 italic">
                            No member information available
                        </div>
                        @endif
                    </div>

                    <!-- Product Information -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-tag text-purple-600 mr-2"></i>
                            Product Information
                        </h5>
                        @if($selectedAccountData->shareProduct)
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Product Name:</span>
                                <span class="font-medium">{{ $selectedAccountData->shareProduct->product_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sub Product:</span>
                                <span class="font-medium">{{ $selectedAccountData->shareProduct->sub_product_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Product ID:</span>
                                <span class="font-medium">{{ $selectedAccountData->sub_product_number }}</span>
                            </div>
                        </div>
                        @else
                        <div class="text-gray-500 italic">
                            No product information available
                        </div>
                        @endif
                    </div>

                    <!-- Account Details -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-green-600 mr-2"></i>
                            Account Details
                        </h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Branch:</span>
                                <span class="font-medium">{{ $selectedAccountData->branch_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created Date:</span>
                                <span class="font-medium">{{ $selectedAccountData->created_at ? $selectedAccountData->created_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium">{{ $selectedAccountData->updated_at ? $selectedAccountData->updated_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-white p-6 rounded-lg border border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-orange-600 mr-2"></i>
                            Financial Summary
                        </h5>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Balance:</span>
                                <span class="font-bold text-lg">{{ number_format($selectedAccountData->balance, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Status:</span>
                                <span class="font-medium">{{ $selectedAccountData->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Product Type:</span>
                                <span class="font-medium">{{ $selectedAccountData->product_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button wire:click="closeAccountDetailsModal" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                    <button class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-900 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Account
                    </button>
                    <button wire:click="downloadAccountStatement('{{ $selectedAccountData->account_number }}')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Download Statement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Account Transactions Modal -->
@if($showTransactionsModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Account Transactions</h3>
                <button wire:click="closeTransactionsModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            @if($selectedAccountTransactions && count($selectedAccountTransactions) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($selectedAccountTransactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->description ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($transaction->balance, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">No transactions found for the selected period.</p>
            </div>
            @endif
            
            <div class="items-center px-4 py-3">
                <button wire:click="closeTransactionsModal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
