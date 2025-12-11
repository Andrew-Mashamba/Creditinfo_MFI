<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Services\TransactionPostingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EarlySettlement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Settlement calculation
    public $selectedLoan = null;
    public $settlementDate;
    public $outstandingPrincipal = 0;
    public $outstandingInterest = 0;
    public $penaltyAmount = 0;
    public $discountAmount = 0;
    public $settlementAmount = 0;
    public $discountRate = 0;
    public $waiverRate = 0;
    public $settlementReason = '';
    public $settlementNotes = '';
    
    // Payment details
    public $paymentMethod = 'cash';
    public $paymentReference = '';
    public $receiptNumber = '';
    
    // Search and filters
    public $searchTerm = '';
    public $statusFilter = 'active';
    public $dateFrom = '';
    public $dateTo = '';
    
    // Modal states
    public $showSettlementModal = false;
    public $showDetailsModal = false;
    public $showApprovalModal = false;
    
    // Approval
    public $settlementToApprove = null;
    public $approvalNotes = '';
    public $rejectionReason = '';

    protected $rules = [
        'settlementDate' => 'required|date',
        'discountRate' => 'nullable|numeric|min:0|max:100',
        'waiverRate' => 'nullable|numeric|min:0|max:100',
        'settlementReason' => 'required|string|min:10',
        'settlementNotes' => 'nullable|string',
        'paymentMethod' => 'required|in:cash,bank_transfer,mobile_money,cheque',
        'paymentReference' => 'required_if:paymentMethod,bank_transfer,mobile_money,cheque',
        'receiptNumber' => 'nullable|string'
    ];

    protected $listeners = [
        'refreshSettlements' => '$refresh',
        'calculateSettlement' => 'calculateSettlementAmount'
    ];

    public function mount()
    {
        $this->settlementDate = now()->format('Y-m-d');
    }

    public function getEligibleLoansProperty()
    {
        return LoansModel::with(['client', 'product'])
            ->where(function($query) {
                $query->where('loan_status', 'ACTIVE')
                      ->orWhere('loan_status', 'OVERDUE');
            })
            ->where('principle', '>', 0)
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loan_id', 'like', "%{$this->searchTerm}%")
                      ->orWhereHas('client', function($subQ) {
                          $subQ->where('first_name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('last_name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('client_number', 'like', "%{$this->searchTerm}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getSettlementHistoryProperty()
    {
        return DB::table('loan_settlements')
            ->join('loans', 'loan_settlements.loan_id', '=', 'loans.loan_id')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->select(
                'loan_settlements.*',
                'loans.loan_id',
                'loans.principle as loan_amount',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as client_name"),
                'clients.client_number'
            )
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loans.loan_id', 'like', "%{$this->searchTerm}%")
                      ->orWhere('clients.first_name', 'like', "%{$this->searchTerm}%")
                      ->orWhere('clients.last_name', 'like', "%{$this->searchTerm}%");
                });
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function($query) {
                $query->where('loan_settlements.status', $this->statusFilter);
            })
            ->when($this->dateFrom, function($query) {
                $query->whereDate('loan_settlements.settlement_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($query) {
                $query->whereDate('loan_settlements.settlement_date', '<=', $this->dateTo);
            })
            ->orderBy('loan_settlements.created_at', 'desc')
            ->paginate(10, ['*'], 'historyPage');
    }

    public function initiateSettlement($loanId)
    {
        $this->selectedLoan = LoansModel::with(['client', 'product'])->find($loanId);
        
        if (!$this->selectedLoan) {
            session()->flash('error', 'Loan not found');
            return;
        }
        
        $this->calculateSettlementAmount();
        $this->showSettlementModal = true;
    }

    public function calculateSettlementAmount()
    {
        if (!$this->selectedLoan) {
            return;
        }
        
        // Get outstanding balances
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', $this->selectedLoan->loan_id)
            ->where('status', '!=', 'PAID')
            ->get();
        
        $this->outstandingPrincipal = $schedules->sum('principle');
        $this->outstandingInterest = $schedules->sum('interest');
        
        // Calculate penalties (if any)
        $penalties = DB::table('loan_penalties')
            ->where('loan_id', $this->selectedLoan->loan_id)
            ->where('status', 'PENDING')
            ->sum('amount');
        
        $this->penaltyAmount = $penalties;
        
        // Apply discount if provided
        $totalOutstanding = $this->outstandingPrincipal + $this->outstandingInterest + $this->penaltyAmount;
        
        if ($this->discountRate > 0) {
            $this->discountAmount = round(($this->outstandingInterest * $this->discountRate / 100), 2);
        }
        
        if ($this->waiverRate > 0) {
            $penaltyWaiver = round(($this->penaltyAmount * $this->waiverRate / 100), 2);
            $this->discountAmount += $penaltyWaiver;
        }
        
        $this->settlementAmount = $totalOutstanding - $this->discountAmount;
    }

    public function processSettlement()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Create settlement record
            $settlementId = DB::table('loan_settlements')->insertGetId([
                'loan_id' => $this->selectedLoan->loan_id,
                'settlement_date' => $this->settlementDate,
                'outstanding_principal' => $this->outstandingPrincipal,
                'outstanding_interest' => $this->outstandingInterest,
                'penalty_amount' => $this->penaltyAmount,
                'discount_amount' => $this->discountAmount,
                'settlement_amount' => $this->settlementAmount,
                'discount_rate' => $this->discountRate,
                'waiver_rate' => $this->waiverRate,
                'reason' => $this->settlementReason,
                'notes' => $this->settlementNotes,
                'payment_method' => $this->paymentMethod,
                'payment_reference' => $this->paymentReference,
                'receipt_number' => $this->receiptNumber,
                'status' => 'pending_approval',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Log the settlement request
            Log::info('Early settlement initiated', [
                'loan_id' => $this->selectedLoan->loan_id,
                'settlement_amount' => $this->settlementAmount,
                'discount' => $this->discountAmount,
                'user' => Auth::user()->name
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Settlement request submitted for approval');
            $this->closeSettlementModal();
            $this->emit('refreshSettlements');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Settlement processing failed', [
                'loan_id' => $this->selectedLoan->loan_id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to process settlement: ' . $e->getMessage());
        }
    }

    public function approveSettlement($settlementId)
    {
        $this->settlementToApprove = DB::table('loan_settlements')
            ->where('id', $settlementId)
            ->first();
        
        if (!$this->settlementToApprove) {
            session()->flash('error', 'Settlement record not found');
            return;
        }
        
        $this->showApprovalModal = true;
    }

    public function confirmApproval()
    {
        try {
            DB::beginTransaction();
            
            $settlement = $this->settlementToApprove;
            $loan = LoansModel::where('loan_id', $settlement->loan_id)->first();
            
            // Update settlement status
            DB::table('loan_settlements')
                ->where('id', $settlement->id)
                ->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approval_date' => now(),
                    'approval_notes' => $this->approvalNotes,
                    'updated_at' => now()
                ]);
            
            // Process payment posting
            $transactionService = new TransactionPostingService();
            
            // Post settlement payment
            $transactionService->postTransaction([
                'transaction_type' => 'LOAN_SETTLEMENT',
                'loan_id' => $loan->loan_id,
                'amount' => $settlement->settlement_amount,
                'payment_method' => $settlement->payment_method,
                'reference' => $settlement->payment_reference,
                'description' => "Early settlement for loan {$loan->loan_id}"
            ]);
            
            // Update loan status
            $loan->update([
                'loan_status' => 'SETTLED',
                'settlement_date' => $settlement->settlement_date,
                'settlement_amount' => $settlement->settlement_amount,
                'updated_at' => now()
            ]);
            
            // Update all pending schedules to SETTLED
            DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->where('status', '!=', 'PAID')
                ->update([
                    'status' => 'SETTLED',
                    'updated_at' => now()
                ]);
            
            // Clear any pending penalties
            DB::table('loan_penalties')
                ->where('loan_id', $loan->loan_id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'WAIVED',
                    'waiver_reason' => 'Early settlement',
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            session()->flash('success', 'Settlement approved and processed successfully');
            $this->closeApprovalModal();
            $this->emit('refreshSettlements');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Settlement approval failed', [
                'settlement_id' => $settlement->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to approve settlement: ' . $e->getMessage());
        }
    }

    public function rejectSettlement()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:10'
        ]);
        
        try {
            DB::table('loan_settlements')
                ->where('id', $this->settlementToApprove->id)
                ->update([
                    'status' => 'rejected',
                    'rejected_by' => Auth::id(),
                    'rejection_date' => now(),
                    'rejection_reason' => $this->rejectionReason,
                    'updated_at' => now()
                ]);
            
            session()->flash('info', 'Settlement request rejected');
            $this->closeApprovalModal();
            $this->emit('refreshSettlements');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reject settlement: ' . $e->getMessage());
        }
    }

    public function exportSettlements()
    {
        $settlements = DB::table('loan_settlements')
            ->join('loans', 'loan_settlements.loan_id', '=', 'loans.loan_id')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->select(
                'loan_settlements.*',
                'loans.loan_id',
                'loans.principle as loan_amount',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as client_name"),
                'clients.client_number'
            )
            ->when($this->dateFrom, function($query) {
                $query->whereDate('loan_settlements.settlement_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($query) {
                $query->whereDate('loan_settlements.settlement_date', '<=', $this->dateTo);
            })
            ->get();
        
        $csv = "Loan ID,Client Name,Client Number,Settlement Date,Outstanding Principal,Outstanding Interest,Penalties,Discount,Settlement Amount,Status\n";
        
        foreach ($settlements as $settlement) {
            $csv .= "{$settlement->loan_id},"
                  . "{$settlement->client_name},"
                  . "{$settlement->client_number},"
                  . "{$settlement->settlement_date},"
                  . "{$settlement->outstanding_principal},"
                  . "{$settlement->outstanding_interest},"
                  . "{$settlement->penalty_amount},"
                  . "{$settlement->discount_amount},"
                  . "{$settlement->settlement_amount},"
                  . "{$settlement->status}\n";
        }
        
        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'settlements_' . now()->format('Y-m-d') . '.csv');
    }

    public function closeSettlementModal()
    {
        $this->showSettlementModal = false;
        $this->resetSettlementData();
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->settlementToApprove = null;
        $this->approvalNotes = '';
        $this->rejectionReason = '';
    }

    private function resetSettlementData()
    {
        $this->selectedLoan = null;
        $this->outstandingPrincipal = 0;
        $this->outstandingInterest = 0;
        $this->penaltyAmount = 0;
        $this->discountAmount = 0;
        $this->settlementAmount = 0;
        $this->discountRate = 0;
        $this->waiverRate = 0;
        $this->settlementReason = '';
        $this->settlementNotes = '';
        $this->paymentMethod = 'cash';
        $this->paymentReference = '';
        $this->receiptNumber = '';
        $this->settlementDate = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.active-loan.early-settlement', [
            'eligibleLoans' => $this->eligibleLoans,
            'settlementHistory' => $this->settlementHistory
        ]);
    }
}
