<div class="space-y-8">
    {{-- Enhanced Reports Hero Section --}}
    <div class="bg-gradient-to-br from-pink-500 via-pink-600 to-pink-700 rounded-3xl p-8 text-white shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold mb-2">Reports & Analytics Center</h3>
                <p class="text-pink-100 text-lg">Comprehensive insights and analytics for informed decision making</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-2xl p-6 backdrop-blur-sm">
                    <h4 class="text-lg font-semibold mb-2">Generated Reports</h4>
                    <p class="text-4xl font-bold">{{ count($generatedReports ?? []) }}</p>
                    <p class="text-pink-100 text-sm">This month</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Analytics Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-700">Daily Volume</p>
                    <p class="text-2xl font-bold text-blue-900">TZS {{ number_format($dailyVolume ?? 0) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center">
                <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-700">Growth Rate</p>
                    <p class="text-2xl font-bold text-green-900">+{{ number_format($growthRate ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
            <div class="flex items-center">
                <div class="p-3 bg-purple-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-700">Avg. Processing Time</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $avgProcessingTime ?? 0 }}m</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl p-6 border border-orange-200">
            <div class="flex items-center">
                <div class="p-3 bg-orange-500 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-orange-700">Success Rate</p>
                    <p class="text-2xl font-bold text-orange-900">{{ number_format($successRate ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Generation Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- Quick Reports --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Quick Reports</h3>
            </div>
            
            <div class="space-y-4">
                <button wire:click="generateDailyReport" class="w-full flex items-center p-4 text-left bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-blue-200">
                    <div class="p-2 bg-blue-500 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-blue-900">Daily Cash Summary</h4>
                        <p class="text-sm text-blue-700">Today's cash movements and balances</p>
                    </div>
                    <div class="text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>
                
                <button wire:click="generateWeeklyReport" class="w-full flex items-center p-4 text-left bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-green-200">
                    <div class="p-2 bg-green-500 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-green-900">Weekly Performance</h4>
                        <p class="text-sm text-green-700">7-day analysis and trends</p>
                    </div>
                    <div class="text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>
                
                <button wire:click="generateMonthlyReport" class="w-full flex items-center p-4 text-left bg-gradient-to-r from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-purple-200">
                    <div class="p-2 bg-purple-500 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-purple-900">Monthly Overview</h4>
                        <p class="text-sm text-purple-700">Comprehensive monthly analysis</p>
                    </div>
                    <div class="text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>
                
                <button wire:click="generateTillReport" class="w-full flex items-center p-4 text-left bg-gradient-to-r from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md border border-orange-200">
                    <div class="p-2 bg-orange-500 rounded-lg mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-orange-900">Till Performance</h4>
                        <p class="text-sm text-orange-700">Individual till analysis</p>
                    </div>
                    <div class="text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </button>
            </div>
        </div>

        {{-- Custom Report Builder --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Custom Report Builder</h3>
            </div>
            
            <form wire:submit.prevent="generateCustomReport" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Report Type</label>
                    <select wire:model="customReportType" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                        <option value="">Select Report Type</option>
                        <option value="transaction_summary">Transaction Summary</option>
                        <option value="cash_position">Cash Position</option>
                        <option value="till_performance">Till Performance</option>
                        <option value="variance_analysis">Variance Analysis</option>
                        <option value="approval_audit">Approval Audit</option>
                        <option value="compliance_report">Compliance Report</option>
                    </select>
                    @error('customReportType') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                        <input type="date" wire:model="reportFromDate" 
                               class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                        @error('reportFromDate') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                        <input type="date" wire:model="reportToDate" 
                               class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                        @error('reportToDate') <p class="text-red-500 text-sm mt-2 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filter Options</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <select wire:model="reportTillFilter" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                                <option value="">All Tills</option>
                                @foreach($availableTills ?? [] as $till)
                                    <option value="{{ $till->id }}">{{ $till->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model="reportUserFilter" class="w-full rounded-xl border-2 border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all duration-200 p-4">
                                <option value="">All Users</option>
                                @foreach($availableUsers ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Output Format</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-transparent cursor-pointer hover:border-indigo-200 transition-all duration-200">
                            <input type="radio" wire:model="reportFormat" value="pdf" class="mr-3">
                            <span class="text-sm font-medium">PDF</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-transparent cursor-pointer hover:border-indigo-200 transition-all duration-200">
                            <input type="radio" wire:model="reportFormat" value="excel" class="mr-3">
                            <span class="text-sm font-medium">Excel</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-50 rounded-xl border-2 border-transparent cursor-pointer hover:border-indigo-200 transition-all duration-200">
                            <input type="radio" wire:model="reportFormat" value="csv" class="mr-3">
                            <span class="text-sm font-medium">CSV</span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white py-4 px-6 rounded-xl hover:from-indigo-600 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 transition-all duration-200 font-bold text-lg shadow-lg">
                    Generate Custom Report
                </button>
            </form>
        </div>
    </div>

    {{-- Analytics Dashboard --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- Performance Metrics --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Performance Metrics</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($performanceMetrics ?? [] as $metric)
                    <div class="bg-gray-50 rounded-2xl p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-medium text-gray-900">{{ $metric['name'] }}</h4>
                            <span class="text-sm font-bold {{ $metric['trend'] === 'up' ? 'text-green-600' : ($metric['trend'] === 'down' ? 'text-red-600' : 'text-gray-600') }}">
                                {{ $metric['value'] }}
                                @if($metric['trend'] === 'up')
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"></path>
                                    </svg>
                                @elseif($metric['trend'] === 'down')
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"></path>
                                    </svg>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $metric['percentage'] }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ $metric['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- Key Insights --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <div class="flex items-center mb-6">
                <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Key Insights</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($keyInsights ?? [] as $insight)
                    <div class="bg-gradient-to-r from-{{ $insight['color'] }}-50 to-{{ $insight['color'] }}-100 rounded-2xl p-4 border border-{{ $insight['color'] }}-200">
                        <div class="flex items-start">
                            <div class="p-2 bg-{{ $insight['color'] }}-500 rounded-lg mr-3 flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $insight['icon'] }}"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-{{ $insight['color'] }}-900 mb-1">{{ $insight['title'] }}</h4>
                                <p class="text-sm text-{{ $insight['color'] }}-800">{{ $insight['description'] }}</p>
                                @if(isset($insight['action']))
                                    <button class="mt-2 text-{{ $insight['color'] }}-700 hover:text-{{ $insight['color'] }}-900 text-sm font-medium">
                                        {{ $insight['action'] }} →
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Generated Reports History --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 ml-4">Generated Reports History</h3>
            </div>
            <button wire:click="clearReportHistory" class="text-red-600 hover:text-red-800 text-sm font-medium bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-all duration-200">
                Clear History
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Report Name</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Type</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Generated By</th>
                        <th class="text-left py-4 px-6 text-sm font-bold text-gray-700">Generated On</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Size</th>
                        <th class="text-center py-4 px-6 text-sm font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($generatedReports ?? [] as $report)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-all duration-200">
                            <td class="py-4 px-6">
                                <div class="flex items-center">
                                    <div class="p-2 bg-pink-100 rounded-lg mr-3">
                                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $report->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $report->description ?? 'No description' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucwords(str_replace('_', ' ', $report->type)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm font-medium text-gray-900">{{ $report->generated_by->name ?? 'System' }}</p>
                                <p class="text-xs text-gray-600">{{ $report->generated_by->email ?? 'system@app.com' }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-sm text-gray-900">{{ $report->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-600">{{ $report->created_at->format('H:i:s') }}</p>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="text-sm text-gray-600">{{ $report->file_size ?? 'Unknown' }}</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="downloadReport({{ $report->id }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-all duration-200">
                                        Download
                                    </button>
                                    <button wire:click="viewReport({{ $report->id }})" 
                                            class="text-green-600 hover:text-green-800 text-sm font-medium bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg transition-all duration-200">
                                        View
                                    </button>
                                    <button wire:click="deleteReport({{ $report->id }})" 
                                            class="text-red-600 hover:text-red-800 text-sm font-medium bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition-all duration-200">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-20 h-20 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-xl font-bold text-gray-600 mb-2">No Reports Generated</h3>
                                    <p class="text-gray-500">Generate your first report to see it here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div> 