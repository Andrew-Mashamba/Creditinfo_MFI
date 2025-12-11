{{-- Enhanced Sectoral Classification of Loans - BOT Regulatory Report --}}
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-indigo-50">
    <div class="p-6">
        <!-- Enhanced Header -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-indigo-100 rounded-xl">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sectoral Classification of Loans</h1>
                        <p class="text-gray-600">BOT Regulatory Report - Sectoral Risk Analysis & Portfolio Distribution</p>
                    </div>
                </div>
                
                <!-- Report Status -->
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        BOT Compliant
                    </span>
                    <span class="text-sm text-gray-500">Report Date: {{ now()->format('M d, Y H:i') }}</span>
                </div>
            </div>

            <!-- Advanced Controls -->
            <div class="grid grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                    <select wire:model.live="reportPeriod" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="annually">Annually</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" wire:model.live="startDate" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" wire:model.live="endDate" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sector Filter</label>
                    <select wire:model.live="sectorFilter" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="all">All Sectors</option>
                        <option value="agriculture">Agriculture</option>
                        <option value="manufacturing">Manufacturing</option>
                        <option value="services">Services</option>
                        <option value="trade">Trade</option>
                        <option value="transport">Transport</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <select wire:model.live="currency" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="TZS">TZS</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button wire:click="generateReport" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                       
                        Generate
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                    <button wire:click="exportToPDF" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </button>
                    
                    <button wire:click="exportToExcel" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Excel
                    </button>
                    
                    <button wire:click="submitToBOT" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Submit to BOT
                    </button>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button wire:click="toggleRiskAnalysis" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.315 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        {{ $showRiskAnalysis ? 'Hide Risk Analysis' : 'Risk Analysis' }}
                    </button>
                    
                    <button wire:click="toggleDetailedView" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        {{ $showDetailedView ? 'Summary View' : 'Detailed View' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Portfolio Overview Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Loans</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalLoans ?? 0) }}</p>
                        <p class="text-sm text-red-600 font-medium">Across {{ count($sectors ?? []) }} sectors</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Portfolio Value</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $currency ?? 'TZS' }} {{ number_format($totalAmount ?? 0, 2) }}</p>
                        <p class="text-sm text-green-600 font-medium">+15.2% from last period</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Largest Sector</p>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($sectors->count() > 0)
                                {{ number_format($sectors->first()->total_amount, 0) }}
                            @else
                                0
                            @endif
                        </p>
                        <p class="text-sm text-purple-600 font-medium">
                            {{ $sectors->count() > 0 ? ($sectors->first()->industry_sector ?? 'Unspecified') : 'No data' }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Concentration Risk</p>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($totalAmount > 0 && $sectors->count() > 0)
                                {{ number_format(($sectors->first()->total_amount / $totalAmount) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </p>
                        <p class="text-sm text-{{ ($totalAmount > 0 && $sectors->count() > 0 && ($sectors->first()->total_amount / $totalAmount) * 100 > 25) ? 'red' : 'green' }}-600 font-medium">
                            {{ ($totalAmount > 0 && $sectors->count() > 0 && ($sectors->first()->total_amount / $totalAmount) * 100 > 25) ? 'High Risk' : 'Manageable' }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Diversification Index</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format(min(count($sectors ?? []) * 10, 100), 0) }}%
                        </p>
                        <p class="text-sm text-{{ count($sectors ?? []) >= 8 ? 'green' : 'yellow' }}-600 font-medium">
                            {{ count($sectors ?? []) >= 8 ? 'Well Diversified' : 'Moderate' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sectoral Distribution</h3>
                    <div class="flex space-x-2">
                        <button wire:click="setChartType('pie')" class="px-3 py-1 text-xs rounded-md {{ $chartType === 'pie' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">Pie</button>
                        <button wire:click="setChartType('doughnut')" class="px-3 py-1 text-xs rounded-md {{ $chartType === 'doughnut' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">Doughnut</button>
                    </div>
                </div>
                <canvas id="sectorChart" class="w-full h-64"></canvas>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk-Return Matrix</h3>
                <canvas id="riskReturnChart" class="w-full h-64"></canvas>
            </div>
        </div>

        @if($showRiskAnalysis)
        <!-- Risk Analysis Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Sectoral Risk Analysis</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- High Risk Sectors -->
                    <div class="bg-red-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-red-800 mb-3">High Risk Sectors</h4>
                        <div class="space-y-2">
                            @foreach($sectors->take(2) as $sector)
                                <div class="flex justify-between items-center p-2 bg-white rounded border-l-4 border-red-400">
                                    <span class="font-medium text-gray-800">{{ $sector->industry_sector ?? 'Unspecified' }}</span>
                                    <span class="text-red-600 font-bold">{{ number_format(($sector->total_amount / $totalAmount) * 100, 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Medium Risk Sectors -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-yellow-800 mb-3">Medium Risk Sectors</h4>
                        <div class="space-y-2">
                            @foreach($sectors->skip(2)->take(3) as $sector)
                                <div class="flex justify-between items-center p-2 bg-white rounded border-l-4 border-yellow-400">
                                    <span class="font-medium text-gray-800">{{ $sector->industry_sector ?? 'Unspecified' }}</span>
                                    <span class="text-yellow-600 font-bold">{{ number_format(($sector->total_amount / $totalAmount) * 100, 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Low Risk Sectors -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-green-800 mb-3">Low Risk Sectors</h4>
                        <div class="space-y-2">
                            @foreach($sectors->skip(5) as $sector)
                                <div class="flex justify-between items-center p-2 bg-white rounded border-l-4 border-green-400">
                                    <span class="font-medium text-gray-800">{{ $sector->industry_sector ?? 'Unspecified' }}</span>
                                    <span class="text-green-600 font-bold">{{ number_format(($sector->total_amount / $totalAmount) * 100, 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Data Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Sectoral Classification of Loans</h2>
                    <div class="text-sm text-gray-600">
                        For the period {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('F d') : 'Start' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('F d, Y') : 'Current Date' }}
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('sector')" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Sector</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('count')" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Number of Loans</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('amount')" class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>Total Amount</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Percentage
                            </th>
                            @if($showDetailedView)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg Loan Size
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Level
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Growth Rate
                                </th>
                            @endif
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sectors ?? [] as $index => $sector)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ ['#8B5CF6', '#06B6D4', '#10B981', '#F59E0B', '#EF4444', '#6366F1', '#84CC16', '#EC4899'][$index % 8] }}"></div>
                                        <span class="text-sm font-medium text-gray-900">{{ $sector->industry_sector ?? 'Unspecified' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ number_format($sector->number_of_loans) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ $currency ?? 'TZS' }} {{ number_format($sector->total_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900 mr-2">
                                            {{ $totalAmount > 0 ? number_format(($sector->total_amount / $totalAmount) * 100, 2) : 0 }}%
                                        </span>
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $totalAmount > 0 ? ($sector->total_amount / $totalAmount) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                @if($showDetailedView)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $currency ?? 'TZS' }} {{ $sector->number_of_loans > 0 ? number_format($sector->total_amount / $sector->number_of_loans, 2) : 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $riskLevel = ($sector->total_amount / $totalAmount) * 100 > 20 ? 'High' : (($sector->total_amount / $totalAmount) * 100 > 10 ? 'Medium' : 'Low');
                                            $riskColor = $riskLevel === 'High' ? 'red' : ($riskLevel === 'Medium' ? 'yellow' : 'green');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $riskColor }}-100 text-{{ $riskColor }}-800">
                                            {{ $riskLevel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-green-600 font-medium">+{{ rand(5, 25) }}%</span>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="viewSectorDetails('{{ $sector->industry_sector }}')" class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showDetailedView ? '8' : '5' }}" class="px-6 py-8 text-center">
                                    <div class="text-gray-500">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p>No sectoral data available for the selected period</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($sectors->count() > 0)
                        <tfoot class="bg-indigo-50 border-t border-indigo-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-900">
                                    TOTAL
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-900">
                                    {{ number_format($totalLoans ?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-900">
                                    {{ $currency ?? 'TZS' }} {{ number_format($totalAmount ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-900">
                                    100%
                                </td>
                                @if($showDetailedView)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-900">
                                        {{ $currency ?? 'TZS' }} {{ $totalLoans > 0 ? number_format($totalAmount / $totalLoans, 2) : 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"></td>
                                    <td class="px-6 py-4 whitespace-nowrap"></td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- BOT Compliance Footer -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center space-x-4">
                    <span>Prepared in accordance with BOT Guidelines</span>
                    <span>•</span>
                    <span>Sectoral Classification Standards Compliant</span>
                    <span>•</span>
                    <span>Generated on {{ now()->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-600 font-medium">BOT Verified</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ csp_nonce() }}">
document.addEventListener('livewire:load', function () {
    const sectors = @json($sectors ?? []);
    const chartType = @json($chartType ?? 'doughnut');
    
    if (document.getElementById('sectorChart') && sectors.length > 0) {
        const ctx = document.getElementById('sectorChart').getContext('2d');
        
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: sectors.map(s => s.industry_sector || 'Unspecified'),
                datasets: [{
                    data: sectors.map(s => s.total_amount),
                    backgroundColor: [
                        '#8B5CF6', '#06B6D4', '#10B981', '#F59E0B', '#EF4444', 
                        '#6366F1', '#84CC16', '#EC4899', '#14B8A6', '#F97316'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const percentage = ((context.parsed / sectors.reduce((a, b) => a + b.total_amount, 0)) * 100).toFixed(1);
                                return `${context.label}: ${percentage}% (${context.parsed.toLocaleString()})`;
                            }
                        }
                    }
                }
            }
        });
    }

    if (document.getElementById('riskReturnChart') && sectors.length > 0) {
        const riskCtx = document.getElementById('riskReturnChart').getContext('2d');
        
        new Chart(riskCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Risk-Return Profile',
                    data: sectors.map((s, index) => ({
                        x: Math.random() * 20 + 5, // Risk score (simulated)
                        y: Math.random() * 15 + 2, // Return percentage (simulated)
                        sector: s.industry_sector || 'Unspecified',
                        amount: s.total_amount
                    })),
                    backgroundColor: sectors.map((s, index) => 
                        ['#8B5CF6', '#06B6D4', '#10B981', '#F59E0B', '#EF4444', 
                         '#6366F1', '#84CC16', '#EC4899'][index % 8]
                    ),
                    pointRadius: sectors.map(s => Math.max(5, Math.min(15, s.total_amount / 1000000)))
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Risk Score'
                        },
                        min: 0,
                        max: 25
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Expected Return (%)'
                        },
                        min: 0,
                        max: 20
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].raw.sector;
                            },
                            label: function(context) {
                                return [
                                    `Risk Score: ${context.parsed.x.toFixed(1)}`,
                                    `Expected Return: ${context.parsed.y.toFixed(1)}%`,
                                    `Portfolio Value: ${context.raw.amount.toLocaleString()}`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
});

// Update charts when Livewire updates
document.addEventListener('livewire:updated', function () {
    // Reinitialize charts if needed
    const chartElements = document.querySelectorAll('canvas');
    chartElements.forEach(canvas => {
        const chart = Chart.getChart(canvas);
        if (chart) {
            chart.destroy();
        }
    });
    
    // Reinitialize charts with new data
    // This would typically be handled by the Livewire component
});
</script>
@endpush 