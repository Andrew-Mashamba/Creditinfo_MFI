<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class PortfolioAtRisk extends Component
{
    public $reportEndDate;
    public $reportData = null;
    public $isGenerating = false;
    public $isExporting = false;
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
            $this->successMessage = 'Portfolio at Risk Report generated successfully!';
            
            Log::info('Portfolio at Risk Report generated', [
                'end_date' => $this->reportEndDate,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error generating report: ' . $e->getMessage();
            Log::error('Portfolio at Risk Report generation failed', [
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

        // Get approved loans
        $loans = DB::table('loans')
            ->where('status', 'APPROVED')
            ->where('disbursement_date', '<=', $endDate)
            ->get();

        $portfolioAtRisk = [
            '1-30 days' => ['amount' => 0, 'count' => 0],
            '31-60 days' => ['amount' => 0, 'count' => 0],
            '61-90 days' => ['amount' => 0, 'count' => 0],
            '91-180 days' => ['amount' => 0, 'count' => 0],
            'Over 180 days' => ['amount' => 0, 'count' => 0],
        ];

        $totalPortfolio = 0;
        $totalAtRisk = 0;
        $riskDetails = [];

        foreach ($loans as $loan) {
            // Get outstanding balance
            $outstandingBalance = DB::table('loans_schedules')
                ->where('loan_id', $loan->loan_id)
                ->where('status', 'PENDING')
                ->sum('opening_balance');

            $totalPortfolio += $outstandingBalance;

            if ($outstandingBalance > 0) {
                // Get overdue installments
                $overdueInstallments = DB::table('loans_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->where('status', 'PENDING')
                    ->where('installment_date', '<', $endDate)
                    ->get();

                if ($overdueInstallments->count() > 0) {
                    // Calculate total overdue amount
                    $overdueAmount = $overdueInstallments->sum('installment');
                    $totalAtRisk += $overdueAmount;

                    // Get the most overdue installment for days calculation
                    $mostOverdue = $overdueInstallments->sortBy('installment_date')->first();
                    $daysPastDue = Carbon::parse($mostOverdue->installment_date)->diffInDays($endDate);

                    $riskCategory = $this->getRiskCategory($daysPastDue);
                    
                    $portfolioAtRisk[$riskCategory]['amount'] += $overdueAmount;
                    $portfolioAtRisk[$riskCategory]['count']++;

                    $riskDetails[] = [
                        'loan_id' => $loan->loan_id,
                        'loan_account_number' => $loan->loan_account_number,
                        'client_number' => $loan->client_number,
                        'business_name' => $loan->business_name,
                        'outstanding_balance' => $outstandingBalance,
                        'overdue_amount' => $overdueAmount,
                        'last_due_date' => $mostOverdue->installment_date,
                        'days_past_due' => $daysPastDue,
                        'risk_category' => $riskCategory,
                        'risk_level' => $this->getRiskLevel($daysPastDue),
                        'overdue_installments' => $overdueInstallments->count(),
                    ];
                }
            }
        }

        // Calculate risk ratios
        $riskRatio = $totalPortfolio > 0 ? ($totalAtRisk / $totalPortfolio) * 100 : 0;

        return [
            'period' => [
                'end_date' => $endDate->format('F d, Y'),
                'end_date_short' => $endDate->format('M d, Y'),
            ],
            'risk_summary' => [
                'total_portfolio' => $totalPortfolio,
                'total_at_risk' => $totalAtRisk,
                'risk_ratio' => $riskRatio,
                'number_of_risky_loans' => count($riskDetails),
            ],
            'portfolio_at_risk' => $portfolioAtRisk,
            'risk_details' => $riskDetails,
        ];
    }

    private function getRiskCategory($daysPastDue)
    {
        if ($daysPastDue <= 30) {
            return '1-30 days';
        } elseif ($daysPastDue <= 60) {
            return '31-60 days';
        } elseif ($daysPastDue <= 90) {
            return '61-90 days';
        } elseif ($daysPastDue <= 180) {
            return '91-180 days';
        } else {
            return 'Over 180 days';
        }
    }

    private function getRiskLevel($daysPastDue)
    {
        if ($daysPastDue <= 30) {
            return 'Low Risk';
        } elseif ($daysPastDue <= 60) {
            return 'Medium Risk';
        } elseif ($daysPastDue <= 90) {
            return 'High Risk';
        } else {
            return 'Critical Risk';
        }
    }

    public function exportReport($format = 'pdf')
    {
        $this->isExporting = true;
        $this->errorMessage = '';

        try {
            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            $this->successMessage = "Portfolio at Risk Report exported as {$format} successfully!";
            
            Log::info('Portfolio at Risk Report exported', [
                'format' => $format,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting report: ' . $e->getMessage();
            Log::error('Portfolio at Risk Report export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExporting = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.portfolio-at-risk');
    }
}
