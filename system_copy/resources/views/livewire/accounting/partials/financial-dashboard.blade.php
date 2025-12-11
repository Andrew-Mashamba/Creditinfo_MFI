<div>
    {{-- Dashboard Overview --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Financial Overview - {{ $selectedYear }}</h2>
        
        {{-- Key Metrics Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Assets --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Total Assets</p>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ $this->formatNumber($balanceSheetData['total_assets'] ?? 0) }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-200 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Liabilities --}}
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600">Total Liabilities</p>
                        <p class="text-2xl font-bold text-red-900">
                            {{ $this->formatNumber($balanceSheetData['liabilities']['total'] ?? 0) }}
                        </p>
                    </div>
                    <div class="p-3 bg-red-200 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Equity --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600">Total Equity</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ $this->formatNumber($balanceSheetData['equity']['total'] ?? 0) }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-200 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Net Income --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600">Net Income</p>
                        <p class="text-2xl font-bold {{ ($incomeStatementData['net_income'] ?? 0) >= 0 ? 'text-green-900' : 'text-red-900' }}">
                            {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-200 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Balance Sheet Equation Check --}}
        <div class="bg-white rounded-lg border {{ $balanceSheetData['is_balanced'] ?? false ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }} p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold {{ $balanceSheetData['is_balanced'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                        Accounting Equation Status
                    </h3>
                    <p class="text-xs {{ $balanceSheetData['is_balanced'] ?? false ? 'text-green-600' : 'text-red-600' }} mt-1">
                        Assets ({{ $this->formatNumber($balanceSheetData['total_assets'] ?? 0) }}) = 
                        Liabilities ({{ $this->formatNumber($balanceSheetData['liabilities']['total'] ?? 0) }}) + 
                        Equity ({{ $this->formatNumber($balanceSheetData['equity']['total'] ?? 0) }})
                    </p>
                </div>
                <div>
                    @if($balanceSheetData['is_balanced'] ?? false)
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>

        {{-- Statement Relationships --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Statement Relationships</h3>
            
            <div class="space-y-4">
                {{-- Income Statement to Equity --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">Income Statement → Statement of Changes in Equity</span>
                    </div>
                    <div class="text-sm font-medium text-gray-900">
                        Net Income: {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }}
                    </div>
                </div>

                {{-- Equity to Balance Sheet --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">Statement of Changes in Equity → Balance Sheet</span>
                    </div>
                    <div class="text-sm font-medium text-gray-900">
                        Total Equity: {{ $this->formatNumber($equityStatementData['total_equity'] ?? 0) }}
                    </div>
                </div>

                {{-- Cash Flow to Balance Sheet --}}
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">Cash Flow Statement → Balance Sheet</span>
                    </div>
                    <div class="text-sm font-medium text-gray-900">
                        Ending Cash: {{ $this->formatNumber($cashFlowData['ending_cash'] ?? 0) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Summary Table --}}
        <div class="mt-6 bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Financial Summary</h3>
            </div>
            <div class="p-6">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-2 text-gray-600">Total Revenue</td>
                            <td class="py-2 text-right font-medium">{{ $this->formatNumber($incomeStatementData['total_revenue'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Total Expenses</td>
                            <td class="py-2 text-right font-medium">{{ $this->formatNumber($incomeStatementData['total_expenses'] ?? 0) }}</td>
                        </tr>
                        <tr class="font-semibold">
                            <td class="py-2 text-gray-900">Net Income</td>
                            <td class="py-2 text-right {{ ($incomeStatementData['net_income'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $this->formatNumber($incomeStatementData['net_income'] ?? 0) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Operating Cash Flow</td>
                            <td class="py-2 text-right font-medium">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['operating'] ?? [], 'amount'))) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Investing Cash Flow</td>
                            <td class="py-2 text-right font-medium">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['investing'] ?? [], 'amount'))) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-gray-600">Financing Cash Flow</td>
                            <td class="py-2 text-right font-medium">
                                {{ $this->formatNumber(array_sum(array_column($cashFlowData['financing'] ?? [], 'amount'))) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>