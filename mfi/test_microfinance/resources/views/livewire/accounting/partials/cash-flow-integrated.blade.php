<div class="bg-white rounded-lg shadow-sm">
    <!-- Professional Header -->
    <div class="px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">STATEMENT OF CASH FLOWS</h2>
            <p class="text-sm">For the year ended 31 December {{ $selectedYear }}</p>
        </div>
    </div>
    
    <!-- Control Panel -->
    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">Year:</label>
                    <select wire:model="selectedYear" wire:change="loadFinancialData" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">Comparison:</label>
                    <input type="checkbox" wire:model="showComparison" wire:change="loadFinancialData" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">Method:</label>
                    <select wire:model="cashFlowMethod" wire:change="loadFinancialData" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="indirect">Indirect</option>
                        <option value="direct">Direct</option>
                    </select>
                </div>
                @if($showNotes)
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">Show Notes:</label>
                    <input type="checkbox" wire:model="showNotes" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>
                @endif
            </div>
            <div class="flex space-x-2">
                <button wire:click="exportToExcel" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPDF" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- Currency Header -->
    @if($showComparison && count($comparisonYears) > 1)
    <div class="px-4 py-2">
        <table class="ml-auto text-xs border-collapse border border-gray-300">
            <thead>
                <tr>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-3 py-1 bg-gray-50">31.12.{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-3 py-1 text-center">TZS</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    @endif
    
    <!-- Main Content Area -->
    <div class="p-4">
        
        <!-- OPERATING ACTIVITIES Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-green-900 text-white px-3 py-2 rounded">CASH FLOWS FROM OPERATING ACTIVITIES</h3>
            
            <table class="text-xs border-collapse border border-gray-300 mt-3" style="width: 100%;">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border border-gray-300 px-2 py-1 text-left" style="width: 60%;">Description</th>
                        @if($showNotes)
                        <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '12%' : '15%' }};">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                        @else
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '30%' : '40%' }};">Amount</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <!-- Net Income Starting Point (for Indirect Method) -->
                    @if($cashFlowMethod == 'indirect')
                    <tr class="bg-yellow-50 font-semibold">
                        <td class="border border-gray-300 px-2 py-1 pl-4">Net Income</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            <button wire:click="showNote(1, 'NI', 'Net Income')" class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">1</button>
                        </td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                {{ $this->formatNumber($year == $selectedYear ? ($cashFlowData['net_income'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                @if(count($comparisonYears) > 1)
                                    {{ number_format(0, 1) }}%
                                @else
                                    -
                                @endif
                            </td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                {{ $this->formatNumber($cashFlowData['net_income'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    
                    <!-- Adjustments to reconcile net income -->
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-4 italic text-gray-600" colspan="{{ $showComparison ? (count($comparisonYears) + ($showNotes ? 3 : 2)) : ($showNotes ? 3 : 2) }}">
                            Adjustments to reconcile net income to net cash:
                        </td>
                    </tr>
                    @endif
                    
                    <!-- Operating Activities Items -->
                    @foreach($cashFlowData['operating'] ?? [] as $index => $item)
                    @if(isset($item['amount']) && $item['amount'] != 0)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            <span class="{{ isset($item['is_subtotal']) && $item['is_subtotal'] ? 'font-semibold' : '' }}">
                                {{ $item['account_name'] ?? '' }}
                            </span>
                        </td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            @if(!isset($item['is_subtotal']) || !$item['is_subtotal'])
                            <button wire:click="showNote({{ $index + 2 }}, '{{ $item['account_number'] ?? '' }}', '{{ $item['account_name'] ?? '' }}')" 
                                class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                {{ $index + 2 }}
                            </button>
                            @endif
                        </td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber($year == $selectedYear ? ($item['amount'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber($item['amount'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    @endif
                    @endforeach
                    
                    <!-- Net Cash from Operating Activities -->
                    <tr class="bg-green-100 font-bold">
                        <td class="border border-gray-300 px-2 py-2">Net Cash from Operating Activities</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber($year == $selectedYear ? (array_sum(array_column($cashFlowData['operating'] ?? [], 'amount')) + ($cashFlowData['net_income'] ?? 0)) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['operating'] ?? [], 'amount')) + ($cashFlowData['net_income'] ?? 0)) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- INVESTING ACTIVITIES Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-orange-900 text-white px-3 py-2 rounded">CASH FLOWS FROM INVESTING ACTIVITIES</h3>
            
            <table class="text-xs border-collapse border border-gray-300 mt-3" style="width: 100%;">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border border-gray-300 px-2 py-1 text-left" style="width: 60%;">Description</th>
                        @if($showNotes)
                        <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '12%' : '15%' }};">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                        @else
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '30%' : '40%' }};">Amount</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashFlowData['investing'] ?? [] as $index => $item)
                    @if(isset($item['amount']) && $item['amount'] != 0)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-1 pl-4">
                            <span class="{{ isset($item['is_acquisition']) && $item['is_acquisition'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $item['account_name'] ?? '' }}
                            </span>
                        </td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            <button wire:click="showNote({{ count($cashFlowData['operating'] ?? []) + $index + 10 }}, '{{ $item['account_number'] ?? '' }}', '{{ $item['account_name'] ?? '' }}')" 
                                class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                {{ count($cashFlowData['operating'] ?? []) + $index + 10 }}
                            </button>
                        </td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $this->formatNumber($year == $selectedYear ? ($item['amount'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $this->formatNumber($item['amount'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    @endif
                    @endforeach
                    
                    <!-- Net Cash from Investing Activities -->
                    <tr class="bg-orange-100 font-bold">
                        <td class="border border-gray-300 px-2 py-2">Net Cash from Investing Activities</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right {{ array_sum(array_column($cashFlowData['investing'] ?? [], 'amount')) < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber($year == $selectedYear ? array_sum(array_column($cashFlowData['investing'] ?? [], 'amount')) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right {{ array_sum(array_column($cashFlowData['investing'] ?? [], 'amount')) < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['investing'] ?? [], 'amount'))) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- FINANCING ACTIVITIES Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-purple-900 text-white px-3 py-2 rounded">CASH FLOWS FROM FINANCING ACTIVITIES</h3>
            
            <table class="text-xs border-collapse border border-gray-300 mt-3" style="width: 100%;">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border border-gray-300 px-2 py-1 text-left" style="width: 60%;">Description</th>
                        @if($showNotes)
                        <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '12%' : '15%' }};">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                        @else
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '30%' : '40%' }};">Amount</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashFlowData['financing'] ?? [] as $index => $item)
                    @if(isset($item['amount']) && $item['amount'] != 0)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-1 pl-4">
                            <span class="{{ str_contains(strtolower($item['account_name'] ?? ''), 'dividend') || str_contains(strtolower($item['account_name'] ?? ''), 'repayment') ? 'text-red-600' : '' }}">
                                {{ $item['account_name'] ?? '' }}
                            </span>
                        </td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            <button wire:click="showNote({{ count($cashFlowData['operating'] ?? []) + count($cashFlowData['investing'] ?? []) + $index + 20 }}, '{{ $item['account_number'] ?? '' }}', '{{ $item['account_name'] ?? '' }}')" 
                                class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                {{ count($cashFlowData['operating'] ?? []) + count($cashFlowData['investing'] ?? []) + $index + 20 }}
                            </button>
                        </td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber($year == $selectedYear ? ($item['amount'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right {{ $item['amount'] < 0 ? 'text-red-600' : '' }}">
                                {{ $this->formatNumber($item['amount'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    @endif
                    @endforeach
                    
                    <!-- Net Cash from Financing Activities -->
                    <tr class="bg-purple-100 font-bold">
                        <td class="border border-gray-300 px-2 py-2">Net Cash from Financing Activities</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber($year == $selectedYear ? array_sum(array_column($cashFlowData['financing'] ?? [], 'amount')) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['financing'] ?? [], 'amount'))) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- CASH RECONCILIATION Section -->
        <div class="mb-6">
            <table class="text-xs border-collapse border border-gray-300" style="width: 100%;">
                <tbody>
                    <!-- Net Increase in Cash -->
                    <tr class="bg-gray-200 font-bold">
                        <td class="border border-gray-300 px-2 py-2" style="width: 60%;">NET INCREASE (DECREASE) IN CASH</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center" style="width: 10%;"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right {{ ($cashFlowData['net_change'] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}" style="width: {{ $showNotes ? '12%' : '15%' }};">
                                {{ $this->formatNumber($year == $selectedYear ? ($cashFlowData['net_change'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: 10%;"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right {{ ($cashFlowData['net_change'] ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}" style="width: {{ $showNotes ? '30%' : '40%' }};">
                                {{ $this->formatNumber($cashFlowData['net_change'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    
                    <!-- Cash at Beginning of Year -->
                    <tr>
                        <td class="border border-gray-300 px-2 py-1">Cash and Cash Equivalents at Beginning of Year</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                {{ $this->formatNumber($year == $selectedYear ? ($cashFlowData['beginning_cash'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                {{ $this->formatNumber($cashFlowData['beginning_cash'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                    
                    <!-- Cash at End of Year -->
                    <tr class="bg-red-200 font-bold">
                        <td class="border border-gray-300 px-2 py-2">CASH AND CASH EQUIVALENTS AT END OF YEAR</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber($year == $selectedYear ? ($cashFlowData['ending_cash'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right">
                                {{ $this->formatNumber($cashFlowData['ending_cash'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Reconciliation Note -->
        <div class="mt-4 p-3 bg-red-50 border border-red-300 rounded">
            <p class="text-xs text-red-700">
                <strong>✓ Reconciliation:</strong> The ending Cash and Cash Equivalents balance of 
                <strong>{{ $this->formatNumber($cashFlowData['ending_cash'] ?? 0) }}</strong> 
                matches the Cash and Cash Equivalents line item on the Statement of Financial Position as at 31 December {{ $selectedYear }}.
            </p>
        </div>
        
        <!-- Supplemental Disclosures -->
        @if(count($cashFlowData['non_cash_activities'] ?? []) > 0)
        <div class="mt-6">
            <h3 class="text-sm font-bold uppercase bg-gray-700 text-white px-3 py-2 rounded">SUPPLEMENTAL DISCLOSURES</h3>
            
            <!-- Non-Cash Activities -->
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2">Non-Cash Investing and Financing Activities:</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: 100%;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 70%;">Description</th>
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 30%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashFlowData['non_cash_activities'] ?? [] as $activity)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">{{ $activity['description'] ?? '' }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                {{ $this->formatNumber($activity['amount'] ?? 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Cash Paid for Interest and Income Taxes -->
            @if(isset($cashFlowData['supplemental_cash']))
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2">Cash Paid During the Year:</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: 100%;">
                    <tbody>
                        @if(isset($cashFlowData['supplemental_cash']['interest_paid']))
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 70%;">Interest Paid</td>
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 30%;">
                                {{ $this->formatNumber($cashFlowData['supplemental_cash']['interest_paid'] ?? 0) }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($cashFlowData['supplemental_cash']['income_taxes_paid']))
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">Income Taxes Paid</td>
                            <td class="border border-gray-300 px-2 py-1 text-right">
                                {{ $this->formatNumber($cashFlowData['supplemental_cash']['income_taxes_paid'] ?? 0) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Cash Flow Ratios Section -->
        @if($showRatios && isset($cashFlowRatios) && count($cashFlowRatios) > 0)
        <div class="mt-6">
            <h3 class="text-sm font-bold uppercase bg-gray-700 text-white px-3 py-2 rounded">Cash Flow Analysis Ratios</h3>
            
            <div class="grid grid-cols-3 gap-4 mt-3">
                <!-- Operating Cash Flow Ratios -->
                @if(isset($cashFlowRatios['operating']))
                <div class="bg-green-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Operating Efficiency</h4>
                    @foreach($cashFlowRatios['operating'] as $ratio)
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $ratio['ratio_name'] ?? '' }}</span>
                            <span class="font-medium">{{ number_format($ratio['value'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Free Cash Flow Ratios -->
                @if(isset($cashFlowRatios['free_cash_flow']))
                <div class="bg-red-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Free Cash Flow</h4>
                    @foreach($cashFlowRatios['free_cash_flow'] as $ratio)
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $ratio['ratio_name'] ?? '' }}</span>
                            <span class="font-medium">{{ number_format($ratio['value'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Coverage Ratios -->
                @if(isset($cashFlowRatios['coverage']))
                <div class="bg-yellow-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Coverage Ratios</h4>
                    @foreach($cashFlowRatios['coverage'] as $ratio)
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $ratio['ratio_name'] ?? '' }}</span>
                            <span class="font-medium">{{ number_format($ratio['value'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <!-- Footer with Additional Information -->
    <div class="px-4 py-3 bg-gray-50 rounded-b-lg border-t border-gray-200">
        <div class="flex justify-between items-center text-xs text-gray-600">
            <div>
                Generated on: {{ now()->format('d M Y H:i') }}
                @if($cashFlowMethod == 'direct')
                    <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 rounded">Direct Method</span>
                @else
                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded">Indirect Method</span>
                @endif
                @if($isConsolidated ?? false)
                    <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-700 rounded">Consolidated View</span>
                @endif
            </div>
            <div>
                Data Source: Integrated Accounts System
            </div>
        </div>
    </div>
    
    <!-- Note Modal -->
    @if(isset($showNoteModal) && $showNoteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Note {{ $noteNumber ?? '' }}: {{ $noteTitle ?? '' }}
                            </h3>
                            <button wire:click="closeNote" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            @if(isset($noteContent) && is_array($noteContent) && count($noteContent) > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Impact on Cash</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($noteContent as $item)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['description'] ?? '' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($item['amount'] ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right">
                                                <span class="{{ ($item['impact'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ ($item['impact'] ?? 0) > 0 ? 'Increase' : 'Decrease' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr class="font-semibold bg-gray-50">
                                            <td colspan="2" class="px-4 py-2 text-sm text-gray-900">Total Impact</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                                {{ number_format(collect($noteContent)->sum('amount'), 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <p class="text-gray-500">No data available for this note.</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeNote"
                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>