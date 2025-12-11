<?php

namespace App\Traits;

use App\Services\FinancialReportingService;
use Carbon\Carbon;

trait HasFinancialReporting
{
    // Common reporting properties
    public $reportPeriod = 'monthly';
    public $currency = 'TZS';
    public $viewFormat = 'detailed';
    public $startDate;
    public $endDate;
    
    // Toggle properties
    public $showCharts = false;
    public $showComparison = false;
    public $isLoading = false;
    
    // Data properties
    public $reportData = [];
    public $previousPeriodData = [];

    /**
     * Get the financial reporting service instance
     */
    protected function getReportingService(): FinancialReportingService
    {
        return app(FinancialReportingService::class);
    }

    /**
     * Initialize common reporting properties
     */
    public function initializeReporting(): void
    {
        if (!$this->startDate) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->endDate) {
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
    }

    /**
     * Handle period selection changes
     */
    public function updatedReportPeriod(): void
    {
        $this->setDateRangeByPeriod();
        $this->loadReportData();
    }

    /**
     * Handle start date changes
     */
    public function updatedStartDate(): void
    {
        $this->loadReportData();
    }

    /**
     * Handle end date changes
     */
    public function updatedEndDate(): void
    {
        $this->loadReportData();
    }

    /**
     * Handle currency changes
     */
    public function updatedCurrency(): void
    {
        $this->dispatch('currencyChanged', $this->currency);
    }

    /**
     * Handle view format changes
     */
    public function updatedViewFormat(): void
    {
        $this->dispatch('viewFormatChanged', $this->viewFormat);
    }

    /**
     * Set date range based on period
     */
    protected function setDateRangeByPeriod(): void
    {
        $dates = $this->getReportingService()->setDateRangeByPeriod($this->reportPeriod);
        $this->startDate = $dates['startDate'];
        $this->endDate = $dates['endDate'];
    }

    /**
     * Load report data - should be implemented by the component
     */
    abstract protected function loadReportData(): void;

    /**
     * Get report type - should be implemented by the component
     */
    abstract protected function getReportType(): string;

    /**
     * Generate the report
     */
    public function generateReport(): void
    {
        $this->isLoading = true;
        
        try {
            $this->loadReportData();
            session()->flash('success', 'Report generated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Export to PDF
     */
    public function exportToPDF()
    {
        try {
            $reportingService = $this->getReportingService();
            $reportType = $this->getReportType();
            
            // Ensure we have current data
            $this->loadReportData();
            
            return $reportingService->exportToPDF($reportType, $this->reportData);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export to Excel
     */
    public function exportToExcel()
    {
        try {
            $reportingService = $this->getReportingService();
            $reportType = $this->getReportType();
            
            // Ensure we have current data
            $this->loadReportData();
            
            return $reportingService->exportToExcel($reportType, $this->reportData);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting Excel: ' . $e->getMessage());
        }
    }

    /**
     * Schedule the report
     */
    public function scheduleReport(): void
    {
        try {
            $reportingService = $this->getReportingService();
            $reportType = $this->getReportType();
            
            $config = [
                'reportPeriod' => $this->reportPeriod,
                'currency' => $this->currency,
                'viewFormat' => $this->viewFormat,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate
            ];
            
            $reportingService->scheduleReport($reportType, $config, auth()->id());
            
            session()->flash('success', 'Report scheduled successfully! You will receive it via email.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error scheduling report: ' . $e->getMessage());
        }
    }

    /**
     * Toggle chart view
     */
    public function toggleChartView(): void
    {
        $this->showCharts = !$this->showCharts;
    }

    /**
     * Toggle comparison view
     */
    public function toggleComparison(): void
    {
        $this->showComparison = !$this->showComparison;
        
        if ($this->showComparison) {
            $this->loadComparisonData();
        }
    }

    /**
     * Load comparison data
     */
    protected function loadComparisonData(): void
    {
        try {
            // Load previous period data for comparison
            $previousStartDate = Carbon::parse($this->startDate)->subMonth()->startOfMonth();
            $previousEndDate = Carbon::parse($this->endDate)->subMonth()->endOfMonth();
            
            $reportingService = $this->getReportingService();
            $reportType = $this->getReportType();
            
            $params = [
                'startDate' => $previousStartDate->format('Y-m-d'),
                'endDate' => $previousEndDate->format('Y-m-d'),
                'currency' => $this->currency
            ];
            
            $this->previousPeriodData = $reportingService->generateFinancialData($reportType, $params);
            $this->previousPeriodData['period'] = $previousStartDate->format('M Y');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading comparison data: ' . $e->getMessage());
        }
    }

    /**
     * Get available currencies
     */
    public function getCurrencies(): array
    {
        return $this->getReportingService()->getCurrencies();
    }

    /**
     * Get available periods
     */
    public function getPeriods(): array
    {
        return $this->getReportingService()->getPeriods();
    }

    /**
     * Get available formats
     */
    public function getFormats(): array
    {
        return $this->getReportingService()->getFormats();
    }

    /**
     * Get period display name
     */
    public function getPeriodDisplayName(): string
    {
        switch ($this->reportPeriod) {
            case 'monthly': return 'Monthly Report';
            case 'quarterly': return 'Quarterly Report';
            case 'annually': return 'Annual Report';
            case 'custom': return 'Custom Period Report';
            default: return 'Financial Report';
        }
    }

    /**
     * Check if data is balanced (for balance sheet reports)
     */
    public function isBalanced(): bool
    {
        if (!isset($this->reportData['totalAssets'], $this->reportData['totalLiabilities'], $this->reportData['totalEquity'])) {
            return false;
        }

        $totalAssets = $this->reportData['totalAssets'];
        $totalLiabilitiesAndEquity = $this->reportData['totalLiabilities'] + $this->reportData['totalEquity'];
        
        return abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;
    }
} 