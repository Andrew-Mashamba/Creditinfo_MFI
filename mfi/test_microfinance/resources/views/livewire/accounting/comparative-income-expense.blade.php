<div>
    {{-- View Mode Toggle --}}
    @if($viewMode == 'excel')
        {{-- Excel-Style Income Statement --}}
        @include('livewire.accounting.income-statement-excel-simple')
    @else
    <div class="bg-gradient-to-br from-slate-50 via-red-50 to-indigo-50 p-2">
    {{-- Compact Header Section --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 mb-3 overflow-hidden">
        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-red-900 via-red-800 to-red-900 text-white px-3 py-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="p-1.5 bg-white/10 backdrop-blur-sm rounded">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-base font-bold tracking-tight">Income Statement</h1>
                        <p class="text-gray-500 text-xs">For the Year Ended December 31, {{ $selectedYear }}</p>
                    </div>
                </div>
                
                {{-- Compact Action Buttons --}}
                <div class="flex items-center space-x-2">
                    <button wire:click="exportToPDF" class="bg-white/10 backdrop-blur-sm text-white px-2 py-1 text-xs rounded hover:bg-white/20 transition-all flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Compact Controls Section --}}
        <div class="px-3 py-2 bg-gray-50/50 border-b border-gray-100">
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Reporting Year</label>
                    <select wire:model="selectedYear" class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-red-500 focus:border-transparent">
                        @for($i = date('Y'); $i >= date('Y') - 10; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-center">
                    <div class="text-xs text-gray-600">
                        <span class="font-medium">Comparison:</span>
                        <span class="text-red-900 font-semibold">{{ implode(' vs ', $comparisonYears) }}</span>
                    </div>
                </div>
                <div class="flex items-end justify-end">
                    <div class="text-xs text-gray-500">
                        {{ now()->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Compact Executive Summary --}}
        <div class="px-3 py-2">
            <div class="grid grid-cols-4 gap-2">
                @foreach($comparisonYears as $year)
                <div class="text-center p-2 bg-gradient-to-br from-gray-50 to-white rounded border border-gray-100">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Net {{ $year }}</div>
                    <div class="text-sm font-bold @if(($summaryData[$year]['net_income'] ?? 0) >= 0) text-emerald-600 @else text-red-600 @endif">
                        {{ number_format($summaryData[$year]['net_income'] ?? 0, 0) }}
                    </div>
                    @if($loop->index > 0 && isset($summaryData[$comparisonYears[$loop->index - 1]]))
                    @php
                        $current = $summaryData[$year]['net_income'] ?? 0;
                        $previous = $summaryData[$comparisonYears[$loop->index - 1]]['net_income'] ?? 0;
                        $variance = $this->calculateVariance($current, $previous);
                    @endphp
                    <div class="text-xs @if($variance >= 0) text-emerald-600 @else text-red-600 @endif">
                        {{ $variance >= 0 ? '↗' : '↘' }} {{ number_format(abs($variance), 1) }}%
                    </div>
                    @endif
                </div>
                @endforeach
                
                @foreach($comparisonYears as $year)
                <div class="text-center p-2 bg-gradient-to-br from-red-50 to-indigo-50 rounded border border-red-100">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Margin {{ $year }}</div>
                    <div class="text-sm font-bold text-red-900">
                        {{ number_format($summaryData[$year]['profit_margin'] ?? 0, 1) }}%
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    {{-- Account Statement Modal --}}
    @if($showStatement)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-red-900 to-red-800 text-white px-3 py-2 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold">General Ledger Statement</h3>
                    <p class="text-xs text-gray-500">{{ $selectedAccount['name'] ?? '' }} • {{ $selectedAccount['number'] ?? '' }}</p>
                </div>
                <button wire:click="closeStatement" class="text-white hover:bg-white/20 p-1 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[70vh] p-3">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b">Date</th>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b">Time</th>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b">Reference</th>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b">Description</th>
                            <th class="px-2 py-1 text-right font-semibold text-gray-700 border-b">Debit</th>
                            <th class="px-2 py-1 text-right font-semibold text-gray-700 border-b">Credit</th>
                            <th class="px-2 py-1 text-right font-semibold text-gray-700 border-b">Balance</th>
                            <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($accountStatement as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1 text-gray-900">{{ $entry['date'] }}</td>
                            <td class="px-2 py-1 text-gray-600">{{ $entry['time'] }}</td>
                            <td class="px-2 py-1 text-gray-700 font-mono">{{ $entry['reference'] }}</td>
                            <td class="px-2 py-1 text-gray-700">{{ Str::limit($entry['description'], 40) }}</td>
                            <td class="px-2 py-1 text-right text-red-600">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}</td>
                            <td class="px-2 py-1 text-right text-emerald-600">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}</td>
                            <td class="px-2 py-1 text-right font-semibold @if($entry['balance'] >= 0) text-red-900 @else text-red-600 @endif">
                                {{ number_format($entry['balance'], 2) }}
                            </td>
                            <td class="px-2 py-1 text-gray-500 text-xs">{{ $entry['user'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-2 py-4 text-center text-gray-500">No transactions found for this account</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Loading Indicator --}}
    <div wire:loading class="fixed top-4 right-4 z-50">
        <div class="bg-red-600 text-white px-3 py-2 rounded-lg shadow-lg flex items-center space-x-2">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs">Loading...</span>
        </div>
    </div>
    
    {{-- Compact Income Statement --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 mb-3 overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-green-600 text-white px-3 py-1.5">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold flex items-center">
                    <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center mr-2">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    REVENUE & INCOME
                </h2>
                <div class="text-xs text-green-100">
                    {{ count($incomeData) }} Categories
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-2 py-1 text-left font-bold text-gray-700 uppercase tracking-wider">Account</th>
                        @foreach($comparisonYears as $year)
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">{{ $year }}</th>
                        @endforeach
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">Change</th>
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($incomeData as $category)
                    {{-- L2 Category Row --}}
                    <tr class="group hover:bg-emerald-50/30 transition-all @if(in_array($category['category_code_key'], $expandedCategories)) bg-emerald-50/50 @endif">
                        <td class="px-2 py-1">
                            <div class="flex items-center">
                                @if($category['has_children'] ?? false)
                                <button wire:click="toggleCategory('{{ $category['category_code_key'] }}')" class="mr-2 p-0.5 hover:bg-emerald-100 rounded transition-colors">
                                    @if(in_array($category['category_code_key'], $expandedCategories))
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </button>
                                @else
                                <button wire:click="showAccountStatement('{{ $category['account_number'] }}', '{{ $category['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                    <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>
                                @endif
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 mr-1">L2</span>
                                        <div>
                                            <div class="font-semibold text-gray-900 text-sm">{{ $category['account_name'] }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $category['account_number'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="px-2 py-1 text-right">
                            <div class="font-semibold text-emerald-600 text-sm">
                                {{ number_format($category['years'][$year] ?? 0, 0) }}
                            </div>
                        </td>
                        @endforeach
                        <td class="px-2 py-1 text-right">
                            @if(count($comparisonYears) >= 2)
                            @php
                                $current = $category['years'][$comparisonYears[0]] ?? 0;
                                $previous = $category['years'][$comparisonYears[1]] ?? 0;
                                $variance = $this->calculateVariance($current, $previous);
                            @endphp
                            <span class="text-xs font-medium @if($variance >= 0) text-emerald-600 @else text-red-600 @endif">
                                {{ number_format(abs($variance), 1) }}%
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1 text-right">
                            @php
                                $current = $category['years'][$comparisonYears[0]] ?? 0;
                                $total = $summaryData[$comparisonYears[0]]['total_income'] ?? 0;
                                $percentage = $this->calculatePercentageOfTotal($current, $total);
                            @endphp
                            <div class="text-xs text-gray-600">{{ number_format($percentage, 1) }}%</div>
                        </td>
                    </tr>
                    
                    {{-- L3 Subcategory Rows --}}
                    @if(in_array($category['category_code_key'], $expandedCategories))
                        @foreach($category['subcategories'] as $subcategory)
                        <tr class="bg-emerald-25 hover:bg-emerald-50 transition-all border-l-2 border-emerald-200">
                            <td class="px-2 py-1 pl-6">
                                <div class="flex items-center">
                                    @if($subcategory['has_children'] ?? false)
                                    <button wire:click="toggleSubcategory('{{ $subcategory['subcategory_key'] ?? $subcategory['account_number'] }}')" class="mr-2 p-0.5 hover:bg-emerald-100 rounded transition-colors">
                                        @if(in_array($subcategory['subcategory_key'] ?? $subcategory['account_number'], $expandedSubcategories))
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    </button>
                                    @else
                                    <button wire:click="showAccountStatement('{{ $subcategory['account_number'] }}', '{{ $subcategory['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                        <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </button>
                                    @endif
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 mr-1">L3</span>
                                        <div>
                                            <div class="font-medium text-gray-700">{{ $subcategory['account_name'] }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $subcategory['account_number'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1 text-right">
                                <div class="text-emerald-600">
                                    {{ number_format($subcategory['years'][$year] ?? 0, 0) }}
                                </div>
                            </td>
                            @endforeach
                            <td class="px-2 py-1 text-right">
                                @if(count($comparisonYears) >= 2)
                                @php
                                    $current = $subcategory['years'][$comparisonYears[0]] ?? 0;
                                    $previous = $subcategory['years'][$comparisonYears[1]] ?? 0;
                                    $variance = $this->calculateVariance($current, $previous);
                                @endphp
                                <span class="text-xs @if($variance >= 0) text-emerald-600 @else text-red-600 @endif">
                                    {{ number_format($variance, 1) }}%
                                </span>
                                @endif
                            </td>
                            <td class="px-2 py-1 text-right">
                                @php
                                    $current = $subcategory['years'][$comparisonYears[0]] ?? 0;
                                    $total = $summaryData[$comparisonYears[0]]['total_income'] ?? 0;
                                    $percentage = $this->calculatePercentageOfTotal($current, $total);
                                @endphp
                                <span class="text-xs text-gray-500">{{ number_format($percentage, 1) }}%</span>
                            </td>
                        </tr>
                        
                        {{-- L4 Detail Account Rows --}}
                        @if(in_array($subcategory['subcategory_key'] ?? $subcategory['account_number'], $expandedSubcategories))
                            @foreach($subcategory['details'] as $detail)
                            <tr class="bg-emerald-25/50 hover:bg-emerald-50/50 transition-all border-l-2 border-emerald-100">
                                <td class="px-2 py-1 pl-10">
                                    <div class="flex items-center">
                                        <button wire:click="showAccountStatement('{{ $detail['account_number'] }}', '{{ $detail['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                            <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </button>
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-500 mr-1">L4</span>
                                        <div>
                                            <div class="text-gray-600">{{ $detail['account_name'] }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $detail['account_number'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach($comparisonYears as $year)
                                <td class="px-2 py-1 text-right">
                                    <div class="text-gray-600">
                                        {{ number_format($detail['years'][$year] ?? 0, 0) }}
                                    </div>
                                </td>
                                @endforeach
                                <td class="px-2 py-1 text-right">
                                    @if(count($comparisonYears) >= 2)
                                    @php
                                        $current = $detail['years'][$comparisonYears[0]] ?? 0;
                                        $previous = $detail['years'][$comparisonYears[1]] ?? 0;
                                        $variance = $this->calculateVariance($current, $previous);
                                    @endphp
                                    <span class="text-xs @if($variance >= 0) text-emerald-500 @else text-red-500 @endif">
                                        {{ number_format($variance, 1) }}%
                                    </span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right">
                                    <span class="text-xs text-gray-400">-</span>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="px-2 py-6 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm">No income accounts found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    {{-- Total Income Row --}}
                    <tr class="bg-gradient-to-r from-emerald-100 to-green-100 font-bold border-t-2 border-emerald-300">
                        <td class="px-2 py-1.5 text-emerald-900 font-bold">TOTAL REVENUE</td>
                        @foreach($comparisonYears as $year)
                        <td class="px-2 py-1.5 text-right text-emerald-800 font-bold">
                            {{ number_format($summaryData[$year]['total_income'] ?? 0, 0) }}
                        </td>
                        @endforeach
                        <td class="px-2 py-1.5 text-right">
                            @if(count($comparisonYears) >= 2)
                            @php
                                $current = $summaryData[$comparisonYears[0]]['total_income'] ?? 0;
                                $previous = $summaryData[$comparisonYears[1]]['total_income'] ?? 0;
                                $variance = $this->calculateVariance($current, $previous);
                            @endphp
                            <span class="text-xs font-bold @if($variance >= 0) text-emerald-700 @else text-red-600 @endif">
                                {{ number_format($variance, 1) }}%
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 text-right">
                            <span class="text-xs font-bold text-emerald-700">100%</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Compact Expenses Statement --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 mb-3 overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white px-3 py-1.5">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold flex items-center">
                    <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center mr-2">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    EXPENSES & COSTS
                </h2>
                <div class="text-xs text-red-100">
                    {{ count($expenseData) }} Categories
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-2 py-1 text-left font-bold text-gray-700 uppercase tracking-wider">Account</th>
                        @foreach($comparisonYears as $year)
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">{{ $year }}</th>
                        @endforeach
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">Change</th>
                        <th class="px-2 py-1 text-right font-bold text-gray-700 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenseData as $category)
                    {{-- L2 Category Row for Expenses --}}
                    <tr class="group hover:bg-red-50/30 transition-all @if(in_array($category['category_code_key'], $expandedCategories)) bg-red-50/50 @endif">
                        <td class="px-2 py-1">
                            <div class="flex items-center">
                                @if($category['has_children'] ?? false)
                                <button wire:click="toggleCategory('{{ $category['category_code_key'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                    @if(in_array($category['category_code_key'], $expandedCategories))
                                        <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </button>
                                @else
                                <button wire:click="showAccountStatement('{{ $category['account_number'] }}', '{{ $category['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                    <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>
                                @endif
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-1">L2</span>
                                        <div>
                                            <div class="font-semibold text-gray-900 text-sm">{{ $category['account_name'] }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $category['account_number'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="px-2 py-1 text-right">
                            <div class="font-semibold text-red-600 text-sm">
                                ({{ number_format($category['years'][$year] ?? 0, 0) }})
                            </div>
                        </td>
                        @endforeach
                        <td class="px-2 py-1 text-right">
                            @if(count($comparisonYears) >= 2)
                            @php
                                $current = $category['years'][$comparisonYears[0]] ?? 0;
                                $previous = $category['years'][$comparisonYears[1]] ?? 0;
                                $variance = $this->calculateVariance($current, $previous);
                            @endphp
                            <span class="text-xs font-medium @if($variance >= 0) text-red-600 @else text-emerald-600 @endif">
                                {{ number_format(abs($variance), 1) }}%
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1 text-right">
                            @php
                                $current = $category['years'][$comparisonYears[0]] ?? 0;
                                $total = $summaryData[$comparisonYears[0]]['total_expenses'] ?? 0;
                                $percentage = $this->calculatePercentageOfTotal($current, $total);
                            @endphp
                            <div class="text-xs text-gray-600">{{ number_format($percentage, 1) }}%</div>
                        </td>
                    </tr>
                    
                    {{-- L3 Subcategory Rows for Expenses --}}
                    @if(in_array($category['category_code_key'], $expandedCategories))
                        @foreach($category['subcategories'] as $subcategory)
                        <tr class="bg-red-25 hover:bg-red-50 transition-all border-l-2 border-red-200">
                            <td class="px-2 py-1 pl-6">
                                <div class="flex items-center">
                                    @if($subcategory['has_children'] ?? false)
                                    <button wire:click="toggleSubcategory('{{ $subcategory['subcategory_key'] ?? $subcategory['account_number'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                        @if(in_array($subcategory['subcategory_key'] ?? $subcategory['account_number'], $expandedSubcategories))
                                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    </button>
                                    @else
                                    <button wire:click="showAccountStatement('{{ $subcategory['account_number'] }}', '{{ $subcategory['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                        <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </button>
                                    @endif
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 mr-1">L3</span>
                                        <div>
                                            <div class="font-medium text-gray-700">{{ $subcategory['account_name'] }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $subcategory['account_number'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1 text-right">
                                <div class="text-red-600">
                                    ({{ number_format($subcategory['years'][$year] ?? 0, 0) }})
                                </div>
                            </td>
                            @endforeach
                            <td class="px-2 py-1 text-right">
                                @if(count($comparisonYears) >= 2)
                                @php
                                    $current = $subcategory['years'][$comparisonYears[0]] ?? 0;
                                    $previous = $subcategory['years'][$comparisonYears[1]] ?? 0;
                                    $variance = $this->calculateVariance($current, $previous);
                                @endphp
                                <span class="text-xs @if($variance >= 0) text-red-600 @else text-emerald-600 @endif">
                                    {{ number_format($variance, 1) }}%
                                </span>
                                @endif
                            </td>
                            <td class="px-2 py-1 text-right">
                                @php
                                    $current = $subcategory['years'][$comparisonYears[0]] ?? 0;
                                    $total = $summaryData[$comparisonYears[0]]['total_expenses'] ?? 0;
                                    $percentage = $this->calculatePercentageOfTotal($current, $total);
                                @endphp
                                <span class="text-xs text-gray-500">{{ number_format($percentage, 1) }}%</span>
                            </td>
                        </tr>
                        
                        {{-- L4 Detail Account Rows for Expenses --}}
                        @if(in_array($subcategory['subcategory_key'] ?? $subcategory['account_number'], $expandedSubcategories))
                            @foreach($subcategory['details'] as $detail)
                            <tr class="bg-red-25/50 hover:bg-red-50/50 transition-all border-l-2 border-red-100">
                                <td class="px-2 py-1 pl-10">
                                    <div class="flex items-center">
                                        <button wire:click="showAccountStatement('{{ $detail['account_number'] }}', '{{ $detail['account_name'] }}')" class="mr-2 p-0.5 hover:bg-red-100 rounded transition-colors">
                                            <svg class="w-3 h-3 text-red-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </button>
                                        <span class="inline-flex items-center px-1 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-500 mr-1">L4</span>
                                        <div>
                                            <div class="text-gray-600">{{ $detail['account_name'] }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ $detail['account_number'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach($comparisonYears as $year)
                                <td class="px-2 py-1 text-right">
                                    <div class="text-gray-600">
                                        ({{ number_format($detail['years'][$year] ?? 0, 0) }})
                                    </div>
                                </td>
                                @endforeach
                                <td class="px-2 py-1 text-right">
                                    @if(count($comparisonYears) >= 2)
                                    @php
                                        $current = $detail['years'][$comparisonYears[0]] ?? 0;
                                        $previous = $detail['years'][$comparisonYears[1]] ?? 0;
                                        $variance = $this->calculateVariance($current, $previous);
                                    @endphp
                                    <span class="text-xs @if($variance >= 0) text-red-500 @else text-emerald-500 @endif">
                                        {{ number_format($variance, 1) }}%
                                    </span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right">
                                    <span class="text-xs text-gray-400">-</span>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="5" class="px-2 py-6 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-500 text-sm">No expense accounts found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                    
                    {{-- Total Expenses Row --}}
                    <tr class="bg-gradient-to-r from-red-100 to-rose-100 font-bold border-t-2 border-red-300">
                        <td class="px-2 py-1.5 text-red-900 font-bold">TOTAL EXPENSES</td>
                        @foreach($comparisonYears as $year)
                        <td class="px-2 py-1.5 text-right text-red-800 font-bold">
                            ({{ number_format($summaryData[$year]['total_expenses'] ?? 0, 0) }})
                        </td>
                        @endforeach
                        <td class="px-2 py-1.5 text-right">
                            @if(count($comparisonYears) >= 2)
                            @php
                                $current = $summaryData[$comparisonYears[0]]['total_expenses'] ?? 0;
                                $previous = $summaryData[$comparisonYears[1]]['total_expenses'] ?? 0;
                                $variance = $this->calculateVariance($current, $previous);
                            @endphp
                            <span class="text-xs font-bold @if($variance >= 0) text-red-700 @else text-emerald-600 @endif">
                                {{ number_format($variance, 1) }}%
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 text-right">
                            <span class="text-xs font-bold text-red-700">100%</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Compact Net Income Summary --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-100 mb-3 overflow-hidden">
        <div class="bg-gradient-to-r from-red-900 to-red-800 text-white px-3 py-2">
            <h2 class="text-sm font-bold flex items-center">
                <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center mr-2">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                FINANCIAL PERFORMANCE SUMMARY
            </h2>
        </div>
        
        <div class="p-2">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="px-2 py-1 text-left font-bold text-gray-700">Metrics</th>
                            @foreach($comparisonYears as $year)
                            <th class="px-2 py-1 text-right font-bold text-gray-700">{{ $year }}</th>
                            @endforeach
                            <th class="px-2 py-1 text-right font-bold text-gray-700">Variance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1 text-gray-900 font-medium">Revenue</td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1 text-right font-semibold text-emerald-600">
                                {{ number_format($summaryData[$year]['total_income'] ?? 0, 0) }}
                            </td>
                            @endforeach
                            <td class="px-2 py-1 text-right text-gray-500">-</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1 text-gray-900 font-medium">Expenses</td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1 text-right font-semibold text-red-600">
                                ({{ number_format($summaryData[$year]['total_expenses'] ?? 0, 0) }})
                            </td>
                            @endforeach
                            <td class="px-2 py-1 text-right text-gray-500">-</td>
                        </tr>
                        <tr class="bg-gradient-to-r from-red-50 to-indigo-50 border-y border-red-200">
                            <td class="px-2 py-1.5 text-red-900 font-bold">NET INCOME</td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1.5 text-right font-bold @if(($summaryData[$year]['net_income'] ?? 0) >= 0) text-red-900 @else text-red-700 @endif">
                                {{ number_format($summaryData[$year]['net_income'] ?? 0, 0) }}
                            </td>
                            @endforeach
                            <td class="px-2 py-1.5 text-right">
                                @if(count($comparisonYears) >= 2)
                                @php
                                    $current = $summaryData[$comparisonYears[0]]['net_income'] ?? 0;
                                    $previous = $summaryData[$comparisonYears[1]]['net_income'] ?? 0;
                                    $variance = $this->calculateVariance($current, $previous);
                                @endphp
                                <span class="font-bold @if($variance >= 0) text-emerald-700 @else text-red-600 @endif">
                                    {{ number_format(abs($variance), 1) }}%
                                </span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-1 text-gray-900 font-medium">Margin %</td>
                            @foreach($comparisonYears as $year)
                            <td class="px-2 py-1 text-right font-semibold text-red-600">
                                {{ number_format($summaryData[$year]['profit_margin'] ?? 0, 1) }}%
                            </td>
                            @endforeach
                            <td class="px-2 py-1 text-right text-gray-500">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div class="text-center text-xs text-gray-500 py-2">
        <p>IAS 1 Compliant • Generated {{ now()->format('d M Y, H:i:s') }} • MFI Core System</p>
    </div>
</div>

{{-- Custom Styles --}}
<style nonce="{{ csp_nonce() }}">
.bg-emerald-25 {
    background-color: rgba(5, 150, 105, 0.025);
}

.bg-red-25 {
    background-color: rgba(239, 68, 68, 0.025);
}
</style>
    </div>
    @endif
</div>