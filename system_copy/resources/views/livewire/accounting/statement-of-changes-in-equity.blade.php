<div class="bg-white rounded-lg shadow-sm">
    <!-- Professional Header -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3 rounded-t-lg">
        <div class="text-center">
            <h1 class="text-xl font-bold uppercase">{{ $companyName }}</h1>
            <h2 class="text-lg font-semibold">STATEMENT OF CHANGES IN EQUITY</h2>
            <p class="text-sm">For the year ended 31 December {{ $selectedYear }}</p>
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
                <button wire:click="toggleDetailedView" 
                        class="px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-700">
                    {{ $showDetailed ? 'Simple View' : 'Detailed View' }}
                </button>
                <button wire:click="addEntry" 
                        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                    Add Entry
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
    </div>
    
    <!-- Main Statement Table -->
    <div class="p-4 overflow-x-auto">
        <table class="w-full text-xs border-collapse border border-gray-300">
            <thead>
                <tr class="bg-blue-50">
                    <th class="border border-gray-300 px-2 py-2 text-left font-bold" style="width: 20%;">
                        Particulars
                    </th>
                    @foreach($equityAccounts as $account)
                    <th class="border border-gray-300 px-2 py-2 text-center font-medium" style="width: {{ 60 / count($equityAccounts) }}%;">
                        <div class="flex flex-col">
                            <span>{{ $account->account_name }}</span>
                            @if($account->percent)
                            <span class="text-gray-500 text-xs">({{ $account->percent }}%)</span>
                            @endif
                        </div>
                    </th>
                    @endforeach
                    <th class="border border-gray-300 px-2 py-2 text-center font-bold" style="width: 20%;">
                        Total Equity
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($comparisonYears as $yearIndex => $year)
                @php
                    $yearData = $equityData[$year];
                    $previousYear = $yearIndex > 0 ? $comparisonYears[$yearIndex - 1] : null;
                @endphp
                
                <!-- Year Section Header -->
                <tr class="bg-gray-100">
                    <td colspan="{{ count($equityAccounts) + 2 }}" class="border border-gray-300 px-2 py-1 font-bold">
                        Year {{ $year }}
                    </td>
                </tr>
                
                <!-- Opening Balance -->
                <tr>
                    <td class="border border-gray-300 px-2 py-1 font-semibold">
                        Balance at 1 January {{ $year }}
                    </td>
                    @php $openingTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $openingBalance = $yearData['opening_balance'][$account->account_number] ?? 0;
                        $openingTotal += $openingBalance;
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($openingBalance) }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right font-semibold">
                        {{ $this->formatNumber($openingTotal) }}
                    </td>
                </tr>
                
                <!-- Comprehensive Income Section -->
                @if($yearData['profit_for_year'] != 0 || $yearData['other_comprehensive_income'] != 0)
                <tr class="bg-blue-50">
                    <td colspan="{{ count($equityAccounts) + 2 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                        Comprehensive Income
                    </td>
                </tr>
                
                <!-- Profit for the year -->
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Profit for the year
                    </td>
                    @foreach($equityAccounts as $account)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        @if(str_contains(strtolower($account->account_name), 'retained'))
                            {{ $this->formatNumber($yearData['profit_for_year']) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right font-medium">
                        {{ $this->formatNumber($yearData['profit_for_year']) }}
                    </td>
                </tr>
                
                @if($yearData['other_comprehensive_income'] != 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Other comprehensive income
                    </td>
                    @foreach($equityAccounts as $account)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        -
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($yearData['other_comprehensive_income']) }}
                    </td>
                </tr>
                @endif
                
                <!-- Total Comprehensive Income -->
                <tr class="font-semibold bg-gray-50">
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Total comprehensive income
                    </td>
                    @foreach($equityAccounts as $account)
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        @if(str_contains(strtolower($account->account_name), 'retained'))
                            {{ $this->formatNumber($yearData['total_comprehensive_income']) }}
                        @else
                            -
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right font-semibold">
                        {{ $this->formatNumber($yearData['total_comprehensive_income']) }}
                    </td>
                </tr>
                @endif
                
                <!-- Transactions with Owners Section -->
                @if(count($yearData['appropriations']) > 0 || count($yearData['dividends']) > 0 || count($yearData['contributions']) > 0)
                <tr class="bg-yellow-50">
                    <td colspan="{{ count($equityAccounts) + 2 }}" class="border border-gray-300 px-2 py-1 font-semibold italic">
                        Transactions with Owners
                    </td>
                </tr>
                
                <!-- Appropriations -->
                @if(count($yearData['appropriations']) > 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Appropriations for the year
                    </td>
                    @php $appropriationTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $appropriation = $yearData['appropriations'][$account->account_number] ?? 0;
                        if (!str_contains(strtolower($account->account_name), 'retained')) {
                            $appropriationTotal += $appropriation;
                        }
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        @if(str_contains(strtolower($account->account_name), 'retained'))
                            ({{ number_format($appropriationTotal, 2) }})
                        @else
                            {{ $appropriation > 0 ? $this->formatNumber($appropriation) : '-' }}
                        @endif
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        -
                    </td>
                </tr>
                @endif
                
                <!-- Dividends -->
                @if(array_sum($yearData['dividends']) > 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Dividends paid
                    </td>
                    @php $dividendTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $dividend = $yearData['dividends'][$account->account_number] ?? 0;
                        $dividendTotal += $dividend;
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $dividend > 0 ? '(' . number_format($dividend, 2) . ')' : '-' }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        ({{ number_format($dividendTotal, 2) }})
                    </td>
                </tr>
                @endif
                
                <!-- Contributions -->
                @if(array_sum($yearData['contributions']) > 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1 pl-4">
                        Contributions by members
                    </td>
                    @php $contributionTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $contribution = $yearData['contributions'][$account->account_number] ?? 0;
                        $contributionTotal += $contribution;
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $contribution > 0 ? $this->formatNumber($contribution) : '-' }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($contributionTotal) }}
                    </td>
                </tr>
                @endif
                @endif
                
                <!-- Other Changes -->
                @if(array_sum($yearData['other_changes']) > 0)
                <tr>
                    <td class="border border-gray-300 px-2 py-1">
                        Other changes in equity
                    </td>
                    @php $otherTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $other = $yearData['other_changes'][$account->account_number] ?? 0;
                        $otherTotal += $other;
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $other != 0 ? $this->formatNumber($other) : '-' }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($otherTotal) }}
                    </td>
                </tr>
                @endif
                
                <!-- Closing Balance -->
                <tr class="font-bold bg-green-50">
                    <td class="border border-gray-300 px-2 py-1">
                        Balance at 31 December {{ $year }}
                    </td>
                    @php $closingTotal = 0; @endphp
                    @foreach($equityAccounts as $account)
                    @php 
                        $closingBalance = $yearData['closing_balance'][$account->account_number] ?? 0;
                        $closingTotal += $closingBalance;
                    @endphp
                    <td class="border border-gray-300 px-2 py-1 text-right">
                        {{ $this->formatNumber($closingBalance) }}
                    </td>
                    @endforeach
                    <td class="border border-gray-300 px-2 py-1 text-right font-bold">
                        {{ $this->formatNumber($closingTotal) }}
                    </td>
                </tr>
                
                @if($yearIndex < count($comparisonYears) - 1)
                <!-- Spacer between years -->
                <tr>
                    <td colspan="{{ count($equityAccounts) + 2 }}" class="border-0 py-2"></td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Detailed Movement Analysis (if enabled) -->
    @if($showDetailed)
    <div class="p-4 border-t border-gray-200">
        <h3 class="text-sm font-bold mb-3">Detailed Movement Analysis</h3>
        
        <div class="grid grid-cols-2 gap-4">
            @foreach($equityAccounts as $account)
            <div class="border border-gray-200 rounded p-3">
                <h4 class="text-xs font-semibold mb-2 flex justify-between items-center">
                    <span>{{ $account->account_name }}</span>
                    <button wire:click="toggleAccount('{{ $account->account_number }}')" 
                            class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4 transform {{ in_array($account->account_number, $expandedAccounts) ? 'rotate-90' : '' }}" 
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                        </svg>
                    </button>
                </h4>
                
                @if(in_array($account->account_number, $expandedAccounts))
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-2 py-1 text-left">Year</th>
                            <th class="px-2 py-1 text-right">Opening</th>
                            <th class="px-2 py-1 text-right">Changes</th>
                            <th class="px-2 py-1 text-right">Closing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparisonYears as $year)
                        @php
                            $yearData = $equityData[$year];
                            $opening = $yearData['opening_balance'][$account->account_number] ?? 0;
                            $closing = $yearData['closing_balance'][$account->account_number] ?? 0;
                            $change = $closing - $opening;
                        @endphp
                        <tr class="border-t">
                            <td class="px-2 py-1">{{ $year }}</td>
                            <td class="px-2 py-1 text-right">{{ $this->formatNumber($opening) }}</td>
                            <td class="px-2 py-1 text-right {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->formatNumber($change) }}
                            </td>
                            <td class="px-2 py-1 text-right font-semibold">{{ $this->formatNumber($closing) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Add Entry Modal -->
    @if($showAddEntry)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white px-4 py-3 rounded-t-lg">
                <h3 class="text-sm font-bold">Add Equity Movement Entry</h3>
            </div>
            
            <div class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Entry Type</label>
                        <select wire:model="entryType" class="w-full text-xs border-gray-300 rounded-md">
                            <option value="">Select type...</option>
                            <option value="dividend">Dividend Payment</option>
                            <option value="appropriation">Profit Appropriation</option>
                            <option value="contribution">Member Contribution</option>
                            <option value="adjustment">Prior Year Adjustment</option>
                            <option value="other">Other Movement</option>
                        </select>
                        @error('entryType') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" wire:model="entryDescription" 
                               class="w-full text-xs border-gray-300 rounded-md"
                               placeholder="Enter description...">
                        @error('entryDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Account Amounts</label>
                        <div class="space-y-2">
                            @foreach($equityAccounts as $account)
                            <div class="flex items-center space-x-2">
                                <label class="text-xs w-1/2">{{ $account->account_name }}</label>
                                <input type="number" step="0.01" 
                                       wire:model="entryAccounts.{{ $account->account_number }}"
                                       class="w-1/2 text-xs border-gray-300 rounded-md"
                                       placeholder="0.00">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4">
                    <button wire:click="cancelEntry" 
                            class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button wire:click="saveEntry" 
                            class="px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-700">
                        Save Entry
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Messages -->
    @if (session()->has('message'))
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('message') }}
    </div>
    @endif
    
    @if (session()->has('error'))
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ session('error') }}
    </div>
    @endif
</div>