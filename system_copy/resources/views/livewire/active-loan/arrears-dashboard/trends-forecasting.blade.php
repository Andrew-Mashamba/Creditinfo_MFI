{{-- Trends & Forecasting Dashboard --}}
<div class="space-y-6">
    <!-- Trend Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Monthly Trend -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Monthly Trend</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">-5.2%</p>
                    <p class="text-sm text-gray-500 mt-1">vs last month</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">Improving trend</span>
                </span>
            </div>
        </div>

        <!-- Quarterly Trend -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Quarterly Trend</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">-12.8%</p>
                    <p class="text-sm text-gray-500 mt-1">vs last quarter</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">Strong improvement</span>
                </span>
            </div>
        </div>

        <!-- Annual Trend -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Annual Trend</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">-18.5%</p>
                    <p class="text-sm text-gray-500 mt-1">vs last year</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">Excellent progress</span>
                </span>
            </div>
        </div>

        <!-- Forecast -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Next Month Forecast</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">-3.1%</p>
                    <p class="text-sm text-gray-500 mt-1">Expected change</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-blue-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">Based on current trends</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Trend Analysis Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Arrears Trend Over Time -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Arrears Trend Over Time</h3>
            
            <div class="space-y-4">
                <!-- Last 12 Months -->
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-700">Last 12 Months</h4>
                    <div class="grid grid-cols-6 gap-2">
                        <div class="text-center">
                            <div class="h-16 bg-red-200 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Jan</p>
                        </div>
                        <div class="text-center">
                            <div class="h-20 bg-red-300 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Feb</p>
                        </div>
                        <div class="text-center">
                            <div class="h-18 bg-orange-300 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Mar</p>
                        </div>
                        <div class="text-center">
                            <div class="h-14 bg-orange-200 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Apr</p>
                        </div>
                        <div class="text-center">
                            <div class="h-12 bg-yellow-200 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">May</p>
                        </div>
                        <div class="text-center">
                            <div class="h-10 bg-yellow-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Jun</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-6 gap-2">
                        <div class="text-center">
                            <div class="h-8 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Jul</p>
                        </div>
                        <div class="text-center">
                            <div class="h-6 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Aug</p>
                        </div>
                        <div class="text-center">
                            <div class="h-5 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Sep</p>
                        </div>
                        <div class="text-center">
                            <div class="h-4 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Oct</p>
                        </div>
                        <div class="text-center">
                            <div class="h-3 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Nov</p>
                        </div>
                        <div class="text-center">
                            <div class="h-2 bg-green-100 rounded mb-1"></div>
                            <p class="text-xs text-gray-500">Dec</p>
                        </div>
                    </div>
                </div>
                
                <!-- Trend Summary -->
                <div class="mt-6 p-4 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="text-sm font-medium text-green-800">Consistent downward trend observed</span>
                    </div>
                    <p class="text-xs text-green-700 mt-1">Arrears have decreased by 75% over the past 12 months</p>
                </div>
            </div>
        </div>

        <!-- Seasonal Patterns -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Seasonal Patterns</h3>
            
            <div class="space-y-4">
                <!-- Q1 Pattern -->
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Q1 (Jan-Mar)</p>
                        <p class="text-xs text-gray-500">Peak arrears period</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-red-600">+15%</p>
                        <p class="text-xs text-gray-500">Above average</p>
                    </div>
                </div>
                
                <!-- Q2 Pattern -->
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Q2 (Apr-Jun)</p>
                        <p class="text-xs text-gray-500">Gradual improvement</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-orange-600">+5%</p>
                        <p class="text-xs text-gray-500">Above average</p>
                    </div>
                </div>
                
                <!-- Q3 Pattern -->
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Q3 (Jul-Sep)</p>
                        <p class="text-xs text-gray-500">Stable period</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-yellow-600">-2%</p>
                        <p class="text-xs text-gray-500">Below average</p>
                    </div>
                </div>
                
                <!-- Q4 Pattern -->
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Q4 (Oct-Dec)</p>
                        <p class="text-xs text-gray-500">Best performance</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-green-600">-8%</p>
                        <p class="text-xs text-gray-500">Below average</p>
                    </div>
                </div>
            </div>
            
            <!-- Seasonal Insights -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-blue-900 mb-2">Seasonal Insights</h4>
                <ul class="text-xs text-blue-800 space-y-1">
                    <li>• Q1 shows highest arrears due to holiday spending</li>
                    <li>• Q4 shows best performance due to year-end collections</li>
                    <li>• Agricultural loans peak in Q2-Q3</li>
                    <li>• Business loans stable throughout the year</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Forecasting Models -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Forecasting Models</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Linear Regression -->
            <div class="border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Linear Regression Model</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Month:</span>
                        <span class="text-sm font-medium text-gray-900">-3.1%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Quarter:</span>
                        <span class="text-sm font-medium text-gray-900">-8.5%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Confidence:</span>
                        <span class="text-sm font-medium text-green-600">85%</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        High Accuracy
                    </span>
                </div>
            </div>
            
            <!-- Time Series -->
            <div class="border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">Time Series Model</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Month:</span>
                        <span class="text-sm font-medium text-gray-900">-2.8%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Quarter:</span>
                        <span class="text-sm font-medium text-gray-900">-7.2%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Confidence:</span>
                        <span class="text-sm font-medium text-green-600">78%</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Good Accuracy
                    </span>
                </div>
            </div>
            
            <!-- Seasonal Model -->
            <div class="border border-purple-200 rounded-lg p-4">
                <h4 class="font-medium text-purple-900 mb-2">Seasonal Model</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Month:</span>
                        <span class="text-sm font-medium text-gray-900">-4.2%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Next Quarter:</span>
                        <span class="text-sm font-medium text-gray-900">-9.1%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Confidence:</span>
                        <span class="text-sm font-medium text-green-600">82%</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        Seasonal Adjusted
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Scenarios -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk Scenarios & Stress Testing</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Base Case -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Base Case</h4>
                <p class="text-2xl font-bold text-green-600 mb-1">-3.1%</p>
                <p class="text-sm text-gray-600">Current trend continues</p>
                <p class="text-xs text-gray-500 mt-1">Probability: 60%</p>
            </div>
            
            <!-- Optimistic -->
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Optimistic</h4>
                <p class="text-2xl font-bold text-blue-600 mb-1">-6.5%</p>
                <p class="text-sm text-gray-600">Enhanced collection efforts</p>
                <p class="text-xs text-gray-500 mt-1">Probability: 25%</p>
            </div>
            
            <!-- Pessimistic -->
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Pessimistic</h4>
                <p class="text-2xl font-bold text-yellow-600 mb-1">+2.1%</p>
                <p class="text-sm text-gray-600">Economic downturn</p>
                <p class="text-xs text-gray-500 mt-1">Probability: 10%</p>
            </div>
            
            <!-- Stress Test -->
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Stress Test</h4>
                <p class="text-2xl font-bold text-red-600 mb-1">+8.3%</p>
                <p class="text-sm text-gray-600">Severe economic crisis</p>
                <p class="text-xs text-gray-500 mt-1">Probability: 5%</p>
            </div>
        </div>
    </div>

    <!-- Predictive Analytics Tools -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Update Models -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Update Models</p>
                <p class="text-xs text-gray-500">Refresh predictions</p>
            </div>
        </button>
        
        <!-- Export Forecasts -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Export Forecasts</p>
                <p class="text-xs text-gray-500">Download reports</p>
            </div>
        </button>
        
        <!-- Scenario Planning -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Scenario Planning</p>
                <p class="text-xs text-gray-500">Test scenarios</p>
            </div>
        </button>
        
        <!-- Alert Settings -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L12 7H4.828zM4.828 17l2.586-2.586a2 2 0 012.828 0L12 17H4.828z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Alert Settings</p>
                <p class="text-xs text-gray-500">Configure alerts</p>
            </div>
        </button>
    </div>
</div>
