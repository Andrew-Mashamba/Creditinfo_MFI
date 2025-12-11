<div class="bg-white rounded-lg shadow-sm">
    <!-- Professional Header -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">STATEMENT OF FINANCIAL POSITION</h2>
            <p class="text-sm">As at 31 December {{ $selectedYear }}</p>
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
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-700">View Level:</label>
                    <select wire:model="viewLevel" wire:change="loadFinancialData" class="text-xs border-gray-300 rounded-md shadow-sm">
                        <option value="2">L2 - Summary</option>
                        <option value="3">L3 - Detailed</option>
                        <option value="4">L4 - Full Detail</option>
                    </select>
                </div>
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
    
    <!-- Main Content Area -->
    <div class="p-4">
        
        <!-- ASSETS Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-blue-900 text-white px-3 py-2 rounded">ASSETS</h3>
            
            <!-- Current Assets -->
            @if(count($assetsData['current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Current Assets</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 40%;">Account</th>
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assetsData['current'] as $index => $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $asset['account_name'] }}</span>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">
                                <button 
                                    wire:click="showNote({{ $index + 5 }}, '{{ $asset['account_name'] }}')"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold cursor-pointer">
                                    {{ $index + 5 }}
                                </button>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" font-medium">
                                {{ $this->formatNumber($asset['years'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" {{ $this->calculateVariance($asset['years'][$comparisonYears[0]] ?? 0, $asset['years'][$comparisonYears[1]] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($this->calculateVariance($asset['years'][$comparisonYears[0]] ?? 0, $asset['years'][$comparisonYears[1]] ?? 0), 1) }}%
                            </td>
                        </tr>
                        
                        @if(in_array($asset['account_number'], $expandedCategories))
                            @foreach($asset['children'] as $l3)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1 pl-6" style="width: 40%;">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">{{ $l3['account_name'] }}</span>
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-gray-600">
                                    {{ $this->formatNumber($l3['years'][$year] ?? 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                            </tr>
                            
                            @if(in_array($l3['account_number'], $expandedSubcategories))
                                @foreach($l3['children'] as $l4)
                                <tr class="bg-blue-50">
                                    <td class="border border-gray-300 px-2 py-1 pl-10" style="width: 40%;">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500 text-xs">{{ $l4['account_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                    @foreach($comparisonYears as $year)
                                    <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-xs text-gray-500">
                                        {{ $this->formatNumber($l4['years'][$year] ?? 0) }}
                                    </td>
                                    @endforeach
                                    <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                                </tr>
                                @endforeach
                            @endif
                            @endforeach
                        @endif
                        @endforeach
                        
                        <!-- Current Assets Subtotal -->
                        <tr class="bg-blue-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Current Assets</td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                            @php
                                $currentAssetsTotal = [];
                                foreach($comparisonYears as $year) {
                                    $currentAssetsTotal[$year] = 0;
                                    foreach($assetsData['current'] as $asset) {
                                        $currentAssetsTotal[$year] += $asset['years'][$year] ?? 0;
                                    }
                                }
                            @endphp
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $this->formatNumber($currentAssetsTotal[$year]) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Non-Current Assets -->
            @if(count($assetsData['non_current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Non-Current Assets</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 40%;">Account</th>
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assetsData['non_current'] as $index => $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $asset['account_name'] }}</span>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">
                                <button 
                                    wire:click="showNote({{ count($assetsData['current']) + $index + 5 }}, '{{ $asset['account_name'] }}')"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold cursor-pointer">
                                    {{ count($assetsData['current']) + $index + 5 }}
                                </button>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" font-medium">
                                {{ $this->formatNumber($asset['years'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" {{ $this->calculateVariance($asset['years'][$comparisonYears[0]] ?? 0, $asset['years'][$comparisonYears[1]] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($this->calculateVariance($asset['years'][$comparisonYears[0]] ?? 0, $asset['years'][$comparisonYears[1]] ?? 0), 1) }}%
                            </td>
                        </tr>
                        
                        @if(in_array($asset['account_number'], $expandedCategories))
                            @foreach($asset['children'] as $l3)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1 pl-6" style="width: 40%;">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">{{ $l3['account_name'] }}</span>
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-gray-600">
                                    {{ $this->formatNumber($l3['years'][$year] ?? 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                        
                        <!-- Non-Current Assets Subtotal -->
                        <tr class="bg-blue-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Non-Current Assets</td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                            @php
                                $nonCurrentAssetsTotal = [];
                                foreach($comparisonYears as $year) {
                                    $nonCurrentAssetsTotal[$year] = 0;
                                    foreach($assetsData['non_current'] as $asset) {
                                        $nonCurrentAssetsTotal[$year] += $asset['years'][$year] ?? 0;
                                    }
                                }
                            @endphp
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $this->formatNumber($nonCurrentAssetsTotal[$year]) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Total Assets -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-blue-200  font-bold">
                        <td class="border border-gray-300 px-2 py-2" style="width: 40%;">TOTAL ASSETS</td>
                        <td class="border border-gray-300 px-2 py-2 text-center" style="width: 10%;"></td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-2 text-right" style="width: 16.67%;">
                            {{ $this->formatNumber($assetsData['total'][$year] ?? 0) }}
                        </td>
                        @endforeach
                        <td class="border border-gray-300 px-2 py-2 text-right" style="width: 16.67%;">
                            {{ number_format($this->calculateVariance($assetsData['total'][$comparisonYears[0]] ?? 0, $assetsData['total'][$comparisonYears[1]] ?? 0), 1) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- EQUITY AND LIABILITIES Section -->
        <div class="mb-6">
            <h3 class="text-sm font-bold uppercase bg-blue-900 text-white px-3 py-2 rounded">EQUITY AND LIABILITIES</h3>
            
            <!-- Equity -->
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Members' Equity</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 40%;">Account</th>
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_merge($equityData['current'], $equityData['non_current']) as $index => $equity)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $equity['account_name'] }}</span>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">
                                <button 
                                    wire:click="showNote({{ $index + 7 }}, '{{ $equity['account_name'] }}')"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold cursor-pointer">
                                    {{ $index + 7 }}
                                </button>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" font-medium">
                                {{ $this->formatNumber($equity['years'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" {{ $this->calculateVariance($equity['years'][$comparisonYears[0]] ?? 0, $equity['years'][$comparisonYears[1]] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($this->calculateVariance($equity['years'][$comparisonYears[0]] ?? 0, $equity['years'][$comparisonYears[1]] ?? 0), 1) }}%
                            </td>
                        </tr>
                        
                        @if(in_array($equity['account_number'], $expandedCategories))
                            @foreach($equity['children'] as $l3)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1 pl-6" style="width: 40%;">
                                    <span class="text-gray-600">{{ $l3['account_name'] }}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-gray-600">
                                    {{ $this->formatNumber($l3['years'][$year] ?? 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                        
                        <!-- Total Equity -->
                        <tr class="bg-blue-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Equity</td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $this->formatNumber($equityData['total'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Current Liabilities -->
            @if(count($liabilitiesData['current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Current Liabilities</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 40%;">Account</th>
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liabilitiesData['current'] as $index => $liability)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $liability['account_name'] }}</span>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">
                                <button 
                                    wire:click="showNote({{ $index + 6 }}, '{{ $liability['account_name'] }}')"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold cursor-pointer">
                                    {{ $index + 6 }}
                                </button>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" font-medium">
                                {{ $this->formatNumber($liability['years'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" {{ $this->calculateVariance($liability['years'][$comparisonYears[0]] ?? 0, $liability['years'][$comparisonYears[1]] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($this->calculateVariance($liability['years'][$comparisonYears[0]] ?? 0, $liability['years'][$comparisonYears[1]] ?? 0), 1) }}%
                            </td>
                        </tr>
                        
                        @if(in_array($liability['account_number'], $expandedCategories))
                            @foreach($liability['children'] as $l3)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1 pl-6" style="width: 40%;">
                                    <span class="text-gray-600">{{ $l3['account_name'] }}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-gray-600">
                                    {{ $this->formatNumber($l3['years'][$year] ?? 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                        
                        <!-- Current Liabilities Subtotal -->
                        <tr class="bg-blue-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Current Liabilities</td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                            @php
                                $currentLiabilitiesTotal = [];
                                foreach($comparisonYears as $year) {
                                    $currentLiabilitiesTotal[$year] = 0;
                                    foreach($liabilitiesData['current'] as $liability) {
                                        $currentLiabilitiesTotal[$year] += $liability['years'][$year] ?? 0;
                                    }
                                }
                            @endphp
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $this->formatNumber($currentLiabilitiesTotal[$year]) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Non-Current Liabilities -->
            @if(count($liabilitiesData['non_current']) > 0)
            <div class="mt-3">
                <h4 class="text-xs font-semibold text-gray-700 mb-2 px-2">Non-Current Liabilities</h4>
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 40%;">Account</th>
                            <th class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">Note</th>
                            @foreach($comparisonYears as $year)
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">{{ $year }}</th>
                            @endforeach
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Change %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liabilitiesData['non_current'] as $index => $liability)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">{{ $liability['account_name'] }}</span>
                                </div>
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;">
                                <button 
                                    wire:click="showNote({{ count($liabilitiesData['current']) + $index + 6 }}, '{{ $liability['account_name'] }}')"
                                    class="text-blue-600 hover:text-blue-800 underline font-semibold cursor-pointer">
                                    {{ count($liabilitiesData['current']) + $index + 6 }}
                                </button>
                            </td>
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" font-medium">
                                {{ $this->formatNumber($liability['years'][$year] ?? 0) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" {{ $this->calculateVariance($liability['years'][$comparisonYears[0]] ?? 0, $liability['years'][$comparisonYears[1]] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($this->calculateVariance($liability['years'][$comparisonYears[0]] ?? 0, $liability['years'][$comparisonYears[1]] ?? 0), 1) }}%
                            </td>
                        </tr>
                        
                        @if(in_array($liability['account_number'], $expandedCategories))
                            @foreach($liability['children'] as $l3)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-2 py-1 pl-6" style="width: 40%;">
                                    <span class="text-gray-600">{{ $l3['account_name'] }}</span>
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                                @foreach($comparisonYears as $year)
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;" text-gray-600">
                                    {{ $this->formatNumber($l3['years'][$year] ?? 0) }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                            </tr>
                            @endforeach
                        @endif
                        @endforeach
                        
                        <!-- Non-Current Liabilities Subtotal -->
                        <tr class="bg-blue-100 font-semibold">
                            <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Non-Current Liabilities</td>
                            <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;""></td>
                            @php
                                $nonCurrentLiabilitiesTotal = [];
                                foreach($comparisonYears as $year) {
                                    $nonCurrentLiabilitiesTotal[$year] = 0;
                                    foreach($liabilitiesData['non_current'] as $liability) {
                                        $nonCurrentLiabilitiesTotal[$year] += $liability['years'][$year] ?? 0;
                                    }
                                }
                            @endphp
                            @foreach($comparisonYears as $year)
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $this->formatNumber($nonCurrentLiabilitiesTotal[$year]) }}
                            </td>
                            @endforeach
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;""></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Total Liabilities -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-blue-100 font-semibold">
                        <td class="border border-gray-300 px-2 py-1" style="width: 40%;">Total Liabilities</td>
                        <td class="border border-gray-300 px-2 py-1 text-center" style="width: 10%;"></td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">
                            {{ $this->formatNumber($liabilitiesData['total'][$year] ?? 0) }}
                        </td>
                        @endforeach
                        <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Total Equity and Liabilities -->
            <table class="text-xs border-collapse border border-gray-300 mt-2" style="width: calc(100% - 2rem); margin-left: 2rem;">
                <tbody>
                    <tr class="bg-blue-200 font-bold">
                        <td class="border border-gray-300 px-2 py-2" style="width: 40%;">TOTAL EQUITY AND LIABILITIES</td>
                        <td class="border border-gray-300 px-2 py-2 text-center" style="width: 10%;"></td>
                        @foreach($comparisonYears as $year)
                        <td class="border border-gray-300 px-2 py-2 text-right" style="width: 16.67%;">
                            {{ $this->formatNumber($summaryData[$year]['total_liabilities_equity'] ?? 0) }}
                        </td>
                        @endforeach
                        <td class="border border-gray-300 px-2 py-2 text-right" style="width: 16.67%;">
                            {{ number_format($this->calculateVariance(
                                $summaryData[$comparisonYears[0]]['total_liabilities_equity'] ?? 0, 
                                $summaryData[$comparisonYears[1]]['total_liabilities_equity'] ?? 0
                            ), 1) }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Balance Check -->
        @if(abs($summaryData[$comparisonYears[0]]['balance_check'] ?? 0) > 0.01)
        <div class="mt-4 p-3 bg-red-50 border border-red-300 rounded">
            <p class="text-xs text-red-700">
                <strong>Warning:</strong> The balance sheet is not balanced. 
                Difference: {{ $this->formatNumber($summaryData[$comparisonYears[0]]['balance_check']) }}
            </p>
        </div>
        @endif
    </div>
    
    <!-- Account Detail Modal -->
    @if($showAccountDetail)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[80vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-bold">General Ledger Transactions</h3>
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
                <table class="text-xs border-collapse border border-gray-300" style="width: calc(100% - 2rem); margin-left: 2rem;">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 16.67%;">Date</th>
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 16.67%;">Reference</th>
                            <th class="border border-gray-300 px-2 py-1 text-left" style="width: 33.33%;">Description</th>
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Debit</th>
                            <th class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accountTransactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;">{{ $transaction['date'] }}</td>
                            <td class="border border-gray-300 px-2 py-1" style="width: 16.67%;">{{ $transaction['reference'] }}</td>
                            <td class="border border-gray-300 px-2 py-1" style="width: 33.33%;">{{ $transaction['description'] }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
                                {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-1 text-right" style="width: 16.67%;"">
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
    
    <!-- Note Modal -->
    @if($showNoteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Note {{ $noteNumber }}: {{ $noteTitle }}
                            </h3>
                            <button wire:click="closeNote" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            @if(is_array($noteContent) && count($noteContent) > 0)
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
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_number'] }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_name'] }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($item['balance'], 2) }}
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
                            class="inline-flex w-full justify-center rounded-md bg-blue-200 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>