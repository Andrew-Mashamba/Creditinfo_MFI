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
                <button onclick="toggleIncomeView()" class="text-sm text-red-600 hover:text-red-800 underline">
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
                        <div class="font-bold text-xl text-gray-900">INCOME STATEMENT</div>
                        <div class="text-sm text-gray-600 mt-1">For the Year Ended December 31, {{ $this->selectedYear ?? date('Y') }}</div>
                    </td>
                </tr>

                {{-- Header Row --}}
                <tr class="bg-gray-100">
                    <td class="border border-gray-400 px-4 py-2 font-bold text-sm text-gray-800" style="width: 50%;">PARTICULARS</td>
                    <td class="border border-gray-400 px-2 py-2 text-center font-bold text-sm text-gray-800" style="width: 10%;">Note</td>
                    <td class="border border-gray-400 px-4 py-2 text-right font-bold text-sm text-gray-800" style="width: 20%;">{{ $comparisonYears[0] ?? date('Y') }}</td>
                    <td class="border border-gray-400 px-4 py-2 text-right font-bold text-sm text-gray-800" style="width: 20%;">{{ $comparisonYears[1] ?? date('Y')-1 }}</td>
                </tr>

                {{-- INCOME SECTION --}}
                <tr>
                    <td colspan="4" class="border border-gray-400 px-4 py-2 font-bold text-sm bg-red-50 text-red-900">INCOME</td>
                </tr>

                {{-- Income Items --}}
                @php
                    $totalIncome = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                    $noteNumber = 1;
                @endphp

                @forelse($incomeData as $income)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">{{ $income['account_name'] }}</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm text-gray-500">{{ $noteNumber++ }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($income['years'][$comparisonYears[0]] ?? 0, 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($income['years'][$comparisonYears[1]] ?? 0, 2) }}</td>
                </tr>
                @php
                    $totalIncome[$comparisonYears[0]] += $income['years'][$comparisonYears[0]] ?? 0;
                    $totalIncome[$comparisonYears[1]] += $income['years'][$comparisonYears[1]] ?? 0;
                @endphp
                @empty
                <tr>
                    <td colspan="4" class="border border-gray-400 px-4 py-2 text-center text-sm text-gray-500">No income records found</td>
                </tr>
                @endforelse

                {{-- Total Income Row --}}
                <tr class="bg-red-100 font-bold">
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">Total Income</td>
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
                    <td colspan="4" class="border border-gray-400 px-4 py-2 font-bold text-sm bg-red-50 text-red-900">EXPENSES</td>
                </tr>

                {{-- Expense Items --}}
                @php
                    $totalExpenses = [
                        $comparisonYears[0] => 0,
                        $comparisonYears[1] => 0
                    ];
                @endphp

                @forelse($expenseData as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-400 px-4 py-1.5 text-sm">{{ $expense['account_name'] }}</td>
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm text-gray-500">{{ $noteNumber++ }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($expense['years'][$comparisonYears[0]] ?? 0, 2) }}</td>
                    <td class="border border-gray-400 px-4 py-1.5 text-right text-sm">{{ number_format($expense['years'][$comparisonYears[1]] ?? 0, 2) }}</td>
                </tr>
                @php
                    $totalExpenses[$comparisonYears[0]] += $expense['years'][$comparisonYears[0]] ?? 0;
                    $totalExpenses[$comparisonYears[1]] += $expense['years'][$comparisonYears[1]] ?? 0;
                @endphp
                @empty
                <tr>
                    <td colspan="4" class="border border-gray-400 px-4 py-2 text-center text-sm text-gray-500">No expense records found</td>
                </tr>
                @endforelse

                {{-- Total Expenses Row --}}
                <tr class="bg-red-100 font-bold">
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">Total Expenses</td>
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
                    <td class="border-2 border-gray-400 px-4 py-2 text-sm">Profit Before Tax</td>
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
                    <td class="border border-gray-400 px-2 py-1.5 text-center text-sm text-gray-500">{{ $noteNumber++ }}</td>
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
                    <td class="border-2 border-gray-500 px-4 py-3 text-sm">NET PROFIT FOR THE YEAR</td>
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
                    <strong>Notes:</strong> The accompanying notes form an integral part of these financial statements.
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Generated on {{ now()->format('F d, Y H:i:s') }} | MFI Core System
                </p>
            </div>
        </div>
    </div>

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

<script nonce="{{ csp_nonce() }}">
function toggleIncomeView() {
    // This would typically make an AJAX call to update the session
    fetch('/toggle-income-view', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ view: 'detailed' })
    }).then(() => {
        window.location.reload();
    });
}
</script>