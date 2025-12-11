<div class="space-y-4">
    {{-- Bank Accounts --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-sm font-semibold mb-2">Bank Accounts for Disbursement</h3>
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-1">Account</th>
                    <th class="text-left py-1">Mirror Account</th>
                    <th class="text-right py-1">Balance</th>
                    <th class="text-center py-1">Status</th>
                    <th class="text-center py-1">Select</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bankAccounts as $account)
                <tr class="border-b">
                    <td class="py-1">{{ $account['name'] }}</td>
                    <td class="py-1 text-xs font-mono">{{ $account['mirror_account'] }}</td>
                    <td class="py-1 text-right {{ $account['can_disburse'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($account['balance'], 2) }}
                    </td>
                    <td class="py-1 text-center">
                        {{ $account['can_disburse'] ? '✓' : '✗' }}
                    </td>
                    <td class="py-1 text-center">
                        <input type="radio" name="selected_bank" value="{{ $account['mirror_account'] }}" 
                               wire:model="selectedBankMirror"
                               {{ $account['can_disburse'] ? '' : 'disabled' }}>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Journal Entries with Correct Flow --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-sm font-semibold mb-2">Journal Entries Configuration (Double-Entry Bookkeeping)</h3>
        
        @php
        // Get the configured accounts from loan_sub_products
        $loanProductAccount = $glAccountMappings['0101100012001295'] ?? null;
        $interestAccount = $glAccountMappings['0101400040004010'] ?? null;
        $chargesAccount = $glAccountMappings['0101400041004120'] ?? null;
        $insuranceAccount = $glAccountMappings['0101400041004110'] ?? null;
        
        // Selected bank mirror account (example: if first bank is selected)
        $selectedBankMirror = $bankAccounts[0]['mirror_account'] ?? '0101100010001010';
        $selectedBankBalance = $bankAccounts[0]['balance'] ?? 0;
        
        // Build journal entries based on the CORRECT accounting flow
        // ONE DEBIT to Loan Account, MULTIPLE CREDITS to other accounts
        $journalEntries = [];
        $totalDebits = 0;
        $totalCredits = 0;
        $entryNumber = 1;

        // SINGLE DEBIT ENTRY: Loan Account (Asset increases)
        $journalEntries[] = [
            'step' => '1',
            'entry_no' => $entryNumber++,
            'account' => $loan->loan_account_number ?? 'BUS202596153',
            'name' => 'Loan Receivable Account (Under ' . ($loanProductAccount['account_name'] ?? 'MKOPO WA BIASHARA') . ')',
            'type' => 'Loan Disbursement - Create Loan Asset',
            'current_balance' => 0, // New loan account
            'debit' => $loan->principle ?? 0,
            'credit' => 0,
            'new_balance' => $loan->principle ?? 0, // Positive - it's an asset (loan receivable)
            'side' => 'DR',
            'editable' => false,
            'description' => 'Loan asset created for full principal amount'
        ];
        $totalDebits += $loan->principle ?? 0;

        // MULTIPLE CREDIT ENTRIES:

        // Calculate net bank disbursement (principal minus all deductions)
        $totalDeductions = ($deductionEntries['charges']['total'] ?? 0) +
                          ($deductionEntries['insurance']['total'] ?? 0) +
                          ($deductionEntries['first_interest']['total'] ?? 0);
        $netBankDisbursement = ($loan->principle ?? 0) - $totalDeductions;

        // Credit 1: Bank Account (Cash outflow - net amount)
        $journalEntries[] = [
            'step' => '2',
            'entry_no' => $entryNumber++,
            'account' => $selectedBankMirror,
            'name' => 'Bank Mirror Account (Selected Bank)',
            'type' => 'Cash Disbursement',
            'current_balance' => $selectedBankBalance,
            'debit' => 0,
            'credit' => $netBankDisbursement,
            'new_balance' => $selectedBankBalance - $netBankDisbursement,
            'side' => 'CR',
            'editable' => false,
            'description' => 'Net cash disbursed to client (after deductions)'
        ];
        $totalCredits += $netBankDisbursement;

        // Credit 2: Processing Charges Income (if applicable)
        if(isset($deductionEntries['charges']['total']) && $deductionEntries['charges']['total'] > 0) {
            $journalEntries[] = [
                'step' => '3',
                'entry_no' => $entryNumber++,
                'account' => '0101400041004120',
                'name' => $chargesAccount['account_name'] ?? 'LOAN PROCESSING FEES',
                'type' => 'Processing Charges Income',
                'current_balance' => $chargesAccount['current_balance'] ?? 0,
                'debit' => 0,
                'credit' => $deductionEntries['charges']['total'],
                'new_balance' => ($chargesAccount['current_balance'] ?? 0) + $deductionEntries['charges']['total'],
                'side' => 'CR',
                'editable' => true,
                'description' => 'Income from processing charges'
            ];
            $totalCredits += $deductionEntries['charges']['total'];
        }

        // Credit 3: Insurance Income (if applicable)
        if(isset($deductionEntries['insurance']['total']) && $deductionEntries['insurance']['total'] > 0) {
            $journalEntries[] = [
                'step' => '4',
                'entry_no' => $entryNumber++,
                'account' => '0101400041004110',
                'name' => $insuranceAccount['account_name'] ?? 'LATE PAYMENT FEES',
                'type' => 'Insurance Premium Income',
                'current_balance' => $insuranceAccount['current_balance'] ?? 0,
                'debit' => 0,
                'credit' => $deductionEntries['insurance']['total'],
                'new_balance' => ($insuranceAccount['current_balance'] ?? 0) + $deductionEntries['insurance']['total'],
                'side' => 'CR',
                'editable' => true,
                'issue' => true,
                'issue_note' => 'Wrong account - should be Insurance Income (0101400041004111)',
                'description' => 'Income from insurance premium'
            ];
            $totalCredits += $deductionEntries['insurance']['total'];
        }

        // Credit 4: Interest Income (if applicable)
        if(isset($deductionEntries['first_interest']['total']) && $deductionEntries['first_interest']['total'] > 0) {
            $journalEntries[] = [
                'step' => '5',
                'entry_no' => $entryNumber++,
                'account' => '0101400040004010',
                'name' => $interestAccount['account_name'] ?? 'INTEREST INCOME - CURRENT',
                'type' => 'First Interest Income (' . ($deductionEntries['first_interest']['days'] ?? 0) . ' days)',
                'current_balance' => $interestAccount['current_balance'] ?? 0,
                'debit' => 0,
                'credit' => $deductionEntries['first_interest']['total'],
                'new_balance' => ($interestAccount['current_balance'] ?? 0) + $deductionEntries['first_interest']['total'],
                'side' => 'CR',
                'editable' => true,
                'description' => 'Income from first interest period'
            ];
            $totalCredits += $deductionEntries['first_interest']['total'];
        }

        // Credit 5: Original Loan Account (for Top-up loans)
        $isTopUpLoan = isset($loan->loan_type_2) && in_array($loan->loan_type_2, ['Top Up', 'Top-up', 'Topup']);
        $topUpAmount = 0;
        $originalLoanAccount = null;
        $originalLoanBalance = 0;

        if($isTopUpLoan) {
            // Get the original loan being topped up
            $originalLoanId = $loan->top_up_loan_id ?? $loan->topup_loan_id ?? $loan->original_loan_id ?? null;

            if($originalLoanId) {
                $originalLoan = DB::table('loans')->where('id', $originalLoanId)->first();

                if($originalLoan) {
                    $originalLoanAccount = $originalLoan->loan_account_number;

                    // Get current balance of original loan
                    if($originalLoanAccount) {
                        $originalAccount = DB::table('accounts')
                            ->where('account_number', $originalLoanAccount)
                            ->first();
                        $originalLoanBalance = $originalAccount ? abs($originalAccount->balance ?? 0) : 0;
                    }

                    // Top-up amount is typically the outstanding balance of original loan
                    // Or it could be specified in deductionEntries if configured
                    $topUpAmount = $deductionEntries['top_up']['total'] ?? $originalLoanBalance;

                    if($topUpAmount > 0) {
                        $journalEntries[] = [
                            'step' => '6',
                            'entry_no' => $entryNumber++,
                            'account' => $originalLoanAccount,
                            'name' => 'Original Loan Account (Being Topped Up: ' . ($originalLoan->loan_id ?? 'N/A') . ')',
                            'type' => 'Top-up Payment to Original Loan',
                            'current_balance' => $originalLoanBalance,
                            'debit' => 0,
                            'credit' => $topUpAmount,
                            'new_balance' => $originalLoanBalance - $topUpAmount,
                            'side' => 'CR',
                            'editable' => false,
                            'description' => 'Payment reduces original loan balance',
                            'highlight' => true
                        ];
                        $totalCredits += $topUpAmount;

                        // Adjust net bank disbursement since top-up reduces what client receives
                        $netBankDisbursement -= $topUpAmount;

                        // Update the bank entry with new amount
                        foreach($journalEntries as $key => $entry) {
                            if($entry['step'] == '2' && strpos($entry['type'], 'Cash Disbursement') !== false) {
                                $journalEntries[$key]['credit'] = $netBankDisbursement;
                                $journalEntries[$key]['new_balance'] = $selectedBankBalance - $netBankDisbursement;
                                $totalCredits = $totalCredits - $entry['credit'] + $netBankDisbursement;
                            }
                        }
                    }
                }
            }
        }

        // Available GL accounts for dropdown selection
        $availableAccounts = [
            '0101400040004010' => 'INTEREST INCOME - CURRENT',
            '0101400041004120' => 'LOAN PROCESSING FEES',
            '0101400041004110' => 'LATE PAYMENT FEES (Wrong for Insurance)',
            '0101400041004111' => 'INSURANCE INCOME (Correct for Insurance)',
            '0101400041004130' => 'OTHER LOAN INCOME',
            '0101100012001295' => 'MKOPO WA BIASHARA (Loan Portfolio)',
        ];
        
        // Calculate net effect on bank
        $totalDeductions = ($deductionEntries['charges']['total'] ?? 0) + 
                          ($deductionEntries['insurance']['total'] ?? 0) + 
                          ($deductionEntries['first_interest']['total'] ?? 0);
        $netBankOutflow = ($loan->principle ?? 0) - $totalDeductions;
        @endphp
        
        <div class="mb-3 p-3 {{ $isTopUpLoan ? 'bg-gradient-to-r from-blue-50 to-purple-50 border-2 border-blue-400' : 'bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-300' }} rounded-lg">
            @if($isTopUpLoan)
            <div class="mb-2 flex items-center text-blue-700 font-semibold text-xs">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                </svg>
                TOP-UP LOAN - Includes payment to original loan
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-{{ $isTopUpLoan && $topUpAmount > 0 ? '5' : '4' }} gap-2 text-xs">
                <div>
                    <div class="text-gray-600 font-medium">Loan Principal (DR)</div>
                    <div class="text-red-700 font-bold text-sm">{{ number_format($loan->principle ?? 0, 2) }} TZS</div>
                </div>
                <div>
                    <div class="text-gray-600 font-medium">Bank Disbursement (CR)</div>
                    <div class="text-green-700 font-bold text-sm">{{ number_format($netBankDisbursement, 2) }} TZS</div>
                </div>
                <div>
                    <div class="text-gray-600 font-medium">Income Deductions (CR)</div>
                    <div class="text-green-700 font-bold text-sm">{{ number_format($totalDeductions, 2) }} TZS</div>
                </div>
                @if($isTopUpLoan && $topUpAmount > 0)
                <div class="bg-blue-100 rounded px-2 py-1">
                    <div class="text-blue-800 font-semibold">Top-up Payment (CR)</div>
                    <div class="text-blue-900 font-bold text-sm">{{ number_format($topUpAmount, 2) }} TZS</div>
                </div>
                @endif
                <div>
                    <div class="text-gray-600 font-medium">Selected Bank</div>
                    <div class="text-blue-900 font-mono text-xs">{{ $selectedBankMirror }}</div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-center py-1 px-1">Step</th>
                        <th class="text-center py-1 px-1">#</th>
                        <th class="text-center py-1 px-1">Side</th>
                        <th class="text-left py-1 px-2">GL Account</th>
                        <th class="text-left py-1 px-2">Account Name</th>
                        <th class="text-left py-1 px-2">Transaction Type</th>
                        <th class="text-right py-1 px-2">Current Bal</th>
                        <th class="text-right py-1 px-2">Debit</th>
                        <th class="text-right py-1 px-2">Credit</th>
                        <th class="text-right py-1 px-2">New Balance</th>
                        <th class="text-center py-1 px-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $currentStep = '';
                    @endphp
                    @foreach($journalEntries as $index => $entry)
                    <tr class="border-b transition-all {{ isset($entry['issue']) && $entry['issue'] ? 'bg-red-50 hover:bg-red-100 animate-pulse' : (isset($entry['highlight']) && $entry['highlight'] ? 'bg-blue-50 hover:bg-blue-100 border-l-4 border-blue-500' : 'hover:bg-gray-50') }}">
                        <td class="py-1 px-1 text-center font-semibold">
                            @if($currentStep != $entry['step'])
                                {{ $entry['step'] }}
                                @php $currentStep = $entry['step']; @endphp
                            @endif
                        </td>
                        <td class="py-1 px-1 text-center">{{ $entry['entry_no'] }}</td>
                        <td class="py-1 px-1 text-center">
                            @if($entry['side'] == 'DR')
                                <span class="text-red-600 font-semibold">DR</span>
                            @else
                                <span class="text-green-600 font-semibold">CR</span>
                            @endif
                        </td>
                        <td class="py-1 px-2">
                            @if($entry['editable'])
                                @php
                                $fieldName = '';
                                if(strpos($entry['type'], 'Charges') !== false) {
                                    $fieldName = 'charges_account';
                                } elseif(strpos($entry['type'], 'Insurance') !== false) {
                                    $fieldName = 'insurance_account';
                                } elseif(strpos($entry['type'], 'Interest') !== false) {
                                    $fieldName = 'interest_account';
                                }
                                @endphp
                                <select class="text-xs border rounded px-1 py-0.5 w-full {{ isset($entry['issue']) && $entry['issue'] ? 'border-red-500 bg-red-50 font-semibold' : '' }}" 
                                        wire:change="updateAccount('{{ $fieldName }}', $event.target.value)"
                                        value="{{ $entry['account'] }}"
                                        title="Select the appropriate GL account">
                                    @foreach($availableAccounts as $acctNum => $acctName)
                                        <option value="{{ $acctNum }}" {{ $entry['account'] == $acctNum ? 'selected' : '' }}
                                                title="{{ $acctName }}">
                                            {{ $acctNum }} {{ strpos($acctName, 'Correct') !== false ? '✓' : (strpos($acctName, 'Wrong') !== false ? '✗' : '') }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="font-mono">{{ $entry['account'] }}</span>
                            @endif
                        </td>
                        <td class="py-1 px-2">
                            {{ $entry['name'] }}
                            @if(isset($entry['issue']) && $entry['issue'])
                                <span class="text-red-600 text-xs block">⚠ {{ $entry['issue_note'] }}</span>
                            @endif
                        </td>
                        <td class="py-1 px-2">{{ $entry['type'] }}</td>
                        <td class="py-1 px-2 text-right">{{ number_format($entry['current_balance'], 2) }}</td>
                        <td class="py-1 px-2 text-right {{ $entry['debit'] > 0 ? 'font-semibold text-red-600' : 'text-gray-400' }}">
                            {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                        </td>
                        <td class="py-1 px-2 text-right {{ $entry['credit'] > 0 ? 'font-semibold text-green-600' : 'text-gray-400' }}">
                            {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                        </td>
                        <td class="py-1 px-2 text-right font-semibold">{{ number_format($entry['new_balance'], 2) }}</td>
                        <td class="py-1 px-2 text-center">
                            @if($entry['editable'])
                                <span class="text-blue-600 text-xs">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Editable
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Fixed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t bg-gray-100 font-semibold">
                        <td colspan="7" class="py-1 px-2 text-right">Total</td>
                        <td class="py-1 px-2 text-right text-red-600">{{ number_format($totalDebits, 2) }}</td>
                        <td class="py-1 px-2 text-right text-green-600">{{ number_format($totalCredits, 2) }}</td>
                        <td colspan="2" class="py-1 px-2 text-center">
                            <span class="{{ abs($totalDebits - $totalCredits) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                                {{ abs($totalDebits - $totalCredits) < 0.01 ? '✓ Balanced' : '✗ Diff: ' . number_format(abs($totalDebits - $totalCredits), 2) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- Save/Reset Buttons --}}
        @if(count($editedAccounts) > 0)
        <div class="mt-3 flex justify-between items-center">
            <div class="text-xs text-gray-600">
                <span class="font-semibold">{{ count($editedAccounts) }}</span> account(s) modified
            </div>
            <div class="flex gap-2">
                <button wire:click="resetAccountConfiguration" 
                        class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded hover:bg-gray-200">
                    Reset Changes
                </button>
                <button wire:click="saveAccountConfiguration" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-3 py-1 bg-blue-900 text-white text-xs rounded hover:bg-blue-700 inline-flex items-center">
                    <span wire:loading.remove>Save Configuration</span>
                    <span wire:loading class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
        @endif
        
        {{-- Save Status Message --}}
        @if($saveMessage)
        <div class="mt-3 p-3 rounded {{ $saveStatus == 'success' ? 'bg-green-50 border border-green-200' : ($saveStatus == 'error' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200') }}">
            <p class="text-xs {{ $saveStatus == 'success' ? 'text-green-800' : ($saveStatus == 'error' ? 'text-red-800' : 'text-yellow-800') }}">
                {{ $saveMessage }}
            </p>
        </div>
        @endif
        
        {{-- Configuration Issues Alert --}}
        @php
        $hasIssues = false;
        foreach($journalEntries as $entry) {
            if(isset($entry['issue']) && $entry['issue']) {
                $hasIssues = true;
                break;
            }
        }
        @endphp
        
        @if($hasIssues)
        <div class="mt-3 p-3 bg-red-50 border-2 border-red-300 rounded">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-xs font-semibold text-red-900 mb-1">⚠ GL Account Configuration Issues Detected</p>
                    <p class="text-xs text-red-800">
                        • Insurance premium is being credited to "<strong>LATE PAYMENT FEES</strong>" account (0101400041004110)<br>
                        • This is incorrect - insurance should use the "<strong>Insurance Income</strong>" account (0101400041004111)<br>
                        • <strong>Action Required:</strong> Use the dropdown menu in the table above to select the correct account<br>
                        • Click "<strong>Save Configuration</strong>" after making changes to update the loan product settings
                    </p>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Accounting Flow Explanation --}}
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
            <h4 class="text-xs font-semibold mb-2">Accounting Flow Explanation (Double-Entry Bookkeeping):</h4>
            <div class="text-xs space-y-1">
                <div><strong>Single DEBIT Entry:</strong></div>
                <div class="ml-4">• DR: Loan Receivable Account ({{ $loan->loan_account_number ?? 'BUS202596153' }}) - {{ number_format($loan->principle ?? 0, 2) }} TZS</div>
                <div class="ml-8 text-gray-600">→ Asset increases by full loan amount</div>

                <div class="mt-2"><strong>Multiple CREDIT Entries:</strong></div>
                <div class="ml-4">1. <strong>Bank Cash Outflow:</strong></div>
                <div class="ml-8">• CR: Bank Mirror Account ({{ $selectedBankMirror }}) - {{ number_format($netBankDisbursement, 2) }} TZS</div>
                <div class="ml-12 text-gray-600">→ Net cash disbursed to client (Principal - Deductions)</div>

                @if(isset($deductionEntries['charges']['total']) && $deductionEntries['charges']['total'] > 0)
                <div class="ml-4 mt-1">2. <strong>Processing Charges Income:</strong></div>
                <div class="ml-8">• CR: Charges Income (0101400041004120) - {{ number_format($deductionEntries['charges']['total'], 2) }} TZS</div>
                <div class="ml-12 text-gray-600">→ Revenue from processing fees</div>
                @endif

                @if(isset($deductionEntries['insurance']['total']) && $deductionEntries['insurance']['total'] > 0)
                <div class="ml-4 mt-1">3. <strong>Insurance Premium Income:</strong></div>
                <div class="ml-8">• CR: Insurance Income (0101400041004110) - {{ number_format($deductionEntries['insurance']['total'], 2) }} TZS</div>
                <div class="ml-12 text-gray-600">→ Revenue from insurance premium</div>
                @endif

                @if(isset($deductionEntries['first_interest']['total']) && $deductionEntries['first_interest']['total'] > 0)
                <div class="ml-4 mt-1">4. <strong>First Interest Income:</strong></div>
                <div class="ml-8">• CR: Interest Income (0101400040004010) - {{ number_format($deductionEntries['first_interest']['total'], 2) }} TZS</div>
                <div class="ml-12 text-gray-600">→ Revenue from interest ({{ $deductionEntries['first_interest']['days'] ?? 0 }} days)</div>
                @endif

                @if($isTopUpLoan && $topUpAmount > 0)
                <div class="ml-4 mt-1">5. <strong>Original Loan Payment (Top-up):</strong></div>
                <div class="ml-8">• CR: Original Loan Account ({{ $originalLoanAccount }}) - {{ number_format($topUpAmount, 2) }} TZS</div>
                <div class="ml-12 text-blue-700 font-semibold">→ Payment to settle/reduce original loan balance</div>
                <div class="ml-12 text-gray-600">→ Original balance: {{ number_format($originalLoanBalance, 2) }} TZS</div>
                <div class="ml-12 text-gray-600">→ New balance: {{ number_format($originalLoanBalance - $topUpAmount, 2) }} TZS</div>
                @endif

                <div class="mt-2 pt-2 border-t border-blue-300"><strong>Summary:</strong></div>
                <div class="ml-4">• Total Debits: {{ number_format($loan->principle ?? 0, 2) }} TZS</div>
                <div class="ml-4">• Total Credits: {{ number_format($netBankDisbursement + $totalDeductions + $topUpAmount, 2) }} TZS</div>
                <div class="ml-4">• Net to Client: {{ number_format($netBankDisbursement, 2) }} TZS</div>
                <div class="ml-4">• Total Deductions: {{ number_format($totalDeductions, 2) }} TZS</div>
                @if($isTopUpLoan && $topUpAmount > 0)
                <div class="ml-4">• Top-up Payment: {{ number_format($topUpAmount, 2) }} TZS</div>
                @endif
                <div class="ml-4">• New Loan Receivable Balance: {{ number_format($loan->principle ?? 0, 2) }} TZS (full amount)</div>
            </div>
        </div>
    </div>
</div>