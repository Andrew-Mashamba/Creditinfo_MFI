<div class="bg-white rounded-lg shadow-sm">
    <!-- Professional Header -->
    <div class="px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">STATEMENT OF FINANCIAL POSITION pa</h2>
            <p class="text-sm">As at 31 December {{ $selectedYear }}</p>
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
        
        <!-- ASSETS Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-red-600 text-white px-3 py-2 rounded">ASSETS</h3>
            
            <!-- Current Assets -->
            @if(isset($balanceSheetData['assets']['current']) && count($balanceSheetData['assets']['current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Current Assets</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Account</th>
                            @if($showNotes)
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                            @else
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceSheetData['assets']['current'] as $index => $asset)
                        @if(isset($asset['amount']) && $asset['amount'] != 0)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <span class="font-medium">{{ $asset['account_name'] ?? '' }}</span>
                            </td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center">
                                <button 
                                    wire:click="showNote({{ $index + 5 }}, '{{ $asset['account_number'] ?? '' }}', '{{ $asset['account_name'] ?? '' }}')"
                                    class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                    {{ $index + 5 }}
                                </button>
                            </td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($year == $selectedYear ? ($asset['amount'] ?? 0) : 0) }}
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
                                    {{ $this->formatNumber($asset['amount'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Current Assets Subtotal -->
                        <tr class="bg-red-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1">Total Current Assets</td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center"></td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['assets']['current_total'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right"></td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($balanceSheetData['assets']['current_total'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Non-Current Assets -->
            @if(isset($balanceSheetData['assets']['non_current']) && count($balanceSheetData['assets']['non_current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Non-Current Assets</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Account</th>
                            @if($showNotes)
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                            @else
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceSheetData['assets']['non_current'] as $index => $asset)
                        @if(isset($asset['amount']) && $asset['amount'] != 0)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <span class="font-medium">{{ $asset['account_name'] ?? '' }}</span>
                            </td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center">
                                <button 
                                    wire:click="showNote({{ count($balanceSheetData['assets']['current']) + $index + 5 }}, '{{ $asset['account_number'] ?? '' }}', '{{ $asset['account_name'] ?? '' }}')"
                                    class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                    {{ count($balanceSheetData['assets']['current']) + $index + 5 }}
                                </button>
                            </td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($year == $selectedYear ? ($asset['amount'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($asset['amount'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Non-Current Assets Subtotal -->
                        <tr class="bg-red-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1">Total Non-Current Assets</td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center"></td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['assets']['non_current_total'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right"></td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($balanceSheetData['assets']['non_current_total'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Total Assets -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-red-200 font-bold">
                        <td class="border border-gray-300 px-2 py-2" style="width: 50%;">TOTAL ASSETS</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center" style="width: 10%;"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">
                                {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['total_assets'] ?? $balanceSheetData['assets']['total'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: 10%;">-</td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">
                                {{ $this->formatNumber($balanceSheetData['total_assets'] ?? $balanceSheetData['assets']['total'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- EQUITY AND LIABILITIES Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-red-600 text-white px-3 py-2 rounded">EQUITY AND LIABILITIES</h3>
            
            <!-- Equity -->
            @if(isset($balanceSheetData['equity']['items']) && count($balanceSheetData['equity']['items']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Members' Equity</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Account</th>
                            @if($showNotes)
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                            @else
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceSheetData['equity']['items'] as $index => $equity)
                        @if(isset($equity['amount']) && $equity['amount'] != 0)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <span class="font-medium">{{ $equity['account_name'] ?? '' }}</span>
                            </td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center">
                                <button 
                                    wire:click="showNote({{ $index + 7 }}, '{{ $equity['account_number'] ?? '' }}', '{{ $equity['account_name'] ?? '' }}')"
                                    class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                    {{ $index + 7 }}
                                </button>
                            </td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium {{ $equity['amount'] < 0 ? 'text-red-600' : '' }}">
                                    {{ $this->formatNumber($year == $selectedYear ? ($equity['amount'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium {{ $equity['amount'] < 0 ? 'text-red-600' : '' }}">
                                    {{ $this->formatNumber($equity['amount'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Total Equity -->
                        <tr class="bg-green-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1">Total Equity</td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center"></td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right {{ ($balanceSheetData['total_equity'] ?? $balanceSheetData['equity']['total'] ?? 0) < 0 ? 'text-red-600' : '' }}">
                                    {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['total_equity'] ?? $balanceSheetData['equity']['total'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right"></td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right {{ ($balanceSheetData['total_equity'] ?? $balanceSheetData['equity']['total'] ?? 0) < 0 ? 'text-red-600' : '' }}">
                                    {{ $this->formatNumber($balanceSheetData['total_equity'] ?? $balanceSheetData['equity']['total'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Current Liabilities -->
            @if(isset($balanceSheetData['liabilities']['current']) && count($balanceSheetData['liabilities']['current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Current Liabilities</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Account</th>
                            @if($showNotes)
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                            @else
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceSheetData['liabilities']['current'] as $index => $liability)
                        @if(isset($liability['amount']) && $liability['amount'] != 0)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <span class="font-medium">{{ $liability['account_name'] ?? '' }}</span>
                            </td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center">
                                <button 
                                    wire:click="showNote({{ $index + 6 }}, '{{ $liability['account_number'] ?? '' }}', '{{ $liability['account_name'] ?? '' }}')"
                                    class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                    {{ $index + 6 }}
                                </button>
                            </td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($year == $selectedYear ? ($liability['amount'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($liability['amount'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Current Liabilities Subtotal -->
                        <tr class="bg-red-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1">Total Current Liabilities</td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center"></td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['liabilities']['current_total'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right"></td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($balanceSheetData['liabilities']['current_total'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Non-Current Liabilities -->
            @if(isset($balanceSheetData['liabilities']['non_current']) && count($balanceSheetData['liabilities']['non_current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Non-Current Liabilities</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 50%;">Account</th>
                            @if($showNotes)
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">{{ $year }}</th>
                                @endforeach
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;">Change %</th>
                            @else
                                <th class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceSheetData['liabilities']['non_current'] as $index => $liability)
                        @if(isset($liability['amount']) && $liability['amount'] != 0)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1">
                                <span class="font-medium">{{ $liability['account_name'] ?? '' }}</span>
                            </td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center">
                                <button 
                                    wire:click="showNote({{ count($balanceSheetData['liabilities']['current']) + $index + 6 }}, '{{ $liability['account_number'] ?? '' }}', '{{ $liability['account_name'] ?? '' }}')"
                                    class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                                    {{ count($balanceSheetData['liabilities']['current']) + $index + 6 }}
                                </button>
                            </td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($year == $selectedYear ? ($liability['amount'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right">-</td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                                    {{ $this->formatNumber($liability['amount'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Non-Current Liabilities Subtotal -->
                        <tr class="bg-red-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1">Total Non-Current Liabilities</td>
                            @if($showNotes)
                            <td class="border border-gray-300 px-2 py-1 text-center"></td>
                            @endif
                            @if($showComparison)
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['liabilities']['non_current_total'] ?? 0) : 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right"></td>
                            @else
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    {{ $this->formatNumber($balanceSheetData['liabilities']['non_current_total'] ?? 0) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Total Liabilities -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-red-200 font-semibold">
                        <td class="border border-gray-300 px-2 py-1" style="width: 50%;">Total Liabilities</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">
                                {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['total_liabilities'] ?? $balanceSheetData['liabilities']['total'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 10%;"></td>
                        @else
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">
                                {{ $this->formatNumber($balanceSheetData['total_liabilities'] ?? $balanceSheetData['liabilities']['total'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
            
            <!-- Total Equity and Liabilities -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-red-200 font-bold">
                        <td class="border border-gray-300 px-2 py-2" style="width: 50%;">TOTAL EQUITY AND LIABILITIES</td>
                        @if($showNotes)
                        <td class="border border-gray-300 px-2 py-2 text-center" style="width: 10%;"></td>
                        @endif
                        @if($showComparison)
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: {{ $showNotes ? '15%' : '20%' }};">
                                {{ $this->formatNumber($year == $selectedYear ? ($balanceSheetData['total_liabilities_equity'] ?? 0) : 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: 10%;">-</td>
                        @else
                            <td class="border border-gray-300 px-2 py-2 text-right" style="width: {{ $showNotes ? '40%' : '50%' }};">
                                {{ $this->formatNumber($balanceSheetData['total_liabilities_equity'] ?? 0) }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Balance Check -->
        @if(isset($balanceSheetData['is_balanced']) && !$balanceSheetData['is_balanced'])
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-300 rounded">
            <p class="text-xs text-yellow-700">
                <strong>Note:</strong> The balance sheet shows a difference. This typically occurs when:
                <ul class="mt-1 ml-4 list-disc">
                    <li>Revenue accounts have not been recorded yet</li>
                    <li>Expenses have been incurred without corresponding revenue</li>
                    <li>The accounting period is still in progress</li>
                </ul>
                @php
                    $difference = ($balanceSheetData['total_assets'] ?? 0) - ($balanceSheetData['total_liabilities_equity'] ?? 0);
                @endphp
                Difference: {{ $this->formatNumber(abs($difference)) }}
            </p>
        </div>
        @else
        <div class="mt-4 p-3 bg-green-50 border border-green-300 rounded">
            <p class="text-xs text-green-700">
                <strong>✓</strong> The balance sheet is balanced.
            </p>
        </div>
        @endif
        
        <!-- Financial Ratios Section -->
        @if($showRatios && isset($financialRatios) && count($financialRatios) > 0)
        <div class="mt-6">
            <h3 class="text-sm font-bold uppercase bg-gray-700 text-white px-3 py-2 rounded">Financial Ratios & Key Performance Indicators</h3>
            
            <div class="grid grid-cols-3 gap-4 mt-3">
                <!-- Liquidity Ratios -->
                @if(isset($financialRatios['liquidity']))
                <div class="bg-red-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Liquidity Ratios</h4>
                    @foreach($financialRatios['liquidity'] as $ratio)
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $ratio['ratio_name'] ?? '' }}</span>
                            <span class="font-medium">{{ number_format($ratio['value'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Solvency Ratios -->
                @if(isset($financialRatios['solvency']))
                <div class="bg-green-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Solvency Ratios</h4>
                    @foreach($financialRatios['solvency'] as $ratio)
                        <div class="flex justify-between text-xs mb-1">
                            <span>{{ $ratio['ratio_name'] ?? '' }}</span>
                            <span class="font-medium">{{ number_format($ratio['value'] ?? 0, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Profitability Ratios -->
                @if(isset($financialRatios['profitability']))
                <div class="bg-yellow-50 p-3 rounded">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Profitability Ratios</h4>
                    @foreach($financialRatios['profitability'] as $ratio)
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
    
    <!-- Off-Balance Sheet Items -->
    @if(isset($offBalanceSheetItems) && isset($offBalanceSheetItems['total_exposure']) && $offBalanceSheetItems['total_exposure'] > 0)
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
        <h4 class="font-bold text-sm mb-3 text-yellow-800">Off-Balance Sheet Items Disclosure</h4>
        <table class="text-xs w-full">
            <thead>
                <tr class="border-b border-yellow-300">
                    <th class="text-left py-1">Description</th>
                    <th class="text-right py-1">Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($offBalanceSheetItems['contingent_liabilities']) && count($offBalanceSheetItems['contingent_liabilities']) > 0)
                <tr>
                    <td class="py-1">Contingent Liabilities</td>
                    <td class="text-right py-1">{{ $this->formatNumber($offBalanceSheetItems['contingent_liabilities']->sum('amount')) }}</td>
                </tr>
                @endif
                
                @if(isset($offBalanceSheetItems['guarantees']) && count($offBalanceSheetItems['guarantees']) > 0)
                <tr>
                    <td class="py-1">Guarantees Issued</td>
                    <td class="text-right py-1">{{ $this->formatNumber($offBalanceSheetItems['guarantees']->sum('guaranteed_amount')) }}</td>
                </tr>
                @endif
                
                @if(isset($offBalanceSheetItems['operating_leases']) && count($offBalanceSheetItems['operating_leases']) > 0)
                <tr>
                    <td class="py-1">Operating Lease Commitments</td>
                    <td class="text-right py-1">{{ $this->formatNumber($offBalanceSheetItems['operating_leases']->sum('total_commitment')) }}</td>
                </tr>
                @endif
                
                <tr class="border-t border-yellow-300 font-bold">
                    <td class="py-1">Total Off-Balance Sheet Exposure</td>
                    <td class="text-right py-1">{{ $this->formatNumber($offBalanceSheetItems['total_exposure']) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="text-xs text-yellow-700 mt-2 italic">
            These items represent potential future obligations that are not currently recognized as liabilities on the balance sheet.
        </p>
    </div>
    @endif
    
    <!-- Footer with Additional Information -->
    <div class="px-4 py-3 bg-gray-50 rounded-b-lg border-t border-gray-200">
        <div class="flex justify-between items-center text-xs text-gray-600">
            <div>
                Generated on: {{ now()->format('d M Y H:i') }}
                @if($isConsolidated ?? false)
                    <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded">Consolidated View</span>
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
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Number</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($noteContent as $item)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_number'] ?? '' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_name'] ?? '' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($item['balance'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr class="font-semibold bg-gray-50">
                                            <td colspan="2" class="px-4 py-2 text-sm text-gray-900">Total</td>
                                            <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                                {{ number_format(collect($noteContent)->sum('balance'), 2) }}
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