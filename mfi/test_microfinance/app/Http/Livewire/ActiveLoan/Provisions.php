<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Services\LoanLossProvisionService;
use Carbon\Carbon;

class Provisions extends Component
{
    public $currentStatus = null; // Will store as array
    public $trends = [];
    public $topProvisions = [];
    public $journalAdvice = null;
    public $selectedDate;
    public $showTrends = false;
    public $showDetails = false;
    public $provisionRates = [];
    
    protected $provisionService;
    
    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->loadProvisionData();
        $this->loadProvisionRates();
    }
    
    public function loadProvisionData()
    {
        $this->provisionService = new LoanLossProvisionService();
        
        // Get current status and convert to array
        $status = $this->provisionService->getCurrentProvisionStatus();
        if ($status) {
            $this->currentStatus = [
                'summary_date' => $status->summary_date,
                'total_loans' => $status->total_loans,
                'total_outstanding' => $status->total_outstanding,
                'performing_balance' => $status->performing_balance,
                'watch_balance' => $status->watch_balance,
                'substandard_balance' => $status->substandard_balance,
                'doubtful_balance' => $status->doubtful_balance,
                'loss_balance' => $status->loss_balance,
                'general_provisions' => $status->general_provisions,
                'specific_provisions' => $status->specific_provisions,
                'total_provisions' => $status->total_provisions,
                'provision_coverage_ratio' => $status->provision_coverage_ratio,
                'npl_ratio' => $status->npl_ratio,
                'statistics' => $status->statistics,
                'formatted_total' => $status->formatted_total ?? 'TZS ' . number_format($status->total_provisions, 2),
                'formatted_outstanding' => $status->formatted_outstanding ?? 'TZS ' . number_format($status->total_outstanding, 2),
                'formatted_npl_ratio' => $status->formatted_npl_ratio ?? number_format($status->npl_ratio, 2) . '%',
                'formatted_coverage' => $status->formatted_coverage ?? number_format($status->provision_coverage_ratio, 2) . '%',
            ];
            
            // Load journal advice if exists
            if ($status->statistics) {
                $statistics = json_decode($status->statistics, true);
                $this->journalAdvice = $statistics['journal_advice'] ?? null;
            }
        } else {
            $this->currentStatus = null;
        }
        
        // Get trends for last 30 days and convert to array
        $trends = $this->provisionService->getProvisionTrends(30);
        $this->trends = [];
        foreach ($trends as $trend) {
            $this->trends[] = [
                'summary_date' => $trend->summary_date,
                'total_provisions' => $trend->total_provisions,
                'npl_ratio' => $trend->npl_ratio,
                'provision_coverage_ratio' => $trend->provision_coverage_ratio,
            ];
        }
        
        // Get top provisions and convert to array
        $provisions = DB::table('loan_loss_provisions')
            ->where('provision_date', DB::raw('(SELECT MAX(provision_date) FROM loan_loss_provisions)'))
            ->where('status', 'active')
            ->orderBy('provision_amount', 'desc')
            ->limit(10)
            ->get();
            
        $this->topProvisions = [];
        foreach ($provisions as $provision) {
            $this->topProvisions[] = [
                'loan_id' => $provision->loan_id,
                'client_number' => $provision->client_number,
                'loan_classification' => $provision->loan_classification,
                'days_in_arrears' => $provision->days_in_arrears,
                'outstanding_balance' => $provision->outstanding_balance,
                'provision_rate' => $provision->provision_rate,
                'provision_amount' => $provision->provision_amount,
            ];
        }
    }
    
    public function loadProvisionRates()
    {
        $rates = DB::table('provision_rates_config')
            ->where('is_active', true)
            ->orderBy('min_days')
            ->get();
            
        $this->provisionRates = [];
        foreach ($rates as $rate) {
            $this->provisionRates[] = [
                'classification' => $rate->classification,
                'min_days' => $rate->min_days,
                'max_days' => $rate->max_days,
                'provision_rate' => $rate->provision_rate,
                'provision_type' => $rate->provision_type,
                'description' => $rate->description,
            ];
        }
    }
    
    public function recalculateProvisions()
    {
        try {
            $provisionService = new LoanLossProvisionService();
            $summary = $provisionService->calculateDailyProvisions($this->selectedDate);
            
            session()->flash('success', 'Provisions recalculated successfully for ' . $this->selectedDate);
            $this->loadProvisionData();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error calculating provisions: ' . $e->getMessage());
        }
    }
    
    public function approveJournalEntry()
    {
        if (!$this->journalAdvice) {
            session()->flash('error', 'No journal entry to approve');
            return;
        }
        
        try {
            // Mark the journal advice as approved
            DB::table('loan_loss_provision_summary')
                ->where('summary_date', $this->journalAdvice['date'])
                ->update([
                    'statistics' => DB::raw("jsonb_set(statistics::jsonb, '{journal_advice,status}', '\"approved\"'::jsonb)")
                ]);
            
            session()->flash('success', 'Journal entry approved. Please post manually to the general ledger.');
            
            // Log the approval
            \Log::info('Loan loss provision journal entry approved', [
                'date' => $this->journalAdvice['date'],
                'amount' => $this->journalAdvice['amount'],
                'type' => $this->journalAdvice['type'],
                'approved_by' => auth()->user()->name ?? 'System'
            ]);
            
            $this->loadProvisionData();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error approving journal entry: ' . $e->getMessage());
        }
    }
    
    public function exportProvisionReport()
    {
        // TODO: Implement Excel export functionality
        session()->flash('info', 'Export functionality will be implemented soon');
    }
    
    public function render()
    {
        return view('livewire.active-loan.provisions');
    }
}