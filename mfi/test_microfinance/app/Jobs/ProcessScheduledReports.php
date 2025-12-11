<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ScheduledReport;
use App\Mail\ScheduledReportEmail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ProcessScheduledReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all scheduled reports that are due to be sent
        $dueReports = ScheduledReport::where('status', 'scheduled')
            ->where(function($query) {
                $query->where('scheduled_at', '<=', now())
                    ->orWhere('next_run_at', '<=', now());
            })
            ->get();

        foreach ($dueReports as $report) {
            try {
                $this->processReport($report);
            } catch (\Exception $e) {
                \Log::error('Failed to process scheduled report: ' . $report->id, [
                    'error' => $e->getMessage(),
                    'report_id' => $report->id
                ]);

                $report->markAsFailed($e->getMessage());
            }
        }
    }

    private function processReport($report)
    {
        // Mark as processing
        $report->markAsProcessing();

        // Get report configuration
        $config = $report->report_config;
        
        // Generate the report based on type
        $reportData = $this->generateReportData($report->report_type, $config);
        
        // Generate PDF
        $pdfPath = $this->generatePDF($report, $reportData, $config);
        
        // Send emails to recipients
        $this->sendEmailsToRecipients($report, $pdfPath, $config);
        
        // Mark as completed and set next run if recurring
        $this->completeReport($report, $pdfPath);
    }

    private function generateReportData($reportType, $config)
    {
        switch ($reportType) {
            case 'Statement of Financial Position':
                return $this->generateFinancialPositionData($config);
            
            case 'Statement of Comprehensive Income':
                return $this->generateComprehensiveIncomeData($config);
                
            case 'Sectoral Classification of Loans':
                return $this->generateSectoralLoansData($config);
                
            default:
                throw new \Exception("Unknown report type: {$reportType}");
        }
    }

    private function generateFinancialPositionData($config)
    {
        // Load Assets (1000 series accounts)
        $assets = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->select('account_name', 'balance', 'account_number')
            ->get();

        // Load Liabilities (2000 series accounts)
        $liabilities = DB::table('accounts')
            ->where('major_category_code', '2000')
            ->select('account_name', 'balance', 'account_number')
            ->get();

        // Load Equity (3000 series accounts)
        $equity = DB::table('accounts')
            ->where('major_category_code', '3000')
            ->select('account_name', 'balance', 'account_number')
            ->get();

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $assets->sum('balance'),
            'totalLiabilities' => $liabilities->sum('balance'),
            'totalEquity' => $equity->sum('balance'),
            'startDate' => $config['startDate'] ?? now()->startOfMonth()->format('Y-m-d'),
            'endDate' => $config['endDate'] ?? now()->endOfMonth()->format('Y-m-d'),
            'currency' => $config['currency'] ?? 'TZS',
            'reportDate' => now()->format('Y-m-d H:i:s')
        ];
    }

    private function generateComprehensiveIncomeData($config)
    {
        // Implementation for Statement of Comprehensive Income
        // This would be similar structure but for income/expense accounts
        return [
            'reportType' => 'Statement of Comprehensive Income',
            'period' => $config['reportPeriod'] ?? 'monthly',
            'currency' => $config['currency'] ?? 'TZS',
            'reportDate' => now()->format('Y-m-d H:i:s')
        ];
    }

    private function generateSectoralLoansData($config)
    {
        // Implementation for Sectoral Classification of Loans
        $sectoral_data = DB::table('loans')
            ->join('clients', 'loans.client_id', '=', 'clients.id')
            ->select('clients.sector', 'loans.amount', 'loans.status')
            ->get()
            ->groupBy('sector');

        return [
            'sectoral_data' => $sectoral_data,
            'reportType' => 'Sectoral Classification of Loans',
            'currency' => $config['currency'] ?? 'TZS',
            'reportDate' => now()->format('Y-m-d H:i:s')
        ];
    }

    private function generatePDF($report, $data, $config)
    {
        try {
            // Create filename
            $filename = 'scheduled-report-' . $report->id . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';
            
            // Generate PDF based on report type
            $pdf = $this->createPDFByType($report->report_type, $data);
            
            // Save PDF to storage
            $path = 'scheduled-reports/' . $filename;
            Storage::put($path, $pdf->output());
            
            return $path;
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF for scheduled report', [
                'report_id' => $report->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createPDFByType($reportType, $data)
    {
        switch ($reportType) {
            case 'Statement of Financial Position':
                return PDF::loadView('pdf.statement-of-financial-position', $data);
                
            case 'Statement of Comprehensive Income':
                return PDF::loadView('pdf.statement-of-comprehensive-income', $data);
                
            case 'Sectoral Classification of Loans':
                return PDF::loadView('pdf.sectoral-classification-of-loans', $data);
                
            default:
                return PDF::loadView('pdf.generic-report', $data);
        }
    }

    private function sendEmailsToRecipients($report, $pdfPath, $config)
    {
        $recipients = explode(',', $report->email_recipients);
        $recipients = array_map('trim', $recipients);
        
        foreach ($recipients as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new ScheduledReportEmail($report, $pdfPath, $config));
                } catch (\Exception $e) {
                    \Log::error('Failed to send scheduled report email', [
                        'report_id' => $report->id,
                        'email' => $email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Mark email as sent
        $report->update(['email_sent' => true]);
    }

    private function completeReport($report, $pdfPath)
    {
        // Mark as completed
        $report->markAsCompleted($pdfPath);
        
        // Calculate next run if recurring
        if ($report->frequency !== 'once') {
            $nextRun = $this->calculateNextRun($report);
            
            // Create new scheduled report for next occurrence
            ScheduledReport::create([
                'report_type' => $report->report_type,
                'report_config' => $report->report_config,
                'user_id' => $report->user_id,
                'status' => 'scheduled',
                'frequency' => $report->frequency,
                'scheduled_at' => $nextRun,
                'next_run_at' => $this->calculateNextRun($report, $nextRun),
                'email_recipients' => $report->email_recipients,
            ]);
        }
    }

    private function calculateNextRun($report, $fromDate = null)
    {
        $baseDate = $fromDate ? Carbon::parse($fromDate) : Carbon::parse($report->scheduled_at);
        
        switch ($report->frequency) {
            case 'daily':
                return $baseDate->addDay();
            case 'weekly':
                return $baseDate->addWeek();
            case 'monthly':
                return $baseDate->addMonth();
            case 'quarterly':
                return $baseDate->addMonths(3);
            case 'annually':
                return $baseDate->addYear();
            default:
                return null;
        }
    }
}
