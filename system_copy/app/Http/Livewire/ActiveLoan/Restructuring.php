<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\LoanScheduleService;
use Illuminate\Support\Facades\Log;

class Restructuring extends Component
{
    use WithPagination;
    
    public $showRestructureModal = false;
    public $selectedLoan = null;
    public $restructureType = 'reschedule'; // reschedule, extend_term, reduce_interest, payment_holiday
    public $newTerms = [];
    public $searchTerm = '';
    public $filterStatus = 'eligible';
    public $dateFrom;
    public $dateTo;
    
    protected $paginationTheme = 'bootstrap';
    
    protected $rules = [
        'restructureType' => 'required|in:reschedule,extend_term,reduce_interest,payment_holiday',
        'newTerms.new_term' => 'required_if:restructureType,extend_term|numeric|min:1',
        'newTerms.new_interest_rate' => 'required_if:restructureType,reduce_interest|numeric|min:0',
        'newTerms.holiday_months' => 'required_if:restructureType,payment_holiday|numeric|min:1|max:6',
        'newTerms.reason' => 'required|min:10',
    ];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->newTerms = [
            'new_term' => '',
            'new_interest_rate' => '',
            'holiday_months' => '',
            'reason' => '',
            'effective_date' => Carbon::now()->format('Y-m-d')
        ];
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
                'loans.interest',
                'loans.repayment_period',
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
            ->whereIn('loans.loan_classification', ['WATCH', 'SUBSTANDARD', 'DOUBTFUL'])
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
    
    public function getRestructuredLoansProperty()
    {
        return DB::table('loan_restructures')
            ->select([
                'loan_restructures.*',
                'loans.loan_id',
                'loans.client_number',
                'loans.loan_sub_product',
                'clients.first_name',
                'clients.last_name',
                'users.name as approved_by_name'
            ])
            ->join('loans', 'loan_restructures.loan_id', '=', 'loans.loan_id')
            ->leftJoin('clients', 'loans.client_id', '=', 'clients.id')
            ->leftJoin('users', 'loan_restructures.approved_by', '=', 'users.id')
            ->whereBetween('loan_restructures.restructure_date', [$this->dateFrom, $this->dateTo])
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('loans.loan_id', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('loans.client_number', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('loan_restructures.restructure_date', 'desc')
            ->paginate(10);
    }
    
    public function initiateRestructure($loanId)
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
            $this->showRestructureModal = true;
        }
    }
    
    public function processRestructure()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            // Create restructure record
            $restructureId = DB::table('loan_restructures')->insertGetId([
                'loan_id' => $this->selectedLoan->loan_id,
                'restructure_date' => now(),
                'restructure_type' => $this->restructureType,
                'old_terms' => json_encode([
                    'term' => $this->selectedLoan->repayment_period,
                    'interest_rate' => $this->selectedLoan->interest,
                    'outstanding' => $this->selectedLoan->outstanding
                ]),
                'new_terms' => json_encode($this->newTerms),
                'reason' => $this->newTerms['reason'],
                'status' => 'pending_approval',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update loan status
            DB::table('loans')
                ->where('id', $this->selectedLoan->id)
                ->update([
                    'loan_status' => 'restructure_pending',
                    'updated_at' => now()
                ]);
            
            // Log the restructure request
            Log::info("Loan restructure initiated", [
                'loan_id' => $this->selectedLoan->loan_id,
                'type' => $this->restructureType,
                'user' => auth()->user()->name ?? 'System'
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Loan restructure initiated successfully. Pending approval.');
            $this->showRestructureModal = false;
            $this->reset(['selectedLoan', 'restructureType', 'newTerms']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing restructure: ' . $e->getMessage());
        }
    }
    
    public function approveRestructure($restructureId)
    {
        try {
            DB::beginTransaction();
            
            $restructure = DB::table('loan_restructures')->where('id', $restructureId)->first();
            $loan = DB::table('loans')->where('loan_id', $restructure->loan_id)->first();
            $newTerms = json_decode($restructure->new_terms, true);
            
            // Update restructure record
            DB::table('loan_restructures')
                ->where('id', $restructureId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_date' => now(),
                    'updated_at' => now()
                ]);
            
            // Apply restructure based on type
            switch ($restructure->restructure_type) {
                case 'extend_term':
                    $this->extendLoanTerm($loan, $newTerms['new_term']);
                    break;
                    
                case 'reduce_interest':
                    $this->reduceInterestRate($loan, $newTerms['new_interest_rate']);
                    break;
                    
                case 'payment_holiday':
                    $this->applyPaymentHoliday($loan, $newTerms['holiday_months']);
                    break;
                    
                case 'reschedule':
                    $this->rescheduleLoan($loan, $newTerms);
                    break;
            }
            
            // Update loan status
            DB::table('loans')
                ->where('loan_id', $restructure->loan_id)
                ->update([
                    'loan_status' => 'restructured',
                    'loan_classification' => 'WATCH', // Move to WATCH after restructuring
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            session()->flash('success', 'Loan restructure approved and applied successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error approving restructure: ' . $e->getMessage());
        }
    }
    
    private function extendLoanTerm($loan, $newTerm)
    {
        // Update loan term
        DB::table('loans')
            ->where('id', $loan->id)
            ->update([
                'repayment_period' => $newTerm,
                'updated_at' => now()
            ]);
        
        // Regenerate schedule with new term
        if (class_exists(\App\Services\LoanScheduleService::class)) {
            $scheduleService = new \App\Services\LoanScheduleService();
            $scheduleService->regenerateSchedule($loan->loan_id, $newTerm);
        }
    }
    
    private function reduceInterestRate($loan, $newRate)
    {
        // Update interest rate
        DB::table('loans')
            ->where('id', $loan->id)
            ->update([
                'interest' => $newRate,
                'updated_at' => now()
            ]);
        
        // Recalculate remaining schedules
        DB::table('loans_schedules')
            ->where('loan_id', $loan->loan_id)
            ->where('installment_date', '>', now())
            ->where('completion_status', 'pending')
            ->update([
                'interest' => DB::raw("principle * $newRate / 100"),
                'updated_at' => now()
            ]);
    }
    
    private function applyPaymentHoliday($loan, $months)
    {
        // Push all future schedules by the holiday period
        DB::table('loans_schedules')
            ->where('loan_id', $loan->loan_id)
            ->where('installment_date', '>', now())
            ->where('completion_status', 'pending')
            ->update([
                'installment_date' => DB::raw("installment_date + INTERVAL '$months months'"),
                'updated_at' => now()
            ]);
        
        // Store holiday period
        DB::table('loan_payment_holidays')->insert([
            'loan_id' => $loan->loan_id,
            'start_date' => now(),
            'end_date' => now()->addMonths($months),
            'months' => $months,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    private function rescheduleLoan($loan, $newTerms)
    {
        // Complete reschedule - combination of all changes
        if (isset($newTerms['new_term'])) {
            $this->extendLoanTerm($loan, $newTerms['new_term']);
        }
        
        if (isset($newTerms['new_interest_rate'])) {
            $this->reduceInterestRate($loan, $newTerms['new_interest_rate']);
        }
        
        // Clear arrears if specified
        if (isset($newTerms['clear_arrears']) && $newTerms['clear_arrears']) {
            DB::table('loans')
                ->where('id', $loan->id)
                ->update([
                    'total_arrears' => 0,
                    'days_in_arrears' => 0,
                    'updated_at' => now()
                ]);
        }
    }
    
    public function render()
    {
        return view('livewire.active-loan.restructuring', [
            'eligibleLoans' => $this->eligibleLoans,
            'restructuredLoans' => $this->restructuredLoans
        ]);
    }
}
