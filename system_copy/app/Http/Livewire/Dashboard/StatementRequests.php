<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use App\Services\MembershipVerificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class StatementRequests extends Component
{
    // Member verification
    public $membershipNumber = '';
    public $verifiedMember = null;
    public $memberAccounts = [];
    public $selectedAccount = '';
    public $accountDetails = null;

    // Statement parameters
    public $startDate = '';
    public $endDate = '';
    public $statementType = 'detailed'; // 'summary' or 'detailed'
    public $format = 'pdf'; // 'pdf' or 'excel'
    public $includeTransactions = true;
    public $includeBalanceHistory = false;

    // Statement data
    public $statementData = null;
    public $transactions = [];
    public $openingBalance = 0;
    public $closingBalance = 0;
    public $totalCredits = 0;
    public $totalDebits = 0;

    // Statistics
    public $totalStatements = 0;
    public $todayStatements = 0;
    public $pendingStatements = 0;
    public $activeAccounts = 0;

    // Loading states
    public $isLoading = false;
    public $isVerifying = false;
    public $isGenerating = false;
    public $isDownloading = false;

    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'membershipNumber' => 'required|min:3',
        'selectedAccount' => 'required',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'statementType' => 'required|in:summary,detailed',
        'format' => 'required|in:pdf,excel',
    ];

    protected $messages = [
        'membershipNumber.required' => 'Membership number is required.',
        'selectedAccount.required' => 'Please select an account.',
        'startDate.required' => 'Start date is required.',
        'endDate.required' => 'End date is required.',
        'endDate.after_or_equal' => 'End date must be after or equal to start date.',
        'statementType.required' => 'Statement type is required.',
        'format.required' => 'Format is required.',
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function loadStatistics()
    {
        try {
            // Total statements (simulated - in real system, track statement requests)
            $this->totalStatements = 0;
            
            // Today's statements
            $this->todayStatements = 0;
            
            // Pending statements
            $this->pendingStatements = 0;
            
            // Active accounts
            $this->activeAccounts = AccountsModel::where('status', 'ACTIVE')->count();

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
            $this->accountDetails = AccountsModel::where('account_number', $this->selectedAccount)->first();
        }
    }

    public function generateStatement()
    {
        $this->validate();

        $this->isGenerating = true;
        $this->errorMessage = '';

        try {
            // Get account details
            $this->accountDetails = AccountsModel::where('account_number', $this->selectedAccount)->first();
            
            if (!$this->accountDetails) {
                throw new \Exception('Account not found.');
            }

            // Generate statement data
            $this->generateStatementData();

            $this->successMessage = 'Statement generated successfully.';
            
            // Log the statement request
            $this->logStatementRequest();

        } catch (\Exception $e) {
            $this->errorMessage = 'Error generating statement: ' . $e->getMessage();
            Log::error('Statement generation error: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    private function generateStatementData()
    {
        try {
            $startDate = Carbon::parse($this->startDate)->startOfDay();
            $endDate = Carbon::parse($this->endDate)->endOfDay();

            // Get opening balance (balance before start date)
            $this->openingBalance = $this->getOpeningBalance($startDate);

            // Get transactions within the period
            $this->transactions = $this->getTransactions($startDate, $endDate);

            // Calculate totals
            $this->totalCredits = $this->transactions->sum('credit');
            $this->totalDebits = $this->transactions->sum('debit');

            // Get closing balance (current balance)
            $this->closingBalance = (float) $this->accountDetails->balance;

            // Prepare statement data
            $this->statementData = [
                'account' => $this->accountDetails,
                'member' => $this->verifiedMember,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'opening_balance' => $this->openingBalance,
                'closing_balance' => $this->closingBalance,
                'total_credits' => $this->totalCredits,
                'total_debits' => $this->totalDebits,
                'transactions' => $this->transactions,
                'statement_type' => $this->statementType,
                'generated_at' => now(),
                'generated_by' => auth()->user()->name ?? 'System'
            ];

        } catch (\Exception $e) {
            Log::error('Error generating statement data: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getOpeningBalance($startDate)
    {
        try {
            // Get the last transaction before the start date
            $lastTransaction = general_ledger::where('record_on_account_number', $this->selectedAccount)
                ->where('created_at', '<', $startDate)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastTransaction) {
                return (float) $lastTransaction->record_on_account_number_balance;
            }

            // If no previous transactions, return 0
            return 0;

        } catch (\Exception $e) {
            Log::error('Error getting opening balance: ' . $e->getMessage());
            return 0;
        }
    }

    private function getTransactions($startDate, $endDate)
    {
        try {
            $transactions = general_ledger::where('record_on_account_number', $this->selectedAccount)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'date' => $transaction->created_at,
                        'reference' => $transaction->reference_number,
                        'description' => $transaction->narration,
                        'debit' => (float) ($transaction->debit ?? 0),
                        'credit' => (float) ($transaction->credit ?? 0),
                        'balance' => (float) ($transaction->record_on_account_number_balance ?? 0),
                        'type' => $transaction->transaction_type,
                        'status' => $transaction->trans_status
                    ];
                });

            return $transactions;

        } catch (\Exception $e) {
            Log::error('Error getting transactions: ' . $e->getMessage());
            return collect();
        }
    }

    public function downloadStatement()
    {
        if (!$this->statementData) {
            $this->errorMessage = 'Please generate a statement first.';
            return;
        }

        $this->isDownloading = true;

        try {
            if ($this->format === 'pdf') {
                $this->downloadPDFStatement();
            } else {
                $this->downloadExcelStatement();
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Error downloading statement: ' . $e->getMessage();
            Log::error('Statement download error: ' . $e->getMessage());
        } finally {
            $this->isDownloading = false;
        }
    }

    private function downloadPDFStatement()
    {
        // In a real implementation, you would use a PDF library like DomPDF or TCPDF
        // For now, we'll simulate the download
        $filename = 'statement_' . $this->selectedAccount . '_' . $this->startDate . '_to_' . $this->endDate . '.pdf';
        
        $this->dispatchBrowserEvent('download-statement', [
            'filename' => $filename,
            'format' => 'pdf',
            'data' => $this->statementData
        ]);

        $this->successMessage = 'PDF statement download initiated.';
    }

    private function downloadExcelStatement()
    {
        // In a real implementation, you would use a library like Laravel Excel
        // For now, we'll simulate the download
        $filename = 'statement_' . $this->selectedAccount . '_' . $this->startDate . '_to_' . $this->endDate . '.xlsx';
        
        $this->dispatchBrowserEvent('download-statement', [
            'filename' => $filename,
            'format' => 'excel',
            'data' => $this->statementData
        ]);

        $this->successMessage = 'Excel statement download initiated.';
    }

    private function logStatementRequest()
    {
        try {
            Log::info('Statement request generated', [
                'membership_number' => $this->membershipNumber,
                'account_number' => $this->selectedAccount,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'statement_type' => $this->statementType,
                'format' => $this->format,
                'request_time' => now(),
                'user_id' => auth()->id()
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging statement request: ' . $e->getMessage());
        }
    }

    public function resetStatement()
    {
        $this->membershipNumber = '';
        $this->verifiedMember = null;
        $this->memberAccounts = [];
        $this->selectedAccount = '';
        $this->accountDetails = null;
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->statementType = 'detailed';
        $this->format = 'pdf';
        $this->includeTransactions = true;
        $this->includeBalanceHistory = false;
        $this->statementData = null;
        $this->transactions = [];
        $this->openingBalance = 0;
        $this->closingBalance = 0;
        $this->totalCredits = 0;
        $this->totalDebits = 0;
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

    public function render()
    {
        return view('livewire.dashboard.statement-requests');
    }
}
