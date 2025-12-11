<div class="w-full">
    <!-- Flash Messages -->
    @if(session()->has('message'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p class="font-bold">Success</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-white border-b">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">List of All Accounts</h1>
                    <p class="text-gray-600 mt-1">Comprehensive account management with full CRUD operations</p>
                </div>
                <button wire:click="openCreateModal()" 
                    class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-800 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Account
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="bg-white border-b">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-4">Account Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Assets -->
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600">Assets</p>
                            <p class="text-lg font-semibold text-blue-900">{{ number_format($accountSummary['ASSET']['balance'] ?? 0, 2) }}</p>
                            <p class="text-xs text-blue-500">{{ $accountSummary['ASSET']['count'] ?? 0 }} accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Liabilities -->
                <div class="bg-red-50 rounded-lg border border-red-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-600">Liabilities</p>
                            <p class="text-lg font-semibold text-red-900">{{ number_format($accountSummary['LIABILITY']['balance'] ?? 0, 2) }}</p>
                            <p class="text-xs text-red-500">{{ $accountSummary['LIABILITY']['count'] ?? 0 }} accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Equity -->
                <div class="bg-green-50 rounded-lg border border-green-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600">Equity</p>
                            <p class="text-lg font-semibold text-green-900">{{ number_format($accountSummary['EQUITY']['balance'] ?? 0, 2) }}</p>
                            <p class="text-xs text-green-500">{{ $accountSummary['EQUITY']['count'] ?? 0 }} accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Income -->
                <div class="bg-purple-50 rounded-lg border border-purple-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600">Income</p>
                            <p class="text-lg font-semibold text-purple-900">{{ number_format($accountSummary['INCOME']['balance'] ?? 0, 2) }}</p>
                            <p class="text-xs text-purple-500">{{ $accountSummary['INCOME']['count'] ?? 0 }} accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Expenses -->
                <div class="bg-orange-50 rounded-lg border border-orange-200 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-orange-600">Expenses</p>
                            <p class="text-lg font-semibold text-orange-900">{{ number_format($accountSummary['EXPENSE']['balance'] ?? 0, 2) }}</p>
                            <p class="text-xs text-orange-500">{{ $accountSummary['EXPENSE']['count'] ?? 0 }} accounts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-gray-50 border-b px-6 py-4">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filter & Search</h3>
            <button wire:click="resetFilters" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset Filters
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Search Accounts</label>
                <input type="text" wire:model.debounce.300ms="searchTerm" 
                    placeholder="Search by name, number, codes..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Account Type</label>
                <select wire:model="selectedType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Types</option>
                    @foreach($accountTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Account Level</label>
                <select wire:model="selectedLevel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Levels</option>
                    @foreach($accountLevels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="selectedStatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Status</option>
                    @foreach($accountStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                <select wire:model="selectedCategory" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm">
                    <option value="">All Categories</option>
                    @foreach($majorCategories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white" wire:loading.class.delay="opacity-60">
        <div class="flex items-center justify-between px-6 pt-4">
            <div class="text-sm text-gray-600">
                {{-- Results summary --}}
                @php
                    $firstItem = $accounts->firstItem();
                    $lastItem = $accounts->lastItem();
                    $total = $accounts->total();
                @endphp
                @if($total > 0)
                    Showing {{ $firstItem }}–{{ $lastItem }} of {{ number_format($total) }} accounts
                @else
                    No accounts to display
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <label for="perPage" class="text-xs text-gray-600">Per page</label>
                <select id="perPage" wire:model="perPage" class="rounded-md border-gray-300 text-sm focus:border-blue-900 focus:ring-blue-900">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" wire:loading.attr="aria-busy">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('account_name')">
                            <div class="flex items-center">
                                Account Name
                                @if($sortField === 'account_name')
                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('account_number')">
                            <div class="flex items-center">
                                Account Number
                                @if($sortField === 'account_number')
                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('balance')">
                            <div class="flex items-center justify-end">
                                Balance
                                @if($sortField === 'balance')
                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50 transition-colors" wire:key="acct-{{ $account->account_number }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @php
                                            $typeIcon = match($account->display_type) {
                                                'ASSET' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                                                'LIABILITY' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>',
                                                'EQUITY' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>',
                                                'INCOME' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>',
                                                'EXPENSE' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>',
                                                default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>'
                                            };
                                        @endphp
                                        <svg class="w-4 h-4 {{ $account->display_type == 'LIABILITY' || $account->display_type == 'EXPENSE' ? 'text-red-600' : 'text-blue-900' }}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            {!! $typeIcon !!}
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $account->account_name }}
                                        </div>
                                        @if($account->notes)
                                            <div class="text-xs text-gray-500">{{ Str::limit($account->notes, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-mono">{{ $account->account_number }}</div>
                                @if($account->major_category_code)
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $account->major_category_code }}-{{ $account->category_code }}-{{ $account->sub_category_code }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $accountTypes[$account->type] ?? $account->display_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $account->account_level == '1' ? 'bg-blue-900 text-white' : ($account->account_level == '2' ? 'bg-blue-700 text-white' : ($account->account_level == '3' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-600')) }}">
                                        L{{ $account->account_level }}
                                    </span>
                                    @if($account->account_use)
                                        <div class="text-xs text-gray-500 mt-1">{{ ucfirst($account->account_use) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $account->status == 'ACTIVE' ? 'bg-green-100 text-green-800' : ($account->status == 'INACTIVE' ? 'bg-red-100 text-red-800' : ($account->status == 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $account->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold {{ floatval($account->current_balance) < 0 ? 'text-red-600' : 'text-gray-900' }} font-mono">
                                    {{ number_format($account->current_balance, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <!-- View Details -->
                                    <button wire:click="openDetailsModal('{{ $account->account_number }}')" 
                                        class="text-blue-900 hover:text-blue-700 transition-colors focus:outline-none" 
                                        title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- View Ledger -->
                                    <button wire:click="openLedgerModal('{{ $account->account_number }}')" 
                                        class="text-green-600 hover:text-green-700 transition-colors focus:outline-none" 
                                        title="View Ledger">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- View Statement -->
                                    <button wire:click="openStatementModal('{{ $account->account_number }}')" 
                                        class="text-purple-600 hover:text-purple-700 transition-colors focus:outline-none" 
                                        title="View Statement">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Edit -->
                                    <button wire:click="openEditModal('{{ $account->account_number }}')" 
                                        class="text-yellow-600 hover:text-yellow-700 transition-colors focus:outline-none" 
                                        title="Edit Account">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Status Toggle -->
                                    @if($account->status == 'ACTIVE')
                                        <button wire:click="toggleAccountStatus('{{ $account->account_number }}', 'INACTIVE')" 
                                            class="text-red-600 hover:text-red-700 transition-colors focus:outline-none" 
                                            title="Deactivate Account">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <button wire:click="toggleAccountStatus('{{ $account->account_number }}', 'ACTIVE')" 
                                            class="text-green-600 hover:text-green-700 transition-colors focus:outline-none" 
                                            title="Activate Account">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <!-- Delete -->
                                    <button wire:click="openDeleteModal('{{ $account->account_number }}')" 
                                        class="text-red-600 hover:text-red-700 transition-colors focus:outline-none" 
                                        title="Delete Account">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">No accounts found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filter criteria</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="bg-white px-6 py-3 border-t border-gray-200">
            {{ $accounts->onEachSide(1)->links() }}
        </div>
    </div>

    <!-- Create Account Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">Create New Account</h3>
                            <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="createAccount" class="mt-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                    <input type="text" wire:model.defer="newAccount.account_name" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                    @error('newAccount.account_name')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <input type="text" wire:model.defer="newAccount.account_number" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                    @error('newAccount.account_number')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                    <select wire:model.defer="newAccount.type" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach($accountTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('newAccount.type')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Level</label>
                                    <select wire:model.defer="newAccount.account_level" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Level</option>
                                        @foreach($accountLevels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('newAccount.account_level')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Major Category</label>
                                    <select wire:model.defer="newAccount.major_category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Category</option>
                                        @foreach($majorCategories as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('newAccount.major_category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Code</label>
                                    <input type="text" wire:model.defer="newAccount.category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="e.g., 2100" required>
                                    @error('newAccount.category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sub Category Code</label>
                                    <input type="text" wire:model.defer="newAccount.sub_category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="e.g., 2101" required>
                                    @error('newAccount.sub_category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Use</label>
                                    <select wire:model.defer="newAccount.account_use" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">Select Use</option>
                                        @foreach($accountUses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Balance</label>
                                    <input type="number" wire:model.defer="newAccount.balance" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        step="0.01" value="0">
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea wire:model.defer="newAccount.notes" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        rows="2"></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800">
                                    Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Account Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">Edit Account</h3>
                            <button wire:click="$set('showEditModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="updateAccount" class="mt-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                    <input type="text" wire:model.defer="editingAccount.account_name" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                    @error('editingAccount.account_name')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <input type="text" wire:model.defer="editingAccount.account_number" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-gray-50"
                                        readonly>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                                    <select wire:model.defer="editingAccount.type" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Type</option>
                                        @foreach($accountTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('editingAccount.type')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Level</label>
                                    <select wire:model.defer="editingAccount.account_level" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Level</option>
                                        @foreach($accountLevels as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('editingAccount.account_level')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Major Category</label>
                                    <select wire:model.defer="editingAccount.major_category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                        <option value="">Select Category</option>
                                        @foreach($majorCategories as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('editingAccount.major_category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category Code</label>
                                    <input type="text" wire:model.defer="editingAccount.category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                    @error('editingAccount.category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sub Category Code</label>
                                    <input type="text" wire:model.defer="editingAccount.sub_category_code" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        required>
                                    @error('editingAccount.sub_category_code')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Use</label>
                                    <select wire:model.defer="editingAccount.account_use" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">Select Use</option>
                                        @foreach($accountUses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select wire:model.defer="editingAccount.status" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        @foreach($accountStatuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Balance</label>
                                    <input type="number" wire:model.defer="editingAccount.balance" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        step="0.01">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Debit</label>
                                    <input type="number" wire:model.defer="editingAccount.debit" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        step="0.01">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Credit</label>
                                    <input type="number" wire:model.defer="editingAccount.credit" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        step="0.01">
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea wire:model.defer="editingAccount.notes" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        rows="2"></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" wire:click="$set('showEditModal', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800">
                                    Update Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Account Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-md sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                            <button wire:click="$set('showDeleteModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Are you sure?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        You are about to delete the account <strong>{{ $selectedAccountData['account_name'] ?? '' }}</strong> 
                                        ({{ $selectedAccountData['account_number'] ?? '' }}). This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button wire:click="deleteAccount"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Account Details Modal -->
    @if($showDetailsModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">Account Details</h3>
                            <button wire:click="$set('showDetailsModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        @if($selectedAccountData)
                            <div class="mt-4 space-y-4">
                                <!-- Account Information -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Account Information</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Account Name</label>
                                            <p class="text-sm font-medium text-gray-900">{{ $selectedAccountData['account_name'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Account Number</label>
                                            <p class="text-sm font-mono text-gray-900">{{ $selectedAccountData['account_number'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Type</label>
                                            <p class="text-sm text-gray-900">{{ $selectedAccountData['type'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Level</label>
                                            <p class="text-sm text-gray-900">Level {{ $selectedAccountData['account_level'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Status</label>
                                            <span class="px-2 py-1 text-xs rounded-full {{ $selectedAccountData['status'] == 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $selectedAccountData['status'] }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Balance</label>
                                            <p class="text-sm font-semibold {{ floatval($selectedAccountData['balance']) < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ number_format($selectedAccountData['balance'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Information -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Category Information</h4>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Major Category</label>
                                            <p class="text-sm font-mono text-gray-900">{{ $selectedAccountData['major_category_code'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Category Code</label>
                                            <p class="text-sm font-mono text-gray-900">{{ $selectedAccountData['category_code'] }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500">Sub Category</label>
                                            <p class="text-sm font-mono text-gray-900">{{ $selectedAccountData['sub_category_code'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Parent Account -->
                                @if(isset($selectedAccountData['parent']))
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-blue-700 mb-3">Parent Account</h4>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-blue-500">Name</label>
                                                <p class="text-sm font-medium text-blue-900">{{ $selectedAccountData['parent']['account_name'] }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-blue-500">Number</label>
                                                <p class="text-sm font-mono text-blue-900">{{ $selectedAccountData['parent']['account_number'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Child Accounts -->
                                @if(isset($selectedAccountData['children']) && count($selectedAccountData['children']) > 0)
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-green-700 mb-3">Child Accounts ({{ count($selectedAccountData['children']) }})</h4>
                                        <div class="space-y-2">
                                            @foreach($selectedAccountData['children'] as $child)
                                                <div class="flex justify-between items-center bg-white rounded p-2">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $child['account_name'] }}</p>
                                                        <p class="text-xs text-gray-500 font-mono">{{ $child['account_number'] }}</p>
                                                    </div>
                                                    <span class="text-sm font-semibold {{ floatval($child['balance']) < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                        {{ number_format($child['balance'], 2) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Notes -->
                                @if($selectedAccountData['notes'])
                                    <div class="bg-yellow-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-yellow-700 mb-3">Notes</h4>
                                        <p class="text-sm text-yellow-900">{{ $selectedAccountData['notes'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-end pt-4 border-t">
                            <button wire:click="$set('showDetailsModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Ledger Modal -->
    @if($showLedgerModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">
                                Ledger Entries - {{ $selectedAccountData['account_name'] ?? '' }}
                            </h3>
                            <button wire:click="$set('showLedgerModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($ledgerEntries as $entry)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                                                    {{ \Carbon\Carbon::parse($entry['created_at'])->format('M d, Y') }}
                                                </td>
                                                <td class="px-3 py-2 text-sm text-gray-900">
                                                    {{ $entry['description'] ?? 'Transaction' }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono">
                                                    @if(floatval($entry['debit']) > 0)
                                                        {{ number_format($entry['debit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono">
                                                    @if(floatval($entry['credit']) > 0)
                                                        {{ number_format($entry['credit'], 2) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono font-semibold">
                                                    {{ number_format($entry['balance'] ?? 0, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                                    No ledger entries found for this account.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button wire:click="$set('showLedgerModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statement Modal -->
    @if($showStatementModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-6 py-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <h3 class="text-lg font-medium text-gray-900">
                                Monthly Statement - {{ $selectedAccountData['account_name'] ?? '' }}
                            </h3>
                            <button wire:click="$set('showStatementModal', false)" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Transactions</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Debits</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Credits</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net Movement</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($statementData as $entry)
                                            @php
                                                $monthName = \Carbon\Carbon::createFromDate($entry['year'], $entry['month'], 1)->format('F Y');
                                                $netMovement = floatval($entry['total_credits']) - floatval($entry['total_debits']);
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $monthName }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right text-gray-900">
                                                    {{ $entry['transaction_count'] }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono text-red-600">
                                                    {{ number_format($entry['total_debits'], 2) }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono text-green-600">
                                                    {{ number_format($entry['total_credits'], 2) }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap text-sm text-right font-mono font-semibold {{ $netMovement >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $netMovement >= 0 ? '+' : '' }}{{ number_format($netMovement, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                                                    No statement data available for this account.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t">
                            <button wire:click="$set('showStatementModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
