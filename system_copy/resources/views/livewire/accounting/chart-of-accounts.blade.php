<div>
    {{-- Enhanced CSS for better UX --}}
    <style nonce="{{ csp_nonce() }}">
        .metric-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .metric-card:hover::before {
            left: 100%;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .table-row-hover {
            transition: all 0.2s ease-in-out;
        }

        .table-row-hover:hover {
            background-color: rgba(59, 130, 246, 0.05);
            transform: scale(1.01);
        }

        .search-highlight {
            background-color: rgba(59, 130, 246, 0.2);
            padding: 2px 4px;
            border-radius: 4px;
        }

        .breadcrumb-item {
            transition: all 0.2s ease-in-out;
        }

        .breadcrumb-item:hover {
            color: #3b82f6;
            transform: translateX(2px);
        }

        .loading-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    {{-- Global Search and Navigation Bar --}}
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                {{-- Page Title and Breadcrumb --}}
                <div class="flex items-center space-x-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Chart of Accounts</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage and organize your financial accounts</p>
                    </div>
                    
                    {{-- Breadcrumb Navigation --}}
                    <nav class="hidden md:flex items-center space-x-2 text-sm">
                        <span class="text-gray-400">Navigation:</span>
                        <button wire:click="resetNavigation" 
                                class="breadcrumb-item text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Dashboard
                        </button>
                        @if($showAccountTable)
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">{{ $selectedAccountTypeName }}</span>
                        @endif
                        @if($showLevel3Table)
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">{{ $selectedLevel2AccountName }}</span>
                        @endif
                        @if($showLevel4Table)
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300">{{ $selectedLevel3AccountName }}</span>
                        @endif
                    </nav>
                </div>

                {{-- Global Search and Quick Actions --}}
                <div class="flex items-center space-x-4">
                    {{-- Global Search --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               wire:model.debounce.300ms="globalSearch" 
                               placeholder="Search accounts, numbers, or names..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        @if($globalSearch)
                            <button wire:click="clearGlobalSearch" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Quick Actions --}}
                    <div class="flex items-center space-x-2">
                        <button wire:click="exportAccounts" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search Results Summary --}}
            @if($globalSearch && $searchResults)
                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                Found {{ count($searchResults) }} results for "{{ $globalSearch }}"
                            </span>
                        </div>
                        <button wire:click="clearGlobalSearch" 
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                            Clear search
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Loading Indicator --}}
    @if($loading)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
                <svg class="loading-spinner w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="text-gray-900 dark:text-white">Loading accounts...</span>
            </div>
        </div>
    @endif

    <div class="space-y-6 p-6">
        {{-- First Row: General Ledger, Revenue, Expenses --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
            {{-- General Ledger Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer" 
                 wire:click="selectAccountType('general_ledger', 'General Ledger')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">General Ledger</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Account Categories</p>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $generalLedgerCount ?? 0 }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total account categories</p>
            </div>

            {{-- Revenue Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
                 wire:click="selectAccountType('revenue', 'Revenue')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Income accounts</p>
                    </div>
                    <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accounts</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $revenueStats['count'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Balance</span>
                        <span class="text-lg font-semibold text-green-600 dark:text-green-400">{{ number_format($revenueStats['balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $revenueStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $revenueStats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Expenses Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
                 wire:click="selectAccountType('expenses', 'Expenses')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Expenses</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cost accounts</p>
                    </div>
                    <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accounts</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $expenseStats['count'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Balance</span>
                        <span class="text-lg font-semibold text-red-600 dark:text-red-400">{{ number_format($expenseStats['balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $expenseStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $expenseStats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Second Row: Assets, Liabilities, Equity --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
            {{-- Assets Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
                 wire:click="selectAccountType('assets', 'Assets')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assets</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">What we own</p>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accounts</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $assetStats['count'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Value</span>
                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ number_format($assetStats['balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $assetStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $assetStats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Liabilities Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
                 wire:click="selectAccountType('liabilities', 'Liabilities')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Liabilities</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">What we owe</p>
                    </div>
                    <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accounts</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $liabilityStats['count'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Amount</span>
                        <span class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ number_format($liabilityStats['balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $liabilityStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $liabilityStats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Equity Card --}}
            <div class="metric-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer"
                 wire:click="selectAccountType('equity', 'Equity')">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Equity</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Owner's capital</p>
                    </div>
                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Accounts</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $equityStats['count'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Capital</span>
                        <span class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ number_format($equityStats['balance'] ?? 0, 2) }} TZS</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</span>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $equityStats['active'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</span>
                        <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $equityStats['inactive'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Tables Section --}}
        @if($showAccountTable && count($level2Accounts) > 0)
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 fade-in">
                {{-- Enhanced Table Header with Filters --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedAccountTypeName }} - Level 2 Accounts</h2>
                            <span class="px-3 py-1 text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                {{ count($level2Accounts) }} accounts
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button wire:click="showCreateAccountModal" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create New Account
                            </button>
                            <button wire:click="closeAccountTable" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Enhanced Filters Section --}}
                    <div class="mt-4 space-y-4">
                        {{-- Search and Quick Filters --}}
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
                            <div class="flex-1 max-w-md">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           wire:model.debounce.300ms="tableSearch" 
                                           placeholder="Search accounts..."
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                {{-- Status Filter --}}
                                <select wire:model="statusFilter" 
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">All Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                </select>

                                {{-- Balance Filter --}}
                                <select wire:model="balanceFilter" 
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">All Balances</option>
                                    <option value="positive">Positive Balance</option>
                                    <option value="negative">Negative Balance</option>
                                    <option value="zero">Zero Balance</option>
                                </select>

                                {{-- Sort Options --}}
                                <select wire:model="sortBy" 
                                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="account_name">Sort by Name</option>
                                    <option value="account_number">Sort by Number</option>
                                    <option value="balance">Sort by Balance</option>
                                    <option value="created_at">Sort by Date</option>
                                </select>

                                {{-- Clear Filters --}}
                                @if($tableSearch || $statusFilter || $balanceFilter)
                                    <button wire:click="clearTableFilters" 
                                            class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200">
                                        Clear Filters
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Active Filters Display --}}
                        @if($tableSearch || $statusFilter || $balanceFilter)
                            <div class="flex flex-wrap items-center space-x-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
                                @if($tableSearch)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                        Search: "{{ $tableSearch }}"
                                        <button wire:click="$set('tableSearch', '')" class="ml-1 hover:text-blue-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                @endif
                                @if($statusFilter)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        Status: {{ $statusFilter }}
                                        <button wire:click="$set('statusFilter', '')" class="ml-1 hover:text-green-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                @endif
                                @if($balanceFilter)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                        Balance: {{ ucfirst($balanceFilter) }}
                                        <button wire:click="$set('balanceFilter', '')" class="ml-1 hover:text-purple-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- Enhanced Table with Better UX --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                    wire:click="sortTable('account_name')">
                                    <div class="flex items-center space-x-1">
                                        <span>Account Name</span>
                                        @if($sortBy === 'account_name')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                    wire:click="sortTable('account_number')">
                                    <div class="flex items-center space-x-1">
                                        <span>Account Number</span>
                                        @if($sortBy === 'account_number')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                    wire:click="sortTable('balance')">
                                    <div class="flex items-center justify-end space-x-1">
                                        <span>Balance (TZS)</span>
                                        @if($sortBy === 'balance')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Credit (TZS)
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Debit (TZS)
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @php
                                $displayAccounts = ($tableSearch || $statusFilter || $balanceFilter) ? $filteredLevel2Accounts : $level2Accounts;
                            @endphp
                            @forelse($displayAccounts as $account)
                                <tr class="table-row-hover cursor-pointer" 
                                    wire:click="selectLevel2Account({{ $account->id }}, '{{ $account->account_name }}')">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    @if($tableSearch)
                                                        {{-- SECURITY: Escape search term and account data to prevent XSS --}}
                                                        {!! str_ireplace(
                                                            e($tableSearch),
                                                            '<span class="search-highlight">' . e($tableSearch) . '</span>',
                                                            e($account->account_name ?? 'No name')
                                                        ) !!}
                                                    @else
                                                        {{ $account->account_name ?? 'No name' }}
                                                    @endif
                                                </div>
                                                @if($account->notes)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $account->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                        @if($tableSearch)
                                            {{-- SECURITY: Escape search term and account data to prevent XSS --}}
                                            {!! str_ireplace(
                                                e($tableSearch),
                                                '<span class="search-highlight">' . e($tableSearch) . '</span>',
                                                e($account->account_number ?? 'No number')
                                            ) !!}
                                        @else
                                            {{ $account->account_number ?? 'No number' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium {{ ($account->balance ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($account->balance ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ number_format($account->credit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($account->debit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ ($account->status ?? 'UNKNOWN') === 'ACTIVE' 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            <span class="w-2 h-2 rounded-full mr-1 {{ ($account->status ?? 'UNKNOWN') === 'ACTIVE' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                            {{ $account->status ?? 'UNKNOWN' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            {{-- Quick Actions Dropdown --}}
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click.stop="open = !open" 
                                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                    </svg>
                                                </button>
                                                
                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border border-gray-200 dark:border-gray-700">
                                                    <div class="py-1">
                                                        <button wire:click.stop="editAccount({{ $account->id }})" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Edit Account
                                                        </button>
                                                        
                                                        @if($account->status === 'ACTIVE')
                                                            <button wire:click.stop="deleteOrDeactivateAccount({{ $account->id }})" 
                                                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900">
                                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                                Deactivate
                                                            </button>
                                                        @else
                                                            <button wire:click.stop="activateAccount({{ $account->id }})" 
                                                                    class="flex items-center w-full px-4 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900">
                                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                                Activate
                                                            </button>
                                                        @endif
                                                        
                                                        <button wire:click.stop="viewAccountStatement({{ $account->id }})" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900">
                                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                            View Statement
                                                        </button>
                                                        
                                                        <button wire:click.stop="downloadAccountStatement({{ $account->id }})" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900">
                                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            Download Statement
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No accounts found</p>
                                        <p class="text-sm mt-1">
                                            @if($tableSearch || $statusFilter || $balanceFilter)
                                                Try adjusting your filters or search terms.
                                            @else
                                                No accounts available for {{ $selectedAccountTypeName }}.
                                            @endif
                                        </p>
                                        @if($tableSearch || $statusFilter || $balanceFilter)
                                            <button wire:click="clearTableFilters" 
                                                    class="mt-3 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                Clear all filters
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Summary --}}
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ count($displayAccounts) }} accounts
                            @if($tableSearch || $statusFilter || $balanceFilter)
                                (filtered from {{ count($level2Accounts) }} total)
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if($tableSearch || $statusFilter || $balanceFilter)
                                <button wire:click="clearTableFilters" 
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                                    Clear all filters
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Level 3 Accounts Table --}}
        @if($showLevel3Table)
            <div class="mt-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedLevel2AccountName }} - Level 3 Accounts</h2>
                            <span class="px-3 py-1 text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                                {{ count($level3Accounts) }} accounts
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button wire:click="closeLevel3Table" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <button wire:click="showCreateLevel3Account()" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-900 hover:bg-blue-900 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Level 3 Account
                            </button>
                            <button wire:click="showCreateLevel4Account({{ $selectedLevel2Account->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Number
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Balance (TZS)
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Credit (TZS)
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Debit (TZS)
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($level3Accounts as $account)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 cursor-pointer" 
                                    wire:click="selectLevel3Account({{ $account->id }}, '{{ $account->account_name }}')">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $account->account_name ?? 'No name' }}
                                                </div>
                                                @if($account->notes)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $account->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $account->account_number ?? 'No number' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium {{ ($account->balance ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($account->balance ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ number_format($account->credit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($account->debit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ ($account->status ?? 'UNKNOWN') === 'ACTIVE' 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $account->status ?? 'UNKNOWN' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            {{-- Edit Account --}}
                                            <button wire:click="editAccount({{ $account->id }})" 
                                                    class="text-blue-500 hover:text-blue-700 transition-colors duration-200" 
                                                    title="Edit Account">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            {{-- Delete/Deactivate Account --}}
                                            @if($account->status === 'ACTIVE')
                                                <button wire:click="deleteOrDeactivateAccount({{ $account->id }})" 
                                                        class="text-red-500 hover:text-red-700 transition-colors duration-200" 
                                                        title="Delete/Deactivate Account">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="activateAccount({{ $account->id }})" 
                                                        class="text-green-500 hover:text-green-700 transition-colors duration-200" 
                                                        title="Activate Account">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- View Statement --}}
                                            <button wire:click="viewAccountStatement({{ $account->id }})" 
                                                    class="text-purple-500 hover:text-purple-700 transition-colors duration-200" 
                                                    title="View Statement">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            {{-- Download Statement --}}
                                            <button wire:click="downloadAccountStatement({{ $account->id }})" 
                                                    class="text-indigo-500 hover:text-blue-900 transition-colors duration-200" 
                                                    title="Download Statement">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm">No Level 3 accounts found for {{ $selectedLevel2AccountName }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Level 4 Accounts Table --}}
        @if($showLevel4Table)
            <div class="mt-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedLevel3AccountName }} - Level 4 Accounts</h2>
                            <span class="px-3 py-1 text-sm font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full">
                                {{ count($level4Accounts) }} accounts
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button wire:click="closeLevel4Table" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <button wire:click="showCreateLevel4Account({{ $selectedLevel3Account->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Level 4 Account
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Account Number
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Balance (TZS)
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Credit (TZS)
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Debit (TZS)
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($level4Accounts as $account)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $account->account_name ?? 'No name' }}
                                                </div>
                                                @if($account->notes)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $account->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                        {{ $account->account_number ?? 'No number' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium {{ ($account->balance ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($account->balance ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ number_format($account->credit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($account->debit ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ ($account->status ?? 'UNKNOWN') === 'ACTIVE' 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $account->status ?? 'UNKNOWN' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            {{-- Edit Account --}}
                                            <button wire:click="editAccount({{ $account->id }})" 
                                                    class="text-blue-500 hover:text-blue-700 transition-colors duration-200" 
                                                    title="Edit Account">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            {{-- Delete/Deactivate Account --}}
                                            @if($account->status === 'ACTIVE')
                                                <button wire:click="deleteOrDeactivateAccount({{ $account->id }})" 
                                                        class="text-red-500 hover:text-red-700 transition-colors duration-200" 
                                                        title="Delete/Deactivate Account">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="activateAccount({{ $account->id }})" 
                                                        class="text-green-500 hover:text-green-700 transition-colors duration-200" 
                                                        title="Activate Account">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- View Statement --}}
                                            <button wire:click="viewAccountStatement({{ $account->id }})" 
                                                    class="text-purple-500 hover:text-purple-700 transition-colors duration-200" 
                                                    title="View Statement">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            {{-- Download Statement --}}
                                            <button wire:click="downloadAccountStatement({{ $account->id }})" 
                                                    class="text-indigo-500 hover:text-blue-900 transition-colors duration-200" 
                                                    title="Download Statement">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                        </svg>
                                        <p class="mt-2 text-sm">No Level 4 accounts found for {{ $selectedLevel3AccountName }}.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- No accounts message --}}
        @if($showAccountTable && count($level2Accounts) === 0)
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-8">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Level 2 Accounts</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No level 2 accounts found for {{ $selectedAccountTypeName }}.
                    </p>
                    <div class="mt-6">
                        <button wire:click="showCreateAccountModal" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create First Account
                        </button>
                        <button wire:click="closeAccountTable" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Create Account Modal --}}
        @if($showCreateAccountModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="createAccountModal">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Create Internal Level 2 Account</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new internal account under the selected parent account</p>
                            </div>
                            <button wire:click="closeCreateAccountModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($parentAccount)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>Parent Account (Level 1):</strong> {{ $parentAccount->account_name ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                    <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $parentAccount->type ?? 'unknown')) }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                    <strong>Account Number:</strong> {{ $parentAccount->account_number ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                    <strong>Product Number:</strong> {{ $parentAccount->product_number ?: '0000' }}
                                </p>
                            </div>
                        @else
                            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900 rounded-lg">
                                <p class="text-sm text-red-800 dark:text-red-200">
                                    <strong>Error:</strong> Parent account information not available.
                                </p>
                            </div>
                        @endif

                        <form wire:submit.prevent="createNewAccount">
                            <div class="space-y-4">
                                <div>
                                    <label for="accountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Account Name
                                    </label>
                                    <input type="text" id="accountName" wire:model="accountName"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Enter account name">
                                    @error('accountName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">
                                        <strong>Note:</strong> This will create an internal account with client number '00000' and inherit the product number from the parent account.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-3 mt-6">
                                <button type="button" wire:click="closeCreateAccountModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Edit Account Modal --}}
        @if($showEditModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="editAccountModal">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Account</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update account information</p>
                            </div>
                            <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($editingAccount)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>Account Number:</strong> {{ $editingAccount->account_number ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                    <strong>Current Balance:</strong> {{ number_format($editingAccount->balance ?? 0, 2) }}
                                </p>
                            </div>
                        @endif

                        <form wire:submit.prevent="updateAccount">
                            <div class="space-y-4">
                                <div>
                                    <label for="editAccountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Account Name
                                    </label>
                                    <input type="text" id="editAccountName" wire:model="editAccountName"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Enter account name">
                                    @error('editAccountName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-3 mt-6">
                                <button type="button" wire:click="closeEditModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Update Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Account Statement Modal --}}
        @if($showStatementModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="statementModal">
                <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Account Statement</h3>
                                @if($statementAccount)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $statementAccount->account_name }} ({{ $statementAccount->account_number }})
                                    </p>
                                @endif
                            </div>
                            <button wire:click="closeStatementModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        @if($statementAccount)
                            {{-- Account Summary --}}
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Current Balance</h4>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-gray-500">
                                        {{ number_format($statementBalance, 2) }}
                                    </p>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Total Credits</h4>
                                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                        {{ number_format($statementTransactions->sum('credit'), 2) }}
                                    </p>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Total Debits</h4>
                                    <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                                        {{ number_format($statementTransactions->sum('debit'), 2) }}
                                    </p>
                                </div>
                                <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Transactions</h4>
                                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                                        {{ count($statementTransactions) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Filters --}}
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filters</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date From</label>
                                        <input type="date" wire:model="statementFilters.date_from" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date To</label>
                                        <input type="date" wire:model="statementFilters.date_to" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type</label>
                                        <select wire:model="statementFilters.transaction_type" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                            <option value="">All Types</option>
                                            <option value="DEPOSIT">Deposit</option>
                                            <option value="WITHDRAWAL">Withdrawal</option>
                                            <option value="TRANSFER">Transfer</option>
                                            <option value="PAYMENT">Payment</option>
                                            <option value="CHARGE">Charge</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Amount</label>
                                        <input type="number" step="0.01" wire:model="statementFilters.min_amount" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Amount</label>
                                        <input type="number" step="0.01" wire:model="statementFilters.max_amount" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Narration</label>
                                        <input type="text" wire:model="statementFilters.narration" 
                                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:text-white">
                                    </div>
                                </div>
                                <div class="flex space-x-3 mt-4">
                                    <button wire:click="applyStatementFilters" 
                                            class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Apply Filters
                                    </button>
                                    <button wire:click="clearStatementFilters" 
                                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Clear Filters
                                    </button>
                                </div>
                            </div>

                            {{-- Transactions Table --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Reference
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Narration
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Debit
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Credit
                                            </th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Balance
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse($statementTransactions as $transaction)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    {{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-mono">
                                                    {{ $transaction->reference_number ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    <div class="max-w-xs truncate" title="{{ $transaction->narration ?? 'No description' }}">
                                                        {{ $transaction->narration ?? 'No description' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    @if($transaction->debit > 0)
                                                        <span class="text-sm font-medium text-red-600 dark:text-red-400">
                                                            {{ number_format($transaction->debit, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    @if($transaction->credit > 0)
                                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                                            {{ number_format($transaction->credit, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                    <span class="text-sm font-medium {{ ($transaction->running_balance ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        {{ number_format($transaction->running_balance ?? 0, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                    <p class="mt-2 text-sm">No transactions found for this account.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Footer Actions --}}
                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Showing {{ count($statementTransactions) }} transactions
                                </div>
                                <div class="flex space-x-3">
                                    <button wire:click="downloadAccountStatement({{ $statementAccount->id }})" 
                                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Download PDF
                                    </button>
                                    <button wire:click="closeStatementModal" 
                                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Close
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Flash Messages --}}
        @if(session()->has('message'))
            <div class="fixed top-4 right-4 z-50">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="fixed top-4 right-4 z-50">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Level 3 Account Creation Modal --}}
        @if($showCreateLevel3Modal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="createLevel3Modal">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Create Level 3 Account
                            </h3>
                            <button wire:click="closeCreateLevel3Modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($level2ParentAccount)
                            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-md">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <strong>Parent Account:</strong> {{ $level2ParentAccount->account_name }} ({{ $level2ParentAccount->account_number }})
                                </p>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                    <strong>Type:</strong> {{ $level2ParentAccount->type }}
                                </p>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="level3AccountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="level3AccountName"
                                   wire:model="level3AccountName"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="Enter Level 3 account name">
                            @error('level3AccountName')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <button wire:click="closeCreateLevel3Modal" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                                Cancel
                            </button>
                            <button wire:click="createLevel3Account" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Create Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Level 4 Account Creation Modal --}}
        @if($showCreateLevel4Modal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="createLevel4Modal">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Create Level 4 Account
                            </h3>
                            <button wire:click="closeCreateLevel4Modal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($level3ParentAccount)
                            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900 rounded-md">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    <strong>Parent Account:</strong> {{ $level3ParentAccount->account_name }} ({{ $level3ParentAccount->account_number }})
                                </p>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    <strong>Type:</strong> {{ $level3ParentAccount->type }}
                                </p>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="level4AccountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="level4AccountName"
                                   wire:model="level4AccountName"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                   placeholder="Enter Level 4 account name">
                            @error('level4AccountName')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end space-x-3">
                            <button wire:click="closeCreateLevel4Modal" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
                                Cancel
                            </button>
                            <button wire:click="createLevel4Account" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                Create Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

  
</div>
