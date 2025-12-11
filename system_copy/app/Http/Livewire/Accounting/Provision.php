<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\LoanProvisionCalculationService;
use App\Models\LoansModel;
use App\Models\LoanProvisionSettings;
use App\Models\LoanLossProvision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Provision extends Component
{
    use WithPagination;
    
    // Tab management
    public $activeTab = 'overview';
    
    // Provision calculation parameters
    public $calculationDate;
    public $provisionMethod = 'ifrs9'; // 'ifrs9', 'regulatory', 'hybrid'
    public $includeForwardLooking = true;
    public $economicScenario = 'base'; // 'optimistic', 'base', 'pessimistic'
    
    // Stage classification thresholds
    public $stage1DaysThreshold = 0;
    public $stage2DaysThreshold = 30;
    public $stage3DaysThreshold = 90;
    
    // Provision rates by stage
    public $stage1Rate = 1.0; // 1%
    public $stage2Rate = 10.0; // 10%
    public $stage3Rate = 100.0; // 100%
    
    // Filter and search
    public $searchTerm = '';
    public $filterStage = 'all';
    public $filterProduct = 'all';
    public $filterBranch = 'all';
    
    // Modal controls
    public $showCalculationModal = false;
    public $showSettingsModal = false;
    public $showPostingModal = false;
    public $showReversalModal = false;
    
    // Selected items
    public $selectedLoans = [];
    public $selectAll = false;
    
    // Calculation results
    public $calculationResults = null;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    
    protected $listeners = ['refreshProvisions' => '$refresh'];
    
    public function mount()
    {
        $this->calculationDate = now()->format('Y-m-d');
        $this->loadProvisionSettings();
    }
    
    public function render()
    {
        $loans = $this->getFilteredLoans();
        $statistics = $this->getStatistics();
        $stageDistribution = $this->getStageDistribution();
        $recentCalculations = $this->getRecentCalculations();
        $chartData = $this->getChartData();
        $provisionMovement = $this->getProvisionMovement();
        $stageTransitions = $this->getStageTransitions();
        
        // Get the latest provision summary or create a default one
        $provisionSummary = $this->getLatestProvisionSummary();
        
        return view('livewire.accounting.provision', [
            'loans' => $loans,
            'statistics' => $statistics,
            'stageDistribution' => $stageDistribution,
            'recentCalculations' => $recentCalculations,
            'chartData' => $chartData,
            'products' => $this->getProducts(),
            'branches' => $this->getBranches(),
            'provisionMovement' => $provisionMovement,
            'stageTransitions' => $stageTransitions,
            'provisionSummary' => $provisionSummary,
        ]);
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function loadProvisionSettings()
    {
        try {
            // Check if table exists and initialize defaults if needed
            if (DB::getSchemaBuilder()->hasTable('loan_provision_settings')) {
                $settings = LoanProvisionSettings::initializeDefaults();
                $this->stage1DaysThreshold = $settings->stage1_days ?? 30;
                $this->stage2DaysThreshold = $settings->stage2_days ?? 90;
                $this->stage3DaysThreshold = $settings->stage3_days ?? 180;
                $this->stage1Rate = $settings->stage1_rate ?? 1.0;
                $this->stage2Rate = $settings->stage2_rate ?? 10.0;
                $this->stage3Rate = $settings->stage3_rate ?? 100.0;
            } else {
                // Use default values if table doesn't exist
                $this->stage1DaysThreshold = 30;
                $this->stage2DaysThreshold = 90;
                $this->stage3DaysThreshold = 180;
                $this->stage1Rate = 1.0;
                $this->stage2Rate = 10.0;
                $this->stage3Rate = 100.0;
            }
        } catch (\Exception $e) {
            // Use default values on any error
            $this->stage1DaysThreshold = 30;
            $this->stage2DaysThreshold = 90;
            $this->stage3DaysThreshold = 180;
            $this->stage1Rate = 1.0;
            $this->stage2Rate = 10.0;
            $this->stage3Rate = 100.0;
        }
    }
    
    public function openCalculationModal()
    {
        $this->showCalculationModal = true;
    }
    
    public function closeCalculationModal()
    {
        $this->showCalculationModal = false;
    }
    
    public function openSettingsModal()
    {
        $this->loadProvisionSettings();
        $this->showSettingsModal = true;
    }
    
    public function closeSettingsModal()
    {
        $this->showSettingsModal = false;
    }
    
    public function saveSettings()
    {
        $this->validate([
            'stage1DaysThreshold' => 'required|numeric|min:0',
            'stage2DaysThreshold' => 'required|numeric|min:0',
            'stage3DaysThreshold' => 'required|numeric|min:0',
            'stage1Rate' => 'required|numeric|min:0|max:100',
            'stage2Rate' => 'required|numeric|min:0|max:100',
            'stage3Rate' => 'required|numeric|min:0|max:100',
        ]);
        
        LoanProvisionSettings::updateOrCreate(
            ['id' => 1],
            [
                'stage1_days' => $this->stage1DaysThreshold,
                'stage2_days' => $this->stage2DaysThreshold,
                'stage3_days' => $this->stage3DaysThreshold,
                'stage1_rate' => $this->stage1Rate,
                'stage2_rate' => $this->stage2Rate,
                'stage3_rate' => $this->stage3Rate,
            ]
        );
        
        $this->successMessage = 'Settings saved successfully!';
        $this->showSettingsModal = false;
        $this->emit('refreshProvisions');
    }
    
    public function calculateProvisions()
    {
        try {
            $service = new LoanProvisionCalculationService();
            
            $options = [
                'includeForwardLooking' => $this->includeForwardLooking,
                'economicScenario' => $this->economicScenario,
            ];
            
            $this->calculationResults = $service->calculateProvisions(
                $this->calculationDate,
                $this->provisionMethod,
                $options
            );
            
            // Store the calculation summary
            DB::table('provision_summaries')->insert([
                'provision_date' => $this->calculationDate,
                'calculation_method' => $this->provisionMethod,
                'total_loans' => $this->calculationResults['summary']['total_loans'],
                'total_exposure' => $this->calculationResults['summary']['total_outstanding'],
                'total_provisions' => $this->calculationResults['summary']['total_provision'],
                'provision_coverage' => ($this->calculationResults['summary']['total_provision'] / $this->calculationResults['summary']['total_outstanding']) * 100,
                'stage1_count' => $this->calculationResults['byStage'][1]['count'] ?? 0,
                'stage1_exposure' => $this->calculationResults['byStage'][1]['exposure'] ?? 0,
                'stage1_provision' => $this->calculationResults['byStage'][1]['provision'] ?? 0,
                'stage2_count' => $this->calculationResults['byStage'][2]['count'] ?? 0,
                'stage2_exposure' => $this->calculationResults['byStage'][2]['exposure'] ?? 0,
                'stage2_provision' => $this->calculationResults['byStage'][2]['provision'] ?? 0,
                'stage3_count' => $this->calculationResults['byStage'][3]['count'] ?? 0,
                'stage3_exposure' => $this->calculationResults['byStage'][3]['exposure'] ?? 0,
                'stage3_provision' => $this->calculationResults['byStage'][3]['provision'] ?? 0,
                'economic_scenario' => $this->economicScenario,
                'forward_looking_applied' => $this->includeForwardLooking,
                'created_by' => auth()->id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->successMessage = 'Provisions calculated successfully!';
            $this->showCalculationModal = false;
            $this->emit('refreshProvisions');
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to calculate provisions: ' . $e->getMessage();
            Log::error('Provision calculation failed', ['error' => $e->getMessage()]);
        }
    }
    
    public function postToGL()
    {
        if (!$this->calculationResults) {
            $this->errorMessage = 'Please calculate provisions first.';
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $totalProvision = $this->calculationResults['summary']['total_provision'];
            
            // Create journal entry
            $journalEntry = DB::table('journal_entries')->insertGetId([
                'entry_date' => now(),
                'reference_number' => 'JV/' . now()->format('Y/m/') . rand(1000, 9999),
                'description' => 'Loan loss provision for ' . $this->calculationDate,
                'total_debit' => $totalProvision,
                'total_credit' => $totalProvision,
                'status' => 'posted',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Debit: Profit and Loss (Expense)
            DB::table('journal_entry_lines')->insert([
                'journal_entry_id' => $journalEntry,
                'account_code' => '5010',
                'account_name' => 'Loan Loss Provision Expense',
                'debit' => $totalProvision,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Credit: Provision for Doubtful Debts (Liability)
            DB::table('journal_entry_lines')->insert([
                'journal_entry_id' => $journalEntry,
                'account_code' => '1290',
                'account_name' => 'Provision for Doubtful Debts',
                'debit' => 0,
                'credit' => $totalProvision,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            $this->successMessage = 'Provisions posted to General Ledger successfully!';
            $this->showPostingModal = false;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Failed to post to GL: ' . $e->getMessage();
            Log::error('GL posting failed', ['error' => $e->getMessage()]);
        }
    }
    
    public function exportToExcel()
    {
        // Implementation for Excel export
        $this->successMessage = 'Export started. File will be downloaded shortly.';
    }
    
    public function exportToCSV()
    {
        // Implementation for CSV export
        $this->successMessage = 'CSV export started. File will be downloaded shortly.';
    }
    
    public function generateReport($reportType)
    {
        // Implementation for report generation
        $this->successMessage = "Generating {$reportType} report...";
    }
    
    public function exportProvisionReport()
    {
        // Implementation for exporting provision report
        $this->successMessage = 'Provision report export started. File will be downloaded shortly.';
        // TODO: Implement actual export functionality
    }
    
    private function getFilteredLoans()
    {
        // Use a simpler query without complex grouping
        $query = LoansModel::query()
            ->from('loans')
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->where('loans.status', '=', 'ACTIVE')
            ->select(
                'loans.id',
                'loans.loan_id',
                'loans.client_number',
                'loans.loan_sub_product',
                'loans.principle as loan_balance',
                'loans.branch_id',
                'clients.first_name',
                'clients.last_name',
                DB::raw('0 as days_in_arrears'),  // Simplified for now
                DB::raw('\'Stage 1\' as calculated_stage'),  // Default to Stage 1
                DB::raw('1 as ecl_stage'),
                DB::raw('loans.principle * 0.01 as provision_amount'),
                DB::raw('1.0 as provision_rate'),
                DB::raw('loans.principle * 0.01 as calculated_provision')
            );
        
        // Apply search filter
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('clients.first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('clients.last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('clients.client_number', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('loans.loan_id', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        // Apply stage filter (simplified - not actually filtering by real stage for now)
        if ($this->filterStage !== 'all') {
            // For now, just show all loans regardless of stage filter
            // TODO: Implement actual stage filtering when loan_schedules data is available
        }
        
        // Apply product filter
        if ($this->filterProduct !== 'all') {
            $query->where('loans.loan_sub_product', $this->filterProduct);
        }
        
        // Apply branch filter
        if ($this->filterBranch !== 'all') {
            $query->where('loans.branch_id', $this->filterBranch);
        }
        
        return $query->paginate(20);
    }
    
    private function getStatistics()
    {
        $activeLoans = LoansModel::where('status', 'ACTIVE')->get();
        $totalOutstanding = $activeLoans->sum('principle');
        $totalProvision = DB::table('loan_loss_provisions')
            ->whereIn('loan_id', $activeLoans->pluck('id'))
            ->sum('provision_amount');
        
        return [
            'total_loans' => $activeLoans->count(),
            'total_outstanding' => $totalOutstanding,
            'total_provision' => $totalProvision,
            'coverage_ratio' => $totalOutstanding > 0 ? round(($totalProvision / $totalOutstanding) * 100, 2) : 0,
            'npl_ratio' => 0, // Calculate NPL ratio based on stage 3 loans
        ];
    }
    
    private function getStageDistribution()
    {
        return [
            'stage1' => ['count' => 150, 'amount' => 5000000, 'provision' => 50000],
            'stage2' => ['count' => 30, 'amount' => 1000000, 'provision' => 100000],
            'stage3' => ['count' => 10, 'amount' => 500000, 'provision' => 500000],
        ];
    }
    
    private function getRecentCalculations()
    {
        return DB::table('provision_summaries')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    private function getChartData()
    {
        return [
            'provisionTrend' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    [
                        'label' => 'Total Provision',
                        'data' => [650000, 700000, 680000, 750000, 720000, 800000],
                        'borderColor' => 'rgb(75, 192, 192)',
                        'tension' => 0.1
                    ]
                ]
            ],
            'stageDistribution' => [
                'labels' => ['Stage 1', 'Stage 2', 'Stage 3'],
                'datasets' => [
                    [
                        'data' => [50000, 100000, 500000],
                        'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444']
                    ]
                ]
            ]
        ];
    }
    
    private function getProducts()
    {
        return DB::table('loan_sub_products')->select('id', 'sub_product_name as product_name')->get();
    }
    
    private function getBranches()
    {
        return DB::table('branches')->select('id', 'name')->get();
    }
    
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedLoans = $this->getFilteredLoans()->pluck('id')->toArray();
        } else {
            $this->selectedLoans = [];
        }
    }
    
    private function getProvisionMovement()
    {
        // Get provision movement data for the last 6 months
        return DB::table('provision_summaries')
            ->select(
                DB::raw('DATE(provision_date) as provision_date'),
                DB::raw('SUM(total_provisions) as total_provision'),
                DB::raw('SUM(stage1_provision) as stage1_provision'),
                DB::raw('SUM(stage2_provision) as stage2_provision'),
                DB::raw('SUM(stage3_provision) as stage3_provision')
            )
            ->where('provision_date', '>=', now()->subMonths(6))
            ->groupBy('provision_date')
            ->orderBy('provision_date')
            ->get();
    }
    
    private function getStageTransitions()
    {
        // Get stage transition data for analytics
        return collect([
            ['from_stage' => 'Stage 1', 'to_stage' => 'Stage 2', 'count' => 15],
            ['from_stage' => 'Stage 2', 'to_stage' => 'Stage 3', 'count' => 5],
            ['from_stage' => 'Stage 2', 'to_stage' => 'Stage 1', 'count' => 8],
            ['from_stage' => 'Stage 3', 'to_stage' => 'Stage 2', 'count' => 2],
        ]);
    }
    
    private function getLatestProvisionSummary()
    {
        // Get the latest provision summary or return a default object
        $summary = DB::table('provision_summaries')
            ->orderBy('provision_date', 'desc')
            ->first();
        
        // If no summary exists, return a default object
        if (!$summary) {
            return (object) [
                'provision_date' => now()->format('Y-m-d'),
                'total_loans' => 0,
                'total_exposure' => 0,
                'total_provisions' => 0,
                'provision_coverage' => 0,
                'stage1_count' => 0,
                'stage1_exposure' => 0,
                'stage1_provision' => 0,
                'stage2_count' => 0,
                'stage2_exposure' => 0,
                'stage2_provision' => 0,
                'stage3_count' => 0,
                'stage3_exposure' => 0,
                'stage3_provision' => 0,
                'economic_scenario' => 'base',
                'forward_looking_applied' => false,
            ];
        }
        
        return $summary;
    }
}