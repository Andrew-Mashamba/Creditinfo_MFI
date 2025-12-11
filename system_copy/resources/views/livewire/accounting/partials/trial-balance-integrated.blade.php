<div>
    {{-- Data Validation --}}
    @php
        $hasData = isset($trialBalanceData) && !empty($trialBalanceData['accounts']);
        $reportDate = isset($trialBalanceData['report_date']) 
            ? \Carbon\Carbon::parse($trialBalanceData['report_date'])->format('d F Y')
            : '31 December ' . ($selectedYear ?? date('Y'));
        
        // Calculate sub-totals
        $assetDebitTotal = 0;
        $assetCreditTotal = 0;
        $liabilityDebitTotal = 0;
        $liabilityCreditTotal = 0;
        $equityDebitTotal = 0;
        $equityCreditTotal = 0;
        $revenueDebitTotal = 0;
        $revenueCreditTotal = 0;
        $expenseDebitTotal = 0;
        $expenseCreditTotal = 0;
        
        if ($hasData) {
            foreach($trialBalanceData['accounts']['assets'] ?? [] as $account) {
                if ($account['balance'] > 0) {
                    $assetDebitTotal += $account['balance'];
                } else {
                    $assetCreditTotal += abs($account['balance']);
                }
            }
            foreach($trialBalanceData['accounts']['liabilities'] ?? [] as $account) {
                if ($account['balance'] < 0) {
                    $liabilityDebitTotal += abs($account['balance']);
                } else {
                    $liabilityCreditTotal += $account['balance'];
                }
            }
            foreach($trialBalanceData['accounts']['equity'] ?? [] as $account) {
                if ($account['balance'] < 0) {
                    $equityDebitTotal += abs($account['balance']);
                } else {
                    $equityCreditTotal += $account['balance'];
                }
            }
            foreach($trialBalanceData['accounts']['revenue'] ?? [] as $account) {
                if ($account['balance'] < 0) {
                    $revenueDebitTotal += abs($account['balance']);
                } else {
                    $revenueCreditTotal += $account['balance'];
                }
            }
            foreach($trialBalanceData['accounts']['expenses'] ?? [] as $account) {
                if ($account['balance'] > 0) {
                    $expenseDebitTotal += $account['balance'];
                } else {
                    $expenseCreditTotal += abs($account['balance']);
                }
            }
        }
    @endphp

    @if(!$hasData)
        <div class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Trial Balance Data</h3>
            <p class="mt-1 text-sm text-gray-500">No accounts data available for the selected period.</p>
        </div>
    @else
        {{-- Export Buttons --}}
        <div class="mb-4 flex justify-end space-x-2">
            <button wire:click="exportTrialBalancePDF" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </button>
            <button wire:click="exportTrialBalanceExcel" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>

        {{-- Statement Header --}}
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 text-center">TRIAL BALANCE</h2>
            <p class="text-sm text-gray-600 text-center">As at {{ $reportDate }}</p>
            @if(isset($trialBalanceData['period']))
                <p class="text-xs text-gray-500 text-center">Period: {{ $trialBalanceData['period'] }}</p>
            @endif
        </div>

        {{-- Main Trial Balance Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-900 text-white">
                        <th class="border border-gray-300 px-3 py-2 text-left">Account Code</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Account Name</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Account Type</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Opening Balance</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Debit Balance</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Credit Balance</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Asset Accounts --}}
                    <tr class="bg-blue-100">
                        <td colspan="6" class="border border-gray-300 px-3 py-1 font-semibold">ASSETS</td>
                    </tr>
                    @forelse($trialBalanceData['accounts']['assets'] ?? [] as $account)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-yellow-50 cursor-pointer" 
                        onclick="Livewire.emit('showAccountDetails', '{{ $account['code'] ?? '' }}')">
                        <td class="border border-gray-300 px-3 py-1">{{ $account['code'] ?? '' }}</td>
                        <td class="border border-gray-300 px-3 py-1 {{ isset($account['is_sub_account']) && $account['is_sub_account'] ? 'pl-8' : 'pl-4' }}">
                            {{ $account['name'] ?? '' }}
                            @if(isset($account['has_sub_accounts']) && $account['has_sub_accounts'])
                                <span class="text-gray-400 text-xs">({{ $account['sub_account_count'] ?? 0 }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-3 py-1">Asset</td>
                        <td class="border border-gray-300 px-3 py-1 text-right text-gray-600">
                            {{ isset($account['opening_balance']) ? $this->formatNumber($account['opening_balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Assets have normal debit balance --}}
                            {{ $account['balance'] > 0 ? $this->formatNumber($account['balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Show in credit column if negative (abnormal balance) --}}
                            {{ $account['balance'] < 0 ? $this->formatNumber(abs($account['balance'])) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-1 text-center text-gray-500">No asset accounts</td>
                    </tr>
                    @endforelse
                    {{-- Asset Sub-total --}}
                    <tr class="bg-blue-50 font-semibold">
                        <td colspan="3" class="border border-gray-300 px-3 py-1 text-right">Sub-total Assets:</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($assetDebitTotal) }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($assetCreditTotal) }}</td>
                    </tr>

                    {{-- Liability Accounts --}}
                    <tr class="bg-red-100 mt-2">
                        <td colspan="6" class="border border-gray-300 px-3 py-1 font-semibold">LIABILITIES</td>
                    </tr>
                    @forelse($trialBalanceData['accounts']['liabilities'] ?? [] as $account)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-yellow-50 cursor-pointer"
                        onclick="Livewire.emit('showAccountDetails', '{{ $account['code'] ?? '' }}')">
                        <td class="border border-gray-300 px-3 py-1">{{ $account['code'] ?? '' }}</td>
                        <td class="border border-gray-300 px-3 py-1 {{ isset($account['is_sub_account']) && $account['is_sub_account'] ? 'pl-8' : 'pl-4' }}">
                            {{ $account['name'] ?? '' }}
                            @if(isset($account['has_sub_accounts']) && $account['has_sub_accounts'])
                                <span class="text-gray-400 text-xs">({{ $account['sub_account_count'] ?? 0 }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-3 py-1">Liability</td>
                        <td class="border border-gray-300 px-3 py-1 text-right text-gray-600">
                            {{ isset($account['opening_balance']) ? $this->formatNumber($account['opening_balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Liabilities have normal credit balance, show in debit if negative (abnormal) --}}
                            {{ $account['balance'] < 0 ? $this->formatNumber(abs($account['balance'])) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Show in credit column if positive (normal balance) --}}
                            {{ $account['balance'] > 0 ? $this->formatNumber($account['balance']) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-1 text-center text-gray-500">No liability accounts</td>
                    </tr>
                    @endforelse
                    {{-- Liability Sub-total --}}
                    <tr class="bg-red-50 font-semibold">
                        <td colspan="3" class="border border-gray-300 px-3 py-1 text-right">Sub-total Liabilities:</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($liabilityDebitTotal) }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($liabilityCreditTotal) }}</td>
                    </tr>

                    {{-- Equity Accounts --}}
                    <tr class="bg-green-100 mt-2">
                        <td colspan="6" class="border border-gray-300 px-3 py-1 font-semibold">EQUITY</td>
                    </tr>
                    @forelse($trialBalanceData['accounts']['equity'] ?? [] as $account)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-yellow-50 cursor-pointer"
                        onclick="Livewire.emit('showAccountDetails', '{{ $account['code'] ?? '' }}')">
                        <td class="border border-gray-300 px-3 py-1">{{ $account['code'] ?? '' }}</td>
                        <td class="border border-gray-300 px-3 py-1 {{ isset($account['is_sub_account']) && $account['is_sub_account'] ? 'pl-8' : 'pl-4' }}">
                            {{ $account['name'] ?? '' }}
                            @if(isset($account['has_sub_accounts']) && $account['has_sub_accounts'])
                                <span class="text-gray-400 text-xs">({{ $account['sub_account_count'] ?? 0 }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-3 py-1">Equity</td>
                        <td class="border border-gray-300 px-3 py-1 text-right text-gray-600">
                            {{ isset($account['opening_balance']) ? $this->formatNumber($account['opening_balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Equity has normal credit balance, show in debit if negative (abnormal) --}}
                            {{ $account['balance'] < 0 ? $this->formatNumber(abs($account['balance'])) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Show in credit column if positive (normal balance) --}}
                            {{ $account['balance'] > 0 ? $this->formatNumber($account['balance']) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-1 text-center text-gray-500">No equity accounts</td>
                    </tr>
                    @endforelse
                    {{-- Equity Sub-total --}}
                    <tr class="bg-green-50 font-semibold">
                        <td colspan="3" class="border border-gray-300 px-3 py-1 text-right">Sub-total Equity:</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($equityDebitTotal) }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($equityCreditTotal) }}</td>
                    </tr>

                    {{-- Revenue Accounts --}}
                    <tr class="bg-yellow-100 mt-2">
                        <td colspan="6" class="border border-gray-300 px-3 py-1 font-semibold">REVENUE</td>
                    </tr>
                    @forelse($trialBalanceData['accounts']['revenue'] ?? [] as $account)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-yellow-50 cursor-pointer"
                        onclick="Livewire.emit('showAccountDetails', '{{ $account['code'] ?? '' }}')">
                        <td class="border border-gray-300 px-3 py-1">{{ $account['code'] ?? '' }}</td>
                        <td class="border border-gray-300 px-3 py-1 {{ isset($account['is_sub_account']) && $account['is_sub_account'] ? 'pl-8' : 'pl-4' }}">
                            {{ $account['name'] ?? '' }}
                            @if(isset($account['has_sub_accounts']) && $account['has_sub_accounts'])
                                <span class="text-gray-400 text-xs">({{ $account['sub_account_count'] ?? 0 }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-3 py-1">Revenue</td>
                        <td class="border border-gray-300 px-3 py-1 text-right text-gray-600">
                            {{ isset($account['opening_balance']) ? $this->formatNumber($account['opening_balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Revenue has normal credit balance, show in debit if negative (abnormal) --}}
                            {{ $account['balance'] < 0 ? $this->formatNumber(abs($account['balance'])) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Show in credit column if positive (normal balance) --}}
                            {{ $account['balance'] > 0 ? $this->formatNumber($account['balance']) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-1 text-center text-gray-500">No revenue accounts</td>
                    </tr>
                    @endforelse
                    {{-- Revenue Sub-total --}}
                    <tr class="bg-yellow-50 font-semibold">
                        <td colspan="3" class="border border-gray-300 px-3 py-1 text-right">Sub-total Revenue:</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($revenueDebitTotal) }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($revenueCreditTotal) }}</td>
                    </tr>

                    {{-- Expense Accounts --}}
                    <tr class="bg-orange-100 mt-2">
                        <td colspan="6" class="border border-gray-300 px-3 py-1 font-semibold">EXPENSES</td>
                    </tr>
                    @forelse($trialBalanceData['accounts']['expenses'] ?? [] as $account)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} hover:bg-yellow-50 cursor-pointer"
                        onclick="Livewire.emit('showAccountDetails', '{{ $account['code'] ?? '' }}')">
                        <td class="border border-gray-300 px-3 py-1">{{ $account['code'] ?? '' }}</td>
                        <td class="border border-gray-300 px-3 py-1 {{ isset($account['is_sub_account']) && $account['is_sub_account'] ? 'pl-8' : 'pl-4' }}">
                            {{ $account['name'] ?? '' }}
                            @if(isset($account['has_sub_accounts']) && $account['has_sub_accounts'])
                                <span class="text-gray-400 text-xs">({{ $account['sub_account_count'] ?? 0 }})</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-3 py-1">Expense</td>
                        <td class="border border-gray-300 px-3 py-1 text-right text-gray-600">
                            {{ isset($account['opening_balance']) ? $this->formatNumber($account['opening_balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Expenses have normal debit balance --}}
                            {{ $account['balance'] > 0 ? $this->formatNumber($account['balance']) : '-' }}
                        </td>
                        <td class="border border-gray-300 px-3 py-1 text-right">
                            {{-- Show in credit column if negative (abnormal balance) --}}
                            {{ $account['balance'] < 0 ? $this->formatNumber(abs($account['balance'])) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-1 text-center text-gray-500">No expense accounts</td>
                    </tr>
                    @endforelse
                    {{-- Expense Sub-total --}}
                    <tr class="bg-orange-50 font-semibold">
                        <td colspan="3" class="border border-gray-300 px-3 py-1 text-right">Sub-total Expenses:</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($expenseDebitTotal) }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-right">{{ $this->formatNumber($expenseCreditTotal) }}</td>
                    </tr>

                    {{-- Grand Totals Row --}}
                    <tr class="font-bold bg-gray-900 text-white">
                        <td colspan="3" class="border border-gray-300 px-3 py-2">GRAND TOTAL</td>
                        <td class="border border-gray-300 px-3 py-2 text-right">-</td>
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($trialBalanceData['totals']['debit'] ?? 0) }}
                        </td>
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            {{ $this->formatNumber($trialBalanceData['totals']['credit'] ?? 0) }}
                        </td>
                    </tr>
            </tbody>
        </table>
    </div>

        {{-- Balance Check --}}
        <div class="mt-4 p-3 {{ $trialBalanceData['is_balanced'] ?? false ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs {{ $trialBalanceData['is_balanced'] ?? false ? 'text-green-700' : 'text-red-700' }} font-semibold">
                        Trial Balance Status: {{ $trialBalanceData['is_balanced'] ?? false ? 'BALANCED' : 'NOT BALANCED' }}
                    </p>
                    <p class="text-xs {{ $trialBalanceData['is_balanced'] ?? false ? 'text-green-600' : 'text-red-600' }} mt-1">
                        Total Debits: {{ $this->formatNumber($trialBalanceData['totals']['debit'] ?? 0) }} | 
                        Total Credits: {{ $this->formatNumber($trialBalanceData['totals']['credit'] ?? 0) }}
                        @if(!($trialBalanceData['is_balanced'] ?? false))
                            | Difference: {{ $this->formatNumber(abs(($trialBalanceData['totals']['debit'] ?? 0) - ($trialBalanceData['totals']['credit'] ?? 0))) }}
                        @endif
                    </p>
                </div>
                <div>
                    @if($trialBalanceData['is_balanced'] ?? false)
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

        {{-- Relationship Note --}}
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-700">
                <strong>Note:</strong> The Trial Balance serves as the foundation for all financial statements. 
                Account balances are used to prepare the Income Statement, Balance Sheet, and other financial reports.
                The trial balance must be balanced (Total Debits = Total Credits) before financial statements can be accurately prepared.
                Click on any account row to view detailed transaction history.
            </p>
        </div>

        {{-- Account Summary --}}
        <div class="mt-4 bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Account Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-xs">
                <div class="text-center">
                    <p class="text-gray-600">Asset Accounts</p>
                    <p class="font-bold text-blue-900">{{ count($trialBalanceData['accounts']['assets'] ?? []) }}</p>
                    <p class="text-gray-500 text-xs">
                        D: {{ $this->formatNumber($assetDebitTotal) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600">Liability Accounts</p>
                    <p class="font-bold text-red-900">{{ count($trialBalanceData['accounts']['liabilities'] ?? []) }}</p>
                    <p class="text-gray-500 text-xs">
                        C: {{ $this->formatNumber($liabilityCreditTotal) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600">Equity Accounts</p>
                    <p class="font-bold text-green-900">{{ count($trialBalanceData['accounts']['equity'] ?? []) }}</p>
                    <p class="text-gray-500 text-xs">
                        C: {{ $this->formatNumber($equityCreditTotal) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600">Revenue Accounts</p>
                    <p class="font-bold text-yellow-900">{{ count($trialBalanceData['accounts']['revenue'] ?? []) }}</p>
                    <p class="text-gray-500 text-xs">
                        C: {{ $this->formatNumber($revenueCreditTotal) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600">Expense Accounts</p>
                    <p class="font-bold text-orange-900">{{ count($trialBalanceData['accounts']['expenses'] ?? []) }}</p>
                    <p class="text-gray-500 text-xs">
                        D: {{ $this->formatNumber($expenseDebitTotal) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Account Details Modal Placeholder --}}
        @if(isset($showAccountModal) && $showAccountModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
                    <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Account Details: {{ $selectedAccountCode ?? '' }}</h3>
                                <button wire:click="closeAccountModal" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="mt-2">
                                {{-- Account transaction details would go here --}}
                                <p class="text-sm text-gray-500">Loading transaction details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>