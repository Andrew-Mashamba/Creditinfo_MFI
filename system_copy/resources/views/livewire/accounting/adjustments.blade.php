<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Accounting Adjustments</h3>
                <p class="text-gray-600 mt-1">Record adjusting entries, corrections, and period-end adjustments</p>
            </div>
            <button wire:click="showForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Adjustment
            </button>
        </div>
        
        {{-- Adjustment Types --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 cursor-pointer hover:bg-blue-100 transition-colors">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Accrual Adjustments</p>
                        <p class="text-sm text-gray-500">Revenues earned or expenses incurred but not recorded</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 cursor-pointer hover:bg-green-100 transition-colors">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Prepaid & Unearned</p>
                        <p class="text-sm text-gray-500">Adjusting advance payments and deferred items</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 cursor-pointer hover:bg-purple-100 transition-colors">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Asset & Provision Adjustments</p>
                        <p class="text-sm text-gray-500">Depreciation, inventory, and bad debt provisions</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Today's Adjustments</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $todayAdjustments }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Adjustments</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $totalAdjustments }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $pendingApprovals }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($totalAmount, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Educational Section --}}
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-200 mb-6">
        <div class="flex items-start">
            <div class="p-2 bg-indigo-100 rounded-lg mr-4">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-indigo-900 mb-2">📘 About Adjusting Entries</h3>
                <p class="text-sm text-blue-900 mb-3">
                    Adjustments are journal entries made at the end of an accounting period to update account balances before financial statements are prepared. They ensure your financial statements accurately reflect your business's true financial position.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h4 class="font-medium text-indigo-900 mb-1">🎯 Purpose:</h4>
                        <ul class="text-blue-900 space-y-1 text-xs">
                            <li>• Match revenues with expenses (Accrual principle)</li>
                            <li>• Recognize unrecorded items</li>
                            <li>• Correct previous estimates or classifications</li>
                            <li>• Ensure GAAP compliance</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-indigo-900 mb-1">⏱️ When Made:</h4>
                        <ul class="text-blue-900 space-y-1 text-xs">
                            <li>• Month-end (monthly reporting)</li>
                            <li>• Quarter-end (quarterly reports)</li>
                            <li>• Year-end (annual statements)</li>
                            <li>• Before preparing trial balance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Adjustment Form Modal --}}
    @if($showAdjustmentForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">New Adjustment Entry</h3>
                    <button wire:click="hideForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitAdjustment" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Adjustment Type --}}
                        <div>
                            <label for="adjustmentType" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type *</label>
                            <select wire:model="adjustmentType" id="adjustmentType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                @foreach($adjustmentTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('adjustmentType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Adjustment Period --}}
                        <div>
                            <label for="adjustmentPeriod" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Period *</label>
                            <select wire:model="adjustmentPeriod" id="adjustmentPeriod" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                @foreach($adjustmentPeriods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('adjustmentPeriod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Adjustment Examples Based on Type --}}
                    @if($adjustmentType)
                        @php
                            $examples = [
                                'accrued_revenue' => ['desc' => 'Interest earned but not yet received', 'debit' => 'Interest Receivable', 'credit' => 'Interest Revenue'],
                                'accrued_expense' => ['desc' => 'Salaries payable, electricity bill due', 'debit' => 'Salaries Expense', 'credit' => 'Salaries Payable'],
                                'prepaid_expense' => ['desc' => 'Rent paid for 12 months; adjust for 1 month used', 'debit' => 'Rent Expense', 'credit' => 'Prepaid Rent'],
                                'unearned_revenue' => ['desc' => 'Advance payment for services now delivered', 'debit' => 'Unearned Revenue', 'credit' => 'Service Revenue'],
                                'depreciation' => ['desc' => 'Monthly depreciation of office equipment', 'debit' => 'Depreciation Expense', 'credit' => 'Accumulated Depreciation'],
                                'inventory_adjustment' => ['desc' => 'End-of-year stocktaking adjustment', 'debit' => 'Cost of Goods Sold', 'credit' => 'Inventory'],
                                'bad_debt_provision' => ['desc' => 'Creating allowance for doubtful debts', 'debit' => 'Bad Debt Expense', 'credit' => 'Allowance for Doubtful Accounts']
                            ];
                            $example = $examples[$adjustmentType] ?? null;
                        @endphp
                        
                        @if($example)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">Example for {{ $adjustmentTypes[$adjustmentType] }}:</p>
                                        <p class="text-sm text-blue-700 mt-1">{{ $example['desc'] }}</p>
                                        <div class="mt-2 text-xs text-blue-600">
                                            <span class="font-medium">Journal Entry:</span><br>
                                            Dr. {{ $example['debit'] }} &nbsp;&nbsp;&nbsp; Cr. {{ $example['credit'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Debit Account --}}
                        <div>
                            <label for="debitAccount" class="block text-sm font-medium text-gray-700 mb-1">Debit Account *</label>
                            <select wire:model="debitAccount" id="debitAccount" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select debit account...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }} - {{ $account->account_number }}</option>
                                @endforeach
                            </select>
                            @error('debitAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Credit Account --}}
                        <div>
                            <label for="creditAccount" class="block text-sm font-medium text-gray-700 mb-1">Credit Account *</label>
                            <select wire:model="creditAccount" id="creditAccount" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select credit account...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->account_name }} - {{ $account->account_number }}</option>
                                @endforeach
                            </select>
                            @error('creditAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Amount --}}
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                            <input type="number" wire:model="amount" id="amount" step="0.01" min="0" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="0.00">
                            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Adjustment Date --}}
                        <div>
                            <label for="adjustmentDate" class="block text-sm font-medium text-gray-700 mb-1">Adjustment Date *</label>
                            <input type="date" wire:model="adjustmentDate" id="adjustmentDate" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('adjustmentDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <input type="text" wire:model="description" id="description" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               placeholder="Enter adjustment description">
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                        <input type="text" wire:model="reference" id="reference" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               placeholder="Optional reference number">
                        @error('reference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Reason --}}
<div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Detailed Reason *</label>
                        <textarea wire:model="reason" id="reason" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  placeholder="Provide detailed justification for this adjustment..."></textarea>
                        @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="hideForm" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <span wire:loading.remove wire:target="submitAdjustment">Create Adjustment</span>
                            <span wire:loading wire:target="submitAdjustment">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Adjustments History --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Adjustments</h3>
                <div class="flex space-x-2">
                    <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search adjustments..." 
                           class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <select wire:model="filterType" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="all">All Types</option>
                        @foreach($adjustmentTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            @if($adjustments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            {{-- Adjustment history will be displayed here when implemented --}}
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No adjustments found</h4>
                    <p class="text-gray-500 mb-4">Start by creating your first adjustment entry.</p>
                    <button wire:click="showForm" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Create Adjustment
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>
