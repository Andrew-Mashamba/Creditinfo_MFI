<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\FinancialReportingService;
use App\Traits\HasFinancialReporting;

class FinancialReportingDashboard extends Component
{
    use HasFinancialReporting;

    public $selectedReportType = '';
    public $availableReports = [];
    public $regulatoryReports = [];
    public $generalReports = [];
    public $reportHistory = [];
    public $showReportModal = false;

    protected function loadReportData(): void
    {
        if (!$this->selectedReportType) {
            return;
        }

        $this->isLoading = true;
        
        try {
            $reportingService = $this->getReportingService();
            
            $params = [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'currency' => $this->currency
            ];
            
            $this->reportData = $reportingService->generateFinancialData($this->selectedReportType, $params);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getReportType(): string
    {
        return $this->selectedReportType;
    }

    public function mount()
    {
        $this->initializeReporting();
        $this->loadAvailableReports();
        $this->loadReportHistory();
    }

    public function loadAvailableReports()
    {
        $reportingService = $this->getReportingService();
        
        $this->availableReports = $reportingService->getReportTypes();
        $this->regulatoryReports = $reportingService->getRegulatoryReports();
        $this->generalReports = $reportingService->getGeneralReports();
    }

    public function loadReportHistory()
    {
        try {
            $reportingService = $this->getReportingService();
            $this->reportHistory = $reportingService->getReportHistory(auth()->id(), 20)->toArray();
        } catch (\Exception $e) {
            // Handle gracefully if table doesn't exist yet
            $this->reportHistory = [];
        }
    }

    public function selectReport($reportType)
    {
        $this->selectedReportType = $reportType;
        $this->loadReportData();
        $this->showReportModal = true;
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->selectedReportType = '';
        $this->reportData = [];
    }

    public function quickGenerate($reportType)
    {
        $this->selectedReportType = $reportType;
        $this->loadReportData();
        
        session()->flash('success', "Report '{$this->availableReports[$reportType]['name']}' generated successfully!");
    }

    public function quickExportPDF($reportType)
    {
        try {
            $this->selectedReportType = $reportType;
            $this->loadReportData();
            
            return $this->exportToPDF();
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.financial-reporting-dashboard');
    }
} 