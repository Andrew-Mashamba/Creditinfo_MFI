<div class="bg-gray-100 min-h-screen p-4">
    {{-- Excel-Style Income Statement Container --}}
    <div class="max-w-6xl mx-auto">
        {{-- Header Controls --}}
        <div class="bg-white border border-gray-400 mb-0 p-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Year:</label>
                <select wire:model="selectedYear" class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-red-500">
                    @for($i = date('Y'); $i >= date('Y') - 10; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                <span class="text-gray-400">|</span>
                <button wire:click="$set('viewMode', 'detailed')" class="text-sm text-red-600 hover:text-red-800 underline">
                    Switch to Detailed View
                </button>
            </div>
            <div class="flex space-x-2">
                <button wire:click="exportToPDF" class="bg-white border border-gray-400 text-gray-700 px-3 py-1 text-sm hover:bg-gray-50 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </button>
                <button wire:click="exportToExcel" class="bg-green-600 text-white px-3 py-1 text-sm hover:bg-green-700 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-5h4" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>

        {{-- Main Excel-Style Table --}}
        <div class="bg-white border border-gray-400" style="font-family: Arial, sans-serif;">
            <table class="w-full" cellspacing="0" cellpadding="0">
                {{-- Title Row --}}
                <tr>
                    <td colspan="4" class="text-center py-3 border-b-2 border-gray-400 bg-gray-50">
                        <div class="font-bold text-xl text-gray-900">INCOME STATEMENT </div>
                        <div class="text-sm text-gray-600 mt-1">For the Year Ended December 31, {{ $selectedYear }}</div>
                    </td>
                </tr>

                {{-- Header Row --}}
                <tr class="bg-gray-100">
                    <td class="border border-gray-400 px-4 py-2 font-bold text-sm text-gray-800" style="width: 50%;">PARTICULARS</td>
                    <td class="border border-gray-400 px-2 py-2 text-center font-bold text-sm text-gray-800" style="width: 10%;">Note</td>
                    <td class="border border-gray-400 px-4 py-2 text-right font-bold text-sm text-gray-800" style="width: 20%;">{{ $comparisonYears[0] }}</td>
                    <td class="border border-gray-400 px-4 py-2 text-right font-bold text-sm text-gray-800" style="width: 20%;">{{ $comparisonYears[1] }}</td>
                </tr>

                {{-- INCOME SECTION --}}
                <tr>
                    <td colspan="4" class="border border-gray-400 px-4 py-2 font-bold text-sm bg-red-50 text-red-900">INCOME</td>
                </tr>

                {{-- Calculate Income Categories --}}
                @php
                    $noteNumber = 1;
                    $interestIncome = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    $interestOnSavings = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    $otherIncome = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    
                    // Categorize income items
                    foreach($incomeData as $income) {
                        $accountName = strtolower($income['account_name']);
                        
                        // Check if it's interest income on loans
                        if (str_contains($accountName, 'interest') && (str_contains($accountName, 'loan') || str_contains($accountName, 'mikopo'))) {
                            $interestIncome[$comparisonYears[0]] += $income['years'][$comparisonYears[0]] ?? 0;
                            $interestIncome[$comparisonYears[1]] += $income['years'][$comparisonYears[1]] ?? 0;
                        }
                        // Check if it's interest on savings/deposits
                        elseif (str_contains($accountName, 'interest') && (str_contains($accountName, 'saving') || str_contains($accountName, 'deposit') || str_contains($accountName, 'akiba'))) {
                            $interestOnSavings[$comparisonYears[0]] += $income['years'][$comparisonYears[0]] ?? 0;
                            $interestOnSavings[$comparisonYears[1]] += $income['years'][$comparisonYears[1]] ?? 0;
                        }
                        // Everything else goes to other income
                        else {
                            $otherIncome[$comparisonYears[0]] += $income['years'][$comparisonYears[0]] ?? 0;
                            $otherIncome[$comparisonYears[1]] += $income['years'][$comparisonYears[1]] ?? 0;
                        }
                    }
                    
                    // Calculate net interest income
                    $netInterestIncome = [
                        $comparisonYears[0] => $interestIncome[$comparisonYears[0]] - $interestOnSavings[$comparisonYears[0]],
                        $comparisonYears[1] => $interestIncome[$comparisonYears[1]] - $interestOnSavings[$comparisonYears[1]]
                    ];
                @endphp

                {{-- Interest Income --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm font-medium">Interest Income on Loans</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Interest Income on Loans')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($interestIncome[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($interestIncome[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Less: Interest on Savings --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Less: Interest on Savings</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Interest on Savings')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm text-red-600">({{ number_format($interestOnSavings[$comparisonYears[0]], 2) }})</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm text-red-600">({{ number_format($interestOnSavings[$comparisonYears[1]], 2) }})</td>
                </tr>

                {{-- Other Income --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Other Income</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Other Income')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($otherIncome[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($otherIncome[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Total Income Row --}}
                @php
                    $totalIncome = [
                        $comparisonYears[0] => $netInterestIncome[$comparisonYears[0]] + $otherIncome[$comparisonYears[0]],
                        $comparisonYears[1] => $netInterestIncome[$comparisonYears[1]] + $otherIncome[$comparisonYears[1]]
                    ];
                @endphp
                <tr class="bg-red-100 font-bold">
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">TOTAL INCOME</td>
                    <td class="border-2 border-gray-400 px-2 py-2 text-center text-sm"></td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm">{{ number_format($totalIncome[$comparisonYears[0]], 2) }}</td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm">{{ number_format($totalIncome[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Blank Row --}}
                <tr>
                    <td colspan="4" class="border-l border-r border-gray-400 py-1">&nbsp;</td>
                </tr>

                {{-- EXPENSES SECTION --}}
                <tr>
                    <td colspan="4" class="border border-gray-400 px-4 py-2 font-bold text-sm bg-red-50 text-red-900">OPERATING EXPENSES</td>
                </tr>

                {{-- Categorize Expenses --}}
                @php
                    $administrativeExpenses = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    $personnelExpenses = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    $operatingExpenses = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    
                    // Categorize expense items
                    foreach($expenseData as $expense) {
                        $accountName = strtolower($expense['account_name']);
                        
                        // Check if it's personnel/staff expenses
                        if (str_contains($accountName, 'salary') || str_contains($accountName, 'wage') || 
                            str_contains($accountName, 'staff') || str_contains($accountName, 'employee') ||
                            str_contains($accountName, 'payroll') || str_contains($accountName, 'utumishi')) {
                            $personnelExpenses[$comparisonYears[0]] += $expense['years'][$comparisonYears[0]] ?? 0;
                            $personnelExpenses[$comparisonYears[1]] += $expense['years'][$comparisonYears[1]] ?? 0;
                        }
                        // Check if it's administrative expenses
                        elseif (str_contains($accountName, 'admin') || str_contains($accountName, 'office') || 
                                str_contains($accountName, 'rent') || str_contains($accountName, 'utility') ||
                                str_contains($accountName, 'utawala')) {
                            $administrativeExpenses[$comparisonYears[0]] += $expense['years'][$comparisonYears[0]] ?? 0;
                            $administrativeExpenses[$comparisonYears[1]] += $expense['years'][$comparisonYears[1]] ?? 0;
                        }
                        // Everything else goes to operating expenses
                        else {
                            $operatingExpenses[$comparisonYears[0]] += $expense['years'][$comparisonYears[0]] ?? 0;
                            $operatingExpenses[$comparisonYears[1]] += $expense['years'][$comparisonYears[1]] ?? 0;
                        }
                    }
                    
                    $totalExpenses = [
                        $comparisonYears[0] => $administrativeExpenses[$comparisonYears[0]] + $personnelExpenses[$comparisonYears[0]] + $operatingExpenses[$comparisonYears[0]],
                        $comparisonYears[1] => $administrativeExpenses[$comparisonYears[1]] + $personnelExpenses[$comparisonYears[1]] + $operatingExpenses[$comparisonYears[1]]
                    ];
                @endphp

                {{-- Administrative Expenses --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Administrative Expenses</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Administrative Expenses')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($administrativeExpenses[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($administrativeExpenses[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Personnel Expenses --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Personnel Expenses</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Personnel Expenses')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($personnelExpenses[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($personnelExpenses[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Operating Expenses --}}
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Operating Expenses</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Operating Expenses')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($operatingExpenses[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($operatingExpenses[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Total Expenses Row --}}
                <tr class="bg-red-100 font-bold">
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">TOTAL OPERATING EXPENSES</td>
                    <td class="border-2 border-gray-400 px-2 py-2 text-center text-sm"></td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm">{{ number_format($totalExpenses[$comparisonYears[0]], 2) }}</td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm">{{ number_format($totalExpenses[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- Blank Row --}}
                <tr>
                    <td colspan="4" class="border-l border-r border-gray-400 py-1">&nbsp;</td>
                </tr>

                {{-- PROFIT BEFORE TAX --}}
                @php
                    $profitBeforeTax = [
                        $comparisonYears[0] => $totalIncome[$comparisonYears[0]] - $totalExpenses[$comparisonYears[0]],
                        $comparisonYears[1] => $totalIncome[$comparisonYears[1]] - $totalExpenses[$comparisonYears[1]]
                    ];
                @endphp
                <tr class="bg-yellow-50 font-bold">
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">SURPLUS/(DEFICIT) BEFORE TAX</td>
                    <td class="border-2 border-gray-400 px-2 py-2 text-center text-sm"></td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm {{ $profitBeforeTax[$comparisonYears[0]] < 0 ? 'text-red-600' : '' }}">
                        {{ number_format($profitBeforeTax[$comparisonYears[0]], 2) }}
                    </td>
                    <td class="border-2 border-gray-400 px-4 py-2 text-right text-sm {{ $profitBeforeTax[$comparisonYears[1]] < 0 ? 'text-red-600' : '' }}">
                        {{ number_format($profitBeforeTax[$comparisonYears[1]], 2) }}
                    </td>
                </tr>

                {{-- TAX EXPENSE --}}
                @php
                    $taxRate = 0.30; // 30% tax rate
                    $taxExpense = [
                        $comparisonYears[0] => max(0, $profitBeforeTax[$comparisonYears[0]] * $taxRate),
                        $comparisonYears[1] => max(0, $profitBeforeTax[$comparisonYears[1]] * $taxRate)
                    ];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">Less: Tax Expense (30%)</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm">
                        <button 
                            wire:click="showNote({{ $noteNumber }}, 'Tax Expense')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $noteNumber++ }}
                        </button>
                    </td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($taxExpense[$comparisonYears[0]], 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($taxExpense[$comparisonYears[1]], 2) }}</td>
                </tr>

                {{-- NET PROFIT --}}
                @php
                    $netProfit = [
                        $comparisonYears[0] => $profitBeforeTax[$comparisonYears[0]] - $taxExpense[$comparisonYears[0]],
                        $comparisonYears[1] => $profitBeforeTax[$comparisonYears[1]] - $taxExpense[$comparisonYears[1]]
                    ];
                @endphp
                <tr class="bg-green-100 font-bold text-lg">
                    <td class="border-2 border-gray-500 px-4 py-3 text-sm">NET SURPLUS/(DEFICIT) FOR THE YEAR</td>
                    <td class="border-2 border-gray-500 px-2 py-3 text-center text-sm"></td>
                    <td class="border-2 border-gray-500 px-4 py-3 text-right text-sm {{ $netProfit[$comparisonYears[0]] < 0 ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($netProfit[$comparisonYears[0]], 2) }}
                    </td>
                    <td class="border-2 border-gray-500 px-4 py-3 text-right text-sm {{ $netProfit[$comparisonYears[1]] < 0 ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($netProfit[$comparisonYears[1]], 2) }}
                    </td>
                </tr>
            </table>

            {{-- Footer Note --}}
            <div class="border border-t-0 border-gray-400 px-4 py-3 bg-gray-50">
                <p class="text-xs text-gray-600">
                    <strong>Notes: </strong> The accompanying notes form an integral part of these financial statements.
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Generated on {{ now()->format('F d, Y H:i:s') }} | MFI Core System
                </p>
            </div>
        </div>
    </div>

    {{-- Note Modal using Livewire --}}
    @if($showNoteModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[80vh] overflow-hidden">
            <div class="bg-gradient-to-r from-red-900 to-red-800 text-white px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-bold">{{ $noteTitle }}</h3>
                <button wire:click="closeNoteModal" class="text-white hover:bg-white/20 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                {{-- Description --}}
                @if(isset($noteContent['description']))
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Description</h4>
                    <p class="text-gray-600 text-sm">{{ $noteContent['description'] }}</p>
                </div>
                @endif
                
                {{-- Accounting Policy --}}
                @if(isset($noteContent['policy']))
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Accounting Policy</h4>
                    <p class="text-gray-600 text-sm">{{ $noteContent['policy'] }}</p>
                </div>
                @endif
                
                {{-- Recognition Criteria --}}
                @if(isset($noteContent['recognition']))
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Recognition Criteria</h4>
                    <p class="text-gray-600 text-sm">{{ $noteContent['recognition'] }}</p>
                </div>
                @endif
                
                {{-- Calculation Method --}}
                @if(isset($noteContent['calculation']))
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Calculation Method</h4>
                    <p class="text-gray-600 text-sm">{{ $noteContent['calculation'] }}</p>
                </div>
                @endif
                
                {{-- Components --}}
                @if(isset($noteContent['components']) && count($noteContent['components']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Components Include</h4>
                    <ul class="list-disc list-inside text-gray-600 text-sm space-y-1">
                        @foreach($noteContent['components'] as $component)
                        <li>{{ $component }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                {{-- Tax Adjustments --}}
                @if(isset($noteContent['adjustments']) && count($noteContent['adjustments']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Tax Computation Adjustments</h4>
                    <ul class="list-disc list-inside text-gray-600 text-sm space-y-1">
                        @foreach($noteContent['adjustments'] as $adjustment)
                        <li>{{ $adjustment }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                {{-- Effective Tax Rate --}}
                @if(isset($noteContent['effective_rate']))
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Effective Tax Rate</h4>
                    <p class="text-gray-600 text-sm">{{ $noteContent['effective_rate'] }}</p>
                </div>
                @endif
                
                {{-- Detailed Breakdown --}}
                @if(isset($noteContent['breakdown']) && count($noteContent['breakdown']) > 0)
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">Detailed Breakdown</h4>
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-3 py-2 text-left text-sm">Account</th>
                                <th class="border border-gray-300 px-3 py-2 text-right text-sm">{{ $comparisonYears[0] }}</th>
                                <th class="border border-gray-300 px-3 py-2 text-right text-sm">{{ $comparisonYears[1] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($noteContent['breakdown'] as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-3 py-1 text-sm">{{ $item['account_name'] }}</td>
                                <td class="border border-gray-300 px-3 py-1 text-right text-sm">
                                    {{ number_format($item['years'][$comparisonYears[0]] ?? 0, 2) }}
                                </td>
                                <td class="border border-gray-300 px-3 py-1 text-right text-sm">
                                    {{ number_format($item['years'][$comparisonYears[1]] ?? 0, 2) }}
                                </td>
                            </tr>
                            @endforeach
                            @if(isset($noteContent['totals']))
                            <tr class="bg-gray-100 font-semibold">
                                <td class="border border-gray-400 px-3 py-2 text-sm">Total</td>
                                <td class="border border-gray-400 px-3 py-2 text-right text-sm">
                                    {{ number_format($noteContent['totals'][$comparisonYears[0]] ?? 0, 2) }}
                                </td>
                                <td class="border border-gray-400 px-3 py-2 text-right text-sm">
                                    {{ number_format($noteContent['totals'][$comparisonYears[1]] ?? 0, 2) }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            
            {{-- Modal Footer with Close Button --}}
            <div class="border-t border-gray-200 px-6 py-3 bg-gray-50 flex justify-end">
                <button wire:click="closeNoteModal" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg">
        <div class="flex items-center">
            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        </div>
    </div>
</div>

{{-- Excel-like print styles --}}
<style nonce="{{ csp_nonce() }}">
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        .no-print {
            display: none !important;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        td, th {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
    }
    
    /* Excel-like cell selection effect */
    td:hover {
        outline: 2px solid #0066cc;
        outline-offset: -1px;
    }
</style>