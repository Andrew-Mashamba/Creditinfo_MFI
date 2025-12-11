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

class ProcessWithdrawals extends Component
{
    // Modal states
    public $showWithdrawDepositsModal = false;
    public $showWithdrawSavingsModal = false;
    public $transactionType = 'deposits'; // 'deposits' or 'savings'

    // Member verification
    public $membershipNumber = '';
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $bankAccounts = [];
    public $selectedBankDetails = null;

    // Transaction details
    public $selectedAccount = '';
    public $selectedAccountBalance = 0;
    public $amount = '';
    public $paymentMethod = 'cash';
    public $selectedBank = '';
    public $referenceNumber = '';
    public $withdrawDate = '';
    public $withdrawTime = '';
    public $withdrawerName = '';
    public $narration = '';

    // OTP verification for cash withdrawals
    public $otpCode = '';
    public $otpVerified = false;
    public $otpSent = false;

    // Statistics
    public $totalWithdrawals = 0;
    public $todayWithdrawals = 0;
    public $pendingWithdrawals = 0;
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
        'paymentMethod' => 'required|in:cash,bank,internal_transfer',
        'withdrawerName' => 'required|min:2',
        'narration' => 'required|string|max:500',
        'selectedBank' => 'required_if:paymentMethod,bank',
        'referenceNumber' => 'required|string|max:255',
        'withdrawDate' => 'required|date',
        'withdrawTime' => 'required|date_format:H:i',
        'otpCode' => 'required_if:paymentMethod,cash',
    ];

    protected $messages = [
        'membershipNumber.required' => 'Membership number is required.',
        'selectedAccount.required' => 'Please select an account.',
        'amount.required' => 'Amount is required.',
        'amount.numeric' => 'Amount must be a valid number.',
        'amount.min' => 'Amount must be greater than 0.',
        'withdrawerName.required' => 'Withdrawer name is required.',
        'selectedBank.required_if' => 'Please select a bank for bank withdrawals.',
        'otpCode.required_if' => 'OTP verification is required for cash withdrawals.',
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->withdrawDate = now()->format('Y-m-d');
        $this->withdrawTime = now()->format('H:i');
    }

    public function loadStatistics()
    {
        try {
            // Total withdrawals from general_ledger
            $this->totalWithdrawals = DB::table('general_ledger')
                ->join('accounts', 'general_ledger.record_on_account_number', '=', 'accounts.account_number')
                ->whereIn('accounts.product_number', ['2000', '3000']) // Savings and Deposits
                ->where('general_ledger.debit', '>', 0)
                ->sum('general_ledger.debit');

            // Today's withdrawals
            $today = now()->format('Y-m-d');
            $this->todayWithdrawals = DB::table('general_ledger')
                ->join('accounts', 'general_ledger.record_on_account_number', '=', 'accounts.account_number')
                ->whereIn('accounts.product_number', ['2000', '3000'])
                ->where('general_ledger.debit', '>', 0)
                ->whereDate('general_ledger.created_at', $today)
                ->sum('general_ledger.debit');

            // Active accounts
            $this->activeAccounts = AccountsModel::whereIn('product_number', ['2000', '3000'])
                ->where('status', 'ACTIVE')
                ->count();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading statistics: ' . $e->getMessage();
        }
    }

    public function showWithdrawDepositsModal()
    {
        $this->transactionType = 'deposits';
        $this->resetForm();
        $this->showWithdrawDepositsModal = true;
    }

    public function showWithdrawSavingsModal()
    {
        $this->transactionType = 'savings';
        $this->resetForm();
        $this->showWithdrawSavingsModal = true;
    }

    public function closeModal()
    {
        $this->showWithdrawDepositsModal = false;
        $this->showWithdrawSavingsModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->membershipNumber = '';
        $this->verifiedMember = null;
        $this->memberAccounts = [];
        $this->bankAccounts = [];
        $this->selectedAccount = '';
        $this->selectedAccountBalance = 0;
        $this->amount = '';
        $this->paymentMethod = 'cash';
        $this->selectedBank = '';
        $this->selectedBankDetails = null;
        $this->referenceNumber = '';
        $this->withdrawDate = now()->format('Y-m-d');
        $this->withdrawTime = now()->format('H:i');
        $this->withdrawerName = '';
        $this->narration = '';
        $this->otpCode = '';
        $this->otpVerified = false;
        $this->otpSent = false;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->resetValidation();
    }

    public function verifyMembership()
    {
        $this->validate([
            'membershipNumber' => 'required|min:1'
        ]);

        try {
            $verificationService = app(MembershipVerificationService::class);
            $result = $verificationService->verifyMembership($this->membershipNumber);

            if ($result['exists'] === true) {
                $this->verifiedMember = $result['member'];
                $this->memberAccounts = AccountsModel::where('client_number', $this->membershipNumber)
                    ->whereIn('product_number', ['2000', '3000']) // Both savings and deposits
                    ->where('status', 'ACTIVE')
                    ->get();
                $this->bankAccounts = BankAccount::where('status', 'ACTIVE')->get();
                
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'success',
                    'message' => $result['message']
                ]);
            } else {
                $this->addError('membershipNumber', $result['message']);
                $this->verifiedMember = null;
                $this->memberAccounts = [];
            }
        } catch (Exception $e) {
            $this->addError('membershipNumber', 'Failed to verify membership. Please try again.');
            Log::error('Membership verification error: ' . $e->getMessage());
            $this->verifiedMember = null;
            $this->memberAccounts = [];
        }
    }

    public function updatedSelectedAccount()
    {
        if ($this->selectedAccount) {
            $account = AccountsModel::where('account_number', $this->selectedAccount)->first();
            if ($account) {
                $this->selectedAccountBalance = $account->balance;
            }
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
            $this->referenceNumber = 'CASH-WD-' . strtoupper(uniqid());
            $this->withdrawDate = now()->format('Y-m-d');
            $this->withdrawTime = now()->format('H:i');
        } elseif ($this->paymentMethod === 'bank') {
            $this->referenceNumber = 'BANK-WD-' . strtoupper(uniqid());
        } else {
            $this->referenceNumber = 'INT-WD-' . strtoupper(uniqid());
        }
    }

    public function sendOTP()
    {
        // In a real implementation, this would send OTP via SMS/Email
        // For now, we'll simulate it
        $this->otpSent = true;
        $this->dispatchBrowserEvent('notify', [
            'type' => 'info',
            'message' => 'OTP sent to member\'s registered phone number.'
        ]);
    }

    public function verifyOTP()
    {
        // In a real implementation, this would verify the OTP
        // For now, we'll accept any 6-digit code
        if (strlen($this->otpCode) === 6) {
            $this->otpVerified = true;
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'OTP verified successfully.'
            ]);
        } else {
            $this->addError('otpCode', 'Invalid OTP code.');
        }
    }

    public function submitWithdrawDeposits()
    {
        $this->isSubmitting = true;
        $this->errorMessage = '';

        DB::beginTransaction();
        try {
            // Validate all required fields
            $this->validate([
                'membershipNumber' => 'required|min:1',
                'selectedAccount' => 'required',
                'amount' => 'required|numeric|min:0.01',
                'paymentMethod' => 'required|in:cash,bank,internal_transfer',
                'withdrawerName' => 'required|min:2',
                'narration' => 'required|string|max:500',
                'withdrawDate' => 'required|date',
                'withdrawTime' => 'required|date_format:H:i',
                'referenceNumber' => 'required|string|max:255',
                'selectedBank' => 'required_if:paymentMethod,bank',
                'otpCode' => 'required_if:paymentMethod,cash',
            ]);

            // Verify member account exists and is active
            $memberAccount = AccountsModel::where('account_number', $this->selectedAccount)->first();
            if (!$memberAccount) {
                throw new \Exception('Member account not found.');
            }

            if ($memberAccount->status !== 'ACTIVE') {
                throw new \Exception('Account is not active.');
            }

            // Check sufficient balance
            if ($memberAccount->balance < $this->amount) {
                throw new \Exception('Insufficient balance. Available balance: TZS ' . number_format($memberAccount->balance, 2));
            }

            // For cash withdrawals, verify OTP
            if ($this->paymentMethod === 'cash' && !$this->otpVerified) {
                throw new \Exception('OTP verification required for cash withdrawals.');
            }

            // Process based on payment method
            if ($this->paymentMethod === 'bank') {
                $this->processBankWithdrawal($memberAccount);
            } elseif ($this->paymentMethod === 'internal_transfer') {
                $this->processInternalTransferWithdrawal($memberAccount);
            } else {
                $this->processCashWithdrawal($memberAccount);
            }

            DB::commit();
            
            $this->successMessage = ucfirst($this->transactionType) . ' withdrawal processed successfully!';
            $this->loadStatistics();
            
            // Reset form after successful submission
            $this->resetForm();
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error processing withdrawal: ' . $e->getMessage();
            Log::error('Error in submitWithdrawDeposits: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    private function processBankWithdrawal($memberAccount)
    {
        if (!empty($this->selectedBankDetails->internal_mirror_account_number)) {
            $totalAmount = $this->amount;
            
            // Post the transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $this->selectedAccount, // Debit account (member)
                'second_account' => $this->selectedBankDetails->internal_mirror_account_number, // Credit account (bank)
                'amount' => $totalAmount,
                'narration' => ucfirst($this->transactionType) . ' withdrawal : ' . $this->amount . ' : ' . $this->withdrawerName . ' : ' . $this->selectedBankDetails->bank_name . ' : ' . $this->referenceNumber,
                'action' => $this->transactionType . '_withdrawal'
            ];

            Log::info('Posting ' . $this->transactionType . ' withdrawal transaction', [
                'transaction_data' => $transactionData
            ]);

            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                Log::error('Transaction posting failed', [
                    'error' => $result['message'] ?? 'Unknown error',
                    'transaction_data' => $transactionData
                ]);
                throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
            }

            Log::info('Transaction posted successfully', [
                'transaction_reference' => $result['reference'] ?? null,
                'amount' => $totalAmount
            ]);
        } else {
            throw new \Exception('Bank account details not found.');
        }
    }

    private function processInternalTransferWithdrawal($memberAccount)
    {
        // For internal transfers, we transfer to member's bank account
        $totalAmount = $this->amount;
        
        // Post the transaction using TransactionPostingService
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->selectedAccount, // Debit account (member)
            'second_account' => $this->verifiedMember['account_number'] ?? '', // Credit account (member's bank)
            'amount' => $totalAmount,
            'narration' => ucfirst($this->transactionType) . ' internal transfer : ' . $this->amount . ' : ' . $this->withdrawerName . ' : ' . $this->referenceNumber,
            'action' => $this->transactionType . '_internal_transfer'
        ];

        Log::info('Posting ' . $this->transactionType . ' internal transfer transaction', [
            'transaction_data' => $transactionData
        ]);

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            Log::error('Internal transfer transaction posting failed', [
                'error' => $result['message'] ?? 'Unknown error',
                'transaction_data' => $transactionData
            ]);
            throw new \Exception('Failed to post internal transfer transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Internal transfer transaction posted successfully', [
            'transaction_reference' => $result['reference'] ?? null,
            'amount' => $totalAmount
        ]);
    }

    private function processCashWithdrawal($memberAccount)
    {
        // For cash withdrawals, we debit the member account and credit the cash account
        $tellerCashAccount = $this->getTellerCashAccount();
        
        if (!$tellerCashAccount) {
            throw new \Exception('Teller cash account not found. Please contact administrator.');
        }

        $totalAmount = $this->amount;
        
        // Post the transaction using TransactionPostingService
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $this->selectedAccount, // Debit account (member)
            'second_account' => $tellerCashAccount, // Credit account (cash)
            'amount' => $totalAmount,
            'narration' => ucfirst($this->transactionType) . ' cash withdrawal : ' . $this->amount . ' : ' . $this->withdrawerName . ' : ' . $this->referenceNumber,
            'action' => $this->transactionType . '_cash_withdrawal'
        ];

        Log::info('Posting ' . $this->transactionType . ' cash withdrawal transaction', [
            'transaction_data' => $transactionData
        ]);

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            Log::error('Cash withdrawal transaction posting failed', [
                'error' => $result['message'] ?? 'Unknown error',
                'transaction_data' => $transactionData
            ]);
            throw new \Exception('Failed to post cash withdrawal transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Cash withdrawal transaction posted successfully', [
            'transaction_reference' => $result['reference'] ?? null,
            'amount' => $totalAmount
        ]);
    }

    private function getTellerCashAccount()
    {
        // Get the teller's cash account based on the logged-in user
        $teller = DB::table('tellers')
            ->where('employee_id', auth()->user()->employeeId)
            ->first();
            
        if ($teller) {
            return $teller->cash_account_number ?? null;
        }
        
        return null;
    }

    public function submitWithdrawSavings()
    {
        // Same logic as deposits, just different account type
        $this->submitWithdrawDeposits();
    }

    public function getBankAccountsProperty()
    {
        return BankAccount::where('status', 'ACTIVE')->get();
    }

    public function render()
    {
        return view('livewire.dashboard.process-withdrawals', [
            'bankAccounts' => $this->bankAccounts
        ]);
    }
}
