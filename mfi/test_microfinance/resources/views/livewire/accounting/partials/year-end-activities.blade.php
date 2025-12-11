<div class="p-6 bg-white rounded-lg shadow-lg">
    {{-- Header --}}
    <div class="mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-900">Year-End Activities Dashboard</h2>
        <p class="text-sm text-gray-600 mt-1">Manage all accounting year-end closing activities for {{ $selectedYear ?? date('Y') }}</p>
        <div class="mt-3 flex items-center space-x-4">
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                Current Year: {{ date('Y') }}
            </span>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                Closing Date: December 31, {{ $selectedYear ?? date('Y') }}
            </span>
        </div>
    </div>

    {{-- Activity Categories --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- 1. FINANCIAL CLOSING ACTIVITIES --}}
        <div class="border rounded-lg p-4 bg-red-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-red-900">Financial Closing</h3>
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Close Books of Accounts --}}
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Close Books of Accounts</p>
                            <p class="text-xs text-gray-600">Lock all transactions for the year</p>
                        </div>
                        <button wire:click="closeBooksOfAccounts" 
                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            Execute
                        </button>
                    </div>
                </div>

                {{-- Generate Trial Balance --}}
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Generate Final Trial Balance</p>
                            <p class="text-xs text-gray-600">Prepare year-end trial balance</p>
                        </div>
                        <button wire:click="generateTrialBalance" 
                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            Generate
                        </button>
                    </div>
                </div>

                {{-- Prepare Financial Statements --}}
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Prepare Financial Statements</p>
                            <p class="text-xs text-gray-600">Generate all financial reports</p>
                        </div>
                        <button wire:click="prepareFinancialStatements" 
                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            Prepare
                        </button>
                    </div>
                </div>

                {{-- Capture Historical Balances --}}
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Capture Historical Balances</p>
                            <p class="text-xs text-gray-600">Archive year-end balances</p>
                        </div>
                        <button wire:click="captureHistoricalBalances" 
                                class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                            Capture
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. PROFIT ALLOCATION ACTIVITIES --}}
        <div class="border rounded-lg p-4 bg-green-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-green-900">Profit Allocation</h3>
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Calculate Net Profit --}}
                <div class="bg-white p-3 rounded border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Calculate Net Profit</p>
                            <p class="text-xs text-gray-600">Determine year's net profit</p>
                        </div>
                        <button wire:click="calculateNetProfit" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            Calculate
                        </button>
                    </div>
                    @if(isset($netProfit))
                    <div class="mt-2 text-sm font-semibold text-green-800">
                        Net Profit: {{ number_format($netProfit, 2) }}
                    </div>
                    @endif
                </div>

                {{-- Transfer to Statutory Reserve --}}
                <div class="bg-white p-3 rounded border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Transfer to Statutory Reserve</p>
                            <p class="text-xs text-gray-600">Mandatory 20% of net profit</p>
                        </div>
                        <button wire:click="transferToStatutoryReserve" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            Transfer
                        </button>
                    </div>
                </div>

                {{-- Transfer to General Reserve --}}
                <div class="bg-white p-3 rounded border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Transfer to General Reserve</p>
                            <p class="text-xs text-gray-600">Board approved allocation</p>
                        </div>
                        <button wire:click="$emit('openTransferToReserveModal', 'GENERAL_RESERVE')" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            Transfer
                        </button>
                    </div>
                </div>

                {{-- Calculate Dividends --}}
                <div class="bg-white p-3 rounded border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Calculate & Propose Dividends</p>
                            <p class="text-xs text-gray-600">Member dividend calculation</p>
                        </div>
                        <button wire:click="calculateDividends" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            Calculate
                        </button>
                    </div>
                </div>

                {{-- Calculate Interest on Savings --}}
                <div class="bg-white p-3 rounded border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Calculate Interest on Savings</p>
                            <p class="text-xs text-gray-600">Annual interest calculation</p>
                        </div>
                        <button wire:click="calculateInterestOnSavings" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                            Calculate
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. PROVISIONS & ADJUSTMENTS --}}
        <div class="border rounded-lg p-4 bg-orange-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-orange-900">Provisions & Adjustments</h3>
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Bad Debt Provision --}}
                <div class="bg-white p-3 rounded border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Bad Debt Provision</p>
                            <p class="text-xs text-gray-600">Provide for doubtful loans</p>
                        </div>
                        <button wire:click="calculateBadDebtProvision" 
                                class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700">
                            Calculate
                        </button>
                    </div>
                </div>

                {{-- Depreciation --}}
                <div class="bg-white p-3 rounded border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Calculate Depreciation</p>
                            <p class="text-xs text-gray-600">Fixed assets depreciation</p>
                        </div>
                        <button wire:click="calculateDepreciation" 
                                class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700">
                            Calculate
                        </button>
                    </div>
                </div>

                {{-- Accruals & Prepayments --}}
                <div class="bg-white p-3 rounded border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Adjust Accruals & Prepayments</p>
                            <p class="text-xs text-gray-600">Year-end adjustments</p>
                        </div>
                        <button wire:click="adjustAccrualsAndPrepayments" 
                                class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700">
                            Adjust
                        </button>
                    </div>
                </div>

                {{-- Write-off Bad Debts --}}
                <div class="bg-white p-3 rounded border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Write-off Bad Debts</p>
                            <p class="text-xs text-gray-600">Remove uncollectible loans</p>
                        </div>
                        <button wire:click="writeOffBadDebts" 
                                class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700">
                            Write-off
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. AUDIT & COMPLIANCE --}}
        <div class="border rounded-lg p-4 bg-purple-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-purple-900">Audit & Compliance</h3>
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Initiate External Audit --}}
                <div class="bg-white p-3 rounded border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Initiate External Audit</p>
                            <p class="text-xs text-gray-600">Prepare for auditors</p>
                        </div>
                        <button wire:click="initiateExternalAudit" 
                                class="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700">
                            Initiate
                        </button>
                    </div>
                </div>

                {{-- Regulatory Returns --}}
                <div class="bg-white p-3 rounded border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Prepare Regulatory Returns</p>
                            <p class="text-xs text-gray-600">TCDC & Registrar reports</p>
                        </div>
                        <button wire:click="prepareRegulatoryReturns" 
                                class="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700">
                            Prepare
                        </button>
                    </div>
                </div>

                {{-- Tax Compliance --}}
                <div class="bg-white p-3 rounded border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Tax Compliance</p>
                            <p class="text-xs text-gray-600">TRA annual returns</p>
                        </div>
                        <button wire:click="prepareTaxCompliance" 
                                class="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700">
                            Prepare
                        </button>
                    </div>
                </div>

                {{-- Capital Adequacy Check --}}
                <div class="bg-white p-3 rounded border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Capital Adequacy Check</p>
                            <p class="text-xs text-gray-600">Verify regulatory ratios</p>
                        </div>
                        <button wire:click="checkCapitalAdequacy" 
                                class="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700">
                            Check
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. AGM PREPARATION --}}
        <div class="border rounded-lg p-4 bg-indigo-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-indigo-900">AGM Preparation</h3>
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Annual Report --}}
                <div class="bg-white p-3 rounded border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Prepare Annual Report</p>
                            <p class="text-xs text-gray-600">Comprehensive annual report</p>
                        </div>
                        <button wire:click="prepareAnnualReport" 
                                class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                            Prepare
                        </button>
                    </div>
                </div>

                {{-- AGM Notice --}}
                <div class="bg-white p-3 rounded border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Prepare AGM Notice</p>
                            <p class="text-xs text-gray-600">Meeting notice & agenda</p>
                        </div>
                        <button wire:click="prepareAGMNotice" 
                                class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                            Prepare
                        </button>
                    </div>
                </div>

                {{-- Board Resolutions --}}
                <div class="bg-white p-3 rounded border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Draft Board Resolutions</p>
                            <p class="text-xs text-gray-600">Dividend & reserve proposals</p>
                        </div>
                        <button wire:click="draftBoardResolutions" 
                                class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                            Draft
                        </button>
                    </div>
                </div>

                {{-- Budget Proposal --}}
                <div class="bg-white p-3 rounded border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Next Year Budget</p>
                            <p class="text-xs text-gray-600">Prepare budget proposal</p>
                        </div>
                        <button wire:click="prepareBudgetProposal" 
                                class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                            Prepare
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 6. SYSTEM & DATA MANAGEMENT --}}
        <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg text-gray-900">System & Data</h3>
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
            </div>
            
            <div class="space-y-3">
                {{-- Backup Data --}}
                <div class="bg-white p-3 rounded border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Full System Backup</p>
                            <p class="text-xs text-gray-600">Archive all data</p>
                        </div>
                        <button wire:click="performFullBackup" 
                                class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                            Backup
                        </button>
                    </div>
                </div>

                {{-- Archive Documents --}}
                <div class="bg-white p-3 rounded border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Archive Documents</p>
                            <p class="text-xs text-gray-600">Store year's documents</p>
                        </div>
                        <button wire:click="archiveDocuments" 
                                class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                            Archive
                        </button>
                    </div>
                </div>

                {{-- Reset Sequences --}}
                <div class="bg-white p-3 rounded border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Reset Sequences</p>
                            <p class="text-xs text-gray-600">Reset invoice/receipt numbers</p>
                        </div>
                        <button wire:click="resetSequences" 
                                class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Create New Financial Period --}}
                <div class="bg-white p-3 rounded border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Create New Period</p>
                            <p class="text-xs text-gray-600">Initialize next year</p>
                        </div>
                        <button wire:click="createNewFinancialPeriod" 
                                class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Tracker --}}
    <div class="mt-8 border-t pt-6">
        <h3 class="font-semibold text-lg mb-4">Year-End Closing Progress</h3>
        <div class="bg-gray-100 rounded-lg p-4">
            <div class="space-y-3">
                @php
                    $activities = [
                        ['name' => 'Financial Closing', 'status' => $financialClosingStatus ?? 'pending'],
                        ['name' => 'Profit Allocation', 'status' => $profitAllocationStatus ?? 'pending'],
                        ['name' => 'Provisions & Adjustments', 'status' => $provisionsStatus ?? 'pending'],
                        ['name' => 'Audit & Compliance', 'status' => $auditStatus ?? 'pending'],
                        ['name' => 'AGM Preparation', 'status' => $agmStatus ?? 'pending'],
                        ['name' => 'System & Data', 'status' => $systemStatus ?? 'pending'],
                    ];
                @endphp
                
                @foreach($activities as $activity)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">{{ $activity['name'] }}</span>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($activity['status'] === 'completed') bg-green-100 text-green-800
                        @elseif($activity['status'] === 'in_progress') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-600
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $activity['status'])) }}
                    </span>
                </div>
                @endforeach
            </div>
            
            {{-- Overall Progress Bar --}}
            @php
                $completedCount = collect($activities)->where('status', 'completed')->count();
                $progressPercentage = ($completedCount / count($activities)) * 100;
            @endphp
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Overall Progress</span>
                    <span>{{ round($progressPercentage) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-6 flex justify-between">
        <button wire:click="exportYearEndReport" 
                class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Report
        </button>
        
        <div class="space-x-3">
            <button wire:click="saveProgress" 
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                Save Progress
            </button>
            <button wire:click="executeAllActivities" 
                    onclick="return confirm('This will execute all year-end activities. Are you sure?')"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Execute All Activities
            </button>
        </div>
    </div>
</div>

{{-- Transfer to Reserve Modal (if needed) --}}
@if($showTransferModal ?? false)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Transfer to Reserve</h3>
        <form wire:submit.prevent="processTransferToReserve">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" wire:model="transferAmount" step="0.01" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Narration</label>
                    <textarea wire:model="transferNarration" rows="2" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" wire:click="$set('showTransferModal', false)" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Transfer
                </button>
            </div>
        </form>
    </div>
</div>
@endif