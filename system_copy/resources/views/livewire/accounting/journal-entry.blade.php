<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white rounded-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Journal Entries</h3>
                <p class="text-gray-600 mt-1">Record double-entry transactions and manual journal entries</p>
            </div>
            <button wire:click="showForm" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Journal Entry
            </button>
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
                        <p class="text-sm font-medium text-gray-500">Today's Entries</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $todayEntries }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Total Entries</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $totalEntries }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">This Month</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $thisMonthEntries }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Current Balance</p>
                        <p class="text-lg font-semibold {{ $totals['balanced'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $totals['balanced'] ? 'Balanced' : 'Unbalanced' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Journal Entry Form Modal --}}
    @if($showEntryForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="hideForm">
            <div class="bg-white rounded-xl p-6 mx-4 overflow-y-auto" style="width: 80%; height: 90vh;">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">New Journal Entry</h3>
                    <button wire:click="hideForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitJournalEntry" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Transaction Date --}}
                        <div>
                            <label for="transactionDate" class="block text-sm font-medium text-gray-700 mb-1">Transaction Date *</label>
                            <input type="date" wire:model="transactionDate" id="transactionDate"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('transactionDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Reference Number --}}
                        <div>
                            <label for="referenceNo" class="block text-sm font-medium text-gray-700 mb-1">Reference Number *</label>
                            <input type="text" wire:model="referenceNo" id="referenceNo"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="JE-20251011123456">
                            @error('referenceNo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <input type="text" wire:model="description" id="description"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter journal entry description">
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Journal Lines --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700">Journal Lines</h4>
                        </div>
                        <div class="overflow-x-auto overflow-y-auto" style="max-height: 50vh;">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Account</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/6">Debit</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/6">Credit</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Description</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-16">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($journalLines as $index => $line)
                                        <tr>
                                            <td class="px-4 py-2">
                                                {{-- Searchable Account Dropdown --}}
                                                <div class="relative">
                                                    @php
                                                        $lineAccount = null;
                                                        if (!empty($line['account_number'])) {
                                                            $lineAccount = $accounts->firstWhere('account_number', $line['account_number']);
                                                        }
                                                        $displayValue = $lineAccount ? $lineAccount->account_name . ' (' . $lineAccount->account_number . ')' : '';
                                                    @endphp

                                                    <input
                                                        type="text"
                                                        wire:model.debounce.300ms="accountSearchTerms.{{ $index }}"
                                                        placeholder="Search by account name, number, member, type, product, category, notes..."
                                                        value="{{ $displayValue }}"
                                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        autocomplete="off">

                                                    {{-- Dropdown Results --}}
                                                    @if(!empty($accountSearchTerms[$index]) && strlen($accountSearchTerms[$index]) >= 2)
                                                        @php
                                                            $filteredAccounts = $this->getFilteredAccounts($index);
                                                        @endphp
                                                        @if($filteredAccounts->count() > 0)
                                                            <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                                                                @foreach($filteredAccounts as $account)
                                                                    <button type="button"
                                                                            wire:click="selectAccount({{ $index }}, '{{ $account->account_number }}')"
                                                                            wire:loading.attr="disabled"
                                                                            class="w-full text-left px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0 transition-colors disabled:opacity-50">
                                                                        <div class="flex justify-between items-start">
                                                                            <div class="flex-1">
                                                                                <p class="text-sm font-medium text-gray-900">{{ $account->account_name }}</p>
                                                                                <p class="text-xs text-gray-500 space-x-2">
                                                                                    <span class="font-mono bg-gray-100 px-1 rounded">{{ $account->account_number }}</span>
                                                                                    @if($account->account_type)
                                                                                        <span class="text-purple-600">{{ $account->account_type }}</span>
                                                                                    @endif
                                                                                    @if($account->type)
                                                                                        <span class="text-indigo-600">{{ $account->type }}</span>
                                                                                    @endif
                                                                                    @if($account->client_number && $account->client_number !== '0000' && $account->client_number !== '0')
                                                                                        <span class="text-blue-600 font-semibold">
                                                                                            Member: {{ $account->client_number }}
                                                                                        </span>
                                                                                    @endif
                                                                                </p>
                                                                                @if($account->product_number)
                                                                                    <p class="text-xs text-gray-400 mt-0.5">
                                                                                        Product: {{ $account->product_number }}
                                                                                        @if($account->category_code)
                                                                                            | Category: {{ $account->category_code }}
                                                                                        @endif
                                                                                        | Balance: TZS {{ number_format($account->balance, 2) }}
                                                                                    </p>
                                                                                @endif
                                                                            </div>
                                                                            <div wire:loading wire:target="selectAccount({{ $index }}, '{{ $account->account_number }}')" class="ml-2">
                                                                                <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                                </svg>
                                                                            </div>
                                                                        </div>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg">
                                                                <div class="px-3 py-2 text-sm text-gray-500">
                                                                    No accounts found
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    {{-- Display selected account --}}
                                                    @if(!empty($line['account_number']) && empty($accountSearchTerms[$index]))
                                                        @php
                                                            $selectedAccount = $accounts->firstWhere('account_number', $line['account_number']);
                                                        @endphp
                                                        @if($selectedAccount)
                                                            <div class="mt-1 text-xs text-gray-600">
                                                                Selected: <span class="font-medium">{{ $selectedAccount->account_name }}</span>
                                                                ({{ $selectedAccount->account_number }})
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @error("journalLines.{$index}.account_number") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" wire:model="journalLines.{{ $index }}.debit" step="0.01" min="0"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="0.00">
                                                @error("journalLines.{$index}.debit") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" wire:model="journalLines.{{ $index }}.credit" step="0.01" min="0"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="0.00">
                                                @error("journalLines.{$index}.credit") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" wire:model="journalLines.{{ $index }}.line_description"
                                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="Line description">
                                                @error("journalLines.{$index}.line_description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                @if(count($journalLines) > 2)
                                                    <button type="button" wire:click="removeLine({{ $index }})" class="text-red-600 hover:text-red-800">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 sticky bottom-0 z-10">
                                    <tr>
                                        <td class="px-4 py-3 text-right font-semibold text-sm text-gray-700">Totals:</td>
                                        <td class="px-4 py-3 font-bold text-sm {{ $totals['debit'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ number_format($totals['debit'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-sm {{ $totals['credit'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ number_format($totals['credit'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 font-bold text-sm {{ $totals['balanced'] ? 'text-green-600' : 'text-red-600' }}" colspan="2">
                                            @if($totals['balanced'])
                                                ✓ Balanced
                                            @else
                                                ✗ Difference: {{ number_format(abs($totals['difference']), 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <button type="button" wire:click="addLine" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Line
                    </button>

                    {{-- Form Actions --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="hideForm" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(!$totals['balanced']) disabled @endif>
                            <span wire:loading.remove wire:target="submitJournalEntry">Create Journal Entry</span>
                            <span wire:loading wire:target="submitJournalEntry">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if($showDetailsModal && $selectedJournalEntry)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeDetails">
            <div class="bg-white rounded-xl p-6 mx-4 overflow-y-auto" style="width: 80%; height: 90vh;">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Journal Entry Details</h3>
                    <button wire:click="closeDetails" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Entry Header Information --}}
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Reference Number</p>
                            <p class="font-semibold text-gray-900">{{ $selectedJournalEntry->reference_no }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Transaction Date</p>
                            <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($selectedJournalEntry->transaction_date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="font-semibold text-gray-900">{{ number_format($selectedJournalEntry->total_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-semibold">
                                @if($selectedJournalEntry->is_posted)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Posted</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Unposted</span>
                                @endif
                                @if($selectedJournalEntry->is_reversal)
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 ml-2">Reversal</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Description</p>
                            <p class="font-semibold text-gray-900">{{ $selectedJournalEntry->description }}</p>
                        </div>
                    </div>
                </div>

                {{-- Journal Entry Lines --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            @foreach($selectedJournalEntryLines as $line)
                                @php
                                    $totalDebit += $line->debit;
                                    $totalCredit += $line->credit;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $line->account_name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $line->account_code }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right {{ $line->debit > 0 ? 'font-semibold text-green-600' : 'text-gray-400' }}">
                                        {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right {{ $line->credit > 0 ? 'font-semibold text-blue-600' : 'text-gray-400' }}">
                                        {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $line->description ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-3 text-right font-bold text-sm text-gray-700">Totals:</td>
                                <td class="px-4 py-3 font-bold text-sm text-right text-green-600">{{ number_format($totalDebit, 2) }}</td>
                                <td class="px-4 py-3 font-bold text-sm text-right text-blue-600">{{ number_format($totalCredit, 2) }}</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Post Confirmation Modal --}}
    @if($showPostConfirm && $selectedJournalEntry)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mx-auto mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Post Journal Entry?</h3>
                <p class="text-sm text-gray-600 text-center mb-6">
                    Are you sure you want to post journal entry <strong>{{ $selectedJournalEntry->reference_no }}</strong>?
                    This will update account balances and create general ledger entries.
                </p>
                <div class="flex space-x-3">
                    <button wire:click="cancelAction" class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="postJournalEntry" class="flex-1 px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <span wire:loading.remove wire:target="postJournalEntry">Yes, Post It</span>
                        <span wire:loading wire:target="postJournalEntry">Posting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reverse Confirmation Modal --}}
    @if($showReverseConfirm && $selectedJournalEntry)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Reverse Journal Entry?</h3>
                <p class="text-sm text-gray-600 text-center mb-4">
                    Are you sure you want to reverse journal entry <strong>{{ $selectedJournalEntry->reference_no }}</strong>?
                    This will create a reversal entry and update all affected accounts.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Reversal *</label>
                    <textarea wire:model="reversalReason" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Please provide a reason..."></textarea>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="cancelAction" class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="reverseJournalEntry" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <span wire:loading.remove wire:target="reverseJournalEntry">Yes, Reverse It</span>
                        <span wire:loading wire:target="reverseJournalEntry">Reversing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Journal Entries History --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Journal Entry Register</h3>
                <div class="flex space-x-2">
                    <input type="date" wire:model="filterDateFrom" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="date" wire:model="filterDateTo" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search by reference or description..."
                           class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($journalEntries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($journalEntries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($entry->transaction_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        {{ $entry->reference_no }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ Str::limit($entry->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                        {{ number_format($entry->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($entry->is_posted)
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Posted</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Unposted</span>
                                        @endif
                                        @if($entry->is_reversal)
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 ml-1">Reversal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-gray-900">{{ $entry->created_by_name ?? 'N/A' }}</div>
                                        @if($entry->is_posted && $entry->posted_by_name)
                                            <div class="text-xs text-gray-500">Posted by: {{ $entry->posted_by_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button wire:click="viewDetails({{ $entry->id }})"
                                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                                View
                                            </button>
                                            @if(!$entry->is_posted)
                                                <button wire:click="confirmPost({{ $entry->id }})"
                                                        class="text-green-600 hover:text-green-800 font-medium">
                                                    Post
                                                </button>
                                            @endif
                                            @if($entry->is_posted && !$entry->is_reversal)
                                                <button wire:click="confirmReverse({{ $entry->id }})"
                                                        class="text-red-600 hover:text-red-800 font-medium">
                                                    Reverse
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $journalEntries->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No journal entries found</h4>
                    <p class="text-gray-500 mb-4">Start by creating your first journal entry.</p>
                    <button wire:click="showForm" class="px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create Journal Entry
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
             class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>
