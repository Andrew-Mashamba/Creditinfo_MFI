<div class="bg-white rounded-lg shadow-sm">
    <!-- Professional Header -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">STATEMENT OF CASH FLOWS x</h2>
            <p class="text-sm">For the year ended 31 December {{ $selectedYear }}</p>
            <p class="text-xs italic">({{ ucfirst($method) }} Method)</p>
        </div>
    </div>
    
    <!-- Control Panel -->
    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">Year:</label>
                    <select wire:model="selectedYear" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <button wire:click="toggleMethod" 
                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                    Switch to {{ $method === 'indirect' ? 'Direct' : 'Indirect' }} Method
                </button>
                <button wire:click="toggleDetailed" 
                        class="px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-700">
                    {{ $showDetailed ? 'Simple View' : 'Detailed View' }}
                </button>
            </div>
            <div class="flex space-x-2">
                <button wire:click="exportToExcel" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                    Export Excel
                </button>
                <button wire:click="exportToPDF" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                    Export PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- Currency Header -->
    <div class="px-4 py-2">
        <div class="flex justify-end">
            <table class="text-xs border-collapse border border-gray-300">
                <thead>
                    <tr>
                        @foreach($comparisonYears as $year)
                        <th class="border border-gray-300 px-3 py-1 bg-gray-50">{{ $year }}</th>
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
    </div>
    
    <!-- Main Cash Flow Statement -->
    <div class="p-4">
        <table class="w-full text-xs border-collapse border border-gray-300">
            <!-- Operating Activities Section -->
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-2 py-2 text-left" style="width: 50%;">
                        <button wire:click="toggleSection('operating')" class="flex items-center justify-between w-full">
                            <span class="font-bold">CASH FLOWS FROM OPERATING ACTIVITIES</span>
                            <svg class="w-4 h-4 transform {{ in_array('operating', $expandedSections) ? 'rotate-180' : '' }}" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                    </th>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-2 py-2 text-right" style="width: 25%;">{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if($method === 'indirect')
                    <!-- Indirect Method - Operating Activities -->
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 font-semibold">Profit before tax</td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['net_income'] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    
                    @if(in_array('operating', $expandedSections) || $showDetailed)
                    <!-- Adjustments for non-cash items -->
                    <tr class="bg-gray-50">
                        <td colspan="{{ count($comparisonYears) + 1 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                            Adjustments for non-cash items:
                        </td>
                    </tr>
                    
                    @foreach($operatingActivities[$comparisonYears[0]]['adjustments'] ?? [] as $key => $value)
                    @if($value != 0 || (isset($operatingActivities[$comparisonYears[1]]['adjustments'][$key]) && $operatingActivities[$comparisonYears[1]]['adjustments'][$key] != 0))
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            {{ ucwords(str_replace('_', ' ', $key)) }}
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['adjustments'][$key] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                    
                    <!-- Working capital changes -->
                    <tr class="bg-gray-50">
                        <td colspan="{{ count($comparisonYears) + 1 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                            Changes in working capital:
                        </td>
                    </tr>
                    
                    @foreach($operatingActivities[$comparisonYears[0]]['working_capital_changes'] ?? [] as $key => $value)
                    @if($value != 0 || (isset($operatingActivities[$comparisonYears[1]]['working_capital_changes'][$key]) && $operatingActivities[$comparisonYears[1]]['working_capital_changes'][$key] != 0))
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            (Increase)/Decrease in {{ ucwords(str_replace('_', ' ', $key)) }}
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['working_capital_changes'][$key] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                    
                    <!-- Cash flows -->
                    @if(isset($operatingActivities[$comparisonYears[0]]['cash_flows']))
                    <tr class="bg-gray-50">
                        <td colspan="{{ count($comparisonYears) + 1 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                            Cash flows:
                        </td>
                    </tr>
                    
                    @foreach($operatingActivities[$comparisonYears[0]]['cash_flows'] ?? [] as $key => $value)
                    @if($value != 0 || (isset($operatingActivities[$comparisonYears[1]]['cash_flows'][$key]) && $operatingActivities[$comparisonYears[1]]['cash_flows'][$key] != 0))
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            {{ ucwords(str_replace('_', ' ', $key)) }}
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['cash_flows'][$key] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                    @endif
                    @endif
                    
                @else
                    <!-- Direct Method - Operating Activities -->
                    <tr class="bg-gray-50">
                        <td colspan="{{ count($comparisonYears) + 1 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                            Cash receipts:
                        </td>
                    </tr>
                    
                    @foreach($operatingActivities[$comparisonYears[0]]['cash_receipts'] ?? [] as $key => $value)
                    @if($value != 0 || (isset($operatingActivities[$comparisonYears[1]]['cash_receipts'][$key]) && $operatingActivities[$comparisonYears[1]]['cash_receipts'][$key] != 0))
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            {{ ucwords(str_replace('_', ' ', $key)) }}
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['cash_receipts'][$key] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                    
                    <tr class="bg-gray-50">
                        <td colspan="{{ count($comparisonYears) + 1 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                            Cash payments:
                        </td>
                    </tr>
                    
                    @foreach($operatingActivities[$comparisonYears[0]]['cash_payments'] ?? [] as $key => $value)
                    @if($value != 0 || (isset($operatingActivities[$comparisonYears[1]]['cash_payments'][$key]) && $operatingActivities[$comparisonYears[1]]['cash_payments'][$key] != 0))
                    <tr>
                        <td class="border border-gray-300 px-2 py-1 pl-6">
                            {{ ucwords(str_replace('_', ' ', $key)) }}
                        </td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right">
                            {{ $this->formatNumber($operatingActivities[$year]['cash_payments'][$key] ?? 0) }}
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                @endif
                
                <!-- Net cash from operating activities -->
                <tr class="bg-blue-100 font-bold">
                    <td class="border border-gray-300 px-2 py-1">Net cash from operating activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($operatingActivities[$year]['total'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
            </tbody>
            
            <!-- Investing Activities Section -->
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        <button wire:click="toggleSection('investing')" class="flex items-center justify-between w-full">
                            <span class="font-bold">CASH FLOWS FROM INVESTING ACTIVITIES</span>
                            <svg class="w-4 h-4 transform {{ in_array('investing', $expandedSections) ? 'rotate-180' : '' }}" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                    </th>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-2 py-2 text-right">{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if(in_array('investing', $expandedSections) || $showDetailed)
                @foreach($investingActivities[$comparisonYears[0]] ?? [] as $key => $value)
                @if($key !== 'total' && ($value != 0 || (isset($investingActivities[$comparisonYears[1]][$key]) && $investingActivities[$comparisonYears[1]][$key] != 0)))
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        {{ ucwords(str_replace('_', ' ', $key)) }}
                    </td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($investingActivities[$year][$key] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                @endif
                @endforeach
                @endif
                
                <!-- Net cash from investing activities -->
                <tr class="bg-green-100 font-bold">
                    <td class="border border-gray-300 px-2 py-1">Net cash used in investing activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($investingActivities[$year]['total'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
            </tbody>
            
            <!-- Financing Activities Section -->
            <thead>
                <tr class="bg-blue-900 text-white">
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        <button wire:click="toggleSection('financing')" class="flex items-center justify-between w-full">
                            <span class="font-bold">CASH FLOWS FROM FINANCING ACTIVITIES</span>
                            <svg class="w-4 h-4 transform {{ in_array('financing', $expandedSections) ? 'rotate-180' : '' }}" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                    </th>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-2 py-2 text-right">{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if(in_array('financing', $expandedSections) || $showDetailed)
                @foreach($financingActivities[$comparisonYears[0]] ?? [] as $key => $value)
                @if($key !== 'total' && ($value != 0 || (isset($financingActivities[$comparisonYears[1]][$key]) && $financingActivities[$comparisonYears[1]][$key] != 0)))
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        {{ ucwords(str_replace('_', ' ', $key)) }}
                    </td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($financingActivities[$year][$key] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                @endif
                @endforeach
                @endif
                
                <!-- Net cash from financing activities -->
                <tr class="bg-yellow-100 font-bold">
                    <td class="border border-gray-300 px-2 py-1">Net cash from financing activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($financingActivities[$year]['total'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        
        <!-- Cash Flow Summary -->
        <table class="w-full text-xs border-collapse border border-gray-300 mt-4">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="border border-gray-300 px-2 py-2 text-left font-bold" style="width: 50%;">
                        CASH AND CASH EQUIVALENTS
                    </th>
                    @foreach($comparisonYears as $year)
                    <th class="border border-gray-300 px-2 py-2 text-right" style="width: 25%;">{{ $year }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-2 py-1">Cash and cash equivalents at beginning of year</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['opening_balance'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-2 py-1 pl-4">Net cash from operating activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['operating_activities'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-2 py-1 pl-4">Net cash used in investing activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['investing_activities'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                
                <tr class="bg-gray-50">
                    <td class="border border-gray-300 px-2 py-1 pl-4">Net cash from financing activities</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['financing_activities'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                
                <tr class="font-semibold">
                    <td class="border border-gray-300 px-2 py-1">Net increase/(decrease) in cash and cash equivalents</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right {{ ($cashFlowSummary[$year]['net_increase'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $this->formatNumber($cashFlowSummary[$year]['net_increase'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                
                @if(($cashFlowSummary[$comparisonYears[0]]['fx_effects'] ?? 0) != 0 || ($cashFlowSummary[$comparisonYears[1]]['fx_effects'] ?? 0) != 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1">Effects of exchange rate changes</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['fx_effects'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
                @endif
                
                <tr class="bg-blue-900 text-white font-bold">
                    <td class="border border-gray-300 px-2 py-2">Cash and cash equivalents at end of year</td>
                    @foreach($comparisonYears as $year)
                    <td class="border border-gray-300 px-2 py-2 text-right">
                        {{ $this->formatNumber($cashFlowSummary[$year]['closing_balance'] ?? 0) }}
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        
        <!-- Supplemental Disclosures -->
        @if($showDetailed)
        <div class="mt-6 border border-gray-300 rounded-lg p-4">
            <h3 class="text-sm font-bold mb-3">SUPPLEMENTAL DISCLOSURES</h3>
            
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <h4 class="font-semibold mb-2">Non-cash Investing and Financing Activities:</h4>
                    <ul class="space-y-1 text-gray-700">
                        <li>• Assets acquired under finance leases: TZS 0.00</li>
                        <li>• Conversion of debt to equity: TZS 0.00</li>
                        <li>• Dividends declared but not paid: TZS 0.00</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-2">Cash and Cash Equivalents comprise:</h4>
                    <ul class="space-y-1 text-gray-700">
                        <li>• Cash on hand</li>
                        <li>• Bank balances</li>
                        <li>• Short-term deposits (< 3 months)</li>
                        <li>• Treasury bills (< 3 months)</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Reconciliation Note -->
        @if($method === 'direct' && $showDetailed)
        <div class="mt-4 border border-gray-300 rounded-lg p-4">
            <h3 class="text-sm font-bold mb-3">RECONCILIATION OF NET INCOME TO NET CASH FROM OPERATING ACTIVITIES</h3>
            <p class="text-xs text-gray-700">
                A reconciliation between net income and net cash flow from operating activities is presented in Note X to the financial statements.
            </p>
        </div>
        @endif
    </div>
    
    <!-- Messages -->
    @if (session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('message') }}
    </div>
    @endif
</div>