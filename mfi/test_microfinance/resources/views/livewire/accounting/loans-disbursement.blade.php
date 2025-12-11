<div class="min-h-screen p-2 sm:p-4 md:p-6 lg:p-8 w-full max-w-full overflow-x-hidden" style="margin: 0;">
    
  

    <!-- Main Content -->
    <div class="w-full">
        
        <!-- Loan Selection Section -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-100">
            <div class="p-3 sm:p-4 md:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Select Loan to Disburse</h2>
                    <div class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">
                        {{ $loans->count() }} Loans
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="relative mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.debounce.300ms="searchTerm"
                           placeholder="Search loans by client name or ID..." 
                           class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                </div>

                <!-- Loan List -->
                <div class="space-y-2 max-h-96 sm:max-h-[500px] overflow-y-auto">
                    @forelse ($loans as $loan)
                        @php
                            $client = \Illuminate\Support\Facades\DB::table('clients')->where('client_number', $loan->client_number)->first();
                            $clientName = $client ? $client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name : 'Unknown Client';
                        @endphp

                        <div wire:click="loanSelected({{ $loan->id }})"
                             wire:key="loan-{{ $loan->id }}"
                             class="relative group cursor-pointer transition-all duration-200 hover:bg-red-50 border border-gray-200 rounded-lg p-3 sm:p-4
                                    @if ($this->tab_id == $loan->id) bg-red-50 border-red-500 ring-2 ring-red-200 @endif">

                            <div class="flex items-start space-x-3 sm:space-x-4">
                                <!-- Client Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center text-white font-semibold text-base sm:text-lg">
                                        {{ strtoupper(substr($clientName, 0, 1)) }}
                                    </div>
                                </div>

                                <!-- Loan Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 truncate group-hover:text-red-600 transition-colors">
                                            {{ $clientName }}
                                        </h3>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                            {{ $loan->loan_type_2 }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Loan Amount:</span>
                                            <span class="font-semibold text-gray-900">{{ number_format($loan->approved_loan_value, 2) }} TZS</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500">Client ID:</span>
                                            <span class="font-mono text-gray-900">{{ $loan->client_number }}</span>
                                        </div>
                                    </div>

                                    <!-- Progress Indicator -->
                                    <div class="mt-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="bg-red-500 h-2 rounded-full" style="width: 100%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 font-medium">Ready for Disbursement</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selection Indicator -->
                                <div class="flex-shrink-0">
                                    @if ($this->tab_id == $loan->id)
                                        <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 border-2 border-gray-300 rounded-full group-hover:border-red-400 transition-colors"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 sm:p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Disbursements</h3>
                            <p class="text-sm text-gray-500">All approved loans have been processed or are awaiting approval.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Selected Loan Details -->
    @if($this->tab_id)
        @php
            $loanID = $this->tab_id;
            $loan = DB::table('loans')->find($loanID);
            $member = null;
            $product = null;
            
            if ($loan) {
                $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
                $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
            }
            
            // Get bank accounts for disbursement
            $bankAccounts = DB::table('bank_accounts')
                ->where('status', 'ACTIVE')
                ->orderBy('bank_name', 'asc')
                ->get();
            
            // Get selected account details for balance check
            $selectedAccount = null;
            if ($this->bank_account) {
                $selectedAccount = $bankAccounts->firstWhere('internal_mirror_account_number', $this->bank_account);
            }
            
            // Calculate total deductions
            $firstInterestAmount = $loan->future_interest ?? 0;
            
            // Get top-up loan amount
            $topUpAmount = 0;
            if (in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
                // Priority 1: Get from top_up_amount field directly
                if (isset($loan->top_up_amount) && $loan->top_up_amount > 0) {
                    $topUpAmount = abs($loan->top_up_amount);
                }
                // Priority 2: Calculate from top_up_loan_id account balance
                elseif (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
                    $originalLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
                    if ($originalLoan && $originalLoan->loan_account_number) {
                        $account = DB::table('accounts')->where('account_number', $originalLoan->loan_account_number)->first();
                        if ($account) {
                            $topUpAmount = abs($account->balance ?? 0);
                        }
                    }
                }
                // Priority 3: Try assessment data
                elseif (isset($loan->assessment_data) && $loan->assessment_data) {
                    $assessmentData = json_decode($loan->assessment_data, true);
                    if (isset($assessmentData['top_up_amount']) && $assessmentData['top_up_amount'] > 0) {
                        $topUpAmount = abs($assessmentData['top_up_amount']);
                    }
                }
            } else {
                // For non-top-up loans, use the old logic
                if ($loan->selectedLoan) {
                    $topUpLoan = DB::table('loans')->where('id', $loan->selectedLoan)->first();
                    if ($topUpLoan) {
                        $topUpAmount = $topUpLoan->amount_to_be_credited ?? 0;
                    }
                }
            }
            
            // Get closed loan balance
            $closedLoanBalance = 0;
            if ($loan->loan_account_number) {
                $closedLoanBalance = DB::table('sub_accounts')
                    ->where('account_number', $loan->loan_account_number)
                    ->value('balance') ?? 0;
            }
            
            // Get outside loan settlements
            $outsideSettlements = 0;
            $settledLoans = DB::table('settled_loans')
                ->where('loan_id', $loan->id)
                ->where('is_selected', true)
                ->get();
            foreach ($settledLoans as $settledLoan) {
                $outsideSettlements += $settledLoan->amount ?? 0;
            }
            
            // Calculate total deductions
            $totalDeductions = $this->totalCharges + $this->totalInsurance + $firstInterestAmount + $topUpAmount + $closedLoanBalance + $outsideSettlements;
            
            // Calculate net disbursement amount
            $netDisbursementAmount = $loan->approved_loan_value - $totalDeductions;
            
            $hasInsufficientFunds = $selectedAccount && $selectedAccount->current_balance < $netDisbursementAmount;
            $canDisburse = $this->bank_account && !$hasInsufficientFunds;
            
            // Validation checks for important data
            $validationErrors = [];
            
            if ($loan->pay_method === 'internal_transfer') {
                if (empty($member->account_number)) {
                    $validationErrors[] = 'Member NBC account number is missing. Please update member profile.';
                }
                if (empty($this->memberAccountHolderName)) {
                    $validationErrors[] = 'Account holder name is required for internal transfer.';
                }
            } elseif ($loan->pay_method === 'tips_mno') {
                if (empty($member->phone_number)) {
                    $validationErrors[] = 'Member phone number is missing. Please update member profile.';
                }
                if (empty($this->memberMnoProvider)) {
                    $validationErrors[] = 'MNO provider is required for mobile money transfer.';
                }
                if (empty($this->memberWalletHolderName)) {
                    $validationErrors[] = 'Wallet holder name is required for mobile money transfer.';
                }
            } elseif ($loan->pay_method === 'tips_bank') {
                if (empty($member->account_number)) {
                    $validationErrors[] = 'Member bank account number is missing. Please update member profile.';
                }
                if (empty($this->memberBankCode)) {
                    $validationErrors[] = 'Bank selection is required for bank transfer.';
                }
                if (empty($this->memberBankAccountHolderName)) {
                    $validationErrors[] = 'Bank account holder name is required for bank transfer.';
                }
            } elseif ($loan->pay_method === 'cash') {
                $memberDepositAccounts = DB::table('accounts')
                    ->where('client_number', $loan->client_number)
                    ->where('product_number', '3000')
                    ->where('status', 'ACTIVE')
                    ->get();
                    
                if ($memberDepositAccounts->count() === 0) {
                    $validationErrors[] = 'Member has no active deposit accounts. Please create a deposit account first.';
                } elseif (empty($this->selectedDepositAccount)) {
                    $validationErrors[] = 'Please select a deposit account for cash disbursement.';
                }
            }
            
            $hasValidationErrors = count($validationErrors) > 0;
        @endphp

        <!-- Disbursement Modal -->
        <style nonce="{{ csp_nonce() }}">
            .modal-scrollable {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e0 #f7fafc;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }
            
            .modal-scrollable::-webkit-scrollbar {
                width: 8px;
            }
            
            .modal-scrollable::-webkit-scrollbar-track {
                background: #f7fafc;
                border-radius: 4px;
            }
            
            .modal-scrollable::-webkit-scrollbar-thumb {
                background: #cbd5e0;
                border-radius: 4px;
                border: 1px solid #e2e8f0;
            }
            
            .modal-scrollable::-webkit-scrollbar-thumb:hover {
                background: #a0aec0;
            }
            
            /* Ensure modal container doesn't overflow viewport */
            .modal-container {
                max-height: 100vh;
                overflow-y: auto;
                padding: 1rem;
            }
            
            /* Smooth transitions for modal content */
            .modal-content {
                transition: all 0.3s ease-in-out;
            }
            
            /* Better mobile scrolling */
            @media (max-width: 768px) {
                .modal-scrollable {
                    -webkit-overflow-scrolling: touch;
                    scroll-behavior: smooth;
                }
            }
        </style>
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto" 
             @if(!$isProcessing) wire:click="closeModal" @endif>
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] z-50 border-4 border-red-900 flex flex-col relative overflow-hidden my-4" wire:click.stop>
                
                <!-- Processing Overlay -->
                @if($isProcessing)
                    <div class="absolute inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center rounded-lg">
                        <div class="text-center">
                            <div class="w-12 h-12 border-4 border-red-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Processing Loan Disbursement</h3>
                            <p class="text-sm text-gray-600">Please wait while we process your request...</p>
                        </div>
                    </div>
                @endif
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Loan Disbursement Details</h3>
                                            <button wire:click="closeModal" 
                                @if($isProcessing) disabled @endif
                                class="transition-colors {{ $isProcessing ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-gray-600' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                </div>

                <!-- Modal Body - Scrollable -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 modal-scrollable" style="max-height: calc(90vh - 200px);">
                                    <!-- Comprehensive Error Display Section -->
                @php
                    $allErrors = $this->getAllErrors();
                    $hasErrors = count($allErrors) > 0;
                @endphp

                @if($hasErrors)
                    <div class="mb-6 space-y-4">
                        <!-- Group errors by severity -->
                        @php
                            $errorGroups = [
                                'error' => array_filter($allErrors, fn($e) => $e['severity'] === 'error'),
                                'warning' => array_filter($allErrors, fn($e) => $e['severity'] === 'warning'),
                                'info' => array_filter($allErrors, fn($e) => $e['severity'] === 'info'),
                                'success' => array_filter($allErrors, fn($e) => $e['severity'] === 'success')
                            ];
                        @endphp

                        <!-- Error Messages -->
                        @if(count($errorGroups['error']) > 0)
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-red-800 mb-2">Issues Found</h4>
                                        <div class="space-y-3">
                                            @foreach($errorGroups['error'] as $error)
                                                <div class="flex items-start">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                    <div class="flex-1">
                                                        <p class="text-sm text-red-700">{{ $error['message'] }}</p>
                                                        @if(isset($error['timestamp']))
                                                            <p class="text-xs text-red-500 mt-1">{{ $error['timestamp'] }}</p>
                                                        @endif
                                                        @if(isset($error['category']) || isset($error['service']) || isset($error['component']))
                                                            <p class="text-xs text-red-400 mt-1">
                                                                @if(isset($error['category'])) Category: {{ $error['category'] }} @endif
                                                                @if(isset($error['service'])) Service: {{ $error['service'] }} @endif
                                                                @if(isset($error['component'])) Component: {{ $error['component'] }} @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Warning Messages -->
                        @if(count($errorGroups['warning']) > 0)
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-yellow-800 mb-2">Warnings</h4>
                                        <div class="space-y-2">
                                            @foreach($errorGroups['warning'] as $warning)
                                                <div class="flex items-start">
                                                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                    <div class="flex-1">
                                                        <p class="text-sm text-yellow-700">{{ $warning['message'] }}</p>
                                                        @if(isset($warning['timestamp']))
                                                            <p class="text-xs text-yellow-500 mt-1">{{ $warning['timestamp'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Info Messages -->
                        @if(count($errorGroups['info']) > 0)
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-red-800 mb-2">Information</h4>
                                        <div class="space-y-2">
                                            @foreach($errorGroups['info'] as $info)
                                                <div class="flex items-start">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                    <div class="flex-1">
                                                        <p class="text-sm text-red-700">{{ $info['message'] }}</p>
                                                        @if(isset($info['timestamp']))
                                                            <p class="text-xs text-red-500 mt-1">{{ $info['timestamp'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Success Messages -->
                        @if(count($errorGroups['success']) > 0)
                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-green-800 mb-2">Success</h4>
                                        <div class="space-y-2">
                                            @foreach($errorGroups['success'] as $success)
                                                <div class="flex items-start">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                    <div class="flex-1">
                                                        <p class="text-sm text-green-700">{{ $success['message'] }}</p>
                                                        @if(isset($success['timestamp']))
                                                            <p class="text-xs text-green-500 mt-1">{{ $success['timestamp'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Error Summary -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Summary:</span>
                                @if(count($errorGroups['error']) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ count($errorGroups['error']) }} Error{{ count($errorGroups['error']) > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if(count($errorGroups['warning']) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ count($errorGroups['warning']) }} Warning{{ count($errorGroups['warning']) > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if(count($errorGroups['info']) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ count($errorGroups['info']) }} Info
                                    </span>
                                @endif
                                @if(count($errorGroups['success']) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ count($errorGroups['success']) }} Success
                                    </span>
                                @endif
                            </div>
                                                    <button wire:click="clearAllErrors" 
                                @if($isProcessing) disabled @endif
                                class="text-xs underline {{ $isProcessing ? 'text-gray-300 cursor-not-allowed' : 'text-gray-500 hover:text-gray-700' }}">
                            Clear All
                        </button>
                        </div>
                    </div>
                @endif

                    <!-- Client & Loan Summary -->
                    <div class="mb-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                {{ strtoupper(substr($member ? $member->first_name : 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-gray-900">
                                    {{ $member ? $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name : 'Unknown Client' }}
                                </h4>
                                <p class="text-sm text-gray-600">ID: {{ $member ? $member->client_number : 'N/A' }} | {{ $member ? $member->phone_number : 'N/A' }}</p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $loan->loan_type_2 }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ ucfirst(str_replace('_', ' ', $loan->pay_method ?? '')) }}
                                    </span>
                                </div>
                            </div>
                            <!-- Progress Indicator -->
                            <div class="text-right">
                                <div class="text-xs text-gray-500 mb-1">Disbursement Status</div>
                                <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500 rounded-full" style="width: 100%"></div>
                                </div>
                                <div class="text-xs text-green-600 font-medium mt-1">Ready</div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Type Information -->
                    @if($loan)
                        @if(in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up']))
                            <!-- Top-Up Loan Information -->
                            @php
                                $originalTopupLoan = null;
                                $originalTopupProduct = null;
                                $topupOutstandingBalance = 0;
                                $topupDaysInArrears = 0;
                                $topupPenaltyAmount = 0;
                                
                                // Get original loan if top_up_loan_id exists
                                if (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
                                    $originalTopupLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
                                }
                                
                                if ($originalTopupLoan) {
                                    // Get original loan product
                                    $originalTopupProduct = DB::table('loan_sub_products')->where('sub_product_id', $originalTopupLoan->loan_sub_product)->first();
                                    
                                    // Get outstanding balance from account
                                    $topupAccount = DB::table('accounts')->where('account_number', $originalTopupLoan->loan_account_number)->first();
                                    $topupOutstandingBalance = $topupAccount ? abs($topupAccount->balance ?? 0) : 0;
                                    
                                    // Get days in arrears (from loan record)
                                    $topupDaysInArrears = $originalTopupLoan->days_in_arrears ?? 0;
                                }
                                
                                // Get penalty amount
                                $topupPenaltyAmount = $loan->top_up_penalty_amount ?? 0;
                                if ($topupPenaltyAmount == 0 && isset($topUpAmount) && $topUpAmount > 0) {
                                    // Calculate penalty if not set
                                    $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
                                    $productPenaltyPercentage = $product->penalty_value ?? 5.0;
                                    $topupPenaltyAmount = $topUpAmount * ($productPenaltyPercentage / 100);
                                }
                                
                                // Use the main topUpAmount variable for consistency
                                $topupAmount = $topUpAmount ?? 0;
                            @endphp
                            
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Topped-Up Loan Information
                                </h4>
                                
                                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                    <div class="grid grid-cols-4 gap-2 text-xs">
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Original Loan ID</div>
                                            <div class="font-semibold text-green-900">{{ $originalTopupLoan->loan_id ?? 'N/A' }}</div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Loan Product</div>
                                            <div class="font-semibold">{{ $originalTopupProduct->sub_product_name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Original Principle</div>
                                            <div class="font-semibold">{{ number_format((float)($originalTopupLoan->principle ?? 0), 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Interest Amount</div>
                                            <div class="font-semibold">{{ number_format((float)($originalTopupLoan->interest ?? 0), 2) }} TZS</div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Outstanding Balance</div>
                                            <div class="font-semibold text-red-600">{{ number_format($topupOutstandingBalance, 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Days in Arrears</div>
                                            <div class="font-semibold {{ $topupDaysInArrears > 30 ? 'text-red-600' : ($topupDaysInArrears > 7 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ $topupDaysInArrears }} days
                                            </div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded">
                                            <div class="text-gray-600">Penalty Amount</div>
                                            <div class="font-semibold text-orange-600">{{ number_format($topupPenaltyAmount, 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-green-200 rounded bg-green-100">
                                            <div class="text-gray-600 font-semibold">Total to Top-Up</div>
                                            <div class="font-semibold text-green-900">{{ number_format($topupAmount, 2) }} TZS</div>
                                        </div>
                                    </div>





                                </div>
                            </div>
                        @endif

                        @if(in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']))
                            <!-- Restructured Loan Information -->
                            @php
                                $originalRestructureLoan = null;
                                $originalRestructureProduct = null;
                                $restructureOutstandingBalance = 0;
                                $restructureDaysInArrears = 0;
                                $restructureArrears = 0;
                                
                                // Get original loan if restructure_loan_id exists
                                if (isset($loan->restructure_loan_id) && $loan->restructure_loan_id) {
                                    $originalRestructureLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
                                }
                                
                                if ($originalRestructureLoan) {
                                    // Get original loan product
                                    $originalRestructureProduct = DB::table('loan_sub_products')->where('sub_product_id', $originalRestructureLoan->loan_sub_product)->first();
                                    
                                    // Get outstanding balance from account
                                    $restructureAccount = DB::table('accounts')->where('account_number', $originalRestructureLoan->loan_account_number)->first();
                                    $restructureOutstandingBalance = $restructureAccount ? abs($restructureAccount->balance ?? 0) : 0;
                                    
                                    // Get days in arrears (from loan record)
                                    $restructureDaysInArrears = $originalRestructureLoan->days_in_arrears ?? 0;
                                    
                                    // Get arrears from loan schedules
                                    $restructureArrears = DB::table('loans_schedules')
                                        ->where('loan_id', $originalRestructureLoan->id)
                                        ->where('completion_status', '!=', 'ACTIVE')
                                        ->sum('amount_in_arrears') ?? 0;
                                }
                            @endphp
                            
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Restructured Loan Information
                                </h4>
                                
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <div class="grid grid-cols-4 gap-2 text-xs">
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Original Loan ID</div>
                                            <div class="font-semibold text-red-900">{{ $originalRestructureLoan->loan_id ?? 'N/A' }}</div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Loan Product</div>
                                            <div class="font-semibold">{{ $originalRestructureProduct->sub_product_name ?? 'N/A' }}</div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Original Principle</div>
                                            <div class="font-semibold">{{ number_format((float)($originalRestructureLoan->principle ?? 0), 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Interest Amount</div>
                                            <div class="font-semibold">{{ number_format((float)($originalRestructureLoan->interest ?? 0), 2) }} TZS</div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-4 gap-2 text-xs mt-2">
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Outstanding Balance</div>
                                            <div class="font-semibold text-red-600">{{ number_format($restructureOutstandingBalance, 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Days in Arrears</div>
                                            <div class="font-semibold {{ $restructureDaysInArrears > 30 ? 'text-red-600' : ($restructureDaysInArrears > 7 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ $restructureDaysInArrears }} days
                                            </div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded">
                                            <div class="text-gray-600">Amount in Arrears</div>
                                            <div class="font-semibold text-red-600">{{ number_format($restructureArrears, 2) }} TZS</div>
                                        </div>
                                        <div class="p-2 border border-red-200 rounded bg-red-100">
                                            <div class="text-gray-600 font-semibold">Total to Restructure</div>
                                            <div class="font-semibold text-red-900">{{ number_format($restructureOutstandingBalance + $restructureArrears, 2) }} TZS</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Loan Calculation Summary -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Loan Calculation Summary
                        </h4>
                        
                        <div class="bg-gradient-to-r from-indigo-50 to-red-50 rounded-lg p-4 border border-indigo-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column: Loan Details -->
                                <div>
                                    <h5 class="text-sm font-semibold text-indigo-800 mb-3">Loan Details</h5>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-indigo-600">Requested Amount:</span>
                                            <span class="font-semibold text-indigo-900">{{ number_format($loan->principle, 2) }} TZS</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-indigo-600">Approved Amount:</span>
                                            <span class="font-bold text-green-600">{{ number_format($loan->approved_loan_value, 2) }} TZS</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-indigo-600">Loan Term:</span>
                                            <span class="font-semibold text-indigo-900">{{ $loan->tenure ?? 12 }} months</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Deductions & Net Amount -->
                                <div>
                                    <h5 class="text-sm font-semibold text-indigo-800 mb-3">Deductions & Net Amount</h5>
                                    <div class="space-y-2 text-sm">
                                        <!-- Individual Charges -->
                                        @if(count($this->charges) > 0)
                                            <div class="border-l-2 border-red-400 pl-2">
                                                <div class="text-xs font-semibold text-red-700 mb-1">Charges & Fees:</div>
                                                @foreach($this->charges as $charge)
                                                    <div class="flex justify-between items-center group relative">
                                                        <span class="text-indigo-600 text-xs">{{ $charge['name'] }}:</span>
                                                        <span class="font-semibold text-red-600 text-xs">-{{ number_format($charge['computed_amount'] ?? $charge['amount'], 2) }} TZS
                                                            @if(isset($charge['cap_applied']))
                                                                <span class="text-xs text-purple-600 ml-1">({{ $charge['cap_applied'] }})</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                                <div class="flex justify-between border-t border-red-200 pt-1 mt-1">
                                                    <span class="text-indigo-700 font-semibold text-xs">Total Charges:</span>
                                                    <span class="font-bold text-red-600 text-xs">-{{ number_format($this->totalCharges, 2) }} TZS</span>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Individual Insurance -->
                                        @php
                                            $insuranceTenure = $loan->tenure ?? 12;
                                            if (in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])) {
                                                // Calculate insurance tenure for restructure loans
                                                $originalLoan = null;
                                                if (isset($loan->restructure_loan_id) && $loan->restructure_loan_id) {
                                                    $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
                                                }
                                                if ($originalLoan) {
                                                    $now = now();
                                                    $originalStartDate = $originalLoan->disbursement_date 
                                                        ? \Carbon\Carbon::parse($originalLoan->disbursement_date)
                                                        : \Carbon\Carbon::parse($originalLoan->created_at);
                                                    $originalEndDate = $originalStartDate->copy()->addMonths($originalLoan->tenure ?? 0);
                                                    $remainingDays = max(0, $now->diffInDays($originalEndDate, false));
                                                    $remainingMonths = max(0, ceil($remainingDays / 30));
                                                    $newTenure = (int)($loan->approved_term ?? 0);
                                                    $insuranceTenure = max(0, $newTenure - $remainingMonths);
                                                }
                                            }
                                        @endphp
                                        @if(count($this->insurance) > 0)
                                            <div class="border-l-2 border-red-400 pl-2 mt-2">
                                                <div class="text-xs font-semibold text-red-700 mb-1">
                                                    Insurance (Monthly × {{ $insuranceTenure }} months)
                                                    @if(in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring']))
                                                        <span class="text-orange-600">(Restructure: {{ $loan->approved_term ?? 0 }} - {{ $remainingMonths ?? 0 }} = {{ $insuranceTenure }} months)</span>
                                                    @endif
                                                </div>
                                                @foreach($this->insurance as $ins)
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-indigo-600 text-xs">
                                                            {{ $ins['name'] }}
                                                            @if($ins['value_type'] === 'percentage')
                                                                <span class="text-gray-500">({{ $ins['value'] }}%/mo)</span>
                                                            @endif
                                                        </span>
                                                        <span class="font-semibold text-red-600 text-xs">-{{ number_format($ins['computed_amount'] ?? $ins['amount'], 2) }} TZS</span>
                                                    </div>
                                                @endforeach
                                                <div class="flex justify-between border-t border-red-200 pt-1 mt-1">
                                                    <span class="text-indigo-700 font-semibold text-xs">Total Insurance:</span>
                                                    <span class="font-bold text-red-600 text-xs">-{{ number_format($this->totalInsurance, 2) }} TZS</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($firstInterestAmount > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">First Interest:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($firstInterestAmount, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        @php
                                            $topUpAmount = 0;
                                            if (in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
                                                // Priority 1: Get from top_up_amount field directly
                                                if (isset($loan->top_up_amount) && $loan->top_up_amount > 0) {
                                                    $topUpAmount = abs($loan->top_up_amount);
                                                }
                                                // Priority 2: Calculate from top_up_loan_id (for existing loans)
                                                elseif (isset($loan->top_up_loan_id) && $loan->top_up_loan_id) {
                                                    $topupLoan = DB::table('loans')->where('id', $loan->top_up_loan_id)->first();
                                                    if ($topupLoan && $topupLoan->loan_account_number) {
                                                        $topupAccount = DB::table('accounts')->where('account_number', $topupLoan->loan_account_number)->first();
                                                        if ($topupAccount) {
                                                            $topUpAmount = abs($topupAccount->balance ?? 0);
                                                        }
                                                    }
                                                }
                                                // Priority 3: Try assessment data
                                                elseif (isset($loan->assessment_data) && $loan->assessment_data) {
                                                    $assessmentData = json_decode($loan->assessment_data, true);
                                                    if (isset($assessmentData['top_up_amount']) && $assessmentData['top_up_amount'] > 0) {
                                                        $topUpAmount = abs($assessmentData['top_up_amount']);
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($topUpAmount > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">Top-Up Loan Balance:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($topUpAmount, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        @php
                                            $penaltyAmount = 0;
                                            if (in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
                                                // Priority 1: Get from top_up_penalty_amount field directly
                                                if (isset($loan->top_up_penalty_amount) && $loan->top_up_penalty_amount > 0) {
                                                    $penaltyAmount = abs($loan->top_up_penalty_amount);
                                                }
                                                // Priority 2: Calculate from top-up amount (5% penalty)
                                                elseif (isset($topUpAmount) && $topUpAmount > 0) {
                                                    $product = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
                                                    $penaltyPercentage = (float)($product->penalty_value ?? 5.0) / 100;
                                                    $penaltyAmount = $topUpAmount * $penaltyPercentage;
                                                }
                                            }
                                        @endphp
                                        @if($penaltyAmount > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">Early Settlement Penalty:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($penaltyAmount, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        @if($closedLoanBalance > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">Closed Loan Balance:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($closedLoanBalance, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        @if($outsideSettlements > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">Outside Loan Settlements:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($outsideSettlements, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        @php
                                            $restructuringAmount = 0;
                                            if (in_array($loan->loan_type_2 ?? '', ['Restructure', 'Restructuring'])) {
                                                // Priority 1: Get from restructure_amount field directly
                                                if (isset($loan->restructure_amount) && $loan->restructure_amount > 0) {
                                                    $restructuringAmount = abs($loan->restructure_amount);
                                                }
                                                // Priority 2: Calculate from restructure_loan_id (outstanding + arrears)
                                                elseif (isset($loan->restructure_loan_id) && $loan->restructure_loan_id) {
                                                    $originalLoan = DB::table('loans')->where('id', $loan->restructure_loan_id)->first();
                                                    if ($originalLoan) {
                                                        // Get outstanding balance from account
                                                        $outstandingBalance = 0;
                                                        if ($originalLoan->loan_account_number) {
                                                            $account = DB::table('accounts')->where('account_number', $originalLoan->loan_account_number)->first();
                                                            if ($account) {
                                                                $outstandingBalance = abs($account->balance ?? 0);
                                                            }
                                                        }
                                                        
                                                        // Get arrears from loan schedules
                                                        $arrears = DB::table('loans_schedules')
                                                            ->where('loan_id', $originalLoan->id)
                                                            ->where('completion_status', '!=', 'ACTIVE')
                                                            ->sum('amount_in_arrears') ?? 0;
                                                        
                                                        $restructuringAmount = $outstandingBalance + $arrears;
                                                    }
                                                }
                                                // Priority 3: Try assessment data
                                                elseif (isset($loan->assessment_data) && $loan->assessment_data) {
                                                    $assessmentData = json_decode($loan->assessment_data, true);
                                                    if (isset($assessmentData['restructure_amount']) && $assessmentData['restructure_amount'] > 0) {
                                                        $restructuringAmount = abs($assessmentData['restructure_amount']);
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($restructuringAmount > 0)
                                            <div class="flex justify-between">
                                                <span class="text-indigo-600">Restructuring Amount:</span>
                                                <span class="font-semibold text-red-600">-{{ number_format($restructuringAmount, 2) }} TZS</span>
                                            </div>
                                        @endif
                                        
                                        <div class="border-t border-indigo-200 pt-2 mt-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm font-semibold text-red-900">Total Deductions:</span>
                                                <span class="text-lg font-bold text-red-600">-{{ number_format($totalDeductions, 2) }} TZS</span>
                                            </div>
                                            <div class="flex justify-between mt-2">
                                                <span class="text-lg font-bold text-green-700">Net Disbursement:</span>
                                                <span class="text-xl font-bold text-green-800">{{ number_format($netDisbursementAmount, 2) }} TZS</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Configuration -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Payment Configuration
                        </h4>
                        
                        <div class="bg-teal-50 rounded-lg p-4 border border-teal-200">
                            <!-- Payment Method Display -->
                            <div class="mb-4 p-3 bg-white rounded-lg border border-teal-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 class="text-sm font-semibold text-teal-900">Payment Method</h5>
                                        <p class="text-sm text-teal-700">{{ ucfirst(str_replace('_', ' ', $loan->pay_method ?? '')) }}</p>
                                    </div>
                                    <span class="text-xs font-medium text-teal-800 px-2 py-1 bg-teal-100 rounded-full">
                                        {{ strtoupper($loan->pay_method ?? '') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Method Specific Configuration -->
                            @if($loan->pay_method === 'cash')
                                <!-- CASH Payment Method -->
                                <div>
                                    <label class="block text-sm font-medium text-teal-700 mb-2">Select Deposit Account</label>
                                    @php
                                        $memberDepositAccounts = DB::table('accounts')
                                            ->where('client_number', $loan->client_number)
                                            ->where('product_number', '3000')
                                            ->where('status', 'ACTIVE')
                                            ->get();
                                    @endphp
                                    
                                    @if($memberDepositAccounts->count() > 0)
                                        <select wire:model.defer="selectedDepositAccount" wire:change="checkValidationErrors" 
                                    @if($isProcessing) disabled @endif
                                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                           {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}">
                                            <option value="">Choose a deposit account...</option>
                                            @foreach($memberDepositAccounts as $account)
                                                <option value="{{ $account->account_number }}">
                                                    {{ $account->account_name }} ({{ $account->account_number }}) - {{ number_format($account->balance, 2) }} TZS
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                            <p class="text-sm text-yellow-700">No active deposit accounts found. Please create a deposit account first.</p>
                                        </div>
                                    @endif
                                </div>

                            @elseif($loan->pay_method === 'internal_transfer')
                                <!-- INTERNAL_TRANSFER Payment Method -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">NBC Account Number</label>
                                        <input type="text" wire:model.defer="memberNbcAccount" wire:change="checkValidationErrors" 
                                        class="w-full px-4 py-3 border border-teal-300 rounded-lg focus:ring-2 focus:ring-teal-500 
                                        focus:border-transparent text-sm" placeholder="Enter NBC account number" disabled>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Account Holder Name</label>
                                        <input type="text" wire:model.defer="memberAccountHolderName" wire:change="checkValidationErrors" 
                                        class="w-full px-4 py-3 border border-teal-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm" 
                                        placeholder="Enter account holder name" disabled>
                                    </div>
                                </div>

                            @elseif($loan->pay_method === 'tips_mno')
                                <!-- TIPS_MNO Payment Method -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Phone Number</label>
                                        <input type="text" wire:model.defer="memberPhoneNumber" wire:change="checkValidationErrors" 
                                               @if($isProcessing) disabled @endif
                                               class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                      {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}" 
                                               placeholder="Enter phone number">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">MNO Provider</label>
                                        <select wire:model.defer="memberMnoProvider" wire:change="checkValidationErrors" 
                                                @if($isProcessing) disabled @endif
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                       {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}">
                                            <option value="">Select MNO provider...</option>
                                            <option value="VMCASHIN">M-PESA</option>
                                            <option value="AMCASHIN">AIRTEL-MONEY</option>
                                            <option value="TPCASHIN">TIGO-PESA</option>
                                            <option value="HPCASHIN">HALLOTEL</option>
                                            <option value="APCASHIN">AZAMPESA</option>
                                            <option value="ZPCASHIN">EZYPESA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Wallet Holder Name</label>
                                        <input type="text" wire:model.defer="memberWalletHolderName" wire:change="checkValidationErrors" 
                                               @if($isProcessing) disabled @endif
                                               class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                      {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}" 
                                               placeholder="Enter wallet holder name">
                                    </div>
                                </div>

                            @elseif($loan->pay_method === 'tips_bank')
                                <!-- TIPS_BANK Payment Method -->
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Bank</label>
                                        <select wire:model.defer="memberBankCode" wire:change="checkValidationErrors" 
                                                @if($isProcessing) disabled @endif
                                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                       {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}">
                                            <option value="">Select bank...</option>
                                            @foreach($this->tipsBanks as $bank)
                                                <option value="{{ $bank['fspCode'] }}">{{ $bank['fspFullNme'] }} ({{ $bank['fspShortNme'] }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Bank Account Number</label>
                                        <input type="text" wire:model.defer="memberBankAccountNumber" wire:change="checkValidationErrors" 
                                               @if($isProcessing) disabled @endif
                                               class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                      {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}" 
                                               placeholder="Enter bank account number">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-teal-700">Account Holder Name</label>
                                        <input type="text" wire:model.defer="memberBankAccountHolderName" wire:change="checkValidationErrors" 
                                               @if($isProcessing) disabled @endif
                                               class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                      {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}" 
                                               placeholder="Enter account holder name">
                                    </div>
                                </div>
                            @endif

                            <!-- Disbursement Account Selection (for all methods except CASH) -->
                            @if($loan->pay_method !== 'cash')
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-teal-700 mb-2">Select Disbursement Account</label>
                                    <select wire:model.defer="bank_account" wire:change="checkValidationErrors" 
                                            @if($isProcessing) disabled @endif
                                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm
                                                   {{ $isProcessing ? 'border-gray-300 bg-gray-100 text-gray-500' : 'border-teal-300' }}">
                                        <option value="">Choose a bank account...</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->internal_mirror_account_number }}">
                                                {{ $account->bank_name }} - {{ $account->account_name }} ({{ $account->account_number }}) : {{ number_format($account->current_balance, 2) }} TZS
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Selected Account Balance Check -->
                                @if($selectedAccount)
                                    <div class="mt-4 bg-white rounded-lg p-4 border border-teal-200">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="text-lg font-semibold text-teal-900">{{ $selectedAccount->bank_name }}</h5>
                                            <span class="text-sm text-teal-500">{{ $selectedAccount->account_number }}</span>
                                        </div>
                                        
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-teal-600">Current Balance:</span>
                                                <span class="font-bold text-teal-900">{{ number_format($selectedAccount->current_balance, 2) }} TZS</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-teal-600">Loan Amount:</span>
                                                <span class="font-bold text-red-600">{{ number_format($netDisbursementAmount, 2) }} TZS</span>
                                            </div>
                                            <div class="flex justify-between border-t border-teal-200 pt-2">
                                                <span class="font-semibold text-teal-700">Remaining Balance:</span>
                                                <span class="font-bold {{ ($selectedAccount->current_balance - $netDisbursementAmount) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($selectedAccount->current_balance - $netDisbursementAmount, 2) }} TZS
                                                </span>
                                            </div>
                                        </div>

                                        @if($selectedAccount->current_balance < $netDisbursementAmount)
                                            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <div>
                                                        <h6 class="text-sm font-semibold text-red-800">Insufficient Funds</h6>
                                                        <p class="text-sm text-red-700">Shortfall: {{ number_format($netDisbursementAmount - $selectedAccount->current_balance, 2) }} TZS</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <div>
                                                        <h6 class="text-sm font-semibold text-green-800">Sufficient Funds</h6>
                                                        <p class="text-sm text-green-700">Ready to process disbursement</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Detailed Charges & Insurance (Collapsible) -->
                    @if($this->totalCharges > 0 || $this->totalInsurance > 0)
                        <div class="mb-6">
                            <details class="group">
                                <summary class="flex items-center cursor-pointer list-none hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        Detailed Charges & Insurance
                                        <span class="text-sm text-gray-500 ml-2">({{ count($this->charges) + count($this->insurance) }} items)</span>
                                        <svg class="w-4 h-4 ml-2 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </h4>
                                </summary>
                                
                                <div class="mt-4 space-y-3">
                                    <!-- Charges -->
                                    @if(count($this->charges) > 0)
                                        <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <h5 class="text-sm font-medium text-red-900">Charges ({{ count($this->charges) }})</h5>
                                                <div class="text-sm font-bold text-red-900">{{ number_format($this->totalCharges, 2) }} TZS</div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($this->charges as $charge)
                                                    <div class="flex justify-between text-xs bg-white p-2 rounded border border-red-100">
                                                        <span class="text-red-700 font-medium">{{ $charge['name'] }}</span>
                                                        <span class="text-red-900 font-semibold">{{ number_format($charge['computed_amount'] ?? $charge['amount'], 2) }} TZS</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Insurance -->
                                    @if(count($this->insurance) > 0)
                                        <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <h5 class="text-sm font-medium text-red-900">Insurance ({{ count($this->insurance) }})</h5>
                                                <div class="text-sm font-bold text-red-900">{{ number_format($this->totalInsurance, 2) }} TZS</div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($this->insurance as $ins)
                                                    <div class="flex justify-between text-xs bg-white p-2 rounded border border-red-100">
                                                        <span class="text-red-700 font-medium">{{ $ins['name'] }}</span>
                                                        <span class="text-red-900 font-semibold">{{ number_format($ins['computed_amount'] ?? $ins['amount'], 2) }} TZS</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif

                    <!-- Additional Deductions (Collapsible) -->
                    @php
                        $topUpAmount = 0;
                        if ($loan->selectedLoan) {
                            $topUpLoan = DB::table('loans')->where('id', $loan->selectedLoan)->first();
                            if ($topUpLoan) {
                                $topUpAmount = $topUpLoan->amount_to_be_credited ?? 0;
                            }
                        }
                        
                        $closedLoanBalance = 0;
                        if ($loan->loan_account_number) {
                            $closedLoanBalance = DB::table('sub_accounts')
                                ->where('account_number', $loan->loan_account_number)
                                ->value('balance') ?? 0;
                        }
                        
                        $settledLoans = DB::table('settled_loans')
                            ->where('loan_id', $loan->id)
                            ->where('is_selected', true)
                            ->get();
                        $outsideSettlements = $settledLoans->sum('amount');
                    @endphp
                    
                    @if($topUpAmount > 0 || $closedLoanBalance > 0 || $outsideSettlements > 0)
                        <div class="mb-6">
                            <details class="group">
                                <summary class="flex items-center cursor-pointer list-none hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        Additional Deductions
                                        <span class="text-sm text-gray-500 ml-2">({{ ($topUpAmount > 0 ? 1 : 0) + ($closedLoanBalance > 0 ? 1 : 0) + ($settledLoans->count()) }} items)</span>
                                        <svg class="w-4 h-4 ml-2 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </h4>
                                </summary>
                                
                                <div class="mt-4 space-y-3">
                                    <!-- Top-Up Loan -->
                                    @if($topUpAmount > 0)
                                        <div class="bg-purple-50 p-3 rounded-lg border border-purple-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <h5 class="text-sm font-medium text-purple-900">Top-Up Loan Balance</h5>
                                                <div class="text-sm font-bold text-purple-900">{{ number_format($topUpAmount, 2) }} TZS</div>
                                            </div>
                                            @if($loan->selectedLoan)
                                                @php $topUpLoan = DB::table('loans')->where('id', $loan->selectedLoan)->first(); @endphp
                                                @if($topUpLoan)
                                                    <div class="text-xs bg-white p-2 rounded border border-purple-100">
                                                        <div class="flex justify-between">
                                                            <span class="text-purple-700 font-medium">Loan ID: {{ $topUpLoan->loan_id ?? 'N/A' }}</span>
                                                            <span class="text-purple-900 font-semibold">{{ number_format($topUpAmount, 2) }} TZS</span>
                                                        </div>
                                                        <div class="text-purple-600 text-xs mt-1">Existing loan balance to be settled</div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Closed Loan Balance -->
                                    @if($closedLoanBalance > 0)
                                        <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <h5 class="text-sm font-medium text-orange-900">Closed Loan Balance</h5>
                                                <div class="text-sm font-bold text-orange-900">{{ number_format($closedLoanBalance, 2) }} TZS</div>
                                            </div>
                                            <div class="text-xs bg-white p-2 rounded border border-orange-100">
                                                <div class="flex justify-between">
                                                    <span class="text-orange-700 font-medium">Account: {{ $loan->loan_account_number ?? 'N/A' }}</span>
                                                    <span class="text-orange-900 font-semibold">{{ number_format($closedLoanBalance, 2) }} TZS</span>
                                                </div>
                                                <div class="text-orange-600 text-xs mt-1">Balance from loan account to be closed</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Outside Loan Settlements -->
                                    @if($settledLoans->count() > 0)
                                        <div class="bg-teal-50 p-3 rounded-lg border border-teal-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <h5 class="text-sm font-medium text-teal-900">Outside Loan Settlements ({{ $settledLoans->count() }})</h5>
                                                <div class="text-sm font-bold text-teal-900">{{ number_format($outsideSettlements, 2) }} TZS</div>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach($settledLoans as $settledLoan)
                                                    <div class="flex justify-between text-xs bg-white p-2 rounded border border-teal-100">
                                                        <div>
                                                            <span class="text-teal-700 font-medium">{{ $settledLoan->institution ?? 'N/A' }}</span>
                                                            <div class="text-teal-600 text-xs">Account: {{ $settledLoan->account ?? 'N/A' }}</div>
                                                        </div>
                                                        <span class="text-teal-900 font-semibold">{{ number_format($settledLoan->amount ?? 0, 2) }} TZS</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between gap-3 p-4 sm:p-6 border-t border-gray-200 flex-shrink-0 bg-white">
                    <!-- Disbursement Summary -->
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center space-x-4">
                            <span>Net Amount: <span class="font-semibold text-gray-900">{{ number_format($netDisbursementAmount, 2) }} TZS</span></span>
                            <span>Method: <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $loan->pay_method ?? '')) }}</span></span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3">
                        <button wire:click="closeModal" 
                                @if($isProcessing) disabled @endif
                                class="px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500
                                       {{ $isProcessing 
                                          ? 'text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed' 
                                          : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' }}">
                            {{ $isProcessing ? 'Processing...' : 'Cancel' }}
                        </button>
                        
                        @php
                            // Determine if disbursement can proceed
                            $canDisburse = false;
                            $buttonText = 'Cannot Process Disbursement';
                            $buttonIcon = 'warning';
                            
                            if (count($this->validationErrors) > 0) {
                                $canDisburse = false;
                                $buttonText = 'Missing Required Information';
                                $buttonIcon = 'warning';
                            } elseif ($loan->pay_method === 'cash') {
                                $canDisburse = !empty($this->selectedDepositAccount);
                                $buttonText = $canDisburse ? 'Process Disbursement' : 'Select Deposit Account';
                                $buttonIcon = $canDisburse ? 'money' : 'warning';
                            } else {
                                $canDisburse = !empty($this->bank_account) && !$hasInsufficientFunds;
                                if (empty($this->bank_account)) {
                                    $buttonText = 'Select Disbursement Account';
                                    $buttonIcon = 'warning';
                                } elseif ($hasInsufficientFunds) {
                                    $buttonText = 'Insufficient Funds';
                                    $buttonIcon = 'warning';
                                } else {
                                    $buttonText = 'Process Disbursement';
                                    $buttonIcon = 'money';
                                }
                            }
                        @endphp
                        
                        <button wire:click="disburseLoan('{{$loan->pay_method}}','{{$loan->loan_type_2}}','{{$loan->loan_sub_product}}')" 
                                @if(!$canDisburse || $isProcessing) disabled @endif
                                class="inline-flex items-center px-6 py-2 font-semibold rounded-lg shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2
                                       {{ $canDisburse && !$isProcessing
                                          ? 'bg-red-600 hover:bg-red-800 text-white focus:ring-red-500' 
                                          : 'bg-gray-300 text-gray-500 cursor-not-allowed' }} text-sm">
                            @if($isProcessing)
                                <div class="mr-2">
                                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                </div>
                                Processing Disbursement...
                            @else
                                <div wire:loading wire:target="disburseLoan" class="mr-2">
                                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                </div>
                                <div wire:loading.remove wire:target="disburseLoan" class="mr-2">
                                    @if($buttonIcon === 'money')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    @endif
                                </div>
                                {{ $buttonText }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 