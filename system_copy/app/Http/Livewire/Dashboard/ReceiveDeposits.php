<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionPostingService;
use App\Services\MembershipVerificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class ReceiveDeposits extends Component
{
    // Modal states
    public $showReceiveDepositsModal = false;
    public $showReceiveSavingsModal = false;
    public $transactionType = 'deposits'; // 'deposits' or 'savings'

    // Member verification
    public $membershipNumber = '';
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $bankAccounts = [];
    public $selectedBankDetails = null;

    // Transaction details
    public $selectedAccount = '';
    public $amount = '';
    public $paymentMethod = 'cash';
    public $selectedBank = '';
    public $referenceNumber = '';
    public $depositDate = '';
    public $depositTime = '';
    public $depositorName = '';
    public $narration = '';

    // Statistics
    public $totalDeposits = 0;
    public $totalSavings = 0;
    public $todayDeposits = 0;
    public $todaySavings = 0;
    public $activeAccounts = 0;

    // Loading states
    public $isLoading = false;
    public $isVerifying = false;
    public $isSubmitting = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'membershipNumber' => 'required|min:3',
        'selectedAccount' => 'required',
        'amount' => 'required|numeric|min:0.01',
        'paymentMethod' => 'required|in:cash,bank',
        'depositorName' => 'required|min:2',
        'narration' => 'nullable|max:500',
        'selectedBank' => 'required_if:paymentMethod,bank',
        'referenceNumber' => 'required_if:paymentMethod,bank',
        'depositDate' => 'required_if:paymentMethod,bank|date',
        'depositTime' => 'required_if:paymentMethod,bank',
    ];

    protected $messages = [
        'membershipNumber.required' => 'Membership number is required.',
        'selectedAccount.required' => 'Please select an account.',
        'amount.required' => 'Amount is required.',
        'amount.numeric' => 'Amount must be a valid number.',
        'amount.min' => 'Amount must be greater than 0.',
        'depositorName.required' => 'Depositor name is required.',
        'selectedBank.required_if' => 'Please select a bank for bank deposits.',
        'referenceNumber.required_if' => 'Reference number is required for bank deposits.',
        'depositDate.required_if' => 'Deposit date is required for bank deposits.',
        'depositTime.required_if' => 'Deposit time is required for bank deposits.',
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->depositDate = now()->format('Y-m-d');
        $this->depositTime = now()->format('H:i');
    }

    public function loadStatistics()
    {
        try {
            // Total deposits and savings
            $this->totalDeposits = AccountsModel::where('product_number', '3000')
                ->where('status', 'ACTIVE')
                ->sum('balance');

            $this->totalSavings = AccountsModel::where('product_number', '2000')
                ->where('status', 'ACTIVE')
                ->sum('balance');

            // Today's transactions from general_ledger table
            $today = now()->format('Y-m-d');
            $this->todayDeposits = DB::table('general_ledger')
                ->join('accounts', 'general_ledger.record_on_account_number', '=', 'accounts.account_number')
                ->where('accounts.product_number', '3000')
                ->where('general_ledger.credit', '>', 0)
                ->whereDate('general_ledger.created_at', $today)
                ->sum('general_ledger.credit');

            $this->todaySavings = DB::table('general_ledger')
                ->join('accounts', 'general_ledger.record_on_account_number', '=', 'accounts.account_number')
                ->where('accounts.product_number', '2000')
                ->where('general_ledger.credit', '>', 0)
                ->whereDate('general_ledger.created_at', $today)
                ->sum('general_ledger.credit');

            // Active accounts
            $this->activeAccounts = AccountsModel::where('status', 'ACTIVE')->count();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading statistics: ' . $e->getMessage();
        }
    }

    public function showReceiveDepositsModal()
    {
        $this->transactionType = 'deposits';
        $this->resetForm();
        $this->showReceiveDepositsModal = true;
    }

    public function showReceiveSavingsModal()
    {
        $this->transactionType = 'savings';
        $this->resetForm();
        $this->showReceiveSavingsModal = true;
    }

    public function closeModal()
    {
        $this->showReceiveDepositsModal = false;
        $this->showReceiveSavingsModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->membershipNumber = '';
        $this->verifiedMember = null;
        $this->memberAccounts = [];
        $this->bankAccounts = [];
        $this->selectedAccount = '';
        $this->amount = '';
        $this->paymentMethod = 'cash';
        $this->selectedBank = '';
        $this->selectedBankDetails = null;
        $this->referenceNumber = '';
        $this->depositDate = now()->format('Y-m-d');
        $this->depositTime = now()->format('H:i');
        $this->depositorName = '';
        $this->narration = '';
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->resetValidation();
    }

    public function verifyMembership()
    {
        Log::info('========== MEMBERSHIP VERIFICATION START ==========', [
            'membership_number' => $this->membershipNumber,
            'user_id' => Auth::id()
        ]);

        $this->validate([
            'membershipNumber' => 'required|min:1'
        ]);

        Log::info('✓ Membership number validation passed');

        try {
            Log::info('Calling MembershipVerificationService');
            $verificationService = app(MembershipVerificationService::class);
            $result = $verificationService->verifyMembership($this->membershipNumber);

            Log::info('Verification service result', [
                'result' => json_encode($result, JSON_PRETTY_PRINT),
                'exists' => $result['exists'] ?? 'N/A',
                'message' => $result['message'] ?? 'N/A'
            ]);

            if ($result['exists'] === true) {
                $this->verifiedMember = $result['member'];

                Log::info('✓ Member verified successfully', [
                    'member_data' => json_encode($result['member'], JSON_PRETTY_PRINT)
                ]);

                Log::info('Loading member accounts', [
                    'client_number' => $this->membershipNumber,
                    'product_number' => '2000'
                ]);

                $this->memberAccounts = AccountsModel::where('client_number', $this->membershipNumber)
                    ->where('product_number', '2000')
                    ->where('status', 'ACTIVE')
                    ->get();

                Log::info('Member accounts loaded', [
                    'total_accounts' => $this->memberAccounts->count(),
                    'accounts' => $this->memberAccounts->map(function($account) {
                        return [
                            'account_number' => $account->account_number,
                            'account_name' => $account->account_name,
                            'balance' => $account->balance,
                            'status' => $account->status
                        ];
                    })->toArray()
                ]);

                $this->bankAccounts = BankAccount::where('status', 'ACTIVE')->get();

                Log::info('Bank accounts loaded', [
                    'total_banks' => $this->bankAccounts->count()
                ]);

                $this->dispatchBrowserEvent('notify', [
                    'type' => 'success',
                    'message' => $result['message']
                ]);

                Log::info('========== MEMBERSHIP VERIFICATION END - SUCCESS ==========');
            } else {
                Log::warning('✗ Member not found or verification failed', [
                    'membership_number' => $this->membershipNumber,
                    'message' => $result['message'] ?? 'N/A'
                ]);

                $this->addError('membershipNumber', $result['message']);
                $this->verifiedMember = null;
                $this->memberAccounts = [];

                Log::info('========== MEMBERSHIP VERIFICATION END - FAILED ==========');
            }
        } catch (Exception $e) {
            Log::error('✗ Membership verification exception', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->addError('membershipNumber', 'Failed to verify membership. Please try again.');
            $this->verifiedMember = null;
            $this->memberAccounts = [];

            Log::info('========== MEMBERSHIP VERIFICATION END - EXCEPTION ==========');
        }
    }

    public function updatedSelectedBank()
    {
        if ($this->selectedBank) {
            $this->selectedBankDetails = BankAccount::find($this->selectedBank);
        } else {
            $this->selectedBankDetails = null;
        }
    }

    public function updatedPaymentMethod()
    {
        if ($this->paymentMethod === 'cash') {
            $this->referenceNumber = 'CASH-' . strtoupper(uniqid());
            $this->depositDate = now()->format('Y-m-d');
            $this->depositTime = now()->format('H:i');
        }
    }

    public function submitReceiveDeposits()
    {
        $this->isSubmitting = true;
        $this->errorMessage = '';

        Log::info('Starting submitReceiveDeposits', [
            'transaction_type' => $this->transactionType,
            'payment_method' => $this->paymentMethod,
            'amount' => $this->amount,
            'user_id' => Auth::id()
        ]);

        DB::beginTransaction();
        try {
            Log::info('Validating form data');

            // Validate all required fields
            $this->validate([
                'membershipNumber' => 'required|min:1',
                'selectedAccount' => 'required',
                'amount' => 'required|numeric|min:0.01',
                'paymentMethod' => 'required|in:cash,bank',
                'depositorName' => 'required|min:2',
                'narration' => 'required|string|max:500',
                'depositDate' => 'required|date',
                'depositTime' => 'required|date_format:H:i',
                'referenceNumber' => 'required|string|max:255',
                'selectedBank' => 'required_if:paymentMethod,bank',
            ]);

            Log::info('✓ Validation passed');

            // Verify member account exists and is active
            Log::info('Verifying member account', [
                'selected_account' => $this->selectedAccount
            ]);

            $memberAccount = AccountsModel::where('account_number', $this->selectedAccount)->first();
            if (!$memberAccount) {
                Log::error('✗ Member account not found', [
                    'account_number' => $this->selectedAccount
                ]);
                throw new \Exception('Member account not found.');
            }

            Log::info('✓ Member account found', [
                'account_number' => $memberAccount->account_number,
                'account_name' => $memberAccount->account_name,
                'product_number' => $memberAccount->product_number,
                'status' => $memberAccount->status,
                'current_balance' => $memberAccount->balance
            ]);

            if ($memberAccount->status !== 'ACTIVE') {
                Log::error('✗ Account is not active', [
                    'account_number' => $memberAccount->account_number,
                    'status' => $memberAccount->status
                ]);
                throw new \Exception('Account is not active.');
            }

            Log::info('✓ Account status is ACTIVE');

            // Process based on payment method
            if ($this->paymentMethod === 'bank') {
                Log::info('Processing as bank deposit');
                $this->processBankDeposit($memberAccount);
            } else {
                Log::info('Processing as cash deposit');
                $this->processCashDeposit($memberAccount);
            }

            DB::commit();

            Log::info('✓ Transaction committed successfully', [
                'transaction_type' => $this->transactionType,
                'amount' => $this->amount,
                'payment_method' => $this->paymentMethod
            ]);

            $this->successMessage = ucfirst($this->transactionType) . ' received successfully!';
            $this->loadStatistics();

            // Reset form after successful submission
            $this->resetForm();
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error processing transaction: ' . $e->getMessage();

            Log::error('✗ Transaction failed and rolled back', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'transaction_type' => $this->transactionType,
                'payment_method' => $this->paymentMethod,
                'amount' => $this->amount,
                'selected_account' => $this->selectedAccount,
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isSubmitting = false;
            Log::info('submitReceiveDeposits completed', [
                'is_submitting' => $this->isSubmitting,
                'has_error' => !empty($this->errorMessage),
                'error_message' => $this->errorMessage
            ]);
        }
    }

    private function processBankDeposit($memberAccount)
    {
        Log::info('========== BANK DEPOSIT PROCESSING START ==========');

        Log::info('Checking bank account details', [
            'selected_bank' => $this->selectedBank,
            'has_selected_bank_details' => !empty($this->selectedBankDetails),
            'selected_account' => $this->selectedAccount
        ]);

        if (empty($this->selectedBankDetails)) {
            Log::error('✗ Selected bank details is empty');
            throw new \Exception('Bank account details not found.');
        }

        Log::info('Selected bank details', [
            'bank_id' => $this->selectedBankDetails->id ?? 'N/A',
            'bank_name' => $this->selectedBankDetails->bank_name ?? 'N/A',
            'account_name' => $this->selectedBankDetails->account_name ?? 'N/A',
            'account_number' => $this->selectedBankDetails->account_number ?? 'N/A',
            'internal_mirror_account' => $this->selectedBankDetails->internal_mirror_account_number ?? 'N/A'
        ]);

        if (!empty($this->selectedBankDetails->internal_mirror_account_number) && !empty($this->selectedAccount)) {
            $totalAmount = $this->amount;

            Log::info('Preparing transaction data', [
                'debit_account' => $this->selectedBankDetails->internal_mirror_account_number,
                'credit_account' => $this->selectedAccount,
                'amount' => $totalAmount
            ]);

            // Post the transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $this->selectedBankDetails->internal_mirror_account_number, // Bank account (Asset)
                'second_account' => $this->selectedAccount, // Member account (Liability)
                'amount' => $totalAmount,
                'narration' => ucfirst($this->transactionType) . ' deposit : ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->selectedBankDetails->bank_name . ' : ' . $this->referenceNumber,
                'action' => $this->transactionType . '_deposit',
                'source_account' => $this->selectedBankDetails->internal_mirror_account_number, // Money flows FROM bank
                'destination_account' => $this->selectedAccount // Money flows TO member savings
            ];

            Log::info('Posting ' . $this->transactionType . ' deposit transaction', [
                'transaction_data' => json_encode($transactionData, JSON_PRETTY_PRINT),
                'first_account' => $transactionData['first_account'],
                'second_account' => $transactionData['second_account'],
                'amount' => $transactionData['amount'],
                'narration' => $transactionData['narration'],
                'action' => $transactionData['action']
            ]);

            $result = $transactionService->postTransaction($transactionData);

            Log::info('TransactionPostingService returned', [
                'result' => json_encode($result, JSON_PRETTY_PRINT),
                'status' => $result['status'] ?? 'N/A',
                'message' => $result['message'] ?? 'N/A',
                'reference' => $result['reference'] ?? 'N/A'
            ]);

            if ($result['status'] !== 'success') {
                Log::error('✗ Transaction posting failed', [
                    'error' => $result['message'] ?? 'Unknown error',
                    'result_status' => $result['status'] ?? 'N/A',
                    'full_result' => json_encode($result, JSON_PRETTY_PRINT),
                    'transaction_data' => $transactionData
                ]);
                throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('✓ Bank deposit transaction posted successfully', [
                'transaction_reference' => $result['reference'] ?? null,
                'amount' => $totalAmount,
                'bank_name' => $this->selectedBankDetails->bank_name,
                'reference_number' => $this->referenceNumber
            ]);

            Log::info('========== BANK DEPOSIT PROCESSING END ==========');
        } else {
            Log::error('✗ Internal mirror account or selected account is missing', [
                'internal_mirror_account' => $this->selectedBankDetails->internal_mirror_account_number ?? 'EMPTY',
                'selected_account' => $this->selectedAccount
            ]);
            throw new \Exception('Bank account details not found.');
        }
    }

    private function processCashDeposit($memberAccount)
    {
        Log::info('========== CASH DEPOSIT PROCESSING START ==========');

        // For cash deposits, we need to debit the cash account and credit the member account
        // Get the teller's cash account
        Log::info('Getting teller cash account', [
            'employee_id' => auth()->user()->employeeId ?? 'N/A',
            'user_id' => Auth::id()
        ]);

        $tellerCashAccount = $this->getTellerCashAccount();

        Log::info('Teller cash account retrieved', [
            'teller_cash_account' => $tellerCashAccount ?? 'NULL'
        ]);

        if (!$tellerCashAccount) {
            Log::error('✗ Teller cash account not found', [
                'employee_id' => auth()->user()->employeeId ?? 'N/A',
                'user_id' => Auth::id()
            ]);
            throw new \Exception('Teller cash account not found. Please contact administrator.');
        }

        Log::info('✓ Teller cash account found', [
            'cash_account' => $tellerCashAccount
        ]);

        $totalAmount = $this->amount;

        Log::info('Preparing cash deposit transaction', [
            'debit_account' => $tellerCashAccount,
            'credit_account' => $this->selectedAccount,
            'amount' => $totalAmount
        ]);

        // Post the transaction using TransactionPostingService
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $tellerCashAccount, // Teller cash account (Asset)
            'second_account' => $this->selectedAccount, // Member account (Liability)
            'amount' => $totalAmount,
            'narration' => ucfirst($this->transactionType) . ' cash deposit : ' . $this->amount . ' : ' . $this->depositorName . ' : ' . $this->referenceNumber,
            'action' => $this->transactionType . '_cash_deposit',
            'source_account' => $tellerCashAccount, // Money flows FROM teller cash
            'destination_account' => $this->selectedAccount // Money flows TO member savings
        ];

        Log::info('Posting ' . $this->transactionType . ' cash deposit transaction', [
            'transaction_data' => json_encode($transactionData, JSON_PRETTY_PRINT),
            'first_account' => $transactionData['first_account'],
            'second_account' => $transactionData['second_account'],
            'amount' => $transactionData['amount'],
            'narration' => $transactionData['narration'],
            'action' => $transactionData['action']
        ]);

        $result = $transactionService->postTransaction($transactionData);

        Log::info('TransactionPostingService returned', [
            'result' => json_encode($result, JSON_PRETTY_PRINT),
            'status' => $result['status'] ?? 'N/A',
            'message' => $result['message'] ?? 'N/A',
            'reference' => $result['reference'] ?? 'N/A'
        ]);

        if ($result['status'] !== 'success') {
            Log::error('✗ Cash deposit transaction posting failed', [
                'error' => $result['message'] ?? 'Unknown error',
                'result_status' => $result['status'] ?? 'N/A',
                'full_result' => json_encode($result, JSON_PRETTY_PRINT),
                'transaction_data' => $transactionData
            ]);
            throw new \Exception('Failed to post cash deposit transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('✓ Cash deposit transaction posted successfully', [
            'transaction_reference' => $result['reference'] ?? null,
            'amount' => $totalAmount,
            'depositor_name' => $this->depositorName,
            'reference_number' => $this->referenceNumber
        ]);

        Log::info('========== CASH DEPOSIT PROCESSING END ==========');
    }

    private function getTellerCashAccount()
    {
        Log::info('Looking up teller cash account', [
            'employee_id' => auth()->user()->employeeId ?? 'N/A'
        ]);

        // Get the teller's cash account based on the logged-in user
        $teller = DB::table('tellers')
            ->where('employee_id', auth()->user()->employeeId)
            ->first();

        if ($teller) {
            Log::info('Teller record found', [
                'teller_id' => $teller->id ?? 'N/A',
                'employee_id' => $teller->employee_id ?? 'N/A',
                'cash_account_number' => $teller->cash_account_number ?? 'NULL'
            ]);
            return $teller->cash_account_number ?? null;
        }

        Log::warning('No teller record found for employee', [
            'employee_id' => auth()->user()->employeeId ?? 'N/A'
        ]);

        return null;
    }

    public function submitReceiveSavings()
    {
        Log::info('========== RECEIVE SAVINGS SUBMISSION START ==========', [
            'user_id' => Auth::id(),
            'transaction_type' => $this->transactionType,
            'timestamp' => now()->toDateTimeString()
        ]);

        Log::info('Receive Savings Form Data', [
            'membership_number' => $this->membershipNumber,
            'verified_member' => $this->verifiedMember,
            'selected_account' => $this->selectedAccount,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'selected_bank' => $this->selectedBank,
            'reference_number' => $this->referenceNumber,
            'deposit_date' => $this->depositDate,
            'deposit_time' => $this->depositTime,
            'depositor_name' => $this->depositorName,
            'narration' => $this->narration
        ]);

        // Same logic as deposits, just different account type
        $this->submitReceiveDeposits();

        Log::info('========== RECEIVE SAVINGS SUBMISSION END ==========');
    }

    public function getBankAccountsProperty()
    {
        return BankAccount::where('status', 'ACTIVE')->get();
    }

    public function render()
    {
        return view('livewire.dashboard.receive-deposits', [
            'bankAccounts' => $this->bankAccounts
        ]);
    }
}
