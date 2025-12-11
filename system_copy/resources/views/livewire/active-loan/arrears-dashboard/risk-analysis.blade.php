{{-- Risk Analysis Dashboard --}}
<div class="space-y-6">
    <!-- Risk Level Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Low Risk -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-low">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Low Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::where('status', 'ACTIVE')->where('days_in_arrears', 0)->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Performing loans</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::where('status', 'ACTIVE')->where('days_in_arrears', 0)->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Medium Risk -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-medium">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Medium Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">1-30 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- High Risk -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-high">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">High Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">31-90 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Critical Risk -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-critical">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Critical Risk</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::where('days_in_arrears', '>', 90)->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">90+ days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::where('days_in_arrears', '>', 90)->sum('principle'), 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Risk Analysis Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Risk Distribution -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk Distribution</h3>
            
            <div class="space-y-4">
                <!-- Low Risk -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Low Risk</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ number_format((App\Models\LoansModel::where('status', 'ACTIVE')->where('days_in_arrears', 0)->count() / App\Models\LoansModel::where('status', 'ACTIVE')->count()) * 100, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ App\Models\LoansModel::where('status', 'ACTIVE')->where('days_in_arrears', 0)->count() }} loans
                        </p>
                    </div>
                </div>
                
                <!-- Medium Risk -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Medium Risk</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ number_format((App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->count() / App\Models\LoansModel::where('status', 'ACTIVE')->count()) * 100, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->count() }} loans
                        </p>
                    </div>
                </div>
                
                <!-- High Risk -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-orange-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">High Risk</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ number_format((App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->count() / App\Models\LoansModel::where('status', 'ACTIVE')->count()) * 100, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->count() }} loans
                        </p>
                    </div>
                </div>
                
                <!-- Critical Risk -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Critical Risk</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ number_format((App\Models\LoansModel::where('days_in_arrears', '>', 90)->count() / App\Models\LoansModel::where('status', 'ACTIVE')->count()) * 100, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ App\Models\LoansModel::where('days_in_arrears', '>', 90)->count() }} loans
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk by Loan Product -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk by Loan Product</h3>
            
            <div class="space-y-4">
                @php
                    $products = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])
                        ->where('days_in_arrears', '>', 0)
                        ->join('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.product_id')
                        ->select('loan_sub_products.sub_product_name', DB::raw('count(*) as count'), DB::raw('sum(loans.principle) as total_amount'))
                        ->groupBy('loan_sub_products.sub_product_name')
                        ->orderBy('count', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @forelse($products as $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $product->sub_product_name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->count }} loans in arrears</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($product->total_amount, 2) }}</p>
                            <p class="text-xs text-gray-500">Total amount</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No product data available.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Risk Factors Analysis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk Factors Analysis</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Business Age Risk -->
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Business Age</h4>
                <p class="text-sm text-gray-600">New businesses (&lt; 2 years) show higher default rates</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        High Risk Factor
                    </span>
                </div>
            </div>
            
            <!-- Loan Amount Risk -->
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Loan Amount</h4>
                <p class="text-sm text-gray-600">Larger loans (&gt; TZS 500K) have higher risk</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        Medium Risk Factor
                    </span>
                </div>
            </div>
            
            <!-- Payment History Risk -->
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Payment History</h4>
                <p class="text-sm text-gray-600">Previous arrears increase future default risk</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        High Risk Factor
                    </span>
                </div>
            </div>
            
            <!-- Collateral Risk -->
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Collateral</h4>
                <p class="text-sm text-gray-600">Adequate collateral reduces default risk</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Low Risk Factor
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- High Risk Loans Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">High Risk Loans Requiring Immediate Attention</h3>
                <div class="flex space-x-2">
                    <select class="text-sm border border-gray-300 rounded-md px-3 py-1">
                        <option>All Risk Levels</option>
                        <option>Medium Risk</option>
                        <option>High Risk</option>
                        <option>Critical Risk</option>
                    </select>
                    <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">Export</button>
                </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Factors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recommended Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $highRiskLoans = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])
                            ->where('days_in_arrears', '>', 0)
                            ->orderBy('days_in_arrears', 'desc')
                            ->limit(15)
                            ->get();
                    @endphp
                    
                    @forelse($highRiskLoans as $loan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $loan->loan_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan->client->first_name ?? 'N/A' }} {{ $loan->client->last_name ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                TZS {{ number_format($loan->principle, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $loan->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : 
                                       ($loan->days_in_arrears > 30 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $loan->days_in_arrears }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $riskScore = 0;
                                    if ($loan->days_in_arrears > 90) $riskScore += 4;
                                    elseif ($loan->days_in_arrears > 30) $riskScore += 3;
                                    elseif ($loan->days_in_arrears > 0) $riskScore += 2;
                                    
                                    if ($loan->principle > 1000000) $riskScore += 3;
                                    elseif ($loan->principle > 500000) $riskScore += 2;
                                    elseif ($loan->principle > 100000) $riskScore += 1;
                                    
                                    if ($loan->business_age < 2) $riskScore += 2;
                                    elseif ($loan->business_age < 5) $riskScore += 1;
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full {{ $riskScore >= 6 ? 'bg-red-500' : ($riskScore >= 4 ? 'bg-orange-500' : 'bg-yellow-500') }}" 
                                             style="width: {{ ($riskScore / 8) * 100 }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $riskScore }}/8</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if($loan->days_in_arrears > 30)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Payment History
                                        </span>
                                    @endif
                                    @if($loan->principle > 500000)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                            Large Amount
                                        </span>
                                    @endif
                                    @if($loan->business_age < 2)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            New Business
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($loan->days_in_arrears > 90)
                                    <span class="text-red-600 font-medium">Legal Action</span>
                                @elseif($loan->days_in_arrears > 30)
                                    <span class="text-orange-600 font-medium">Restructure</span>
                                @else
                                    <span class="text-yellow-600 font-medium">Follow-up</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button class="text-green-600 hover:text-green-900">Contact</button>
                                    <button class="text-purple-600 hover:text-purple-900">Action</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No high risk loans found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">15</span> of <span class="font-medium">{{ $highRiskLoans->count() }}</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Mitigation Strategies -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk Mitigation Strategies</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Early Warning System -->
            <div class="border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Early Warning System</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Automated alerts for payment delays</li>
                    <li>• Risk scoring based on multiple factors</li>
                    <li>• Proactive client communication</li>
                    <li>• Regular portfolio monitoring</li>
                </ul>
            </div>
            
            <!-- Restructuring Options -->
            <div class="border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">Restructuring Options</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Payment term extensions</li>
                    <li>• Interest rate adjustments</li>
                    <li>• Principal moratorium periods</li>
                    <li>• Partial payment arrangements</li>
                </ul>
            </div>
            
            <!-- Recovery Actions -->
            <div class="border border-orange-200 rounded-lg p-4">
                <h4 class="font-medium text-orange-900 mb-2">Recovery Actions</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Collateral realization</li>
                    <li>• Guarantor involvement</li>
                    <li>• Legal proceedings</li>
                    <li>• Credit bureau reporting</li>
                </ul>
            </div>
        </div>
    </div>
</div>
