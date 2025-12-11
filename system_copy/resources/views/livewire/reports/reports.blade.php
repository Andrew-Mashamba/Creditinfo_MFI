{{-- Comprehensive Reports Management Dashboard --}}
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Reports Management</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive financial reporting and analytics dashboard - {{ count($this->getReportTypes()) }} reports available</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="loadAnalytics" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh Analytics
                    </button>
                    <button wire:click="showScheduleReport" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Schedule Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.debounce.300ms="searchTerm" type="text" placeholder="Search reports..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="sm:w-64">
                    <select wire:model="selectedCategory" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($this->getCategoryInfo() as $key => $category)
                            <option value="{{ $key }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Clear Filters -->
                <button wire:click="clearFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($showReportView && $currentReport)
            <!-- Individual Report View -->
            <div class="space-y-6">
                <!-- Report Header -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <button wire:click="backToReportsList" class="mr-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Back to Reports
                                </button>
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ $currentReport['name'] }}</h1>
                                    <p class="mt-1 text-sm text-gray-500">{{ $currentReport['title'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $currentReport['compliance'] }}
                                </span>
                                @if($currentReport['category'] === 'regulatory')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Regulatory
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-gray-700">{{ $currentReport['description'] }}</p>
                    </div>
                </div>

                <!-- Report Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Report Actions</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex flex-wrap gap-4">
                            <button wire:click="generateReport" 
                                    wire:loading.attr="disabled" 
                                    wire:target="generateReport"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                <svg wire:loading.remove wire:target="generateReport" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <svg wire:loading wire:target="generateReport" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                                <span wire:loading wire:target="generateReport">Generating...</span>
                            </button>

                                    @if($currentReportId == 37)
                                        <!-- Statement of Financial Position Export Buttons -->
                                        <button wire:click="exportStatementOfFinancialPosition('pdf')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfFinancialPosition"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfFinancialPosition" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfFinancialPosition" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfFinancialPosition">Export PDF</span>
                                            <span wire:loading wire:target="exportStatementOfFinancialPosition">Exporting...</span>
                                        </button>

                                        <button wire:click="exportStatementOfFinancialPosition('excel')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfFinancialPosition"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfFinancialPosition" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfFinancialPosition" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfFinancialPosition">Export Excel</span>
                                            <span wire:loading wire:target="exportStatementOfFinancialPosition">Exporting...</span>
                                        </button>
                                    @elseif($currentReportId == 38)
                                        <!-- Statement of Comprehensive Income Export Buttons -->
                                        <button wire:click="exportStatementOfComprehensiveIncome('pdf')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfComprehensiveIncome"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfComprehensiveIncome" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfComprehensiveIncome" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfComprehensiveIncome">Export PDF</span>
                                            <span wire:loading wire:target="exportStatementOfComprehensiveIncome">Exporting...</span>
                                        </button>

                                        <button wire:click="exportStatementOfComprehensiveIncome('excel')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfComprehensiveIncome"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfComprehensiveIncome" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfComprehensiveIncome" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfComprehensiveIncome">Export Excel</span>
                                            <span wire:loading wire:target="exportStatementOfComprehensiveIncome">Exporting...</span>
                                        </button>
                                    @else
                                        <!-- Default Export Buttons -->
                                        <button wire:click="exportStatementOfFinancialPosition('pdf')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfFinancialPosition"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfFinancialPosition" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfFinancialPosition" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfFinancialPosition">Export PDF</span>
                                            <span wire:loading wire:target="exportStatementOfFinancialPosition">Exporting...</span>
                                        </button>

                                        <button wire:click="exportStatementOfFinancialPosition('excel')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportStatementOfFinancialPosition"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportStatementOfFinancialPosition" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportStatementOfFinancialPosition" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportStatementOfFinancialPosition">Export Excel</span>
                                            <span wire:loading wire:target="exportStatementOfFinancialPosition">Exporting...</span>
                                        </button>
                                    @else
                                        <!-- Default Export Buttons -->
                                        <button wire:click="exportReport('pdf')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportReport"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportReport" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportReport" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportReport">Export PDF</span>
                                            <span wire:loading wire:target="exportReport">Exporting...</span>
                                        </button>

                                        <button wire:click="exportReport('excel')"
                                                wire:loading.attr="disabled"
                                                wire:target="exportReport"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                            <svg wire:loading.remove wire:target="exportReport" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <svg wire:loading wire:target="exportReport" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="exportReport">Export Excel</span>
                                            <span wire:loading wire:target="exportReport">Exporting...</span>
                                        </button>
                                    @endif

                            <button wire:click="showScheduleReport" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Schedule Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Content -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Report Content</h3>
                    </div>
                    <div class="px-6 py-8">
                        @if($showStatementView && $statementData && $currentReportId == 37)
                            <!-- Statement of Financial Position -->
                            <div class="space-y-6">
                                <!-- Statement Header -->
                                <div class="text-center border-b pb-4">
                                    <h2 class="text-2xl font-bold text-gray-900">STATEMENT OF FINANCIAL POSITION</h2>
                                    <p class="text-lg text-gray-600 mt-2">As at {{ \Carbon\Carbon::parse($statementData['as_of_date'])->format('F d, Y') }}</p>
                                    <p class="text-sm text-gray-500 mt-1">(All amounts in Tanzanian Shillings)</p>
                                    @if(DB::table('general_ledger')->count() > 0 && DB::table('general_ledger')->count() <= 10)
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-sm text-blue-800">
                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                <strong>Demo Mode:</strong> This statement includes sample data for demonstration purposes. In a live system, this would show actual transaction data.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Statement Content -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <!-- Assets Section -->
                                    <div class="space-y-4">
                                        <h3 class="text-xl font-bold text-gray-900 border-b-2 border-blue-600 pb-2">ASSETS</h3>
                                        
                                        @foreach($statementData['assets']['categories'] as $categoryCode => $category)
                                            <div class="space-y-2">
                                                <h4 class="text-lg font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                                <div class="ml-4 space-y-1">
                                                    @foreach($category['accounts'] as $account)
                                                        <div class="flex justify-between items-center py-1">
                                                            <span class="text-sm text-gray-700">{{ $account->account_name }}</span>
                                                            <span class="text-sm font-mono text-gray-900">{{ number_format($account->current_balance, 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                    <div class="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                                        <span class="text-sm text-gray-800">Subtotal</span>
                                                        <span class="text-sm font-mono text-gray-900">{{ number_format($category['subtotal'], 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <div class="flex justify-between items-center py-3 border-t-2 border-blue-600 font-bold text-lg">
                                            <span class="text-gray-900">TOTAL ASSETS</span>
                                            <span class="font-mono text-gray-900">{{ number_format($statementData['assets']['total'], 2) }}</span>
                                        </div>
                                    </div>

                                    <!-- Liabilities and Equity Section -->
                                    <div class="space-y-4">
                                        <!-- Liabilities -->
                                        <div class="space-y-4">
                                            <h3 class="text-xl font-bold text-gray-900 border-b-2 border-red-600 pb-2">LIABILITIES</h3>
                                            
                                            @foreach($statementData['liabilities']['categories'] as $categoryCode => $category)
                                                <div class="space-y-2">
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                                    <div class="ml-4 space-y-1">
                                                        @foreach($category['accounts'] as $account)
                                                            <div class="flex justify-between items-center py-1">
                                                                <span class="text-sm text-gray-700">{{ $account->account_name }}</span>
                                                                <span class="text-sm font-mono text-gray-900">{{ number_format($account->current_balance, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                        <div class="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                                            <span class="text-sm text-gray-800">Subtotal</span>
                                                            <span class="text-sm font-mono text-gray-900">{{ number_format($category['subtotal'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <div class="flex justify-between items-center py-3 border-t-2 border-red-600 font-bold text-lg">
                                                <span class="text-gray-900">TOTAL LIABILITIES</span>
                                                <span class="font-mono text-gray-900">{{ number_format($statementData['liabilities']['total'], 2) }}</span>
                                            </div>
                                        </div>

                                        <!-- Equity -->
                                        <div class="space-y-4">
                                            <h3 class="text-xl font-bold text-gray-900 border-b-2 border-green-600 pb-2">EQUITY</h3>
                                            
                                            @foreach($statementData['equity']['categories'] as $categoryCode => $category)
                                                <div class="space-y-2">
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                                    <div class="ml-4 space-y-1">
                                                        @foreach($category['accounts'] as $account)
                                                            <div class="flex justify-between items-center py-1">
                                                                <span class="text-sm text-gray-700">{{ $account->account_name }}</span>
                                                                <span class="text-sm font-mono text-gray-900">{{ number_format($account->current_balance, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                        <div class="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                                            <span class="text-sm text-gray-800">Subtotal</span>
                                                            <span class="text-sm font-mono text-gray-900">{{ number_format($category['subtotal'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <div class="flex justify-between items-center py-3 border-t-2 border-green-600 font-bold text-lg">
                                                <span class="text-gray-900">TOTAL EQUITY</span>
                                                <span class="font-mono text-gray-900">{{ number_format($statementData['equity']['total'], 2) }}</span>
                                            </div>
                                        </div>

                                        <!-- Total Liabilities and Equity -->
                                        <div class="flex justify-between items-center py-3 border-t-2 border-gray-400 font-bold text-lg">
                                            <span class="text-gray-900">TOTAL LIABILITIES & EQUITY</span>
                                            <span class="font-mono text-gray-900">{{ number_format($statementData['totals']['total_liabilities_and_equity'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Balance Check -->
                                <div class="mt-8 p-4 rounded-lg {{ $statementData['totals']['is_balanced'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                    <div class="flex items-center justify-center">
                                        @if($statementData['totals']['is_balanced'])
                                            <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-green-800 font-semibold">Statement is Balanced: Assets = Liabilities + Equity</span>
                                        @else
                                            <svg class="w-6 h-6 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="text-red-800 font-semibold">Statement is NOT Balanced - Difference: {{ number_format($statementData['totals']['difference'], 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($showStatementView && $statementData && $currentReportId == 38)
                            <!-- Statement of Comprehensive Income -->
                            <div class="space-y-6">
                                <!-- Statement Header -->
                                <div class="text-center border-b pb-4">
                                    <h2 class="text-2xl font-bold text-gray-900">STATEMENT OF COMPREHENSIVE INCOME</h2>
                                    <p class="text-lg text-gray-600 mt-2">
                                        For the period from {{ \Carbon\Carbon::parse($statementData['period_start'])->format('F d, Y') }} 
                                        to {{ \Carbon\Carbon::parse($statementData['period_end'])->format('F d, Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">(All amounts in Tanzanian Shillings)</p>
                                    @if(DB::table('general_ledger')->count() > 0 && DB::table('general_ledger')->count() <= 20)
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-sm text-blue-800">
                                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                <strong>Demo Mode:</strong> This statement includes sample data for demonstration purposes. In a live system, this would show actual transaction data.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Statement Content -->
                                <div class="space-y-6">
                                    <!-- Revenue Section -->
                                    <div class="bg-white border border-gray-200 rounded-lg">
                                        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                                            <h3 class="text-xl font-bold text-gray-900">REVENUE</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            @foreach($statementData['income']['categories'] as $categoryCode => $category)
                                                <div class="space-y-2 mb-4">
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                                    <div class="ml-4 space-y-1">
                                                        @foreach($category['accounts'] as $account)
                                                            <div class="flex justify-between items-center py-1">
                                                                <span class="text-sm text-gray-700">{{ $account->account_name }}</span>
                                                                <span class="text-sm font-mono text-gray-900">{{ number_format($account->current_balance, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                        <div class="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                                            <span class="text-sm text-gray-800">Subtotal</span>
                                                            <span class="text-sm font-mono text-gray-900">{{ number_format($category['subtotal'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <div class="flex justify-between items-center py-3 border-t-2 border-green-600 font-bold text-lg">
                                                <span class="text-gray-900">TOTAL REVENUE</span>
                                                <span class="font-mono text-gray-900">{{ number_format($statementData['income']['total'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Expenses Section -->
                                    <div class="bg-white border border-gray-200 rounded-lg">
                                        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                                            <h3 class="text-xl font-bold text-gray-900">EXPENSES</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            @foreach($statementData['expenses']['categories'] as $categoryCode => $category)
                                                <div class="space-y-2 mb-4">
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $category['name'] }}</h4>
                                                    <div class="ml-4 space-y-1">
                                                        @foreach($category['accounts'] as $account)
                                                            <div class="flex justify-between items-center py-1">
                                                                <span class="text-sm text-gray-700">{{ $account->account_name }}</span>
                                                                <span class="text-sm font-mono text-gray-900">{{ number_format($account->current_balance, 2) }}</span>
                                                            </div>
                                                        @endforeach
                                                        <div class="flex justify-between items-center py-2 border-t border-gray-200 font-semibold">
                                                            <span class="text-sm text-gray-800">Subtotal</span>
                                                            <span class="text-sm font-mono text-gray-900">{{ number_format($category['subtotal'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <div class="flex justify-between items-center py-3 border-t-2 border-red-600 font-bold text-lg">
                                                <span class="text-gray-900">TOTAL EXPENSES</span>
                                                <span class="font-mono text-gray-900">{{ number_format($statementData['expenses']['total'], 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Net Income Section -->
                                    <div class="bg-white border border-gray-200 rounded-lg">
                                        <div class="px-6 py-4 border-b border-gray-200 {{ $statementData['totals']['is_profitable'] ? 'bg-blue-50' : 'bg-red-50' }}">
                                            <h3 class="text-xl font-bold text-gray-900">NET INCOME</h3>
                                        </div>
                                        <div class="px-6 py-4">
                                            <div class="flex justify-between items-center py-3 border-t-2 border-gray-400 font-bold text-xl">
                                                <span class="text-gray-900">NET INCOME (LOSS)</span>
                                                <span class="font-mono {{ $statementData['totals']['is_profitable'] ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($statementData['totals']['net_income'], 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Performance Summary -->
                                    <div class="mt-8 p-4 rounded-lg {{ $statementData['totals']['is_profitable'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                        <div class="flex items-center justify-center">
                                            @if($statementData['totals']['is_profitable'])
                                                <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-green-800 font-semibold">Profitable Period: Net Income of {{ number_format($statementData['totals']['net_income'], 2) }} TZS</span>
                                            @else
                                                <svg class="w-6 h-6 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-red-800 font-semibold">Loss Period: Net Loss of {{ number_format(abs($statementData['totals']['net_income']), 2) }} TZS</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Default Report Preview -->
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Report Preview</h3>
                                <p class="mt-1 text-sm text-gray-500">Click "Generate Report" to view the report content.</p>
                                <div class="mt-6">
                                    <button wire:click="generateReport" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- Reports Grid -->
            <div class="space-y-8">
            @php
                $filteredReports = $this->getFilteredReports();
                $categorizedReports = [];
                foreach ($filteredReports as $id => $report) {
                    $category = $report['category'];
                    if (!isset($categorizedReports[$category])) {
                        $categorizedReports[$category] = [];
                    }
                    $categorizedReports[$category][$id] = $report;
                }
            @endphp

            @if(empty($filteredReports))
                <!-- No Results -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No reports found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                    <div class="mt-6">
                        <button wire:click="clearFilters" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Clear Filters
                        </button>
                    </div>
                </div>
            @else
                @foreach($categorizedReports as $categoryKey => $reports)
                    @php
                        $categoryInfo = $this->getCategoryInfo()[$categoryKey];
                        $colorClasses = [
                            'red' => 'border-red-200 bg-red-50',
                            'blue' => 'border-blue-200 bg-blue-50', 
                            'green' => 'border-green-200 bg-green-50',
                            'purple' => 'border-purple-200 bg-purple-50',
                            'yellow' => 'border-yellow-200 bg-yellow-50',
                            'indigo' => 'border-indigo-200 bg-indigo-50',
                            'pink' => 'border-pink-200 bg-pink-50'
                        ];
                        $iconColors = [
                            'red' => 'text-red-500',
                            'blue' => 'text-blue-500',
                            'green' => 'text-green-500', 
                            'purple' => 'text-purple-500',
                            'yellow' => 'text-yellow-500',
                            'indigo' => 'text-indigo-500',
                            'pink' => 'text-pink-500'
                        ];
                    @endphp
                    
                    <!-- Category Section -->
                    <div class="bg-white shadow rounded-lg {{ $colorClasses[$categoryInfo['color']] ?? 'border-gray-200 bg-gray-50' }}">
                        <div class="px-6 py-4 border-b {{ $colorClasses[$categoryInfo['color']] ?? 'border-gray-200' }}">
                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 {{ $iconColors[$categoryInfo['color']] ?? 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($categoryInfo['icon'] === 'shield-check')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    @elseif($categoryInfo['icon'] === 'currency-dollar')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    @elseif($categoryInfo['icon'] === 'chart-bar')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    @elseif($categoryInfo['icon'] === 'clipboard-document-check')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    @elseif($categoryInfo['icon'] === 'exclamation-triangle')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    @elseif($categoryInfo['icon'] === 'table-cells')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    @elseif($categoryInfo['icon'] === 'sparkles')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    @endif
                                </svg>
                                {{ $categoryInfo['name'] }}
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ count($reports) }} reports
                                </span>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $categoryInfo['description'] }}</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($reports as $id => $report)
                                    <button wire:click="menuItemClicked({{ $id }})" class="w-full text-left p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 group">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <h4 class="font-medium text-gray-900 group-hover:text-indigo-600">{{ $report['name'] }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-500 mb-2">{{ $report['description'] }}</p>
                                                <div class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $report['compliance'] }}
                                                    </span>
                                                    @if($report['category'] === 'regulatory')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                            Regulatory
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            </div>
        @endif
    </div>

    <!-- Success/Error Messages -->
    @if($successMessage)
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Loading Overlay -->
    @if($isLoading)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Loading Analytics</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Please wait while we load the latest data...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
