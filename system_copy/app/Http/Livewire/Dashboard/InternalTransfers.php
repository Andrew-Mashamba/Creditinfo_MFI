<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\BankAccount;
use App\Models\InternalTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionPostingService;
use App\Services\MembershipVerificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class InternalTransfers extends Component
{
    // Modal states
    public $showTransferModal = false;
    public $showTransferHistoryModal = false;

    // Member verification
    public $membershipNumber = '';
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $bankAccounts = [];

    // Transfer details
    public $transferType = 'member_to_member'; // 'member_to_member', 'member_to_bank', 'bank_to_member'
    public $fromAccount = '';
    public $toAccount = '';
    public $toAccountDetails = null;
    public $amount = '';
    public $narration = '';
    public $transferDate = '';
    public $referenceNumber = '';

    // Account balances
    public $fromAccountBalance = 0;
    public $toAccountBalance = 0;

    // Statistics
    public $totalTransfers = 0;
    public $todayTransfers = 0;
    public $pendingTransfers = 0;
    public $transferVolume = 0;

    // Loading states
    public $isLoading = false;
    public $isVerifying = false;
    public $isSubmitting = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    // Transfer history
    public $transferHistory = [];

    protected $rules = [
        'membershipNumber' => 'required|min:3',
        'transferType' => 'required|in:member_to_member,member_to_bank,bank_to_member',
        'fromAccount' => 'required',
        'toAccount' => 'required',
        'amount' => 'required|numeric|min:0.01',
        'narration' => 'required|string|max:500',
        'transferDate' => 'required|date',
        'referenceNumber' => 'required|string|max:255',
    ];

    protected $messages = [
        'membershipNumber.required' => 'Membership number is required.',
        'transferType.required' => 'Transfer type is required.',
        'fromAccount.required' => 'Source account is required.',
        'toAccount.required' => 'Destination account is required.',
        'amount.required' => 'Amount is required.',
        'amount.numeric' => 'Amount must be a valid number.',
        'amount.min' => 'Amount must be greater than 0.',
        'narration.required' => 'Transfer description is required.',
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->transferDate = now()->format('Y-m-d');
        $this->referenceNumber = 'INT-' . strtoupper(uniqid());
    }

    public function loadStatistics()
    {
        try {
            // Total transfers from internal_transfers table
            $this->totalTransfers = InternalTransfer::count();

            // Today's transfers
            $today = now()->format('Y-m-d');
            $this->todayTransfers = InternalTransfer::whereDate('transfer_date', $today)->count();

            // Transfer volume (total amount)
            $this->transferVolume = InternalTransfer::where('status', 'posted')->sum('amount');

            // Load recent transfer history
            $this->transferHistory = InternalTransfer::with(['fromAccount', 'toAccount', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading statistics: ' . $e->getMessage();
        }
    }

    public function showTransferModal()
    {
        $this->resetForm();
        $this->showTransferModal = true;
    }

    public function showTransferHistoryModal()
    {
        $this->loadStatistics();
        $this->showTransferHistoryModal = true;
    }

    public function closeModal()
    {
        $this->showTransferModal = false;
        $this->showTransferHistoryModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->membershipNumber = '';
        $this->verifiedMember = null;
        $this->memberAccounts = [];
        $this->bankAccounts = [];
        $this->transferType = 'member_to_member';
        $this->fromAccount = '';
        $this->toAccount = '';
        $this->toAccountDetails = null;
        $this->amount = '';
        $this->narration = '';
        $this->transferDate = now()->format('Y-m-d');
        $this->referenceNumber = 'INT-' . strtoupper(uniqid());
        $this->fromAccountBalance = 0;
        $this->toAccountBalance = 0;
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
                    ->whereIn('product_number', ['2000', '3000']) // Savings and Deposits
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

    public function updatedTransferType()
    {
        $this->fromAccount = '';
        $this->toAccount = '';
        $this->toAccountDetails = null;
        $this->fromAccountBalance = 0;
        $this->toAccountBalance = 0;
    }

    public function updatedFromAccount()
    {
        if ($this->fromAccount) {
            $account = AccountsModel::where('account_number', $this->fromAccount)->first();
            if ($account) {
                $this->fromAccountBalance = $account->balance;
            }
        }
    }

    public function updatedToAccount()
    {
        if ($this->toAccount) {
            if ($this->transferType === 'member_to_bank') {
                $bankAccount = BankAccount::find($this->toAccount);
                if ($bankAccount) {
                    $this->toAccountDetails = $bankAccount;
                }
            } else {
                $account = AccountsModel::where('account_number', $this->toAccount)->first();
                if ($account) {
                    $this->toAccountBalance = $account->balance;
                    $this->toAccountDetails = $account;
                }
            }
        }
    }

    public function submitTransfer()
    {
        $this->isSubmitting = true;
        $this->errorMessage = '';

        DB::beginTransaction();
        try {
            // Validate all required fields
            $this->validate();

            // Verify source account exists and is active
            $fromAccount = AccountsModel::where('account_number', $this->fromAccount)->first();
            if (!$fromAccount) {
                throw new \Exception('Source account not found.');
            }

            if ($fromAccount->status !== 'ACTIVE') {
                throw new \Exception('Source account is not active.');
            }

            // Check sufficient balance
            if ($fromAccount->balance < $this->amount) {
                throw new \Exception('Insufficient balance. Available balance: TZS ' . number_format($fromAccount->balance, 2));
            }

            // Verify destination account
            $toAccount = null;
            if ($this->transferType === 'member_to_bank') {
                $toAccount = BankAccount::find($this->toAccount);
                if (!$toAccount) {
                    throw new \Exception('Destination bank account not found.');
                }
            } else {
                $toAccount = AccountsModel::where('account_number', $this->toAccount)->first();
                if (!$toAccount) {
                    throw new \Exception('Destination account not found.');
                }
                if ($toAccount->status !== 'ACTIVE') {
                    throw new \Exception('Destination account is not active.');
                }
            }

            // Process the transfer
            $this->processTransfer($fromAccount, $toAccount);

            DB::commit();
            
            $this->successMessage = 'Internal transfer processed successfully!';
            $this->loadStatistics();
            
            // Reset form after successful submission
            $this->resetForm();
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error processing transfer: ' . $e->getMessage();
            Log::error('Error in submitTransfer: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    private function processTransfer($fromAccount, $toAccount)
    {
        $totalAmount = $this->amount;
        
        // Create internal transfer record
        $transfer = InternalTransfer::create([
            'transfer_date' => $this->transferDate,
            'transfer_type' => $this->transferType,
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => $totalAmount,
            'narration' => $this->narration,
            'status' => 'posted',
            'created_by' => Auth::id(),
        ]);

        // Post the transaction using TransactionPostingService
        $transactionService = new TransactionPostingService();
        
        if ($this->transferType === 'member_to_bank') {
            // Member to bank transfer
            $transactionData = [
                'first_account' => $this->fromAccount, // Debit account (member)
                'second_account' => $toAccount->internal_mirror_account_number, // Credit account (bank)
                'amount' => $totalAmount,
                'narration' => 'Internal transfer to bank: ' . $this->narration . ' - Ref: ' . $this->referenceNumber,
                'action' => 'internal_transfer_member_to_bank'
            ];
        } else {
            // Member to member transfer
            $transactionData = [
                'first_account' => $this->fromAccount, // Debit account (source member)
                'second_account' => $this->toAccount, // Credit account (destination member)
                'amount' => $totalAmount,
                'narration' => 'Internal transfer between members: ' . $this->narration . ' - Ref: ' . $this->referenceNumber,
                'action' => 'internal_transfer_member_to_member'
            ];
        }

        Log::info('Posting internal transfer transaction', [
            'transfer_id' => $transfer->id,
            'transaction_data' => $transactionData
        ]);

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            Log::error('Internal transfer transaction posting failed', [
                'transfer_id' => $transfer->id,
                'error' => $result['message'] ?? 'Unknown error',
                'transaction_data' => $transactionData
            ]);
            throw new \Exception('Failed to post transfer transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Internal transfer transaction posted successfully', [
            'transfer_id' => $transfer->id,
            'transaction_reference' => $result['reference'] ?? null,
            'amount' => $totalAmount
        ]);
    }

    public function getBankAccountsProperty()
    {
        return BankAccount::where('status', 'ACTIVE')->get();
    }

    public function render()
    {
        return view('livewire.dashboard.internal-transfers', [
            'bankAccounts' => $this->bankAccounts
        ]);
    }
}
