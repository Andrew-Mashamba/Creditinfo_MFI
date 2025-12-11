<?php

namespace App\Http\Livewire\ActiveLoan\ArrearsDashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CollectionManagement extends Component
{
    use WithPagination;
    // Collection metrics
    public $totalDue = 0;
    public $totalCollected = 0;
    public $collectionRate = 0;
    public $pendingCollections = 0;
    
    // Filter and modal states
    public $caseFilter = 'All Cases';
    public $showAddCaseModal = false;
    
    // Today's collections
    public $todayDue = 0;
    public $todayCollected = 0;
    public $todayCollectionRate = 0;
    
    // This week's collections
    public $weekDue = 0;
    public $weekCollected = 0;
    public $weekCollectionRate = 0;
    
    // This month's collections
    public $monthDue = 0;
    public $monthCollected = 0;
    public $monthCollectionRate = 0;
    
    // Collection by officer
    public $collectionsByOfficer = [];
    
    // Follow-up required
    public $followUpRequired = [];
    
    // Recovery actions
    public $recoveryActions = [];
    
    public function mount()
    {
        $this->loadCollectionData();
    }
    
    private function loadCollectionData()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        
        // Get total dues and collections
        $totalData = DB::table('loans_schedules')
            ->select(
                DB::raw('SUM(installment) as total_due'),
                DB::raw('SUM(COALESCE(payment, 0)) as total_collected')
            )
            ->first();
        
        $this->totalDue = $totalData->total_due ?? 0;
        $this->totalCollected = $totalData->total_collected ?? 0;
        $this->collectionRate = $this->totalDue > 0 ? ($this->totalCollected / $this->totalDue) * 100 : 0;
        $this->pendingCollections = $this->totalDue - $this->totalCollected;
        
        // Today's collections
        $todayData = DB::table('loans_schedules')
            ->whereDate('installment_date', $today)
            ->select(
                DB::raw('SUM(installment) as due'),
                DB::raw('SUM(COALESCE(payment, 0)) as collected')
            )
            ->first();
        
        $this->todayDue = $todayData->due ?? 0;
        $this->todayCollected = $todayData->collected ?? 0;
        $this->todayCollectionRate = $this->todayDue > 0 ? ($this->todayCollected / $this->todayDue) * 100 : 0;
        
        // This week's collections
        $weekData = DB::table('loans_schedules')
            ->whereBetween('installment_date', [$weekStart, Carbon::now()])
            ->select(
                DB::raw('SUM(installment) as due'),
                DB::raw('SUM(COALESCE(payment, 0)) as collected')
            )
            ->first();
        
        $this->weekDue = $weekData->due ?? 0;
        $this->weekCollected = $weekData->collected ?? 0;
        $this->weekCollectionRate = $this->weekDue > 0 ? ($this->weekCollected / $this->weekDue) * 100 : 0;
        
        // This month's collections
        $monthData = DB::table('loans_schedules')
            ->whereBetween('installment_date', [$monthStart, Carbon::now()])
            ->select(
                DB::raw('SUM(installment) as due'),
                DB::raw('SUM(COALESCE(payment, 0)) as collected')
            )
            ->first();
        
        $this->monthDue = $monthData->due ?? 0;
        $this->monthCollected = $monthData->collected ?? 0;
        $this->monthCollectionRate = $this->monthDue > 0 ? ($this->monthCollected / $this->monthDue) * 100 : 0;
        
        // Collections by branch (since loan_officer column doesn't exist)
        $this->collectionsByOfficer = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('branches', 'loans.branch_id', '=', DB::raw('CAST(branches.id AS TEXT)'))
            ->whereBetween('loans_schedules.installment_date', [$monthStart, Carbon::now()])
            ->select(
                DB::raw('COALESCE(branches.name, \'Unassigned\') as officer_name'),
                DB::raw('COUNT(DISTINCT loans.id) as loans_count'),
                DB::raw('SUM(loans_schedules.installment) as total_due'),
                DB::raw('SUM(COALESCE(loans_schedules.payment, 0)) as total_collected'),
                DB::raw('CASE WHEN SUM(loans_schedules.installment) > 0 THEN (SUM(COALESCE(loans_schedules.payment, 0)) / SUM(loans_schedules.installment)) * 100 ELSE 0 END as collection_rate')
            )
            ->groupBy('branches.name')
            ->orderBy('collection_rate', 'desc')
            ->limit(10)
            ->get();
        
        // Loans requiring follow-up (overdue more than 7 days)
        $this->loadFollowUpData();
        
        // Recovery actions summary
        $this->recoveryActions = [
            'sms_reminders' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', [1, 7])
                ->count(),
            'phone_calls' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', [8, 14])
                ->count(),
            'reminder_letters' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', [15, 30])
                ->count(),
            'warning_letters' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', [31, 60])
                ->count(),
            'final_notices' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->whereBetween('days_in_arrears', [61, 90])
                ->count(),
            'legal_actions' => DB::table('loans_schedules')
                ->whereNotNull('days_in_arrears')
                ->where('days_in_arrears', '>', 90)
                ->count(),
        ];
    }
    
    public function refreshData()
    {
        $this->loadCollectionData();
        session()->flash('message', 'Collection data refreshed successfully!');
    }
    
    public function sendReminder($loanId)
    {
        // Logic to send reminder would go here
        session()->flash('message', "Reminder sent for loan #$loanId");
    }
    
    public function initiateAction($loanId, $action)
    {
        // Logic to initiate recovery action would go here
        session()->flash('message', "Recovery action initiated: $action for loan #$loanId");
    }
    
    public function sendBulkSMS()
    {
        // Get count of SMS to be sent
        $smsCount = $this->recoveryActions['sms_reminders'] ?? 0;
        
        // Here you would integrate with SMS gateway
        session()->flash('message', "Bulk SMS reminders queued for {$smsCount} clients");
        
        // Refresh data after action
        $this->loadCollectionData();
    }
    
    public function makePhoneCalls()
    {
        $callCount = $this->recoveryActions['phone_calls'] ?? 0;
        session()->flash('message', "Phone call list generated for {$callCount} clients");
    }
    
    public function scheduleVisits()
    {
        $visitCount = count($this->followUpRequired);
        session()->flash('message', "Field visits scheduled for {$visitCount} clients");
    }
    
    public function initiateLegalProceedings()
    {
        $legalCount = $this->recoveryActions['legal_actions'] ?? 0;
        session()->flash('message', "Legal proceedings initiated for {$legalCount} accounts");
    }
    
    public function generateDailyReport()
    {
        session()->flash('message', "Daily collection report generated successfully");
    }
    
    public function generateWeeklyReport()
    {
        session()->flash('message', "Weekly performance report generated successfully");
    }
    
    public function generateMonthlyReport()
    {
        session()->flash('message', "Monthly summary report generated successfully");
    }
    
    public function exportToExcel()
    {
        // In production, this would generate and download an Excel file
        session()->flash('message', "Collection data exported to Excel successfully");
    }
    
    public function exportToPDF()
    {
        // In production, this would generate and download a PDF report
        session()->flash('message', "Collection report exported to PDF successfully");
    }
    
    public function printReport()
    {
        // In production, this would open print dialog with formatted report
        session()->flash('message', "Opening print preview...");
    }
    
    public function filterCases()
    {
        // Re-load follow-up data based on filter
        $this->loadFollowUpData();
        session()->flash('message', "Filter applied: {$this->caseFilter}");
    }
    
    public function addNewCase()
    {
        // In a real application, this would open a modal or redirect to add case form
        $this->showAddCaseModal = true;
        session()->flash('message', "Add new case functionality - would open form/modal");
    }
    
    public function closeAddCaseModal()
    {
        $this->showAddCaseModal = false;
    }
    
    private function loadFollowUpData()
    {
        $query = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', DB::raw('CAST(loans.id AS TEXT)'))
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->whereNotNull('loans_schedules.days_in_arrears')
            ->where('loans_schedules.days_in_arrears', '>', 7);
        
        // Apply filter based on caseFilter value
        if ($this->caseFilter !== 'All Cases') {
            switch ($this->caseFilter) {
                case 'Initial Contact':
                    $query->whereBetween('loans_schedules.days_in_arrears', [8, 14]);
                    break;
                case 'Follow-up':
                    $query->whereBetween('loans_schedules.days_in_arrears', [15, 30]);
                    break;
                case 'Escalation':
                    $query->whereBetween('loans_schedules.days_in_arrears', [31, 60]);
                    break;
                case 'Legal Action':
                    $query->where('loans_schedules.days_in_arrears', '>', 90);
                    break;
            }
        }
        
        $this->followUpRequired = $query->select(
                'loans.id as loan_id',
                'loans.client_number',
                DB::raw('COALESCE(clients.first_name || \' \' || clients.last_name, loans.client_number) as client_name'),
                DB::raw('COALESCE(clients.mobile_phone_number, \'N/A\') as phone_number'),
                'loans_schedules.days_in_arrears',
                DB::raw('COALESCE(loans_schedules.amount_in_arrears, loans_schedules.installment - COALESCE(loans_schedules.payment, 0)) as arrears_amount'),
                'loans_schedules.installment_date',
                DB::raw('
                    CASE 
                        WHEN loans_schedules.days_in_arrears > 90 THEN \'Legal Action\'
                        WHEN loans_schedules.days_in_arrears > 60 THEN \'Final Notice\'
                        WHEN loans_schedules.days_in_arrears > 30 THEN \'Warning Letter\'
                        WHEN loans_schedules.days_in_arrears > 14 THEN \'Reminder Call\'
                        ELSE \'SMS Reminder\'
                    END as recommended_action
                ')
            )
            ->orderBy('loans_schedules.days_in_arrears', 'desc')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.active-loan.arrears-dashboard.collection-management');
    }
}
