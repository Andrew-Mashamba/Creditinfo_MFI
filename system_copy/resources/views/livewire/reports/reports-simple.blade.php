<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if($activeComponent === 'dashboard')
        @if(!($permissions['canView'] ?? false))
        <!-- No Access Message -->
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
            <p class="text-gray-500">You don't have permission to access the reports module.</p>
        </div>
        @else
        <!-- Reports Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reports Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-500">Generate and view comprehensive financial reports</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" 
                                   wire:model.debounce.300ms="searchTerm"
                                   placeholder="Search reports..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <!-- Filter -->
                        <select wire:model="selectedCategory" 
                                class="border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            <option value="regulatory">Regulatory</option>
                            <option value="loans">Loans</option>
                            <option value="operational">Operational</option>
                            <option value="financial">Financial</option>
                            <option value="compliance">Compliance</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Statements Section -->
        @if($permissions['canView'] ?? false)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Financial Statements</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Statement of Financial Position -->
                @if($permissions['canView'] ?? false)
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('statement-of-financial-position')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Statement of Financial Position</h3>
                                    <p class="text-sm text-gray-500">Balance Sheet</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT, IFRS
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Regulatory
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Comprehensive balance sheet showing assets, liabilities, and equity positions</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Statement of Comprehensive Income -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('statement-of-comprehensive-income')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Statement of Comprehensive Income</h3>
                                    <p class="text-sm text-gray-500">Income Statement</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT, IFRS
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Regulatory
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Income statement showing revenue, expenses, and net income</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Statement of Cash Flow -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('statement-of-cash-flow')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Statement of Cash Flow</h3>
                                    <p class="text-sm text-gray-500">Cash Flow</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT, IFRS
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Regulatory
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Statement showing cash inflows and outflows from operating, investing, and financing activities</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Loan Reports Section -->
        @if($permissions['canView'] ?? false)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Loan Reports</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loan Portfolio Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loan-portfolio-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Portfolio Report</h3>
                                    <p class="text-sm text-gray-500">Portfolio Analysis</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    Operational
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Comprehensive analysis of loan portfolio performance and risk</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loan Delinquency Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loan-delinquency-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Delinquency Report</h3>
                                    <p class="text-sm text-gray-500">Risk Management</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mb-1">
                                    Risk
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Analysis of overdue loans and delinquency patterns</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loan Disbursement Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loan-disbursement-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Disbursement Report</h3>
                                    <p class="text-sm text-gray-500">Disbursements</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-1">
                                    Operational
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Summary of loans disbursed during the reporting period</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Portfolio at Risk -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('portfolio-at-risk')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Portfolio at Risk</h3>
                                    <p class="text-sm text-gray-500">Risk Assessment</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mb-1">
                                    Risk
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Analysis of loans at risk and potential losses</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Loans by Officer -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('active-loans-by-officer')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Active Loans by Officer</h3>
                                    <p class="text-sm text-gray-500">Performance</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    Management
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Performance analysis of loan officers and their portfolios</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loan Application Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loan-application-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Application Report</h3>
                                    <p class="text-sm text-gray-500">Applications</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                    Operational
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Summary of loan applications received and processed</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Member Reports Section -->
        @if($permissions['canView'] ?? false)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Member Reports</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Member Details Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('clients-details-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Member Details Report</h3>
                                    <p class="text-sm text-gray-500">Member Information</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    Operational
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Comprehensive client information and membership details</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Member Loan Account -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('client-loan-account')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Member Loan Account</h3>
                                    <p class="text-sm text-gray-500">Account Details</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-1">
                                    Operational
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Individual member loan account statements and balances</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Member Repayment History -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('client-repayment-history')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Member Repayment History</h3>
                                    <p class="text-sm text-gray-500">Payment Records</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                    Historical
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Complete repayment history and payment patterns for members</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Regulatory Reports Section -->
        @if($permissions['canView'] ?? false)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Regulatory Reports</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Capital Adequacy Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('capital-adequacy-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Capital Adequacy Report</h3>
                                    <p class="text-sm text-gray-500">BOT Compliance</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Computation of capital adequacy ratio for regulatory compliance</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Liquid Assets Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('liquid-assets-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Liquid Assets Report</h3>
                                    <p class="text-sm text-gray-500">Liquidity Analysis</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Computation of liquid assets for regulatory compliance</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Deposits and Loans in Banks -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('bank-deposits-loans-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Bank Deposits & Loans</h3>
                                    <p class="text-sm text-gray-500">Interbank Relations</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Deposits and loans in banks and financial institutions</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loans to Insiders -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loans-to-insiders-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loans to Insiders</h3>
                                    <p class="text-sm text-gray-500">Related Parties</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Loans to insiders and related parties disclosure</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Geographical Distribution -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('geographical-distribution-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Geographical Distribution</h3>
                                    <p class="text-sm text-gray-500">Branch Analysis</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Geographical distribution of branches, employees and loans by age</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Complaint Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('complaint-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Complaint Report</h3>
                                    <p class="text-sm text-gray-500">Customer Feedback</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-1">
                                    BOT Required
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Monthly complaint report for regulatory compliance</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Operational Reports Section -->
        @if($permissions['canView'] ?? false)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Operational Reports</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Daily Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('daily-operations-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Daily Report</h3>
                                    <p class="text-sm text-gray-500">Daily Operations</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                    Daily
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Daily operational summary and transaction reports</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Daily Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- General Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('general-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">General Report</h3>
                                    <p class="text-sm text-gray-500">Overview</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-1">
                                    General
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">General operational overview and summary reports</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Financial Ratio Report -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('financial-ratio-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Financial Ratio Report</h3>
                                    <p class="text-sm text-gray-500">Performance Analysis</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                    Analysis
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Financial ratio analysis and performance indicators</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Interest Rates Structure -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('interest-rates-structure-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Interest Rates Structure</h3>
                                    <p class="text-sm text-gray-500">Rate Analysis</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mb-1">
                                    Rates
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Interest rates structure for different loan products</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Sectoral Classification -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('sectoral-classification-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Sectoral Classification</h3>
                                    <p class="text-sm text-gray-500">Loan Categories</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mb-1">
                                    Classification
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Sectoral classification of loans by industry and purpose</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Loan Repayment Schedule -->
                <div class="bg-white shadow rounded-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer"
                     wire:click="showComponent('loan-repayment-schedule-report')">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Loan Repayment Schedule</h3>
                                    <p class="text-sm text-gray-500">Payment Schedule</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 mb-1">
                                    Schedule
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Detailed loan repayment schedules and payment plans</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Monthly Report</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Reports Summary -->
        @if($permissions['canView'] ?? false)
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900">39</h4>
                        <p class="text-sm text-gray-500">Available Reports</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900">Real-time</h4>
                        <p class="text-sm text-gray-500">Data Updates</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900">PDF/Excel</h4>
                        <p class="text-sm text-gray-500">Export Formats</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
        
    @elseif($activeComponent === 'statement-of-financial-position')
        <!-- Statement of Financial Position Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.statement-of-financial-position')
    @elseif($activeComponent === 'statement-of-comprehensive-income')
        <!-- Statement of Comprehensive Income Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.statement-of-comprehensive-income')
    @elseif($activeComponent === 'statement-of-cash-flow')
        <!-- Statement of Cash Flow Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.statement-of-cash-flow')
    @elseif($activeComponent === 'loan-portfolio-report')
        <!-- Loan Portfolio Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loan-portfolio-report')
    @elseif($activeComponent === 'loan-delinquency-report')
        <!-- Loan Delinquency Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loan-delinquency-report')
    @elseif($activeComponent === 'loan-disbursement-report')
        <!-- Loan Disbursement Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loan-disbursement-report')
    @elseif($activeComponent === 'portfolio-at-risk')
        <!-- Portfolio at Risk Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.portifolio-at-risk')
    @elseif($activeComponent === 'active-loans-by-officer')
        <!-- Active Loans by Officer Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.active-loans-by-officer')
    @elseif($activeComponent === 'loan-application-report')
        <!-- Loan Application Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loan-application-report')
    @elseif($activeComponent === 'clients-details-report')
        <!-- Member Details Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.clients-details-report')
    @elseif($activeComponent === 'client-loan-account')
        <!-- Member Loan Account Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.client-loan-account')
    @elseif($activeComponent === 'client-repayment-history')
        <!-- Member Repayment History Component Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.client-repayment-history')
    @elseif($activeComponent === 'capital-adequacy-report')
        <!-- Capital Adequacy Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.capital-adequacy-report')
    @elseif($activeComponent === 'liquid-assets-report')
        <!-- Liquid Assets Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.liquid-assets-report')
    @elseif($activeComponent === 'bank-deposits-loans-report')
        <!-- Bank Deposits & Loans Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.bank-deposits-loans-report')
    @elseif($activeComponent === 'loans-to-insiders-report')
        <!-- Loans to Insiders Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loans-to-insiders-report')
    @elseif($activeComponent === 'geographical-distribution-report')
        <!-- Geographical Distribution Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.geographical-distribution-report')
    @elseif($activeComponent === 'complaint-report')
        <!-- Complaint Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.complaint-report')
    @elseif($activeComponent === 'daily-operations-report')
        <!-- Daily Operations Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.daily-operations-report')
    @elseif($activeComponent === 'general-report')
        <!-- General Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.general-report')
    @elseif($activeComponent === 'financial-ratio-report')
        <!-- Financial Ratio Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.financial-ratio-report')
    @elseif($activeComponent === 'interest-rates-structure-report')
        <!-- Interest Rates Structure Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.interest-rates-structure-report')
    @elseif($activeComponent === 'sectoral-classification-report')
        <!-- Sectoral Classification Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.sectoral-classification-report')
    @elseif($activeComponent === 'loan-repayment-schedule-report')
        <!-- Loan Repayment Schedule Report Component -->
        <div class="mb-6">
            <button wire:click="backToDashboard" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </button>
        </div>
        @livewire('reports.loan-repayment-schedule-report')
    @endif
</div>
