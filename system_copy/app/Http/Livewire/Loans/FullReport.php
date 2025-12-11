<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use App\Services\LoanFullReportService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FullReport extends Component
{
    // Filter properties
    public $format = 'xlsx';
    public $branch_id = '';
    public $loan_status = '';
    public $product_type = '';
    public $classification = '';
    public $date_from = '';
    public $date_to = '';
    public $include_all = false;

    // Statistics
    public $showStatistics = false;
    public $statistics = null;
    public $isGenerating = false;

    // Available options (loaded fresh in render method)
    public $classifications = ['PERFORMING', 'WATCH', 'SUBSTANDARD', 'DOUBTFUL', 'LOSS'];

    public function mount()
    {
        // Don't set default dates - show all loans by default
        // Users can optionally filter by date range
    }

    /**
     * Get report service instance
     */
    private function getReportService()
    {
        return app(LoanFullReportService::class);
    }

    /**
     * Load filter options fresh each time
     */
    private function loadFilterOptions()
    {
        // Load branches
        $branches = DB::table('branches')
            ->select('id', 'name', 'branch_number')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        // Load loan statuses
        $loanStatuses = DB::table('loans')
            ->select('loan_status')
            ->distinct()
            ->whereNotNull('loan_status')
            ->orderBy('loan_status')
            ->pluck('loan_status');

        // Load product types
        $productTypes = DB::table('loans')
            ->select('loan_type_2')
            ->distinct()
            ->whereNotNull('loan_type_2')
            ->orderBy('loan_type_2')
            ->pluck('loan_type_2');

        return [
            'branches' => $branches,
            'loanStatuses' => $loanStatuses,
            'productTypes' => $productTypes
        ];
    }

    public function generateReport()
    {
        $this->isGenerating = true;

        try {
            $reportService = $this->getReportService();
            $filters = $this->prepareFilters();

            if ($this->format === 'csv') {
                $result = $reportService->exportAsCSV($filters);
            } else {
                $result = $reportService->generateFullLoanReport($filters);
            }

            if ($result['success']) {
                $this->emit('reportGenerated', [
                    'file_path' => $result['file_path'],
                    'file_name' => $result['file_name'],
                    'record_count' => $result['record_count']
                ]);

                session()->flash('success', 'Report generated successfully! ' . $result['record_count'] . ' records.');

                // Trigger download
                return response()->download($result['file_path'], $result['file_name'])->deleteFileAfterSend(true);
            } else {
                session()->flash('error', 'Failed to generate report.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function loadStatistics()
    {
        try {
            $reportService = $this->getReportService();
            $filters = $this->prepareFilters();
            $this->statistics = $reportService->getReportStatistics($filters);
            $this->showStatistics = true;
            session()->flash('success', 'Statistics loaded successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading statistics: ' . $e->getMessage());
        }
    }

    public function clearFilters()
    {
        $this->format = 'xlsx';
        $this->branch_id = '';
        $this->loan_status = '';
        $this->product_type = '';
        $this->classification = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->include_all = false;
        $this->showStatistics = false;
        $this->statistics = null;

        session()->flash('info', 'Filters cleared.');
    }

    private function prepareFilters()
    {
        $filters = [
            'report_date' => Carbon::now()
        ];

        if (!empty($this->branch_id)) {
            $filters['branch_id'] = $this->branch_id;
        }

        if (!empty($this->loan_status)) {
            $filters['loan_status'] = $this->loan_status;
        }

        if (!empty($this->product_type)) {
            $filters['product_type'] = $this->product_type;
        }

        if (!empty($this->classification)) {
            $filters['classification'] = $this->classification;
        }

        if (!empty($this->date_from)) {
            $filters['date_from'] = $this->date_from;
        }

        if (!empty($this->date_to)) {
            $filters['date_to'] = $this->date_to;
        }

        if ($this->include_all) {
            $filters['include_all'] = true;
        }

        return $filters;
    }

    public function render()
    {
        $filterOptions = $this->loadFilterOptions();

        return view('livewire.loans.full-report', [
            'branches' => $filterOptions['branches'],
            'loanStatuses' => $filterOptions['loanStatuses'],
            'productTypes' => $filterOptions['productTypes']
        ]);
    }
}
