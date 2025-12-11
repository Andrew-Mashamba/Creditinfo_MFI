<div class="bg-white rounded-lg shadow-sm">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">NOTES TO THE FINANCIAL STATEMENTS v</h2>
            <p class="text-sm">For the year ended 31 December {{ $selectedYear }}</p>
        </div>
    </div>
    
    <!-- Year Selector -->
    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <label class="text-xs font-medium text-gray-700">Reporting Year:</label>
                <select wire:model="selectedYear" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="text-xs text-gray-600">
                Comparative Year: {{ $selectedYear - 1 }}
            </div>
        </div>
    </div>
    
    <!-- Notes Content -->
    <div class="p-4 space-y-6">
        
        <!-- Note 1: Corporate Information -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('corporate')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">1. CORPORATE INFORMATION</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('corporate', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('corporate', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <div class="space-y-2 text-xs">
                    <p><strong>Entity Name:</strong> {{ $corporateInfo['name'] }}</p>
                    <p><strong>Nature of Business:</strong> {{ $corporateInfo['nature'] }}</p>
                    <p><strong>Registration:</strong> {{ $corporateInfo['registration'] }}</p>
                    <p><strong>Registered Address:</strong> {{ $corporateInfo['address'] }}</p>
                    <p><strong>Reporting Period:</strong> {{ $corporateInfo['reporting_period'] }}</p>
                    <p><strong>Functional Currency:</strong> {{ $corporateInfo['functional_currency'] }}</p>
                    
                    <div class="mt-3">
                        <p class="font-semibold mb-1">Principal Activities:</p>
                        <ul class="list-disc list-inside ml-2 space-y-1">
                            @foreach($corporateInfo['principal_activities'] as $activity)
                                <li>{{ $activity }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Note 2: Statement of Compliance -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('compliance')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">2. STATEMENT OF COMPLIANCE</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('compliance', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('compliance', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <div class="text-xs space-y-2">
                    <p>The financial statements have been prepared in accordance with International Financial Reporting Standards (IFRS) as issued by the International Accounting Standards Board (IASB).</p>
                    <p>The financial statements comply with the requirements of the Cooperative Societies Act and other relevant regulations applicable in Tanzania.</p>
                    <p>The accounting policies adopted are consistent with those of the previous financial year.</p>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Note 3: Summary of Significant Accounting Policies -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('policies')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">3. SIGNIFICANT ACCOUNTING POLICIES</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('policies', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('policies', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                @foreach($accountingPolicies as $policy)
                <div class="mb-3 border-b border-gray-100 pb-2 last:border-0">
                    <h4 class="text-xs font-semibold text-blue-800 mb-1">{{ $policy['title'] }}</h4>
                    <p class="text-xs text-gray-700 mb-2">{{ $policy['content'] }}</p>
                    
                    @if(isset($policy['subcategories']) && count($policy['subcategories']) > 0)
                    <div class="ml-3 space-y-1">
                        @foreach($policy['subcategories'] as $subTitle => $subContent)
                        <div class="text-xs">
                            <span class="font-medium text-gray-800">{{ $subTitle }}:</span>
                            <span class="text-gray-600">{{ $subContent }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 4: Critical Accounting Estimates and Judgments -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('estimates')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">4. CRITICAL ACCOUNTING ESTIMATES AND JUDGMENTS</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('estimates', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('estimates', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <p class="text-xs text-gray-700 mb-2">
                    The preparation of financial statements requires management to make judgments, estimates and assumptions that affect the reported amounts. Actual results may differ from these estimates.
                </p>
                <div class="space-y-2">
                    @foreach($significantEstimates as $estimate)
                    <div class="border-l-2 border-blue-300 pl-2">
                        <h5 class="text-xs font-semibold text-gray-800">{{ $estimate['title'] }}</h5>
                        <p class="text-xs text-gray-600">{{ $estimate['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        
        <!-- Note 5: Assets -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('assets')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">5. ASSETS</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('assets', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('assets', $expandedNotes))
            <div class="px-3 py-4 bg-white">
                @foreach($financialData['assets'] as $asset)
                <div class="mb-6 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-xs font-semibold text-gray-800">
                            {{ $asset['account_name'] }}
                            <span class="text-gray-500 font-normal">({{ $asset['account_number'] }})</span>
                        </h5>
                        <button wire:click="showAccountDetails('{{ $asset['account_number'] }}', '{{ $asset['account_name'] }}')"
                                class="text-xs text-blue-600 hover:text-blue-800">
                            View Details →
                        </button>
                    </div>
                    
                    <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Description</th>
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-semibold bg-blue-100">
                                <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($asset['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right {{ $asset['movements']['change_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}" style="width: 16.67%;">
                                    {{ number_format($asset['movements']['change_percentage'], 1) }}%
                                </td>
                            </tr>
                            
                            @if(count($asset['composition']) > 0)
                            <tr>
                                <td colspan="{{ count($comparisonYears) + 2 }}" class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Composition:
                                </td>
                            </tr>
                            @foreach($asset['composition'] as $component)
                            <tr class="bg-gray-50 text-gray-600 hover:bg-gray-100">
                                <td class="border border-gray-300 px-2 py-1 pl-4" style="width: 50%;">{{ $component['account_name'] }}</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($component['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;"></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 6: Liabilities -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('liabilities')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">6. LIABILITIES</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('liabilities', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('liabilities', $expandedNotes))
            <div class="px-3 py-4 bg-white">
                @foreach($financialData['liabilities'] as $liability)
                <div class="mb-6 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-xs font-semibold text-gray-800">
                            {{ $liability['account_name'] }}
                            <span class="text-gray-500 font-normal">({{ $liability['account_number'] }})</span>
                        </h5>
                        <button wire:click="showAccountDetails('{{ $liability['account_number'] }}', '{{ $liability['account_name'] }}')"
                                class="text-xs text-blue-600 hover:text-blue-800">
                            View Details →
                        </button>
                    </div>
                    
                    <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Description</th>
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-semibold bg-blue-100">
                                <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($liability['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right {{ $liability['movements']['change_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}" style="width: 16.67%;">
                                    {{ number_format($liability['movements']['change_percentage'], 1) }}%
                                </td>
                            </tr>
                            
                            @if(count($liability['composition']) > 0)
                            <tr>
                                <td colspan="{{ count($comparisonYears) + 2 }}" class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Composition:
                                </td>
                            </tr>
                            @foreach($liability['composition'] as $component)
                            <tr class="bg-gray-50 text-gray-600 hover:bg-gray-100">
                                <td class="border border-gray-300 px-2 py-1 pl-4" style="width: 50%;">{{ $component['account_name'] }}</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($component['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;"></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 7: Equity -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('equity')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">7. MEMBERS' EQUITY</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('equity', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('equity', $expandedNotes))
            <div class="px-3 py-4 bg-white">
                @foreach($financialData['equity'] as $equity)
                <div class="mb-6 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-xs font-semibold text-gray-800">
                            {{ $equity['account_name'] }}
                            <span class="text-gray-500 font-normal">({{ $equity['account_number'] }})</span>
                        </h5>
                        <button wire:click="showAccountDetails('{{ $equity['account_number'] }}', '{{ $equity['account_name'] }}')"
                                class="text-xs text-blue-600 hover:text-blue-800">
                            View Details →
                        </button>
                    </div>
                    
                    <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Description</th>
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-semibold bg-blue-100">
                                <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($equity['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right {{ $equity['movements']['change_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}" style="width: 16.67%;">
                                    {{ number_format($equity['movements']['change_percentage'], 1) }}%
                                </td>
                            </tr>
                            
                            @if(count($equity['composition']) > 0)
                            <tr>
                                <td colspan="{{ count($comparisonYears) + 2 }}" class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Composition:
                                </td>
                            </tr>
                            @foreach($equity['composition'] as $component)
                            <tr class="bg-gray-50 text-gray-600 hover:bg-gray-100">
                                <td class="border border-gray-300 px-2 py-1 pl-4" style="width: 50%;">{{ $component['account_name'] }}</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($component['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;"></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 8: Revenue -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('income')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">8. REVENUE</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('income', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('income', $expandedNotes))
            <div class="px-3 py-4 bg-white">
                @foreach($financialData['income'] as $income)
                <div class="mb-6 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-xs font-semibold text-gray-800">
                            {{ $income['account_name'] }}
                            <span class="text-gray-500 font-normal">({{ $income['account_number'] }})</span>
                        </h5>
                        <button wire:click="showAccountDetails('{{ $income['account_number'] }}', '{{ $income['account_name'] }}')"
                                class="text-xs text-blue-600 hover:text-blue-800">
                            View Details →
                        </button>
                    </div>
                    
                    <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Description</th>
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-semibold bg-blue-100">
                                <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($income['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right {{ $income['movements']['change_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}" style="width: 16.67%;">
                                    {{ number_format($income['movements']['change_percentage'], 1) }}%
                                </td>
                            </tr>
                            
                            @if(count($income['composition']) > 0)
                            <tr>
                                <td colspan="{{ count($comparisonYears) + 2 }}" class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Breakdown:
                                </td>
                            </tr>
                            @foreach($income['composition'] as $component)
                            <tr class="bg-gray-50 text-gray-600 hover:bg-gray-100">
                                <td class="border border-gray-300 px-2 py-1 pl-4" style="width: 50%;">{{ $component['account_name'] }}</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $this->formatNumber($component['years'][$year] ?? 0) }}</td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;"></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 9: Operating Expenses -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('expenses')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">9. OPERATING EXPENSES</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('expenses', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('expenses', $expandedNotes))
            <div class="px-3 py-4 bg-white">
                @foreach($financialData['expenses'] as $expense)
                <div class="mb-6 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-xs font-semibold text-gray-800">
                            {{ $expense['account_name'] }}
                            <span class="text-gray-500 font-normal">({{ $expense['account_number'] }})</span>
                        </h5>
                        <button wire:click="showAccountDetails('{{ $expense['account_number'] }}', '{{ $expense['account_name'] }}')"
                                class="text-xs text-blue-600 hover:text-blue-800">
                            View Details →
                        </button>
                    </div>
                    
                    <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700">
                                <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Description</th>
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-semibold bg-orange-100">
                                <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">
                                    @if(($expense['years'][$year] ?? 0) < 0)
                                        ({{ number_format(abs($expense['years'][$year] ?? 0), 2) }})
                                    @else
                                        {{ number_format($expense['years'][$year] ?? 0, 2) }}
                                    @endif
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right {{ $expense['movements']['change_percentage'] <= 0 ? 'text-green-600' : 'text-red-600' }}" style="width: 16.67%;">
                                    {{ number_format($expense['movements']['change_percentage'], 1) }}%
                                </td>
                            </tr>
                            
                            @if(count($expense['composition']) > 0)
                            <tr>
                                <td colspan="{{ count($comparisonYears) + 2 }}" class="border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-50">
                                    Breakdown:
                                </td>
                            </tr>
                            @foreach($expense['composition'] as $component)
                            <tr class="bg-gray-50 text-gray-600 hover:bg-gray-100">
                                <td class="border border-gray-300 px-2 py-1 pl-4" style="width: 50%;">{{ $component['account_name'] }}</td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">
                                    @if(($component['years'][$year] ?? 0) < 0)
                                        ({{ number_format(abs($component['years'][$year] ?? 0), 2) }})
                                    @else
                                        {{ number_format($component['years'][$year] ?? 0, 2) }}
                                    @endif
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;"></td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        
        <!-- Note 10: Related Party Transactions -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('related')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">10. RELATED PARTY TRANSACTIONS</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('related', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('related', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <p class="text-xs text-gray-700">
                    The organization enters into transactions with related parties in the normal course of business. 
                    Related parties include members of the Board of Directors, key management personnel, and entities 
                    under common control. All related party transactions are conducted at arm's length basis and on 
                    normal commercial terms.
                </p>
            </div>
            @endif
        </div>
        
        <!-- Note 11: Subsequent Events -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('subsequent')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">11. EVENTS AFTER THE REPORTING PERIOD</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('subsequent', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('subsequent', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <p class="text-xs text-gray-700">
                    There have been no significant events subsequent to the balance sheet date that would require 
                    adjustment or disclosure in these financial statements. The financial statements were authorized 
                    for issue by the Board of Directors on {{ $reportingDate }}.
                </p>
            </div>
            @endif
        </div>
        
        <!-- Note 12: Approval of Financial Statements -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="toggleNote('approval')" 
                    class="w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 transition-colors duration-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">12. APPROVAL OF FINANCIAL STATEMENTS</span>
                <svg class="w-4 h-4 text-blue-600 transform transition-transform {{ in_array('approval', $expandedNotes) ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            @if(in_array('approval', $expandedNotes))
            <div class="px-3 py-2 bg-white">
                <p class="text-xs text-gray-700">
                    These financial statements were approved by the Board of Directors on {{ $reportingDate }} 
                    and were signed on its behalf by:
                </p>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="border-t border-gray-400 mt-8 pt-2">
                            <p class="text-xs font-semibold">Chairman</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="border-t border-gray-400 mt-8 pt-2">
                            <p class="text-xs font-semibold">Chief Executive Officer</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Account Detail Modal -->
    @if($showAccountDetail)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold">Account Detail</h3>
                        <p class="text-xs">{{ $selectedAccountForDetail['name'] }} ({{ $selectedAccountForDetail['number'] }})</p>
                    </div>
                    <button wire:click="closeAccountDetail" class="text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-4 overflow-y-auto max-h-[calc(80vh-100px)]">
                <table class="w-full text-xs border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="border border-gray-300 px-2 py-1 text-left w-1/6">Date</th>
                            <th class="border border-gray-300 px-2 py-1 text-left w-1/6">Reference</th>
                            <th class="border border-gray-300 px-2 py-1 text-left w-2/6">Description</th>
                            <th class="border border-gray-300 px-2 py-1 text-right w-1/6">Debit</th>
                            <th class="border border-gray-300 px-2 py-1 text-right w-1/6">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accountTransactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1 w-1/6">{{ $transaction['date'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 w-1/6">{{ $transaction['reference'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 w-2/6">{{ $transaction['description'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-right w-1/6">
                                {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-right w-1/6">
                                {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>