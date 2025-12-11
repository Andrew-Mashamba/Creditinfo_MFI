<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;

class LoanPortfolioReport extends Component
{
    public $reportEndDate;
    public $reportData = null;
    public $isGenerating = false;
    public $isExportingPdf = false;
    public $isExportingExcel = false;
    public $errorMessage = '';
    public $successMessage = '';

    public function mount()
    {
        $this->reportEndDate = Carbon::now()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->reportData = $this->getReportData();
            $this->successMessage = 'Loan Portfolio Report generated successfully!';
            
            Log::info('Loan Portfolio Report generated', [
                'end_date' => $this->reportEndDate,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error generating report: ' . $e->getMessage();
            Log::error('Loan Portfolio Report generation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function getReportData()
    {
        $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

        // Get approved loans from loans table
        $loans = DB::table('loans')
            ->where('status', 'APPROVED')
            ->whereNotNull('disbursement_date')
            ->where('disbursement_date', '<=', $endDate)
            ->where('loan_status', '!=', 'DECLINED')
            ->get();

        $totalLoanPortfolio = 0;
        $loanDetails = [];
        $portfolioByType = [];
        $portfolioByStatus = [];
        $riskAnalysis = [
            'delinquency_buckets' => [
                'current' => ['amount' => 0, 'count' => 0],
                '1-30_days' => ['amount' => 0, 'count' => 0],
                '31-60_days' => ['amount' => 0, 'count' => 0],
                '61-90_days' => ['amount' => 0, 'count' => 0],
                '91-180_days' => ['amount' => 0, 'count' => 0],
                'over_180_days' => ['amount' => 0, 'count' => 0],
            ],
            'portfolio_at_risk' => 0,
            'risk_distribution' => [
                'low_risk' => ['amount' => 0, 'count' => 0],
                'medium_risk' => ['amount' => 0, 'count' => 0],
                'high_risk' => ['amount' => 0, 'count' => 0],
                'critical_risk' => ['amount' => 0, 'count' => 0],
            ]
        ];
        $financialMetrics = [
            'total_interest_income' => 0,
            'portfolio_yield' => 0,
            'average_interest_rate' => 0,
            'provision_for_losses' => 0,
        ];
        $trendAnalysis = [
            'month_over_month_growth' => 0,
            'year_over_year_growth' => 0,
            'portfolio_aging' => []
        ];

        foreach ($loans as $loan) {
            // Validate loan data
            if (!$this->validateLoanData($loan)) {
                Log::warning('Skipping invalid loan data', ['loan_id' => $loan->loan_id ?? 'unknown']);
                continue;
            }
            
            // Calculate accurate outstanding balance
            $outstandingBalance = $this->calculateAccurateOutstandingBalance($loan->loan_id);

            if ($outstandingBalance['total'] > 0) {
                $totalLoanPortfolio += $outstandingBalance['total'];
                
                // Calculate risk metrics
                $riskMetrics = $this->calculateLoanRiskMetrics($loan, $endDate);
                
                $loanDetails[] = [
                    'loan_id' => $loan->loan_id,
                    'loan_account_number' => $loan->loan_account_number,
                    'client_number' => $loan->client_number,
                    'business_name' => $loan->business_name,
                    'principal' => $loan->principle, // Already cleaned by validateLoanData
                    'outstanding_balance' => $outstandingBalance['total'],
                    'outstanding_principal' => $outstandingBalance['principal'],
                    'outstanding_interest' => $outstandingBalance['interest'],
                    'outstanding_penalties' => $outstandingBalance['penalties'],
                    'interest_rate' => $loan->interest, // Already cleaned by validateLoanData
                    'tenure' => $loan->tenure,
                    'disbursement_date' => $loan->disbursement_date,
                    'category' => $this->getLoanCategory($loan->loan_type_2),
                    'status' => $loan->loan_status,
                    'days_past_due' => $riskMetrics['days_past_due'],
                    'risk_level' => $riskMetrics['risk_level'],
                    'risk_category' => $riskMetrics['risk_category'],
                ];

                // Group by type
                $category = $this->getLoanCategory($loan->loan_type_2);
                if (!isset($portfolioByType[$category])) {
                    $portfolioByType[$category] = 0;
                }
                $portfolioByType[$category] += $outstandingBalance['total'];

                // Group by status
                $status = $loan->loan_status;
                if (!isset($portfolioByStatus[$status])) {
                    $portfolioByStatus[$status] = 0;
                }
                $portfolioByStatus[$status] += $outstandingBalance['total'];

                // Update risk analysis
                $this->updateRiskAnalysis($riskAnalysis, $outstandingBalance['total'], $riskMetrics);

                // Update financial metrics
                $financialMetrics['total_interest_income'] += $this->calculateInterestIncome($loan, $endDate);
            }
        }

        // Calculate portfolio statistics
        $portfolioStats = [
            'total_portfolio' => $totalLoanPortfolio,
            'number_of_loans' => count($loanDetails),
            'average_loan_size' => count($loanDetails) > 0 ? $totalLoanPortfolio / count($loanDetails) : 0,
            'largest_loan' => count($loanDetails) > 0 ? max(array_column($loanDetails, 'outstanding_balance')) : 0,
            'smallest_loan' => count($loanDetails) > 0 ? min(array_column($loanDetails, 'outstanding_balance')) : 0,
        ];

        // Calculate financial metrics
        $financialMetrics['portfolio_yield'] = $totalLoanPortfolio > 0 ? ($financialMetrics['total_interest_income'] / $totalLoanPortfolio) * 100 : 0;
        $financialMetrics['average_interest_rate'] = count($loanDetails) > 0 ? array_sum(array_column($loanDetails, 'interest_rate')) / count($loanDetails) : 0;
        $financialMetrics['provision_for_losses'] = $this->calculateProvisionForLosses($riskAnalysis);

        // Calculate risk ratios
        $riskAnalysis['portfolio_at_risk_ratio'] = $totalLoanPortfolio > 0 ? ($riskAnalysis['portfolio_at_risk'] / $totalLoanPortfolio) * 100 : 0;
        $riskAnalysis['non_performing_loan_ratio'] = $totalLoanPortfolio > 0 ? (($riskAnalysis['risk_distribution']['high_risk']['amount'] + $riskAnalysis['risk_distribution']['critical_risk']['amount']) / $totalLoanPortfolio) * 100 : 0;

        // Calculate trend analysis
        $trendAnalysis = $this->calculateTrendAnalysis($endDate);

        return [
            'period' => [
                'end_date' => $endDate->format('F d, Y'),
                'end_date_short' => $endDate->format('M d, Y'),
            ],
            'portfolio_summary' => $portfolioStats,
            'loan_details' => $loanDetails,
            'portfolio_by_type' => $portfolioByType,
            'portfolio_by_status' => $portfolioByStatus,
            'risk_analysis' => $riskAnalysis,
            'financial_metrics' => $financialMetrics,
            'trend_analysis' => $trendAnalysis,
        ];
    }

    /**
     * Calculate accurate outstanding balance using proper loan schedule logic
     */
    private function calculateAccurateOutstandingBalance($loanId)
    {
        try {
            // Get all pending/active schedules
            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID', 'OVERDUE'])
                ->whereNotNull('installment_date')
                ->get();
                
            $penalties = 0;
            $interest = 0;
            $principal = 0;
            
            foreach ($schedules as $schedule) {
                // Calculate penalties for overdue payments
                if ($this->isScheduleOverdue($schedule)) {
                    $penalties += $this->calculatePenalty($schedule);
                }
                
                // Outstanding interest (interest due - interest paid)
                $interest += (float) ($schedule->interest ?? 0) - (float) ($schedule->interest_payment ?? 0);
                
                // Outstanding principal (principal due - principal paid)
                $principal += (float) ($schedule->principle ?? 0) - (float) ($schedule->principle_payment ?? 0);
            }
            
            return [
                'penalties' => round($penalties, 2),
                'interest' => round($interest, 2),
                'principal' => round($principal, 2),
                'total' => round($penalties + $interest + $principal, 2),
                'schedules_count' => $schedules->count()
            ];
        } catch (Exception $e) {
            Log::error('Error calculating outstanding balance for loan: ' . $loanId, [
                'error' => $e->getMessage(),
                'loan_id' => $loanId
            ]);
            
            return [
                'penalties' => 0,
                'interest' => 0,
                'principal' => 0,
                'total' => 0,
                'schedules_count' => 0
            ];
        }
    }

    /**
     * Check if schedule is overdue
     */
    private function isScheduleOverdue($schedule)
    {
        if (!$schedule->installment_date) {
            return false;
        }
        
        return Carbon::parse($schedule->installment_date)->isPast() && 
               !in_array($schedule->completion_status, ['PAID', 'COMPLETED', 'FULLY_PAID']);
    }

    /**
     * Calculate penalty for overdue payment
     */
    private function calculatePenalty($schedule)
    {
        if (!$this->isScheduleOverdue($schedule)) {
            return 0;
        }
        
        $daysOverdue = Carbon::parse($schedule->installment_date)->diffInDays(now());
        
        // Use existing penalty amount if available, otherwise calculate
        if ($schedule->penalties && $schedule->penalties > 0) {
            return (float) $schedule->penalties;
        }
        
        // Default penalty calculation (1% per month)
        $penaltyRate = 0.01;
        return ($schedule->installment ?? 0) * $penaltyRate * ($daysOverdue / 30);
    }

    /**
     * Calculate loan risk metrics
     */
    private function calculateLoanRiskMetrics($loan, $endDate)
    {
        // Get overdue installments
        $overdueInstallments = DB::table('loans_schedules')
            ->where('loan_id', $loan->loan_id)
            ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID', 'OVERDUE'])
            ->whereNotNull('installment_date')
            ->where('installment_date', '<', $endDate)
            ->get();

        $daysPastDue = 0;
        $riskLevel = 'Low Risk';
        $riskCategory = 'current';

        if ($overdueInstallments->count() > 0) {
            // Get the most overdue installment
            $mostOverdue = $overdueInstallments->sortBy('installment_date')->first();
            $daysPastDue = Carbon::parse($mostOverdue->installment_date)->diffInDays($endDate);
            
            // Determine risk level and category
            if ($daysPastDue <= 30) {
                $riskLevel = 'Low Risk';
                $riskCategory = '1-30_days';
            } elseif ($daysPastDue <= 60) {
                $riskLevel = 'Medium Risk';
                $riskCategory = '31-60_days';
            } elseif ($daysPastDue <= 90) {
                $riskLevel = 'High Risk';
                $riskCategory = '61-90_days';
            } elseif ($daysPastDue <= 180) {
                $riskLevel = 'High Risk';
                $riskCategory = '91-180_days';
            } else {
                $riskLevel = 'Critical Risk';
                $riskCategory = 'over_180_days';
            }
        } else {
            $riskCategory = 'current';
        }

        return [
            'days_past_due' => $daysPastDue,
            'risk_level' => $riskLevel,
            'risk_category' => $riskCategory,
            'overdue_installments' => $overdueInstallments->count()
        ];
    }

    /**
     * Update risk analysis data
     */
    private function updateRiskAnalysis(&$riskAnalysis, $amount, $riskMetrics)
    {
        // Update delinquency buckets
        $riskAnalysis['delinquency_buckets'][$riskMetrics['risk_category']]['amount'] += $amount;
        $riskAnalysis['delinquency_buckets'][$riskMetrics['risk_category']]['count']++;
        
        // Update portfolio at risk (overdue amounts)
        if ($riskMetrics['risk_category'] !== 'current') {
            $riskAnalysis['portfolio_at_risk'] += $amount;
        }
        
        // Update risk distribution
        $riskLevelKey = strtolower(str_replace(' ', '_', $riskMetrics['risk_level']));
        if (isset($riskAnalysis['risk_distribution'][$riskLevelKey])) {
            $riskAnalysis['risk_distribution'][$riskLevelKey]['amount'] += $amount;
            $riskAnalysis['risk_distribution'][$riskLevelKey]['count']++;
        }
    }

    /**
     * Calculate interest income for a loan
     */
    private function calculateInterestIncome($loan, $endDate)
    {
        try {
            // Get total interest paid on this loan
            $interestPaid = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->whereNotNull('installment_date')
                ->where('installment_date', '<=', $endDate)
                ->sum('interest_payment');
                
            return (float) ($interestPaid ?? 0);
        } catch (Exception $e) {
            Log::error('Error calculating interest income for loan: ' . $loan->loan_id, [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Calculate provision for loan losses
     */
    private function calculateProvisionForLosses($riskAnalysis)
    {
        $provision = 0;
        
        // Standard provisioning rates
        $provisionRates = [
            'current' => 0.01,      // 1%
            '1-30_days' => 0.02,    // 2%
            '31-60_days' => 0.05,   // 5%
            '61-90_days' => 0.10,   // 10%
            '91-180_days' => 0.25,  // 25%
            'over_180_days' => 0.50 // 50%
        ];
        
        foreach ($riskAnalysis['delinquency_buckets'] as $category => $data) {
            if (isset($provisionRates[$category])) {
                $provision += $data['amount'] * $provisionRates[$category];
            }
        }
        
        return round($provision, 2);
    }

    /**
     * Calculate trend analysis
     */
    private function calculateTrendAnalysis($endDate)
    {
        $currentMonth = Carbon::parse($endDate)->format('Y-m');
        $previousMonth = Carbon::parse($endDate)->subMonth()->format('Y-m');
        $previousYear = Carbon::parse($endDate)->subYear()->format('Y-m');
        
        // Get current month portfolio
        $currentPortfolio = $this->getPortfolioForPeriod($currentMonth);
        $previousMonthPortfolio = $this->getPortfolioForPeriod($previousMonth);
        $previousYearPortfolio = $this->getPortfolioForPeriod($previousYear);
        
        $monthOverMonthGrowth = $previousMonthPortfolio > 0 ? 
            (($currentPortfolio - $previousMonthPortfolio) / $previousMonthPortfolio) * 100 : 0;
            
        $yearOverYearGrowth = $previousYearPortfolio > 0 ? 
            (($currentPortfolio - $previousYearPortfolio) / $previousYearPortfolio) * 100 : 0;
        
        return [
            'month_over_month_growth' => round($monthOverMonthGrowth, 2),
            'year_over_year_growth' => round($yearOverYearGrowth, 2),
            'current_portfolio' => $currentPortfolio,
            'previous_month_portfolio' => $previousMonthPortfolio,
            'previous_year_portfolio' => $previousYearPortfolio,
        ];
    }

    /**
     * Get portfolio value for a specific period
     */
    private function getPortfolioForPeriod($period)
    {
        $startDate = Carbon::parse($period . '-01')->startOfMonth();
        $endDate = Carbon::parse($period . '-01')->endOfMonth();
        
        $loans = DB::table('loans')
            ->where('status', 'APPROVED')
            ->where('disbursement_date', '<=', $endDate)
            ->get();
            
        $totalPortfolio = 0;
        
        foreach ($loans as $loan) {
            $outstandingBalance = $this->calculateAccurateOutstandingBalance($loan->loan_id);
            $totalPortfolio += $outstandingBalance['total'];
        }
        
        return $totalPortfolio;
    }

    /**
     * Validate and clean loan data
     */
    private function validateLoanData($loan)
    {
        // Ensure required fields are present
        if (!$loan->loan_id || !$loan->client_number) {
            return false;
        }
        
        // Validate numeric fields
        $loan->principle = $this->cleanNumericValue($loan->principle);
        $loan->interest = $this->cleanNumericValue($loan->interest);
        
        return true;
    }
    
    /**
     * Clean and convert numeric values from string to float
     */
    private function cleanNumericValue($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        
        // Remove any non-numeric characters except decimal point and minus sign
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        
        return (float) $cleaned;
    }
    
    /**
     * Get loan category based on loan type
     */
    private function getLoanCategory($loanType)
    {
        if (!$loanType) {
            return 'Other Loans';
        }
        
        $name = strtolower($loanType);
        
        if (strpos($name, 'personal') !== false) {
            return 'Personal Loans';
        } elseif (strpos($name, 'business') !== false || strpos($name, 'commercial') !== false) {
            return 'Business Loans';
        } elseif (strpos($name, 'agriculture') !== false || strpos($name, 'agricultural') !== false) {
            return 'Agricultural Loans';
        } elseif (strpos($name, 'education') !== false || strpos($name, 'school') !== false) {
            return 'Education Loans';
        } elseif (strpos($name, 'short') !== false || strpos($name, 'working') !== false) {
            return 'Short-term Loans';
        } elseif (strpos($name, 'long') !== false || strpos($name, 'term') !== false) {
            return 'Long-term Loans';
        } else {
            return 'Other Loans';
        }
    }

    public function exportReport($format = 'pdf')
    {
        if ($format === 'pdf') {
            return $this->exportToPDF();
        } elseif ($format === 'excel') {
            return $this->exportToExcel();
        }
        
        $this->errorMessage = 'Unsupported export format: ' . $format;
    }

    public function exportToPDF()
    {
        $this->isExportingPdf = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            $filename = 'loan_portfolio_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            
            // Generate PDF using DomPDF
            $pdf = PDF::loadView('pdf.loan-portfolio-report', [
                'reportData' => $this->reportData,
                'reportDate' => $this->reportEndDate
            ]);
            
            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            Log::info('Loan Portfolio Report exported as PDF', [
                'format' => 'pdf',
                'user_id' => auth()->id(),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Portfolio Report exported as PDF successfully!';
            
            // Download the PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting PDF: ' . $e->getMessage();
            Log::error('Loan Portfolio Report PDF export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingPdf = false;
        }
    }

    public function exportToExcel()
    {
        $this->isExportingExcel = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            $filename = 'loan_portfolio_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            Log::info('Loan Portfolio Report exported as Excel', [
                'format' => 'excel',
                'user_id' => auth()->id(),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Portfolio Report exported as Excel successfully!';
            
            // Download the Excel file
            return Excel::download(
                new \App\Exports\LoanPortfolioReportExport($this->reportData, $this->reportEndDate),
                $filename,
                \Maatwebsite\Excel\Excel::XLSX
            );
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting Excel: ' . $e->getMessage();
            Log::error('Loan Portfolio Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.loan-portfolio-report');
    }
}
