{{-- Arrears by Days Analysis --}}
<div class="space-y-6">
    <!-- Risk Classification Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Watch List (1-30 days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-low">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Watch List</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">1-30 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Substandard (31-90 days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-medium">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Substandard</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">31-90 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Doubtful (91-180 days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-high">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Doubtful</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::whereBetween('days_in_arrears', [91, 180])->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">91-180 days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [91, 180])->sum('principle'), 2) }}
                </p>
            </div>
        </div>

        <!-- Loss (180+ days) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 risk-indicator risk-critical">
            <div class="flex justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Loss</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ App\Models\LoansModel::where('days_in_arrears', '>', 180)->count() }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">180+ days overdue</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    Amount: TZS {{ number_format(App\Models\LoansModel::where('days_in_arrears', '>', 180)->sum('principle'), 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Detailed Arrears Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Arrears by Days Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Arrears Distribution by Days</h3>
            
            <div class="space-y-4">
                <!-- 1-7 days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">1-7 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears1to7 }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears1to7), 1) }}%
                        </p>
                    </div>
                </div>
                
                <!-- 8-15 days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-blue-400 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">8-15 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears8to15 }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears8to15), 1) }}%
                        </p>
                    </div>
                </div>
                
                <!-- 16-30 days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">16-30 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears16to30 }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears16to30), 1) }}%
                        </p>
                    </div>
                </div>
                
                <!-- 31-60 days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-orange-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">31-60 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears31to60 }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears31to60), 1) }}%
                        </p>
                    </div>
                </div>
                
                <!-- 61-90 days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-orange-600 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">61-90 days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears61to90 }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears61to90), 1) }}%
                        </p>
                    </div>
                </div>
                
                <!-- 90+ days -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">90+ days</span>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $arrears90plus }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($this->getPercentage($arrears90plus), 1) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Level Summary -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Risk Level Summary</h3>
            
            <div class="space-y-6">
                <!-- Low Risk -->
                <div class="border-l-4 border-blue-500 pl-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Low Risk (1-30 days)</p>
                            <p class="text-xs text-gray-500">Watch list - requires monitoring</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-blue-600">
                                {{ $watchLoans }}
                            </p>
                            <p class="text-xs text-gray-500">
                                TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [1, 30])->sum('principle'), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Medium Risk -->
                <div class="border-l-4 border-yellow-500 pl-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Medium Risk (31-90 days)</p>
                            <p class="text-xs text-gray-500">Substandard - requires action</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-yellow-600">
                                {{ $substandardLoans }}
                            </p>
                            <p class="text-xs text-gray-500">
                                TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [31, 90])->sum('principle'), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- High Risk -->
                <div class="border-l-4 border-orange-500 pl-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">High Risk (91-180 days)</p>
                            <p class="text-xs text-gray-500">Doubtful - high probability of loss</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-orange-600">
                                {{ $doubtfulLoans }}
                            </p>
                            <p class="text-xs text-gray-500">
                                TZS {{ number_format(App\Models\LoansModel::whereBetween('days_in_arrears', [91, 180])->sum('principle'), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Critical Risk -->
                <div class="border-l-4 border-red-500 pl-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Critical Risk (180+ days)</p>
                            <p class="text-xs text-gray-500">Loss - write-off consideration</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-red-600">
                                {{ $lossLoans }}
                            </p>
                            <p class="text-xs text-gray-500">
                                TZS {{ number_format(App\Models\LoansModel::where('days_in_arrears', '>', 180)->sum('principle'), 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Arrears Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Detailed Arrears by Days</h3>
                <div class="flex space-x-2">
                    <select class="text-sm border border-gray-300 rounded-md px-3 py-1">
                        <option>All Risk Levels</option>
                        <option>Low Risk (1-30 days)</option>
                        <option>Medium Risk (31-90 days)</option>
                        <option>High Risk (91-180 days)</option>
                        <option>Critical Risk (180+ days)</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Arrears</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Classification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Officer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $arrearsByDays = App\Models\LoansModel::whereIn('status', ['IN_ARREAR', 'DELINQUENT'])
                            ->where('days_in_arrears', '>', 0)
                            ->orderBy('days_in_arrears', 'desc')
                            ->limit(20)
                            ->get();
                    @endphp
                    
                    @forelse($arrearsByDays as $loan)
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
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $loan->days_in_arrears > 180 ? 'bg-red-100 text-red-800' : 
                                           ($loan->days_in_arrears > 90 ? 'bg-orange-100 text-orange-800' : 
                                           ($loan->days_in_arrears > 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ $loan->days_in_arrears }} days
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $loan->days_in_arrears > 180 ? 'bg-red-100 text-red-800' : 
                                       ($loan->days_in_arrears > 90 ? 'bg-orange-100 text-orange-800' : 
                                       ($loan->days_in_arrears > 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ $loan->days_in_arrears > 180 ? 'Loss' : 
                                       ($loan->days_in_arrears > 90 ? 'Doubtful' : 
                                       ($loan->days_in_arrears > 30 ? 'Substandard' : 'Watch')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan->updated_at ? $loan->updated_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $loan->supervisor_id ? 'Officer ' . $loan->supervisor_id : 'N/A' }}
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
                    Showing <span class="font-medium">1</span> to <span class="font-medium">20</span> of <span class="font-medium">{{ $arrearsByDays->count() }}</span> results
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Recommendations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommended Actions by Risk Level</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Watch List Actions -->
            <div class="border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-2">Watch List (1-30 days)</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Send payment reminders</li>
                    <li>• Schedule follow-up calls</li>
                    <li>• Monitor payment patterns</li>
                    <li>• Update contact information</li>
                </ul>
            </div>
            
            <!-- Substandard Actions -->
            <div class="border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">Substandard (31-90 days)</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Intensify collection efforts</li>
                    <li>• Visit client premises</li>
                    <li>• Review loan terms</li>
                    <li>• Consider restructuring</li>
                </ul>
            </div>
            
            <!-- Doubtful Actions -->
            <div class="border border-orange-200 rounded-lg p-4">
                <h4 class="font-medium text-orange-900 mb-2">Doubtful (91-180 days)</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Legal action preparation</li>
                    <li>• Collateral assessment</li>
                    <li>• Guarantor contact</li>
                    <li>• Write-off consideration</li>
                </ul>
            </div>
            
            <!-- Loss Actions -->
            <div class="border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-900 mb-2">Loss (180+ days)</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Immediate write-off</li>
                    <li>• Legal proceedings</li>
                    <li>• Collateral realization</li>
                    <li>• Credit bureau reporting</li>
                </ul>
            </div>
        </div>
    </div>
</div>
