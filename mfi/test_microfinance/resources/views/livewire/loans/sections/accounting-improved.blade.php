<div>
    <div class="space-y-6">
    @if(!$loan)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <p class="text-yellow-700">No loan selected. Please select a loan to view accounting details.</p>
        </div>
    @else
        {{-- Flash Messages --}}
        @if(session()->has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session()->has('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-800">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session()->has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Bank Accounts Selection --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 {{ $parametersApplied ? 'bg-gray-50' : '' }}">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-semibold">Bank Accounts for Disbursement</h3>
                <div class="flex items-center space-x-3">
                    @if($parametersApplied)
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                        Parameters Applied
                    </span>
                    @endif
                    @if($selectedBankMirror)
                    <span class="text-xs text-red-600 font-medium">
                        Selected: {{ $selectedBankMirror }}
                    </span>
                    @endif
                </div>
            </div>
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
                    <tr class="border-b {{ $selectedBankMirror == $account['mirror_account'] ? 'bg-red-50' : '' }}">
                        <td class="py-1">{{ $account['name'] }}</td>
                        <td class="py-1 text-xs font-mono">{{ $account['mirror_account'] }}</td>
                        <td class="py-1 text-right {{ $account['can_disburse'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($account['balance'], 2) }}
                        </td>
                        <td class="py-1 text-center">
                            {{ $account['can_disburse'] ? '✓' : '✗' }}
                        </td>
                        <td class="py-1 text-center">
                            <input type="radio" 
                                   name="selected_bank" 
                                   value="{{ $account['mirror_account'] }}" 
                                   wire:model.live="selectedBankMirror"
                                   @if($selectedBankMirror == $account['mirror_account']) checked @endif
                                   @if(!$account['can_disburse'] || $parametersApplied) disabled @endif
                                   class="cursor-pointer focus:ring-2 focus:ring-red-500 {{ $parametersApplied ? 'opacity-60' : '' }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Complete Journal Entries Table --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Complete Journal Entries (Double-Entry Bookkeeping)
                </h3>
                <div class="flex space-x-3">
                    {{-- Disbursement Method Toggle --}}
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Method:</label>
                        <select wire:model.live="disbursementMethod" 
                                @if($parametersApplied) disabled @endif
                                class="px-2 py-1 border border-gray-300 rounded text-sm {{ $parametersApplied ? 'bg-gray-100 opacity-60' : '' }}">
                            <option value="gross">Gross (Separate Deductions)</option>
                            <option value="net">Net (Combined Entry)</option>
                        </select>
                    </div>
                    <button wire:click="toggleDetailedEntries" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-sm hover:bg-indigo-200 transition">
                        {{ $showDetailedEntries ? 'Hide' : 'Show' }} Details
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Step</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Number</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                            @if($showDetailedEntries)
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Account Balance</th>
                            @endif
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $journalEntries = [];
                            $entryNumber = 1;
                            $totalDebits = 0;
                            $totalCredits = 0;
                            
                            // Function to get account name and balance from database
                            function getAccountInfo($accountNumber, $fallbackName = null) {
                                $account = DB::table('accounts')->where('account_number', $accountNumber)->first();
                                if ($account) {
                                    return [
                                        'name' => $account->account_name,
                                        'balance' => $account->balance ?? 0
                                    ];
                                }
                                
                                // If not found, it might be a bank mirror account - use fallback name
                                return [
                                    'name' => $fallbackName ?: 'Bank Mirror Account - ' . $accountNumber,
                                    'balance' => 0
                                ];
                            }
                            
                            // Use selected bank or default to first available
                            $selectedBank = null;
                            if ($selectedBankMirror) {
                                foreach ($bankAccounts as $account) {
                                    if ($account['mirror_account'] == $selectedBankMirror) {
                                        $selectedBank = $account;
                                        break;
                                    }
                                }
                            }
                            if (!$selectedBank && !empty($bankAccounts)) {
                                $selectedBank = $bankAccounts[0];
                            }
                            
                            $selectedBankMirrorAccount = $selectedBank['mirror_account'] ?? '0101100010001010';
                            $selectedBankBalance = $selectedBank['balance'] ?? 0;
                            $runningBankBalance = $selectedBankBalance;
                            
                            // Get bank account info
                            $bankAccountInfo = getAccountInfo($selectedBankMirrorAccount, $selectedBank['name'] ?? null);
                            
                            // For bank accounts, use the actual bank balance, not the GL balance
                            $actualBankBalance = $selectedBank['balance'] ?? $bankAccountInfo['balance'];
                            
                            // Initialize running balances for all accounts
                            $runningBalances = [
                                $selectedBankMirrorAccount => $actualBankBalance
                            ];
                            
                            // Helper function to get and update running balance
                            function updateRunningBalance(&$runningBalances, $accountNumber, $debitAmount, $creditAmount, $initialBalance = 0) {
                                if (!isset($runningBalances[$accountNumber])) {
                                    $runningBalances[$accountNumber] = $initialBalance;
                                }
                                
                                // Determine account type based on account number prefix
                                // Assets (1xxx, 01011xxxxx): Normal debit balance
                                // Liabilities (2xxx, 01012xxxxx): Normal credit balance  
                                // Equity (3xxx, 01013xxxxx): Normal credit balance
                                // Income (4xxx, 01014xxxxx): Normal credit balance
                                // Expenses (5xxx, 01015xxxxx): Normal debit balance
                                
                                $isAssetOrExpense = false;
                                if (substr($accountNumber, 0, 1) === '1' || substr($accountNumber, 0, 1) === '5') {
                                    $isAssetOrExpense = true;
                                } elseif (substr($accountNumber, 0, 5) === '01011' || substr($accountNumber, 0, 5) === '01015') {
                                    $isAssetOrExpense = true;
                                } elseif (strpos($accountNumber, 'BUS') === 0) {
                                    // Loan accounts are assets
                                    $isAssetOrExpense = true;
                                }
                                
                                if ($isAssetOrExpense) {
                                    // Assets and Expenses: Debits increase, Credits decrease
                                    $runningBalances[$accountNumber] += ($debitAmount - $creditAmount);
                                } else {
                                    // Liabilities, Equity, Income: Credits increase, Debits decrease
                                    $runningBalances[$accountNumber] += ($creditAmount - $debitAmount);
                                }
                                
                                return $runningBalances[$accountNumber];
                            }
                            
                            // Check disbursement method
                            $isNetMethod = isset($disbursementMethod) && $disbursementMethod === 'net';
                            
                            if ($isNetMethod) {
                                // NET DISBURSEMENT METHOD - Combined entry for net amount
                                $netAmount = ($loan->principle ?? 0) 
                                    - ($deductionEntries['charges']['total'] ?? 0)
                                    - ($deductionEntries['insurance']['total'] ?? 0)
                                    - ($deductionEntries['first_interest']['total'] ?? 0)
                                    - ($deductionEntries['top_up']['total'] ?? 0);
                                
                                // STEP 1: LOAN DISBURSEMENT (NET METHOD)
                                // Entry 1: Debit Loan Account for full principal
                                $loanAccountNumber = $loan->loan_account_number ?? 'BUS202596153';
                                $loanAccountInfo = getAccountInfo($loanAccountNumber);
                                $entry1Override = $this->getOverriddenAccount(0);
                                $entry1Account = $entry1Override ? $entry1Override['account'] : $loanAccountNumber;
                                $entry1Name = $entry1Override ? $entry1Override['account_name'] : ($loanAccountInfo['name'] ?: 'Loan Account - ' . $loan->loan_id);
                                
                                $journalEntries[] = [
                                    'step' => '1',
                                    'entry_no' => $entryNumber++,
                                    'account' => $entry1Account,
                                    'account_name' => $entry1Name,
                                    'description' => 'Loan Receivable (Asset) - Net Method',
                                    'debit' => $loan->principle ?? 0,
                                    'credit' => 0,
                                    'account_balance' => $loanAccountInfo['balance'] + ($loan->principle ?? 0),
                                    'type' => 'disbursement'
                                ];
                                $totalDebits += $loan->principle ?? 0;
                                
                                // Entry 2: Credit Bank for NET amount only
                                $entry2Override = $this->getOverriddenAccount(1);
                                $entry2Account = $entry2Override ? $entry2Override['account'] : $selectedBankMirrorAccount;
                                $entry2Name = $entry2Override ? $entry2Override['account_name'] : $bankAccountInfo['name'];
                                
                                $journalEntries[] = [
                                    'step' => '1',
                                    'entry_no' => $entryNumber++,
                                    'account' => $entry2Account,
                                    'account_name' => $entry2Name,
                                    'description' => 'Net Cash Disbursement (After Deductions)',
                                    'debit' => 0,
                                    'credit' => $netAmount,
                                    'account_balance' => $actualBankBalance - $netAmount,
                                    'type' => 'disbursement'
                                ];
                                $totalCredits += $netAmount;
                                $runningBankBalance -= $netAmount;
                                
                                // STEP 2: DIRECT INCOME RECOGNITION (NET METHOD)
                                // Entry 3: Credit Processing Fees Income
                                if(isset($deductionEntries['charges']['total']) && $deductionEntries['charges']['total'] > 0) {
                                    $chargesAccount = $loan->charge_account_number ?? '0101400040004110';
                                    $chargesAccountInfo = getAccountInfo($chargesAccount);
                                    $entry3Override = $this->getOverriddenAccount(3);
                                    $journalEntries[] = [
                                        'step' => '2',
                                        'entry_no' => $entryNumber++,
                                        'account' => $entry3Override ? $entry3Override['account'] : $chargesAccount,
                                        'account_name' => $entry3Override ? $entry3Override['account_name'] : ($chargesAccountInfo['name'] ?: 'PROCESSING FEES INCOME'),
                                        'description' => 'Processing Fees (Direct to Income)',
                                        'debit' => 0,
                                        'credit' => $deductionEntries['charges']['total'],
                                        'account_balance' => $chargesAccountInfo['balance'] + $deductionEntries['charges']['total'],
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $deductionEntries['charges']['total'];
                                }
                                
                                // Entry 4: Credit Insurance Income
                                if(isset($deductionEntries['insurance']['total']) && $deductionEntries['insurance']['total'] > 0) {
                                    $insuranceAccount = $loan->insurance_account_number ?? '0101400041004111';
                                    $insuranceAccountInfo = getAccountInfo($insuranceAccount);
                                    $entry4Override = $this->getOverriddenAccount(5);
                                    $journalEntries[] = [
                                        'step' => '2',
                                        'entry_no' => $entryNumber++,
                                        'account' => $entry4Override ? $entry4Override['account'] : $insuranceAccount,
                                        'account_name' => $entry4Override ? $entry4Override['account_name'] : ($insuranceAccountInfo['name'] ?: 'INSURANCE PREMIUM INCOME'),
                                        'description' => 'Insurance Premium (Direct to Income)',
                                        'debit' => 0,
                                        'credit' => $deductionEntries['insurance']['total'],
                                        'account_balance' => $insuranceAccountInfo['balance'] + $deductionEntries['insurance']['total'],
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $deductionEntries['insurance']['total'];
                                }
                                
                                // Entry 5: Credit Interest Income
                                if(isset($deductionEntries['first_interest']['total']) && $deductionEntries['first_interest']['total'] > 0) {
                                    $interestAccount = $loan->interest_account_number ?? '0101400040004010';
                                    $interestAccountInfo = getAccountInfo($interestAccount);
                                    $entry5Override = $this->getOverriddenAccount(7);
                                    $journalEntries[] = [
                                        'step' => '2',
                                        'entry_no' => $entryNumber++,
                                        'account' => $entry5Override ? $entry5Override['account'] : $interestAccount,
                                        'account_name' => $entry5Override ? $entry5Override['account_name'] : ($interestAccountInfo['name'] ?: 'INTEREST ON LOANS INCOME'),
                                        'description' => 'First Interest (' . ($deductionEntries['first_interest']['days'] ?? 0) . ' days - Direct to Income)',
                                        'debit' => 0,
                                        'credit' => $deductionEntries['first_interest']['total'],
                                        'account_balance' => $interestAccountInfo['balance'] + $deductionEntries['first_interest']['total'],
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $deductionEntries['first_interest']['total'];
                                }
                                
                            } else {
                                // GROSS DISBURSEMENT METHOD - CORRECTED FLOW
                                // ONE DEBIT to Loan Account, MULTIPLE CREDITS to other accounts

                                // Calculate all deductions
                                $totalDeductions = ($deductionEntries['charges']['total'] ?? 0)
                                    + ($deductionEntries['insurance']['total'] ?? 0)
                                    + ($deductionEntries['first_interest']['total'] ?? 0);

                                $topUpAmount = $deductionEntries['top_up']['total'] ?? 0;
                                $netBankDisbursement = ($loan->principle ?? 0) - $totalDeductions - $topUpAmount;

                                // STEP 1: SINGLE DEBIT ENTRY
                                // Entry 1: Debit Loan Account (asset created) for full principal
                                $loanAccountNumber = $loan->loan_account_number ?? 'BUS202596153';
                                $loanAccountInfo = getAccountInfo($loanAccountNumber);
                                $entry1Override = $this->getOverriddenAccount(0);
                                $entry1Account = $entry1Override ? $entry1Override['account'] : $loanAccountNumber;
                                $entry1Name = $entry1Override ? $entry1Override['account_name'] : ($loanAccountInfo['name'] ?: 'Loan Account - ' . $loan->loan_id);

                                $debitAmount = $loan->principle ?? 0;
                                $newBalance = updateRunningBalance($runningBalances, $entry1Account, $debitAmount, 0, $loanAccountInfo['balance']);
                                $journalEntries[] = [
                                    'step' => '1',
                                    'entry_no' => $entryNumber++,
                                    'account' => $entry1Account,
                                    'account_name' => $entry1Name,
                                    'description' => 'Loan Receivable (Asset) - Full Principal',
                                    'debit' => $debitAmount,
                                    'credit' => 0,
                                    'account_balance' => $newBalance,
                                    'type' => 'disbursement'
                                ];
                                $totalDebits += $debitAmount;

                                // STEP 2: MULTIPLE CREDIT ENTRIES
                                // Entry 2: Credit Bank Mirror Account (net cash disbursed)
                                $entry2Override = $this->getOverriddenAccount(1);
                                $entry2Account = $entry2Override ? $entry2Override['account'] : $selectedBankMirrorAccount;
                                $entry2Name = $entry2Override ? $entry2Override['account_name'] : $bankAccountInfo['name'];

                                $creditAmount = $netBankDisbursement;
                                $newBankBalance = updateRunningBalance($runningBalances, $entry2Account, 0, $creditAmount, $actualBankBalance);
                                $journalEntries[] = [
                                    'step' => '2',
                                    'entry_no' => $entryNumber++,
                                    'account' => $entry2Account,
                                    'account_name' => $entry2Name,
                                    'description' => 'Net Cash Disbursement (Principal - Deductions - Top-up)',
                                    'debit' => 0,
                                    'credit' => $creditAmount,
                                    'account_balance' => $newBankBalance,
                                    'type' => 'disbursement'
                                ];
                                $totalCredits += $creditAmount;
                                
                                // Entry 3: Credit Processing Charges Income (if applicable)
                                if(isset($deductionEntries['charges']['total']) && $deductionEntries['charges']['total'] > 0) {
                                    $chargesAccount = $loan->charge_account_number ?? '0101400041004120';
                                    $chargesAccountInfo = getAccountInfo($chargesAccount);
                                    $creditAmount = $deductionEntries['charges']['total'];
                                    $newChargesBalance = updateRunningBalance($runningBalances, $chargesAccount, 0, $creditAmount, $chargesAccountInfo['balance']);
                                    $journalEntries[] = [
                                        'step' => '3',
                                        'entry_no' => $entryNumber++,
                                        'account' => $chargesAccount,
                                        'account_name' => $chargesAccountInfo['name'] ?: 'LOAN PROCESSING FEES',
                                        'description' => 'Processing Charges Income',
                                        'debit' => 0,
                                        'credit' => $creditAmount,
                                        'account_balance' => $newChargesBalance,
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $creditAmount;
                                }

                                // Entry 4: Credit Insurance Income (if applicable)
                                if(isset($deductionEntries['insurance']['total']) && $deductionEntries['insurance']['total'] > 0) {
                                    $insuranceAccount = $loan->insurance_account_number ?? '0101400041004111';
                                    $insuranceAccountInfo = getAccountInfo($insuranceAccount);
                                    $entry6Override = $this->getOverriddenAccount(5);
                                    $creditAccount = $entry6Override ? $entry6Override['account'] : $insuranceAccount;
                                    $creditAmount = $deductionEntries['insurance']['total'];
                                    $newInsuranceBalance = updateRunningBalance($runningBalances, $creditAccount, 0, $creditAmount, $insuranceAccountInfo['balance']);

                                    $journalEntries[] = [
                                        'step' => '4',
                                        'entry_no' => $entryNumber++,
                                        'account' => $creditAccount,
                                        'account_name' => $entry6Override ? $entry6Override['account_name'] : ($insuranceAccountInfo['name'] ?: 'INSURANCE PREMIUM INCOME'),
                                        'description' => 'Insurance Premium Income',
                                        'debit' => 0,
                                        'credit' => $creditAmount,
                                        'account_balance' => $newInsuranceBalance,
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $creditAmount;
                                }

                                // Entry 5: Credit Interest Income (if applicable)
                                if(isset($deductionEntries['first_interest']['total']) && $deductionEntries['first_interest']['total'] > 0) {
                                    $interestAccount = $loan->interest_account_number ?? '0101400040004010';
                                    $interestAccountInfo = getAccountInfo($interestAccount);
                                    $creditAmount = $deductionEntries['first_interest']['total'];
                                    $newInterestBalance = updateRunningBalance($runningBalances, $interestAccount, 0, $creditAmount, $interestAccountInfo['balance']);

                                    $journalEntries[] = [
                                        'step' => '5',
                                        'entry_no' => $entryNumber++,
                                        'account' => $interestAccount,
                                        'account_name' => $interestAccountInfo['name'] ?: 'INTEREST ON LOANS INCOME',
                                        'description' => 'First Interest Income (' . ($deductionEntries['first_interest']['days'] ?? 0) . ' days)',
                                        'debit' => 0,
                                        'credit' => $creditAmount,
                                        'account_balance' => $newInterestBalance,
                                        'type' => 'income'
                                    ];
                                    $totalCredits += $creditAmount;
                                }

                                // Entry 6: Credit Original Loan Account (for Top-up loans)
                                if(isset($deductionEntries['top_up']) && $deductionEntries['top_up']['total'] > 0) {
                                    $originalLoanAccount = $deductionEntries['top_up']['loan_account'] ?? null;
                                    $originalLoanAccountInfo = $originalLoanAccount ? getAccountInfo($originalLoanAccount) : ['name' => 'Original Loan Account', 'balance' => 0];
                                    $creditAmount = $deductionEntries['top_up']['total'];
                                    $newOriginalLoanBalance = updateRunningBalance($runningBalances, $originalLoanAccount, 0, $creditAmount, $originalLoanAccountInfo['balance']);

                                    $journalEntries[] = [
                                        'step' => '6',
                                        'entry_no' => $entryNumber++,
                                        'account' => $originalLoanAccount,
                                        'account_name' => $originalLoanAccountInfo['name'] ?: 'Original Loan Account',
                                        'description' => 'Top-up Payment to Original Loan (Settlement/Reduction)',
                                        'debit' => 0,
                                        'credit' => $creditAmount,
                                        'account_balance' => $newOriginalLoanBalance,
                                        'type' => 'topup'
                                    ];
                                    $totalCredits += $creditAmount;
                                }
                            } // End of GROSS METHOD block
                            
                            // Calculate net effect values for summary
                            $netCashOutflow = isset($runningBalances[$selectedBankMirrorAccount]) 
                                ? $runningBalances[$selectedBankMirrorAccount] - $actualBankBalance
                                : 0;
                            
                            // Calculate total deductions
                            $totalDeductions = ($deductionEntries['charges']['total'] ?? 0)
                                + ($deductionEntries['insurance']['total'] ?? 0)
                                + ($deductionEntries['first_interest']['total'] ?? 0)
                                + ($deductionEntries['top_up']['total'] ?? 0);
                            
                            $clientReceives = ($loan->principle ?? 0) - $totalDeductions;
                        @endphp
                        
                        @foreach($journalEntries as $index => $entry)
                        <tr class="{{ $entry['type'] === 'topup' ? 'bg-red-100 border-l-4 border-red-500' : ($entry['type'] === 'income' ? 'bg-green-50' : ($entry['type'] === 'disbursement' ? 'bg-gray-50' : '')) }}">
                            <td class="px-3 py-2 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($entry['step'] === '1') bg-red-100 text-red-800
                                    @elseif($entry['step'] === '2') bg-gray-100 text-gray-800
                                    @elseif(in_array($entry['step'], ['3', '4', '5'])) bg-green-100 text-green-800
                                    @elseif($entry['step'] === '6') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    Step {{ $entry['step'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $entry['entry_no'] }}</td>
                            <td class="px-3 py-2 text-sm font-mono text-gray-700">{{ $entry['account'] }}</td>
                            <td class="px-3 py-2 text-sm text-gray-600">{{ $entry['account_name'] ?? 'Unknown Account' }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $entry['description'] }}</td>
                            <td class="px-3 py-2 text-sm text-right {{ $entry['debit'] > 0 ? 'font-semibold text-red-600' : 'text-gray-400' }}">
                                {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right {{ $entry['credit'] > 0 ? 'font-semibold text-green-600' : 'text-gray-400' }}">
                                {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                            </td>
                            @if($showDetailedEntries)
                            <td class="px-3 py-2 text-sm text-right text-gray-600">
                                {{ number_format($entry['account_balance'] ?? 0, 2) }}
                            </td>
                            @endif
                            <td class="px-3 py-2 text-center">
                                @if(!$parametersApplied)
                                <button wire:click="openEditAccountModal({{ $index }}, '{{ $entry['account'] }}', '{{ $entry['type'] }}')" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                        title="Edit Account">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                @else
                                <span class="text-gray-400" title="Parameters Applied - Edit Disabled">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        
                        {{-- Totals Row --}}
                        <tr class="bg-gray-100 font-semibold">
                            <td colspan="5" class="px-3 py-3 text-sm text-right">TOTALS</td>
                            <td class="px-3 py-3 text-sm text-right text-red-600">{{ number_format($totalDebits, 2) }}</td>
                            <td class="px-3 py-3 text-sm text-right text-green-600">{{ number_format($totalCredits, 2) }}</td>
                            @if($showDetailedEntries)
                            <td class="px-3 py-3 text-sm text-right {{ $totalDebits === $totalCredits ? 'text-green-600' : 'text-red-600' }}">
                                {{ $totalDebits === $totalCredits ? 'BALANCED' : 'UNBALANCED: ' . number_format(abs($totalDebits - $totalCredits), 2) }}
                            </td>
                            @endif
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Balance Check --}}
            @if($totalDebits === $totalCredits)
            <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-3 flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-green-800">Journal entries are balanced. Total Debits = Total Credits = {{ number_format($totalDebits, 2) }}</p>
            </div>
            @else
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3 flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-red-800">Journal entries are not balanced. Difference: {{ number_format(abs($totalDebits - $totalCredits), 2) }}</p>
            </div>
            @endif

            {{-- Net Effect Summary --}}
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-red-900 mb-2">Net Effect Summary</h4>
                <div class="grid grid-cols-{{ isset($deductionEntries['top_up']) && $deductionEntries['top_up']['total'] > 0 ? '4' : '3' }} gap-4 text-sm">
                    <div>
                        <p class="text-red-700">Bank Mirror Account:</p>
                        <p class="font-semibold text-red-600">{{ number_format($netCashOutflow ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-600">Net cash outflow</p>
                    </div>
                    <div>
                        <p class="text-red-700">Client Receives:</p>
                        <p class="font-semibold text-green-600">{{ number_format($clientReceives ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-600">After all deductions{{ isset($deductionEntries['top_up']) && $deductionEntries['top_up']['total'] > 0 ? ' & top-up' : '' }}</p>
                    </div>
                    @if(isset($deductionEntries['top_up']) && $deductionEntries['top_up']['total'] > 0)
                    <div class="bg-red-100 rounded px-2 py-1">
                        <p class="text-red-800 font-semibold">Top-up Payment:</p>
                        <p class="font-bold text-red-900">{{ number_format($deductionEntries['top_up']['total'], 2) }}</p>
                        <p class="text-xs text-red-700">Paid to original loan</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-red-700">New Loan Balance:</p>
                        <p class="font-semibold text-red-600">{{ number_format($loan->principle ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-600">Full loan amount</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Apply/Edit Parameters Button --}}
        <div class="flex justify-end">
            @if(!$parametersApplied)
                <button wire:click="applyParameters"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                    <span wire:loading wire:target="applyParameters" class="mr-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <svg class="w-4 h-4 mr-2" wire:loading.remove wire:target="applyParameters" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="applyParameters">Apply Parameters</span>
                    <span wire:loading wire:target="applyParameters">Processing...</span>
                </button>
            @else
                <button wire:click="editParameters" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Parameters
                </button>
            @endif
        </div>
        
        {{-- Account Edit Modal --}}
        @if($showEditAccountModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEditAccountModal"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Edit Account Selection</h3>
                            <button wire:click="closeEditAccountModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            {{-- Current Account Info --}}
                            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                                <p class="text-sm text-gray-600">Current Account:</p>
                                <p class="font-mono text-sm">{{ $editingAccount }}</p>
                                <p class="text-sm text-gray-700">{{ $editingAccountName }}</p>
                                <p class="text-xs text-gray-500 mt-1">Entry Type: {{ ucfirst($editingEntryType) }}</p>
                            </div>

                            {{-- Search Accounts --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Search Account
                                </label>
                                <input type="text" 
                                       wire:model.live="accountSearchQuery"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Search by account number or name...">
                            </div>

                            {{-- Account Type Filter --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Filter by Account Type
                                </label>
                                <select wire:model.live="selectedAccountType" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">All Accounts</option>
                                    <option value="asset">Asset Accounts</option>
                                    <option value="liability">Liability Accounts</option>
                                    <option value="income">Income Accounts</option>
                                    <option value="expense">Expense Accounts</option>
                                    <option value="equity">Equity Accounts</option>
                                </select>
                            </div>

                            {{-- Accounts List --}}
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account Number</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account Name</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Select</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($availableAccounts as $account)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-sm font-mono text-gray-700">{{ $account['account_number'] }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-700">{{ $account['account_name'] }}</td>
                                            <td class="px-3 py-2 text-sm text-right text-gray-700">{{ number_format($account['balance'], 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                <button wire:click="selectAccount('{{ $account['account_number'] }}')"
                                                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Selected Account Preview --}}
                            @if($selectedNewAccount)
                            <div class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                                <p class="text-sm font-medium text-indigo-900">Selected Account:</p>
                                <p class="text-sm font-mono text-indigo-700">{{ $selectedNewAccount }}</p>
                                <p class="text-sm text-indigo-700">{{ $selectedNewAccountName }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                                wire:click="saveAccountChange"
                                @if(!$selectedNewAccount) disabled @endif
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Save Changes
                        </button>
                        <button type="button"
                                wire:click="closeEditAccountModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
    </div>
</div>