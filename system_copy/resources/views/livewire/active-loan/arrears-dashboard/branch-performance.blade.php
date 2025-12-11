{{-- Branch Performance Dashboard --}}
<div class="space-y-6">
    <!-- Branch Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Best Performing Branch -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Best Performing</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">HQ Branch</p>
                    <p class="text-sm text-gray-500 mt-1">Collection Rate: 94.2%</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Arrears: TZS 125,000
                </p>
            </div>
        </div>

        <!-- Worst Performing Branch -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Needs Attention</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">Mwanza Branch</p>
                    <p class="text-sm text-gray-500 mt-1">Collection Rate: 78.5%</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Arrears: TZS 2.1M
                </p>
            </div>
        </div>

        <!-- Average Performance -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Average Performance</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">87.3%</p>
                    <p class="text-sm text-gray-500 mt-1">Collection rate</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="flex items-center text-green-500 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1">+2.1% from last month</span>
                </span>
            </div>
        </div>

        <!-- Total Branches -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Branches</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">8</p>
                    <p class="text-sm text-gray-500 mt-1">Active branches</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Across all regions
                </p>
            </div>
        </div>
    </div>

    <!-- Branch Performance Comparison -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Branch Performance Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Branch Performance Comparison</h3>
            
            <div class="space-y-4">
                <!-- HQ Branch -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">HQ Branch</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">94.2%</p>
                        <p class="text-xs text-gray-500">TZS 125K arrears</p>
                    </div>
                </div>
                
                <!-- Dar es Salaam Branch -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Dar es Salaam</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">91.5%</p>
                        <p class="text-xs text-gray-500">TZS 450K arrears</p>
                    </div>
                </div>
                
                <!-- Arusha Branch -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-400 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Arusha</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">89.3%</p>
                        <p class="text-xs text-gray-500">TZS 320K arrears</p>
                    </div>
                </div>
                
                <!-- Dodoma Branch -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Dodoma</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">85.7%</p>
                        <p class="text-xs text-gray-500">TZS 680K arrears</p>
                    </div>
                </div>
                
                <!-- Mwanza Branch -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Mwanza</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">78.5%</p>
                        <p class="text-xs text-gray-500">TZS 2.1M arrears</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Arrears Distribution -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Branch Arrears Distribution</h3>
            
            <div class="space-y-4">
                @php
                    $branches = App\Models\LoansModel::whereIn('loans.status', ['IN_ARREAR', 'DELINQUENT'])
                        ->where('loans.days_in_arrears', '>', 0)
                        ->join('branches', DB::raw('CAST(loans.branch_id AS BIGINT)'), '=', 'branches.id')
                        ->select('branches.name', DB::raw('count(*) as count'), DB::raw('sum(loans.principle) as total_amount'))
                        ->groupBy('branches.name')
                        ->orderBy('total_amount', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse($branches as $branch)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $branch->name }}</p>
                            <p class="text-xs text-gray-500">{{ $branch->count }} loans in arrears</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($branch->total_amount, 2) }}</p>
                            <p class="text-xs text-gray-500">Total arrears</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No branch data available.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Detailed Branch Performance Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Detailed Branch Performance</h3>
                <div class="flex space-x-2">
                    <select class="text-sm border border-gray-300 rounded-md px-3 py-1">
                        <option>All Branches</option>
                        <option>HQ Branch</option>
                        <option>Dar es Salaam</option>
                        <option>Arusha</option>
                        <option>Dodoma</option>
                        <option>Mwanza</option>
                    </select>
                    <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export</button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Loans</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Loans</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrears Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Sample branch data -->
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">HQ Branch</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">245</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">198</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">12</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS 125,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">94.2%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Excellent
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-green-600 hover:text-green-900">Report</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dar es Salaam</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">189</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">156</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS 450,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">91.5%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Good
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-green-600 hover:text-green-900">Report</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Arusha</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">156</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">134</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">22</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS 320,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">89.3%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Good
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-green-600 hover:text-green-900">Report</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dodoma</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">134</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">112</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">28</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS 680,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">85.7%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Fair
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-orange-600 hover:text-orange-900">Support</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Mwanza</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">98</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">78</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">35</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">TZS 2,100,000</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">78.5%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Poor
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-red-600 hover:text-red-900">Intervene</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">8</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Improvement Recommendations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Branch Improvement Recommendations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Mwanza Branch -->
            <div class="border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">Mwanza Branch - Immediate Action Required</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Collection rate below 80% threshold</li>
                    <li>• High concentration of large arrears</li>
                    <li>• Need additional collection staff</li>
                    <li>• Implement daily collection meetings</li>
                    <li>• Consider branch manager training</li>
                </ul>
            </div>
            
            <!-- Dodoma Branch -->
            <div class="border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">Dodoma Branch - Monitoring Required</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Collection rate declining trend</li>
                    <li>• Increase follow-up frequency</li>
                    <li>• Review collection procedures</li>
                    <li>• Provide additional support</li>
                    <li>• Monthly performance reviews</li>
                </ul>
            </div>
            
            <!-- Best Practices -->
            <div class="border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">Best Practices from HQ Branch</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Proactive client communication</li>
                    <li>• Early warning system implementation</li>
                    <li>• Regular staff training programs</li>
                    <li>• Performance-based incentives</li>
                    <li>• Technology-driven collection tools</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Branch Performance Tools -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Branch Comparison -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Branch Comparison</p>
                <p class="text-xs text-gray-500">Compare performance</p>
            </div>
        </button>
        
        <!-- Performance Reports -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Performance Reports</p>
                <p class="text-xs text-gray-500">Generate reports</p>
            </div>
        </button>
        
        <!-- Training Programs -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Training Programs</p>
                <p class="text-xs text-gray-500">Schedule training</p>
            </div>
        </button>
        
        <!-- Support Requests -->
        <button class="flex items-center justify-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z" />
            </svg>
            <div class="text-left">
                <p class="text-sm font-medium text-gray-900">Support Requests</p>
                <p class="text-xs text-gray-500">Request help</p>
            </div>
        </button>
    </div>
</div>
