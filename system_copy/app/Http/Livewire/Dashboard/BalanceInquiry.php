<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use App\Services\MembershipVerificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class BalanceInquiry extends Component
{
    // Member verification
    public $membershipNumber = '';
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $selectedAccount = '';
    public $accountDetails = null;

    // Balance information
    public $currentBalance = 0;
    public $availableBalance = 0;
    public $lockedAmount = 0;
    public $lastTransactionDate = null;
    public $accountStatus = '';

    // Transaction summary
    public $todayTransactions = 0;
    public $thisMonthTransactions = 0;
    public $lastTransactionAmount = 0;
    public $lastTransactionType = '';

    // Statistics
    public $totalAccounts = 0;
    public $activeAccounts = 0;
    public $totalBalance = 0;
    public $todayInquiries = 0;

    // Loading states
    public $isLoading = false;
    public $isVerifying = false;
    public $isInquiring = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'membershipNumber' => 'required|min:3',
        'selectedAccount' => 'required',
    ];

    protected $messages = [
        'membershipNumber.required' => 'Membership number is required.',
        'selectedAccount.required' => 'Please select an account.',
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        try {
            // Total accounts
            $this->totalAccounts = AccountsModel::where('status', 'ACTIVE')->count();
            
            // Active accounts
            $this->activeAccounts = AccountsModel::where('status', 'ACTIVE')->count();
            
            // Total balance across all accounts
            $this->totalBalance = AccountsModel::where('status', 'ACTIVE')->sum('balance');
            
            // Today's inquiries (simulated - in real system, track inquiry logs)
            $this->todayInquiries = 0;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error loading statistics: ' . $e->getMessage();
        }
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
                    ->whereIn('product_number', ['1000', '2000', '3000']) // Shares, Savings, Deposits
                    ->where('status', 'ACTIVE')
                    ->get();
                
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
            $this->performBalanceInquiry();
        }
    }

    public function performBalanceInquiry()
    {
        if (!$this->selectedAccount) {
            return;
        }

        $this->isInquiring = true;
        $this->errorMessage = '';

        try {
            // Get account details
            $this->accountDetails = AccountsModel::where('account_number', $this->selectedAccount)->first();
            
            if (!$this->accountDetails) {
                throw new \Exception('Account not found.');
            }

            // Get current balance
            $this->currentBalance = (float) $this->accountDetails->balance;
            $this->lockedAmount = (float) ($this->accountDetails->locked_amount ?? 0);
            $this->availableBalance = $this->currentBalance - $this->lockedAmount;
            $this->accountStatus = $this->accountDetails->status;

            // Get transaction statistics
            $this->getTransactionStatistics();

            // Get last transaction date
            $this->getLastTransactionInfo();

            $this->successMessage = 'Balance inquiry completed successfully.';
            
            // Log the inquiry
            $this->logBalanceInquiry();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error performing balance inquiry: ' . $e->getMessage();
            Log::error('Balance inquiry error: ' . $e->getMessage());
        } finally {
            $this->isInquiring = false;
        }
    }

    private function getTransactionStatistics()
    {
        try {
            // Today's transactions
            $today = now()->format('Y-m-d');
            $this->todayTransactions = general_ledger::where('record_on_account_number', $this->selectedAccount)
                ->whereDate('created_at', $today)
                ->count();

            // This month's transactions
            $thisMonth = now()->format('Y-m');
            $this->thisMonthTransactions = general_ledger::where('record_on_account_number', $this->selectedAccount)
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth])
                ->count();

        } catch (\Exception $e) {
            Log::error('Error getting transaction statistics: ' . $e->getMessage());
        }
    }

    private function getLastTransactionInfo()
    {
        try {
            $lastTransaction = general_ledger::where('record_on_account_number', $this->selectedAccount)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastTransaction) {
                $this->lastTransactionDate = $lastTransaction->created_at;
                $this->lastTransactionAmount = (float) ($lastTransaction->credit ?? $lastTransaction->debit ?? 0);
                $this->lastTransactionType = $lastTransaction->transaction_type ?? 'Unknown';
            }

        } catch (\Exception $e) {
            Log::error('Error getting last transaction info: ' . $e->getMessage());
        }
    }

    private function logBalanceInquiry()
    {
        try {
            // In a real system, you would log this to an audit table
            Log::info('Balance inquiry performed', [
                'membership_number' => $this->membershipNumber,
                'account_number' => $this->selectedAccount,
                'balance' => $this->currentBalance,
                'inquiry_time' => now(),
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging balance inquiry: ' . $e->getMessage());
        }
    }

    public function resetInquiry()
    {
        $this->membershipNumber = '';
        $this->verifiedMember = null;
        $this->memberAccounts = [];
        $this->selectedAccount = '';
        $this->accountDetails = null;
        $this->currentBalance = 0;
        $this->availableBalance = 0;
        $this->lockedAmount = 0;
        $this->lastTransactionDate = null;
        $this->accountStatus = '';
        $this->todayTransactions = 0;
        $this->thisMonthTransactions = 0;
        $this->lastTransactionAmount = 0;
        $this->lastTransactionType = '';
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->resetValidation();
    }

    public function getAccountTypeLabel($productNumber)
    {
        switch ($productNumber) {
            case '1000':
                return 'Shares';
            case '2000':
                return 'Savings';
            case '3000':
                return 'Deposits';
            default:
                return 'Unknown';
        }
    }

    public function getAccountTypeColor($productNumber)
    {
        switch ($productNumber) {
            case '1000':
                return 'bg-blue-100 text-blue-800';
            case '2000':
                return 'bg-green-100 text-green-800';
            case '3000':
                return 'bg-purple-100 text-purple-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function getStatusColor($status)
    {
        switch ($status) {
            case 'ACTIVE':
                return 'bg-green-100 text-green-800';
            case 'PENDING':
                return 'bg-yellow-100 text-yellow-800';
            case 'SUSPENDED':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    public function render()
    {
        return view('livewire.dashboard.balance-inquiry');
    }
}
