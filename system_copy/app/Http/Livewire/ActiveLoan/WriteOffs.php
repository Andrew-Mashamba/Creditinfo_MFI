<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Services\LoanLossProvisionService;
use App\Models\LoanWriteOff;
use App\Models\LoanWriteoffRecovery;
use App\Models\LoanCollectionEffort;
use App\Models\WriteoffApprovalWorkflow;
use App\Services\WriteoffAnalyticsService;
use App\Services\MemberCommunicationService;
use Carbon\Carbon;

class WriteOffs extends Component
{
    // Tab management
    public $activeTab = 'overview';
    use WithPagination;
    
    public $showWriteOffModal = false;
    public $showRecoveryModal = false;
    public $showCollectionModal = false;
    public $showAnalyticsModal = false;
    public $showThresholdModal = false;
    public $showApprovalModal = false;
    public $selectedLoan = null;
    public $selectedWriteOff = null;
    public $writeOffReason = '';
    public $writeOffAmount = 0;
    public $searchTerm = '';
    public $filterStatus = 'pending';
    public $dateFrom;
    public $dateTo;
    
    // Recovery fields
    public $recoveryAmount = 0;
    public $recoveryMethod = 'cash';
    public $recoveryDescription = '';
    public $recoverySource = 'client';
    
    // Collection effort fields
    public $effortType = 'call';
    public $effortDescription = '';
    public $effortOutcome = '';
    public $promisedAmount = 0;
    public $promisedDate = null;
    public $clientResponse = '';
    public $costIncurred = 0;
    
    // Board approval fields
    public $boardApprovalThreshold = 1000000;
    public $managerApprovalThreshold = 100000;
    public $directorApprovalThreshold = 500000;
    public $ceoApprovalThreshold = 750000;
    public $requiresDocumentation = true;
    public $minimumCollectionEfforts = 3;
    public $recoveryTrackingPeriod = 12;
    
    // Analytics fields
    public $analyticsDateFrom;
    public $analyticsDateTo;
    public $analyticsType = 'monthly';
    
    protected $paginationTheme = 'bootstrap';
    
    protected $rules = [
        'writeOffReason' => 'required|min:10',
        'writeOffAmount' => 'required|numeric|min:0',
        'recoveryAmount' => 'required|numeric|min:0',
        'recoveryMethod' => 'required|string',
        'recoveryDescription' => 'required|min:10',
        'effortType' => 'required|string',
        'effortDescription' => 'required|min:10',
        'effortOutcome' => 'required|string',
    ];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->analyticsDateFrom = Carbon::now()->subMonths(12)->format('Y-m-d');
        $this->analyticsDateTo = Carbon::now()->format('Y-m-d');
        
        // Load approval thresholds from institution settings
        $institution = DB::table('institutions')->first();
        if ($institution) {
            $this->boardApprovalThreshold = $institution->writeoff_board_approval_threshold ?? 1000000;
            $this->managerApprovalThreshold = $institution->writeoff_manager_approval_threshold ?? 100000;
            $this->minimumCollectionEfforts = $institution->writeoff_minimum_collection_efforts ?? 3;
            $this->recoveryTrackingPeriod = $institution->writeoff_recovery_tracking_period ?? 12;
        }
    }
    
    public function getEligibleLoansProperty()
    {
        return DB::table('loans')
            ->select([
                'loans.id',
                'loans.loan_id',
                'loans.client_number',
                'loans.loan_sub_product',
                'loans.principle',
                DB::raw('loans.principle - COALESCE(loans.total_principal_paid, 0) as outstanding'),
                'loans.total_arrears',
                'loans.days_in_arrears',
                'loans.loan_classification',
                'loans.disbursement_date',
                'clients.first_name',
                'clients.last_name',
                'clients.mobile_phone_number'
            ])
            ->leftJoin('clients', 'loans.client_id', '=', 'clients.id')
            ->where('loans.loan_status', 'active')
            ->where('loans.loan_classification', 'LOSS')
            ->where('loans.days_in_arrears', '>', 180)
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loans.loan_id', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('loans.client_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.last_name', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('loans.days_in_arrears', 'desc')
            ->paginate(10);
    }
    
    public function getWrittenOffLoansProperty()
    {
        return LoanWriteOff::with(['initiator', 'approver', 'loan', 'recoveries'])
            ->join('loans', 'loan_write_offs.loan_id', '=', 'loans.loan_id')
            ->leftJoin('clients', 'loans.client_id', '=', 'clients.id')
            ->select([
                'loan_write_offs.*',
                'loans.loan_sub_product',
                'clients.first_name',
                'clients.last_name'
            ])
            ->whereBetween('loan_write_offs.write_off_date', [$this->dateFrom, $this->dateTo])
            ->when($this->filterStatus !== 'all', function($query) {
                $query->where('loan_write_offs.status', $this->filterStatus);
            })
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loan_write_offs.loan_id', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('loans.client_number', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('clients.last_name', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('loan_write_offs.write_off_date', 'desc')
            ->paginate(10);
    }
    
    public function initiateWriteOff($loanId)
    {
        $this->selectedLoan = DB::table('loans')
            ->select([
                'loans.*',
                'clients.first_name',
                'clients.last_name',
                DB::raw('loans.principle - COALESCE(loans.total_principal_paid, 0) as outstanding')
            ])
            ->leftJoin('clients', 'loans.client_id', '=', 'clients.id')
            ->where('loans.id', $loanId)
            ->first();
            
        if ($this->selectedLoan) {
            $this->writeOffAmount = $this->selectedLoan->outstanding;
            $this->showWriteOffModal = true;
        }
    }
    
    public function processWriteOff()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Check if minimum collection efforts are documented
            $institution = DB::table('institutions')->first();
            $minimumEfforts = $institution->writeoff_minimum_collection_efforts ?? 3;
            
            $effortCount = LoanCollectionEffort::where('loan_id', $this->selectedLoan->loan_id)->count();
            
            if ($this->requiresDocumentation && $effortCount < $minimumEfforts) {
                throw new \Exception("Minimum {$minimumEfforts} collection efforts must be documented before write-off.");
            }
            
            // Calculate breakdown
            $outstandingPrincipal = $this->selectedLoan->principle - ($this->selectedLoan->total_principal_paid ?? 0);
            $outstandingInterest = $this->selectedLoan->total_arrears ?? 0;
            
            // Create comprehensive write-off record
            $writeOff = LoanWriteOff::create([
                'loan_id' => $this->selectedLoan->loan_id,
                'client_number' => $this->selectedLoan->client_number,
                'write_off_date' => now(),
                'principal_amount' => $outstandingPrincipal,
                'interest_amount' => $outstandingInterest,
                'penalty_amount' => 0,
                'total_amount' => $this->writeOffAmount,
                'reason' => $this->writeOffReason,
                'writeoff_type' => 'full',
                'status' => 'pending_approval',
                'initiated_by' => auth()->id(),
                'board_approval_threshold' => $this->boardApprovalThreshold,
                'requires_board_approval' => $this->writeOffAmount >= $this->boardApprovalThreshold,
                'collection_efforts' => LoanCollectionEffort::where('loan_id', $this->selectedLoan->loan_id)
                    ->get()->toArray(),
                'audit_trail' => [[
                    'timestamp' => now()->toISOString(),
                    'action' => 'writeoff_initiated',
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'data' => [
                        'amount' => $this->writeOffAmount,
                        'reason' => $this->writeOffReason,
                        'requires_board_approval' => $this->writeOffAmount >= $this->boardApprovalThreshold
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]]
            ]);
            
            // Initialize approval workflow
            WriteoffApprovalWorkflow::initializeWorkflow($writeOff);
            
            // Update loan status
            DB::table('loans')
                ->where('loan_id', $this->selectedLoan->loan_id)
                ->update([
                    'loan_status' => 'written_off_pending',
                    'updated_at' => now()
                ]);
            
            // Send member notification
            $this->sendMemberNotification($writeOff);
            
            DB::commit();
            
            session()->flash('success', 'Write-off initiated successfully. ' . 
                ($writeOff->requires_board_approval ? 'Requires board approval.' : 'Pending manager approval.'));
            $this->showWriteOffModal = false;
            $this->reset(['selectedLoan', 'writeOffReason', 'writeOffAmount']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing write-off: ' . $e->getMessage());
        }
    }
    
    public function approveWriteOff($writeOffId)
    {
        try {
            DB::beginTransaction();
            
            $writeOff = LoanWriteOff::find($writeOffId);
            
            if (!$writeOff || !$writeOff->canBeApproved()) {
                throw new \Exception('Write-off cannot be approved at this time.');
            }
            
            // Process through provision service
            $provisionService = new LoanLossProvisionService();
            $provisionService->writeOffLoan($writeOff->loan_id, $writeOff->total_amount);
            
            // Update write-off record
            $writeOff->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_date' => now()
            ]);
            
            // Add audit entry
            $writeOff->addAuditEntry('writeoff_approved', [
                'approved_by' => auth()->user()->name,
                'approval_date' => now()->toISOString()
            ]);
            
            // Update loan status
            DB::table('loans')
                ->where('loan_id', $writeOff->loan_id)
                ->update([
                    'loan_status' => 'written_off',
                    'closure_date' => now(),
                    'updated_at' => now()
                ]);
            
            // Send approval notification to member
            $this->sendApprovalNotification($writeOff);
            
            DB::commit();
            
            session()->flash('success', 'Write-off approved successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error approving write-off: ' . $e->getMessage());
        }
    }
    
    // Recovery Management Methods
    public function initiateRecovery($writeOffId)
    {
        $this->selectedWriteOff = LoanWriteOff::find($writeOffId);
        $this->showRecoveryModal = true;
    }
    
    public function processRecovery()
    {
        $this->validate([
            'recoveryAmount' => 'required|numeric|min:0',
            'recoveryMethod' => 'required|string',
            'recoveryDescription' => 'required|min:10'
        ]);
        
        try {
            DB::beginTransaction();
            
            LoanWriteoffRecovery::create([
                'writeoff_id' => $this->selectedWriteOff->id,
                'loan_id' => $this->selectedWriteOff->loan_id,
                'client_number' => $this->selectedWriteOff->client_number,
                'recovery_date' => now(),
                'recovery_amount' => $this->recoveryAmount,
                'recovery_method' => $this->recoveryMethod,
                'recovery_description' => $this->recoveryDescription,
                'recovery_source' => $this->recoverySource,
                'recorded_by' => auth()->id(),
                'status' => 'pending'
            ]);
            
            // Update writeoff audit trail
            $this->selectedWriteOff->addAuditEntry('recovery_recorded', [
                'recovery_amount' => $this->recoveryAmount,
                'recovery_method' => $this->recoveryMethod,
                'recorded_by' => auth()->user()->name
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Recovery recorded successfully. Pending approval.');
            $this->showRecoveryModal = false;
            $this->reset(['recoveryAmount', 'recoveryMethod', 'recoveryDescription', 'recoverySource']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error recording recovery: ' . $e->getMessage());
        }
    }
    
    public function approveRecovery($recoveryId)
    {
        try {
            $recovery = LoanWriteoffRecovery::find($recoveryId);
            if ($recovery && $recovery->approve()) {
                session()->flash('success', 'Recovery approved successfully.');
            } else {
                session()->flash('error', 'Unable to approve recovery.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error approving recovery: ' . $e->getMessage());
        }
    }
    
    // Collection Efforts Management
    public function addCollectionEffort($loanId)
    {
        $this->selectedLoan = (object) ['loan_id' => $loanId];
        $this->showCollectionModal = true;
    }
    
    public function processCollectionEffort()
    {
        $this->validate([
            'effortType' => 'required|string',
            'effortDescription' => 'required|min:10',
            'effortOutcome' => 'required|string'
        ]);
        
        try {
            $effort = LoanCollectionEffort::create([
                'loan_id' => $this->selectedLoan->loan_id,
                'client_number' => $this->selectedLoan->client_number ?? '',
                'effort_date' => now(),
                'effort_type' => $this->effortType,
                'effort_description' => $this->effortDescription,
                'outcome' => $this->effortOutcome,
                'promised_payment_date' => $this->promisedDate,
                'promised_amount' => $this->promisedAmount,
                'client_response' => $this->clientResponse,
                'staff_id' => auth()->id(),
                'cost_incurred' => $this->costIncurred ?? 0
            ]);
            
            session()->flash('success', 'Collection effort recorded successfully.');
            $this->showCollectionModal = false;
            $this->reset(['effortType', 'effortDescription', 'effortOutcome', 'promisedAmount', 'promisedDate', 'clientResponse', 'costIncurred']);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error recording collection effort: ' . $e->getMessage());
        }
    }
    
    // Analytics and Reporting
    public function showAnalytics()
    {
        $this->showAnalyticsModal = true;
    }
    
    public function getWriteoffAnalytics()
    {
        try {
            $analyticsService = new WriteoffAnalyticsService();
            return $analyticsService->generateReport(
                $this->analyticsDateFrom,
                $this->analyticsDateTo,
                $this->analyticsType
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating analytics: ' . $e->getMessage());
            return [];
        }
    }
    
    public function exportWriteOffs()
    {
        try {
            $writeOffs = LoanWriteOff::with(['loan', 'initiator', 'approver', 'recoveries'])
                ->whereBetween('write_off_date', [$this->dateFrom, $this->dateTo])
                ->get();
            
            $filename = 'loan_writeoffs_' . $this->dateFrom . '_to_' . $this->dateTo . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            
            $callback = function() use ($writeOffs) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Loan ID', 'Client Number', 'Write-off Date', 'Principal Amount',
                    'Interest Amount', 'Total Amount', 'Status', 'Recovery Status',
                    'Recovered Amount', 'Recovery %', 'Initiated By', 'Approved By',
                    'Reason', 'Board Approval Required'
                ]);
                
                foreach ($writeOffs as $writeOff) {
                    fputcsv($file, [
                        $writeOff->loan_id,
                        $writeOff->client_number,
                        $writeOff->write_off_date->format('Y-m-d'),
                        $writeOff->principal_amount,
                        $writeOff->interest_amount,
                        $writeOff->total_amount,
                        $writeOff->status,
                        $writeOff->recovery_status,
                        $writeOff->recovered_amount,
                        $writeOff->recovery_percentage,
                        $writeOff->initiator->name ?? '',
                        $writeOff->approver->name ?? '',
                        $writeOff->reason,
                        $writeOff->requires_board_approval ? 'Yes' : 'No'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting data: ' . $e->getMessage());
        }
    }
    
    // Member Communication Methods
    private function sendMemberNotification(LoanWriteOff $writeOff)
    {
        try {
            $communicationService = app(MemberCommunicationService::class);
            $communicationService->sendWriteoffNotification($writeOff, 'initiated');
        } catch (\Exception $e) {
            \Log::error('Failed to send member notification: ' . $e->getMessage());
        }
    }
    
    private function sendApprovalNotification(LoanWriteOff $writeOff)
    {
        try {
            $communicationService = app(MemberCommunicationService::class);
            $communicationService->sendWriteoffNotification($writeOff, 'approved');
        } catch (\Exception $e) {
            \Log::error('Failed to send approval notification: ' . $e->getMessage());
        }
    }
    
    // Board Approval Methods
    public function requiresBoardApproval($amount)
    {
        return $amount >= $this->boardApprovalThreshold;
    }
    
    public function getPendingBoardApprovals()
    {
        return LoanWriteOff::where('requires_board_approval', true)
            ->where('status', 'pending_approval')
            ->whereNull('board_approval_date')
            ->with(['loan', 'initiator'])
            ->get();
    }
    
    // Threshold Configuration Methods
    public function openThresholdModal()
    {
        // Reload current values from database
        $institution = DB::table('institutions')->first();
        if ($institution) {
            $this->boardApprovalThreshold = $institution->writeoff_board_approval_threshold ?? 1000000;
            $this->managerApprovalThreshold = $institution->writeoff_manager_approval_threshold ?? 100000;
            $this->minimumCollectionEfforts = $institution->writeoff_minimum_collection_efforts ?? 3;
            $this->recoveryTrackingPeriod = $institution->writeoff_recovery_tracking_period ?? 12;
        }
        $this->showThresholdModal = true;
    }
    
    public function updateThresholds()
    {
        $this->validate([
            'boardApprovalThreshold' => 'required|numeric|min:0',
            'managerApprovalThreshold' => 'required|numeric|min:0',
            'minimumCollectionEfforts' => 'required|integer|min:1',
            'recoveryTrackingPeriod' => 'required|integer|min:1',
        ]);
        
        try {
            // Ensure the institution record exists
            $institution = DB::table('institutions')->first();
            if (!$institution) {
                // Create a default institution record if it doesn't exist
                DB::table('institutions')->insert([
                    'id' => 1,
                    'name' => 'Default Institution',
                    'writeoff_board_approval_threshold' => $this->boardApprovalThreshold,
                    'writeoff_manager_approval_threshold' => $this->managerApprovalThreshold,
                    'writeoff_minimum_collection_efforts' => $this->minimumCollectionEfforts,
                    'writeoff_recovery_tracking_period' => $this->recoveryTrackingPeriod,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('institutions')
                    ->where('id', $institution->id)
                    ->update([
                        'writeoff_board_approval_threshold' => $this->boardApprovalThreshold,
                        'writeoff_manager_approval_threshold' => $this->managerApprovalThreshold,
                        'writeoff_minimum_collection_efforts' => $this->minimumCollectionEfforts,
                        'writeoff_recovery_tracking_period' => $this->recoveryTrackingPeriod,
                        'updated_at' => now()
                    ]);
            }
            
            session()->flash('success', 'Thresholds updated successfully.');
            $this->showThresholdModal = false;
            $this->emit('refreshComponent'); // Refresh the component to show updated values
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating thresholds: ' . $e->getMessage());
        }
    }
    
    // Summary Statistics
    public function getWriteoffSummary()
    {
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;
        
        return [
            'total_written_off' => LoanWriteOff::getTotalWrittenOff($dateFrom, $dateTo),
            'total_recovered' => LoanWriteOff::getTotalRecovered($dateFrom, $dateTo),
            'recovery_rate' => LoanWriteOff::getRecoveryRate($dateFrom, $dateTo),
            'pending_approval' => LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
                ->where('status', 'pending_approval')->count(),
            'board_pending' => LoanWriteOff::whereBetween('write_off_date', [$dateFrom, $dateTo])
                ->where('requires_board_approval', true)
                ->whereNull('board_approval_date')->count(),
            'collection_efforts_total' => LoanCollectionEffort::whereBetween('effort_date', [$dateFrom, $dateTo])->count(),
        ];
    }
    
    public function render()
    {
        $data = [
            'eligibleLoans' => $this->eligibleLoans,
            'writtenOffLoans' => $this->writtenOffLoans,
            'writeoffSummary' => $this->getWriteoffSummary(),
            'pendingBoardApprovals' => $this->getPendingBoardApprovals(),
            'activeTab' => $this->activeTab
        ];
        
        // Load analytics data if on analytics tab
        if ($this->activeTab === 'analytics') {
            $data['analytics'] = $this->getWriteoffAnalytics();
        } else {
            $data['analytics'] = [];
        }
        
        return view('livewire.active-loan.writeoff-comprehensive', $data);
    }
}
