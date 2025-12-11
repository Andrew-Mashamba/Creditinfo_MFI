<div>
    {{-- Statement Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 text-center">STATEMENT OF COMPREHENSIVE INCOME X</h2>
        <p class="text-sm text-gray-600 text-center">For the year ended 31 December {{ $selectedYear }}</p>
    </div>

    {{-- Main Statement Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse border border-gray-300">
            {{-- REVENUE Section --}}
            <thead>
                <tr class="bg-red-600 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold">REVENUE</th>
                    @if($showNotes)
                    <th class="border border-gray-300 px-3 py-2 text-center">Note</th>
                    @endif
                    <th class="border border-gray-300 px-3 py-2 text-right">{{ $selectedYear }}</th>
                    @if($showComparison)
                        <th class="border border-gray-300 px-3 py-2 text-right">{{ $selectedYear - 1 }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($incomeStatementData['revenue'] ?? [] as $index => $revenue)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-4">{{ $revenue['account_name'] ?? '' }}</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-1 text-center">
                        <button 
                            wire:click="showIncomeNote({{ $index + 20 }}, '{{ $revenue['account_number'] ?? '' }}', '{{ $revenue['account_name'] ?? '' }}')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $index + 20 }}
                        </button>
                    </td>
                    @endif
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber($revenue['amount'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Revenue --}}
                <tr class="font-semibold bg-red-100">
                    <td class="border border-gray-300 px-3 py-2">TOTAL REVENUE</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                    @endif
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($incomeStatementData['total_revenue'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>

                {{-- EXPENSES Section --}}
                <tr class="bg-red-600 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold">EXPENSES</th>
                    @if($showNotes)
                    <th class="border border-gray-300 px-3 py-2 text-center">Note</th>
                    @endif
                    <th class="border border-gray-300 px-3 py-2 text-right"></th>
                    @if($showComparison)
                        <th class="border border-gray-300 px-3 py-2 text-right"></th>
                    @endif
                </tr>
                
                @foreach($incomeStatementData['expenses'] ?? [] as $index => $expense)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-4">{{ $expense['account_name'] ?? '' }}</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-1 text-center">
                        <button 
                            wire:click="showIncomeNote({{ $index + 30 }}, '{{ $expense['account_number'] ?? '' }}', '{{ $expense['account_name'] ?? '' }}')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $index + 30 }}
                        </button>
                    </td>
                    @endif
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber($expense['amount'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Expenses --}}
                <tr class="font-semibold bg-red-100">
                    <td class="border border-gray-300 px-3 py-2">TOTAL EXPENSES</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                    @endif
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($incomeStatementData['total_expenses'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>

                {{-- NET INCOME --}}
                <tr class="font-bold {{ ($incomeStatementData['net_income'] ?? 0) >= 0 ? 'bg-red-200' : 'bg-red-200' }}">
                    <td class="border border-gray-300 px-3 py-2">NET INCOME (LOSS)</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-2 text-center">
                        <button 
                            wire:click="showIncomeNote(50, 'NET_INCOME', 'Net Income Analysis')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            50
                        </button>
                    </td>
                    @endif
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                
                {{-- OTHER COMPREHENSIVE INCOME Section --}}
                @if(isset($incomeStatementData['other_comprehensive_income']) && isset($incomeStatementData['other_comprehensive_income']['items']) && count($incomeStatementData['other_comprehensive_income']['items']) > 0)
                <tr class="bg-purple-900 text-white">
                    <th class="border border-gray-300 px-3 py-2 text-left font-bold">OTHER COMPREHENSIVE INCOME</th>
                    @if($showNotes)
                    <th class="border border-gray-300 px-3 py-2 text-center">Note</th>
                    @endif
                    <th class="border border-gray-300 px-3 py-2 text-right"></th>
                    @if($showComparison)
                        <th class="border border-gray-300 px-3 py-2 text-right"></th>
                    @endif
                </tr>
                
                @foreach($incomeStatementData['other_comprehensive_income']['items'] as $index => $ociItem)
                <tr>
                    <td class="border border-gray-300 px-3 py-1 pl-4">{{ $ociItem['description'] ?? '' }}</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-1 text-center">
                        <button 
                            wire:click="showIncomeNote({{ $index + 60 }}, 'OCI_{{ $index }}', '{{ $ociItem['description'] ?? '' }}')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            {{ $index + 60 }}
                        </button>
                    </td>
                    @endif
                    <td class="border border-gray-300 px-3 py-1 text-right">
                        {{ $this->formatNumber($ociItem['amount'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                @endforeach
                
                {{-- Total Other Comprehensive Income --}}
                <tr class="font-semibold bg-purple-100">
                    <td class="border border-gray-300 px-3 py-2">TOTAL OTHER COMPREHENSIVE INCOME</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-2 text-center"></td>
                    @endif
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($incomeStatementData['other_comprehensive_income']['total'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                @endif
                
                {{-- TOTAL COMPREHENSIVE INCOME --}}
                @if(isset($incomeStatementData['total_comprehensive_income']))
                <tr class="font-bold text-white {{ ($incomeStatementData['total_comprehensive_income'] ?? 0) >= 0 ? 'bg-red-700' : 'bg-red-700' }}">
                    <td class="border border-gray-300 px-3 py-2">TOTAL COMPREHENSIVE INCOME (LOSS)</td>
                    @if($showNotes)
                    <td class="border border-gray-300 px-3 py-2 text-center">
                        <button 
                            wire:click="showIncomeNote(70, 'TOTAL_COMPREHENSIVE', 'Total Comprehensive Income Analysis')"
                            class="text-red-600 hover:text-red-800 underline font-semibold cursor-pointer">
                            70
                        </button>
                    </td>
                    @endif
                    <td class="border border-gray-300 px-3 py-2 text-right">
                        {{ $this->formatNumber($incomeStatementData['total_comprehensive_income'] ?? 0) }}
                    </td>
                    @if($showComparison)
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber(0) }}
                        </td>
                    @endif
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Performance Metrics --}}
    @if(isset($incomeStatementData['total_revenue']) && $incomeStatementData['total_revenue'] > 0)
    <div class="mt-4 grid grid-cols-3 gap-4">
        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Gross Profit Margin</p>
            <p class="text-lg font-bold text-gray-900">
                {{ number_format((($incomeStatementData['total_revenue'] - $incomeStatementData['total_expenses']) / $incomeStatementData['total_revenue']) * 100, 2) }}%
            </p>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Operating Expense Ratio</p>
            <p class="text-lg font-bold text-gray-900">
                {{ number_format(($incomeStatementData['total_expenses'] / $incomeStatementData['total_revenue']) * 100, 2) }}%
            </p>
        </div>
        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
            <p class="text-xs text-gray-600 mb-1">Net Profit Margin</p>
            <p class="text-lg font-bold {{ ($incomeStatementData['net_income'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format(($incomeStatementData['net_income'] / $incomeStatementData['total_revenue']) * 100, 2) }}%
            </p>
        </div>
    </div>
    @endif

    {{-- Relationship Note --}}
    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-xs text-red-700">
            <strong>Note:</strong> Net Income of {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }} 
            flows to the Statement of Changes in Equity and increases Retained Earnings.
            @if(isset($incomeStatementData['other_comprehensive_income']) && $incomeStatementData['other_comprehensive_income']['total'] != 0)
            Other Comprehensive Income of {{ $this->formatNumber($incomeStatementData['other_comprehensive_income']['total']) }} 
            flows directly to equity reserves without affecting net income.
            @endif
        </p>
    </div>
    
    {{-- Income Statement Note Modal --}}
    @if(isset($showIncomeNoteModal) && $showIncomeNoteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Note {{ $incomeNoteNumber ?? '' }}: {{ $incomeNoteTitle ?? '' }}
                            </h3>
                            <button wire:click="closeIncomeNote" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            @if(isset($incomeNoteContent) && is_array($incomeNoteContent) && count($incomeNoteContent) > 0)
                                @if($incomeNoteTitle == 'Net Income Analysis' || $incomeNoteTitle == 'Total Comprehensive Income Analysis')
                                    {{-- Special layout for Net Income analysis --}}
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div class="bg-red-50 p-4 rounded">
                                            <h4 class="font-semibold text-green-800 mb-2">Revenue Components</h4>
                                            <div class="text-sm">
                                                <p>Total Revenue: <span class="font-medium">{{ $this->formatNumber($incomeStatementData['total_revenue'] ?? 0) }}</span></p>
                                                <p class="text-xs text-gray-600 mt-1">From {{ count($incomeStatementData['revenue'] ?? []) }} revenue accounts</p>
                                            </div>
                                        </div>
                                        <div class="bg-red-50 p-4 rounded">
                                            <h4 class="font-semibold text-red-800 mb-2">Expense Components</h4>
                                            <div class="text-sm">
                                                <p>Total Expenses: <span class="font-medium">{{ $this->formatNumber($incomeStatementData['total_expenses'] ?? 0) }}</span></p>
                                                <p class="text-xs text-gray-600 mt-1">From {{ count($incomeStatementData['expenses'] ?? []) }} expense accounts</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded">
                                        <h4 class="font-semibold text-gray-800 mb-2">Net Income Calculation</h4>
                                        <div class="text-sm space-y-1">
                                            <div class="flex justify-between">
                                                <span>Total Revenue</span>
                                                <span>{{ $this->formatNumber($incomeStatementData['total_revenue'] ?? 0) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Less: Total Expenses</span>
                                                <span>({{ $this->formatNumber($incomeStatementData['total_expenses'] ?? 0) }})</span>
                                            </div>
                                            <hr class="my-1">
                                            <div class="flex justify-between font-bold">
                                                <span>Net Income (Loss)</span>
                                                <span class="{{ ($incomeStatementData['net_income'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }}
                                                </span>
                                            </div>
                                            @if(isset($incomeStatementData['other_comprehensive_income']['total']) && $incomeStatementData['other_comprehensive_income']['total'] != 0)
                                            <div class="flex justify-between mt-2">
                                                <span>Other Comprehensive Income</span>
                                                <span>{{ $this->formatNumber($incomeStatementData['other_comprehensive_income']['total']) }}</span>
                                            </div>
                                            <hr class="my-1">
                                            <div class="flex justify-between font-bold">
                                                <span>Total Comprehensive Income</span>
                                                <span class="{{ ($incomeStatementData['total_comprehensive_income'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $this->formatNumber($incomeStatementData['total_comprehensive_income'] ?? 0) }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{-- Standard account details layout --}}
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Number</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($incomeNoteContent as $item)
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_number'] ?? '' }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['account_name'] ?? '' }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ number_format($item['balance'] ?? 0, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @if(count($incomeNoteContent) > 1)
                                            <tr class="font-semibold bg-gray-50">
                                                <td colspan="2" class="px-4 py-2 text-sm text-gray-900">Total</td>
                                                <td class="px-4 py-2 text-sm text-gray-900 text-right">
                                                    {{ number_format(collect($incomeNoteContent)->sum('balance'), 2) }}
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                @endif
                            @else
                                <p class="text-gray-500">No detailed information available for this item.</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeIncomeNote"
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