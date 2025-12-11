<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SectoralClassificationOfLoans extends Component
{
    public $startDate;
    public $endDate;
    public $sectors = [];
    public $totalLoans = 0;
    public $totalAmount = 0;
    
    // Add missing properties
    public $showRiskAnalysis = false;
    public $showDetailedView = false;
    public $reportPeriod = 'monthly';
    public $sectorFilter = 'all';
    public $currency = 'TZS';
    public $chartType = 'pie';

    public function mount()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get loans grouped by sector
        $this->sectors = DB::table('loans')
            ->join('clients', 'loans.client_number', '=', 'clients.client_number')
            ->whereBetween('loans.created_at', [$this->startDate, $this->endDate])
            ->select(
                'clients.industry_sector',
                DB::raw('COUNT(*) as number_of_loans'),
                DB::raw('SUM(loans.principle) as total_amount')
            )
            ->groupBy('clients.industry_sector')
            ->get();

        // Calculate totals
        $this->totalLoans = $this->sectors->sum('number_of_loans');
        $this->totalAmount = $this->sectors->sum('total_amount');
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    // Add missing methods
    public function toggleRiskAnalysis()
    {
        $this->showRiskAnalysis = !$this->showRiskAnalysis;
    }

    public function toggleDetailedView()
    {
        $this->showDetailedView = !$this->showDetailedView;
    }

    public function setChartType($type)
    {
        $this->chartType = $type;
    }

    public function generateReport()
    {
        $this->loadData();
    }

    public function exportToPDF()
    {
        // PDF export logic would go here
        session()->flash('message', 'PDF export functionality will be implemented soon.');
    }

    public function exportToExcel()
    {
        // Excel export logic would go here
        session()->flash('message', 'Excel export functionality will be implemented soon.');
    }

    public function submitToBOT()
    {
        // BOT submission logic would go here
        session()->flash('message', 'BOT submission functionality will be implemented soon.');
    }

    public function render()
    {
        return view('livewire.reports.sectoral-classification-of-loans');
    }
} 