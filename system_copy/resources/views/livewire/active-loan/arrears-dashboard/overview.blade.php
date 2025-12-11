{{-- Arrears Overview Dashboard --}}
<div class="space-y-6">
  
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Portfolio at Risk -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Portfolio at Risk</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($portfolioAtRisk, 2) }}%</p>
                    <p class="text-sm text-gray-500">TZS {{ number_format($this->calculateTotalArrearsAmount(), 2) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Loans in Arrears -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Loans in Arrears</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $loansInArrears }}</p>
                    <p class="text-sm text-gray-500">Out of {{ $totalActiveLoans }} total active loans</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Days in Arrears -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Avg Days in Arrears</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $avgDaysInArrears }}</p>
                    <p class="text-sm text-gray-500">Days overdue across all delinquent loans</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Collection Rate -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Collection Rate</p>
                    <p class="text-2xl font-bold {{ $collectionRate >= 85 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($collectionRate, 1) }}%</p>
                    <p class="text-sm text-gray-500">{{ $collectionRate >= 85 ? 'Above target (85%)' : 'Below target (85%)' }}</p>
                </div>
                <div class="p-3 {{ $collectionRate >= 85 ? 'bg-green-100' : 'bg-red-100' }} rounded-full">
                    <svg class="w-6 h-6 {{ $collectionRate >= 85 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Critical Arrears (90+ days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-critical">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Critical Arrears</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $criticalArrears }}</p>
                    <p class="text-sm text-gray-500 mt-1">90+ days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Amount: TZS {{ number_format($criticalArrearsAmount, 2) }}</p>
            </div>
        </div>

        <!-- High Risk Arrears (30-90 days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-high">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">High Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $highRisk }}</p>
                    <p class="text-sm text-gray-500 mt-1">30-90 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Amount: TZS {{ number_format($highRiskAmount, 2) }}</p>
            </div>
        </div>

        <!-- Medium Risk Arrears (1-30 days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-medium">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Medium Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $mediumRisk }}</p>
                    <p class="text-sm text-gray-500 mt-1">1-30 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Amount: TZS {{ number_format($mediumRiskAmount, 2) }}</p>
            </div>
        </div>

        <!-- Performing Loans -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-low">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Performing</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $performing }}</p>
                    <p class="text-sm text-gray-500 mt-1">Current loans</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Amount: TZS {{ number_format($performingSchedulesAmount, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Portfolio at Risk Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Portfolio at Risk Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Portfolio at Risk (PAR)</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-full">30 Days</button>
                    <button class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-50 rounded-full">90 Days</button>
                </div>
            </div>
            
            <div class="space-y-4">
                <!-- PAR 30 -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">PAR 30</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($par30, 2) }}%</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($par30Amount, 2) }}</p>
                    </div>
                </div>
                
                <!-- PAR 90 -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">PAR 90</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($par90, 2) }}%</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($par90Amount, 2) }}</p>
                    </div>
                </div>
                
                <!-- Performing Portfolio -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Performing</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ number_format(100 - $portfolioAtRisk, 2) }}%</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($performingAmount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrears Distribution -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Arrears Distribution by Days</h3>
            
            <div class="space-y-4">
                <!-- 1-7 days -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">1-7 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ $arrears1to7 }}</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($this->calculateArrearsAmountByDays(1, 7), 2) }}</p>
                    </div>
                </div>
                
                <!-- 8-30 days -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">8-30 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ $arrears8to30 }}</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($this->calculateArrearsAmountByDays(8, 30), 2) }}</p>
                    </div>
                </div>
                
                <!-- 31-90 days -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">31-90 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ $arrears31to90 }}</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($this->calculateArrearsAmountByDays(31, 90), 2) }}</p>
                    </div>
                </div>
                
                <!-- 90+ days -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">90+ days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">{{ $arrears90plus }}</p>
                        <p class="text-xs text-gray-500">TZS {{ number_format($this->calculateArrearsAmountByDays(91, 9999), 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Arrears Activity -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Recent Arrears Activity</h3>
                <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentArrearsActivity as $arrears)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $arrears->loan_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $arrears->client_name ?? $arrears->client_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($arrears->arrears_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $arrears->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : 
                                       ($arrears->days_in_arrears > 30 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $arrears->days_in_arrears }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $arrears->risk_level == 'Critical' ? 'bg-red-100 text-red-800' : 
                                       ($arrears->risk_level == 'High' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $arrears->risk_level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $arrears->branch_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button class="text-green-600 hover:text-green-900">Contact</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No recent arrears activity found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Contact Clients</span>
            </button>
            
            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Schedule Follow-up</span>
            </button>
            
            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Generate Report</span>
            </button>
            
            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">Set Reminders</span>
            </button>
        </div>
    </div>
</div>
