<div class="min-h-screen bg-whitex">
    <!-- Enhanced Header Section -->
    <div class="bg-white border-b border-slate-200 sticky top-0 z-10 rounded-md">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-red-600 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Management Dashboard</h1>
                        <p class="mt-1 text-slate-600 font-medium">Executive overview and strategic insights</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm text-slate-500 font-medium">Last updated</p>
                            <p class="text-sm font-semibold text-slate-900">{{ now()->format('M d, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-green-600 font-medium">Live</span>
                        </div>
                    </div>
                    <button class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Section Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-1 h-8 bg-red-600 rounded-full"></div>
                <h2 class="text-2xl font-bold text-slate-900">Key Performance Indicators</h2>
            </div>
            <p class="text-slate-600 font-medium">Real-time financial metrics and performance indicators</p>
        </div>
        
        <!-- Enhanced Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Total Members -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Members</p>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 mb-1">{{ number_format($totalMembers) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Active accounts</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Savings -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Savings</p>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 mb-1">TZS {{ number_format($totalSavings, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Product #2000</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Deposits -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Deposits</p>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 mb-1">TZS {{ number_format($totalDeposits, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Product #3000</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Shares -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Shares</p>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 mb-1">TZS {{ number_format($totalShares, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Product #1000</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

       

        <!-- Second Row Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-8">
            <!-- Loan Portfolio -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Loan Portfolio</p>
                        </div>
                        <p class="text-3xl font-bold text-slate-900 mb-1">TZS {{ number_format($totalLoanPortfolio, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Product #4000</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Income -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Monthly Income</p>
                        </div>
                        <p class="text-3xl font-bold text-red-900 mb-1">TZS {{ number_format($monthlyIncome, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Current month</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Expenses -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Monthly Expenses</p>
                        </div>
                        <p class="text-3xl font-bold text-red-600 mb-1">TZS {{ number_format($monthlyExpenses, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Current month</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- NPL Ratio -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 {{ $nplRatio > 5 ? 'bg-red-50' : 'bg-red-50' }} opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 {{ $nplRatio > 5 ? 'bg-red-600' : 'bg-red-600' }} rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">NPL Ratio</p>
                        </div>
                        <p class="text-3xl font-bold {{ $nplRatio > 5 ? 'text-red-600' : 'text-red-900' }} mb-1">{{ $nplRatio }}%</p>
                        <p class="text-xs text-slate-500 font-medium">Non-performing loans</p>
                    </div>
                    <div class="p-4 {{ $nplRatio > 5 ? 'bg-red-600' : 'bg-red-600' }} rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Net Income YTD -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 {{ $netIncomeYTD >= 0 ? 'bg-red-50' : 'bg-red-50' }} opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 {{ $netIncomeYTD >= 0 ? 'bg-red-600' : 'bg-red-600' }} rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Net Income YTD</p>
                        </div>
                        <p class="text-3xl font-bold {{ $netIncomeYTD >= 0 ? 'text-red-900' : 'text-red-600' }} mb-1">TZS {{ number_format($netIncomeYTD, 0) }}</p>
                        <p class="text-xs text-slate-500 font-medium">Year to date</p>
                    </div>
                    <div class="p-4 {{ $netIncomeYTD >= 0 ? 'bg-red-600' : 'bg-red-600' }} rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ROI from Investments -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">ROI from Investments</p>
                        </div>
                        <p class="text-3xl font-bold text-red-900 mb-1">{{ $roiInvestments }}%</p>
                        <p class="text-xs text-slate-500 font-medium">Return on investment</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Budget Utilization -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-red-50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                            <p class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Budget Utilization</p>
                        </div>
                        <p class="text-3xl font-bold text-red-900 mb-1">{{ $budgetUtilization['percent'] }}%</p>
                        <p class="text-xs text-slate-500 font-medium">{{ number_format($budgetUtilization['utilized']) }} / {{ number_format($budgetUtilization['target']) }}</p>
                    </div>
                    <div class="p-4 bg-red-600 rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-1 h-8 bg-red-600 rounded-full"></div>
                <h2 class="text-2xl font-bold text-slate-900">Analytics & Insights</h2>
            </div>
            <p class="text-slate-600 font-medium">Comprehensive data visualization and trend analysis</p>
        </div>
        
        <!-- Enhanced Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Loan Disbursement vs Repayments -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4m8 0l-3 3-3-3 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Loan Disbursement vs Repayments</h3>
                            <p class="text-sm text-slate-600 font-medium">12-month trend analysis</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-600 rounded-full mr-2 shadow-sm"></div>
                            <span class="text-xs font-medium text-slate-700">Disbursed</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-600 rounded-full mr-2 shadow-sm"></div>
                            <span class="text-xs font-medium text-slate-700">Repaid</span>
                        </div>
                    </div>
                </div>
                <div id="loanChart" class="h-80 rounded-xl overflow-hidden"></div>
                <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-red-800 font-medium">
                            <span class="font-semibold">Note:</span> Repayments line shows actual completed payments only.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Expense Breakdown</h3>
                            <p class="text-sm text-slate-600 font-medium">Current month distribution</p>
                        </div>
                    </div>
                </div>
                <div id="expenseChart" class="h-80 rounded-xl overflow-hidden"></div>
            </div>

            <!-- Savings per Branch -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Savings per Branch</h3>
                            <p class="text-sm text-slate-600 font-medium">Branch performance comparison</p>
                        </div>
                    </div>
                </div>
                <div id="savingsChart" class="h-80 rounded-xl overflow-hidden"></div>
            </div>

            <!-- Deposits per Branch -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Deposits per Branch</h3>
                            <p class="text-sm text-slate-600 font-medium">Branch deposit analysis</p>
                        </div>
                    </div>
                </div>
                <div id="depositsChart" class="h-80 rounded-xl overflow-hidden"></div>
            </div>

            <!-- Shares per Branch -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Shares per Branch</h3>
                            <p class="text-sm text-slate-600 font-medium">Share distribution analysis</p>
                        </div>
                    </div>
                </div>
                <div id="sharesChart" class="h-80 rounded-xl overflow-hidden"></div>
            </div>

            <!-- Branch Locations -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Branch Locations</h3>
                            <p class="text-sm text-slate-600 font-medium">Geographic distribution</p>
                        </div>
                    </div>
                </div>
                <div class="h-80 bg-slate-50 rounded-xl border-2 border-dashed border-slate-300 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <p class="text-slate-500 font-medium">Interactive Map</p>
                        <p class="text-sm text-slate-400">Coming soon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Strategic Insights Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 mt-8">
            <!-- Pending High-Value Loans -->
            <div class="group bg-white rounded-2xl shadow-md border border-slate-200 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-yellow-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Pending High-Value Loans</h3>
                            <p class="text-sm text-slate-600 font-medium">Requires immediate attention</p>
                        </div>
                    </div>
                    <div class="px-4 py-2 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full border border-yellow-200">
                        {{ $pendingHighValueLoans->count() }} pending
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse($pendingHighValueLoans as $loan)
                        <div class="group/item flex items-center justify-between p-4 bg-yellow-50 rounded-xl border border-slate-200 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $loan->client_number }}</p>
                                    <p class="text-sm text-slate-600 font-medium">Loan ID: {{ $loan->id }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-900 text-lg">TZS {{ number_format($loan->principle, 0) }}</p>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    {{ $loan->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-slate-600 font-medium">No pending high-value loans</p>
                            <p class="text-sm text-slate-500 mt-1">All loans are processed</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Delinquent Loans -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Top Delinquent Loans</h3>
                        <p class="text-sm text-slate-600 mt-1">Requires collection action</p>
                    </div>
                    <div class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                        {{ $topDelinquentLoans->count() }} delinquent
                    </div>
                </div>
                <div class="space-y-4">
                    @forelse($topDelinquentLoans as $loan)
                        <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-900">{{ $loan->client_number }}</p>
                                <p class="text-sm text-slate-600">Loan ID: {{ $loan->id }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900">TZS {{ number_format($loan->principle, 0) }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $loan->days_in_arrears }} days
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-slate-500">No delinquent loans</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Branch Performance Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Branch Profitability -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Branch Profitability</h3>
                        <p class="text-sm text-slate-600 mt-1">Performance ranking</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse($branchProfitability as $index => $branch)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $branch->name }}</p>
                                    <p class="text-sm text-slate-600">Branch ID: {{ $branch->id }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-600">Profitability</p>
                                <p class="font-semibold text-slate-900">--</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="text-slate-500">No branch data available</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Branch Performance Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Branch Performance Summary</h3>
                        <p class="text-sm text-slate-600 mt-1">Regional overview</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse($branchPerformance as $branch)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div>
                                <p class="font-medium text-slate-900">{{ $branch->name }}</p>
                                <p class="text-sm text-slate-600">{{ $branch->region }}, {{ $branch->wilaya }}</p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="text-slate-500">No branch performance data</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.addEventListener('livewire:load', function() {
    // Loan Chart (Line Chart)
    const loanChartOptions = {
        series: {!! json_encode($loanChartData['series']) !!},
        chart: {
            height: 320,
            type: 'line',
            zoom: {
                enabled: false
            },
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false,
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        title: {
            text: 'Loan Disbursement vs Repayments',
            align: 'left',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        },
        grid: {
            row: {
                colors: ['#f8fafc', 'transparent'],
                opacity: 0.5
            },
            borderColor: '#e2e8f0'
        },
        xaxis: {
            categories: {!! json_encode($loanChartData['labels']) !!},
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return "TZS " + (val / 1000000).toFixed(1) + "M"
                }
            }
        },
        legend: {
            fontFamily: 'Inter, sans-serif',
            position: 'top'
        },
        tooltip: {
            style: {
                fontFamily: 'Inter, sans-serif'
            },
            y: {
                formatter: function (val) {
                    return "TZS " + val.toLocaleString()
                }
            }
        },
        colors: ['#1E3A8A', '#DC2626']
    };

    const loanChart = new ApexCharts(document.querySelector("#loanChart"), loanChartOptions);
    loanChart.render();

    // Expense Chart (Pie Chart)
    const expenseChartOptions = {
        series: {!! json_encode($expenseChartData['series']) !!},
        chart: {
            type: 'pie',
            height: 320,
            fontFamily: 'Inter, sans-serif'
        },
        labels: {!! json_encode($expenseChartData['labels']) !!},
        colors: ['#1E3A8A', '#DC2626', '#1E40AF', '#B91C1C', '#1D4ED8'],
        legend: {
            position: 'bottom',
            fontFamily: 'Inter, sans-serif'
        },
        title: {
            text: 'Expense Breakdown',
            align: 'center',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        },
        dataLabels: {
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '0%'
                }
            }
        }
    };

    const expenseChart = new ApexCharts(document.querySelector("#expenseChart"), expenseChartOptions);
    expenseChart.render();

    // Savings Chart (Column Chart)
    const savingsChartOptions = {
        series: [{
            name: 'Savings',
            data: {!! json_encode($savingsChartData['series']) !!}
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                endingShape: 'rounded',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false,
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: {!! json_encode($savingsChartData['labels']) !!},
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Amount (TZS)',
                style: {
                    fontFamily: 'Inter, sans-serif'
                }
            },
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return "TZS " + (val / 1000000).toFixed(1) + "M"
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            style: {
                fontFamily: 'Inter, sans-serif'
            },
            y: {
                formatter: function (val) {
                    return "TZS " + val.toLocaleString()
                }
            }
        },
        colors: ['#1E3A8A'],
        title: {
            text: 'Savings per Branch',
            align: 'center',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        }
    };

    const savingsChart = new ApexCharts(document.querySelector("#savingsChart"), savingsChartOptions);
    savingsChart.render();

    // Deposits Chart (Column Chart)
    const depositsChartOptions = {
        series: [{
            name: 'Deposits',
            data: {!! json_encode($depositsChartData['series']) !!}
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                endingShape: 'rounded',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false,
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: {!! json_encode($depositsChartData['labels']) !!},
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Amount (TZS)',
                style: {
                    fontFamily: 'Inter, sans-serif'
                }
            },
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return "TZS " + (val / 1000000).toFixed(1) + "M"
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            style: {
                fontFamily: 'Inter, sans-serif'
            },
            y: {
                formatter: function (val) {
                    return "TZS " + val.toLocaleString()
                }
            }
        },
        colors: ['#DC2626'],
        title: {
            text: 'Deposits per Branch',
            align: 'center',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        }
    };

    const depositsChart = new ApexCharts(document.querySelector("#depositsChart"), depositsChartOptions);
    depositsChart.render();

    // Shares Chart (Column Chart)
    const sharesChartOptions = {
        series: [{
            name: 'Shares',
            data: {!! json_encode($sharesChartData['series']) !!}
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                endingShape: 'rounded',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false,
            style: {
                fontFamily: 'Inter, sans-serif'
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: {!! json_encode($sharesChartData['labels']) !!},
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Amount (TZS)',
                style: {
                    fontFamily: 'Inter, sans-serif'
                }
            },
            labels: {
                style: {
                    fontFamily: 'Inter, sans-serif',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return "TZS " + (val / 1000000).toFixed(1) + "M"
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            style: {
                fontFamily: 'Inter, sans-serif'
            },
            y: {
                formatter: function (val) {
                    return "TZS " + val.toLocaleString()
                }
            }
        },
        colors: ['#1E3A8A'],
        title: {
            text: 'Shares per Branch',
            align: 'center',
            style: {
                fontFamily: 'Inter, sans-serif',
                fontWeight: 600
            }
        }
    };

    const sharesChart = new ApexCharts(document.querySelector("#sharesChart"), sharesChartOptions);
    sharesChart.render();
});
</script>
@endpush
