{{-- Arrears by Amount Analysis --}}
<div class="space-y-6">
    <!-- Amount-based Risk Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Small Amount Arrears (< 100,000) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Small Amount</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '<', 100000)->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">&lt; TZS 100,000</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Total: TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '<', 100000)->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Medium Amount Arrears (100,000 - 500,000) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Medium Amount</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [100000, 500000])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">TZS 100K - 500K</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Total: TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [100000, 500000])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Large Amount Arrears (500,000 - 1,000,000) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Large Amount</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [500000, 1000000])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">TZS 500K - 1M</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Total: TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [500000, 1000000])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Critical Amount Arrears (> 1,000,000) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Critical Amount</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '>', 1000000)->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">&gt; TZS 1M</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Total: TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '>', 1000000)->sum('principle'), 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Portfolio at Risk Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Amount Distribution Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Arrears Distribution by Amount</h3>
            
            <div class="space-y-4">
                <!-- < 50,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">&lt; TZS 50,000</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '<', 50000)->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '<', 50000)->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
                
                <!-- 50,000 - 100,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-400 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">TZS 50K - 100K</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [50000, 100000])->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [50000, 100000])->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
                
                <!-- 100,000 - 250,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">TZS 100K - 250K</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [100000, 250000])->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [100000, 250000])->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
                
                <!-- 250,000 - 500,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-600 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">TZS 250K - 500K</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [250000, 500000])->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [250000, 500000])->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
                
                <!-- 500,000 - 1,000,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-orange-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">TZS 500K - 1M</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [500000, 1000000])->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->whereBetween('principle', [500000, 1000000])->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
                
                <!-- > 1,000,000 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">&gt; TZS 1M</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '>', 1000000)->count() }}
                        </p>
                        <p class="text-xs text-gray-500">
                            TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('principle', '>', 1000000)->sum('principle'), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Arrears by Amount -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Top Arrears by Amount</h3>
            
            <div class="space-y-4">
                @php
                    $topArrears = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])
                        ->where('days_in_arrears', '>', 0)
                        ->orderBy('principle', 'desc')
                        ->limit(10)
                        ->get();
                @endphp
                
                @forelse($topArrears as $index => $loan)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-blue-600">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $loan->loan_id }}</p>
                                <p class="text-xs text-gray-500">{{ $loan->client->first_name ?? 'N/A' }} {{ $loan->client->last_name ?? '' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">TZS {{ number_format($loan->principle, 2) }}</p>
                            <p class="text-xs text-gray-500">{{ $loan->days_in_arrears }} days</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No arrears found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Concentration Risk Analysis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Concentration Risk Analysis</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Top 10 Concentration -->
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">
                    @php
                        $totalArrears = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->sum('principle');
                        $top10Arrears = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->orderBy('principle', 'desc')->limit(10)->sum('principle');
                        $top10Percentage = $totalArrears > 0 ? ($top10Arrears / $totalArrears) * 100 : 0;
                    @endphp
                    {{ number_format($top10Percentage, 1) }}%
                </div>
                <p class="text-sm text-gray-600">Top 10 Loans</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format($top10Arrears, 2) }}
                </p>
            </div>
            
            <!-- Top 20 Concentration -->
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600 mb-2">
                    @php
                        $top20Arrears = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->orderBy('principle', 'desc')->limit(20)->sum('principle');
                        $top20Percentage = $totalArrears > 0 ? ($top20Arrears / $totalArrears) * 100 : 0;
                    @endphp
                    {{ number_format($top20Percentage, 1) }}%
                </div>
                <p class="text-sm text-gray-600">Top 20 Loans</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format($top20Arrears, 2) }}
                </p>
            </div>
            
            <!-- Average Arrears Amount -->
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">
                    TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->avg('principle') ?? 0, 0) }}
                </div>
                <p class="text-sm text-gray-600">Average Amount</p>
                <p class="text-xs text-gray-500 mt-1">Per delinquent loan</p>
            </div>
        </div>
    </div>

    <!-- Detailed Arrears by Amount Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Detailed Arrears by Amount</h3>
                <div class="flex space-x-2">
                    <select class="text-sm border border-gray-300 rounded-md px-3 py-1">
                        <option>All Amount Ranges</option>
                        <option>&lt; TZS 50,000</option>
                        <option>TZS 50K - 100K</option>
                        <option>TZS 100K - 250K</option>
                        <option>TZS 250K - 500K</option>
                        <option>TZS 500K - 1M</option>
                        <option>&gt; TZS 1M</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $arrearsByAmount = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])
                            ->where('days_in_arrears', '>', 0)
                            ->orderBy('principle', 'desc')
                            ->limit(20)
                            ->get();
                    @endphp
                    
                    @forelse($arrearsByAmount as $loan)
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
                                    {{ $loan->principle > 1000000 ? 'bg-red-100 text-red-800' : 
                                       ($loan->principle > 500000 ? 'bg-orange-100 text-orange-800' : 
                                       ($loan->principle > 100000 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ $loan->principle > 1000000 ? 'Critical' : 
                                       ($loan->principle > 500000 ? 'Large' : 
                                       ($loan->principle > 100000 ? 'Medium' : 'Small')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $loan->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : 
                                       ($loan->days_in_arrears > 30 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $loan->days_in_arrears }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $loan->days_in_arrears > 90 ? 'bg-red-100 text-red-800' : 
                                       ($loan->days_in_arrears > 30 ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $loan->days_in_arrears > 90 ? 'High' : 
                                       ($loan->days_in_arrears > 30 ? 'Medium' : 'Low') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan->loanBranch->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900">View</button>
                                    <button class="text-green-600 hover:text-green-900">Contact</button>
                                    <button class="text-purple-600 hover:text-purple-900">Restructure</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No arrears found matching the criteria.
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
                    Showing <span class="font-medium">1</span> to <span class="font-medium">20</span> of <span class="font-medium">{{ $arrearsByAmount->count() }}</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recovery Potential Analysis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Recovery Potential Analysis</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- High Recovery Potential -->
            <div class="border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-2">High Recovery Potential</h4>
                <p class="text-2xl font-bold text-green-600 mb-1">
                    {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('days_in_arrears', '<=', 30)->where('principle', '<=', 250000)->count() }}
                </p>
                <p class="text-sm text-gray-600">Small amounts, recent arrears</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where('days_in_arrears', '<=', 30)->where('principle', '<=', 250000)->sum('principle'), 2) }}
                </p>
            </div>
            
            <!-- Medium Recovery Potential -->
            <div class="border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">Medium Recovery Potential</h4>
                <p class="text-2xl font-bold text-yellow-600 mb-1">
                    {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 30)->where('days_in_arrears', '<=', 90)
                              ->orWhere('principle', '>', 250000)->where('principle', '<=', 500000);
                    })->count() }}
                </p>
                <p class="text-sm text-gray-600">Moderate amounts or duration</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 30)->where('days_in_arrears', '<=', 90)
                              ->orWhere('principle', '>', 250000)->where('principle', '<=', 500000);
                    })->sum('principle'), 2) }}
                </p>
            </div>
            
            <!-- Low Recovery Potential -->
            <div class="border border-orange-200 rounded-lg p-4">
                <h4 class="font-medium text-orange-900 mb-2">Low Recovery Potential</h4>
                <p class="text-2xl font-bold text-orange-600 mb-1">
                    {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 90)->where('days_in_arrears', '<=', 180)
                              ->orWhere('principle', '>', 500000)->where('principle', '<=', 1000000);
                    })->count() }}
                </p>
                <p class="text-sm text-gray-600">Large amounts or long duration</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 90)->where('days_in_arrears', '<=', 180)
                              ->orWhere('principle', '>', 500000)->where('principle', '<=', 1000000);
                    })->sum('principle'), 2) }}
                </p>
            </div>
            
            <!-- Very Low Recovery Potential -->
            <div class="border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">Very Low Recovery Potential</h4>
                <p class="text-2xl font-bold text-red-600 mb-1">
                    {{ App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 180)
                              ->orWhere('principle', '>', 1000000);
                    })->count() }}
                </p>
                <p class="text-sm text-gray-600">Critical amounts or duration</p>
                <p class="text-xs text-gray-500 mt-1">
                    TZS {{ number_format(App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])->where(function($query) {
                        $query->where('days_in_arrears', '>', 180)
                              ->orWhere('principle', '>', 1000000);
                    })->sum('principle'), 2) }}
                </p>
            </div>
        </div>
    </div>
</div>
