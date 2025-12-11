<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\Share;
use App\Models\Account;
use App\Models\Transaction;

class ReportGenerationService
{
    public function generateLoanArrearsReport($date)
    {
        // Implement loan arrears report generation logic here
    }

    public function generateDepositInterestReport($date)
    {
        // Implement deposit interest report generation logic here
    }

    public function generateShareMovementReport($date)
    {
        // Implement share movement report generation logic here
    }

    public function generateDailyTrialBalance($date)
    {
        // Implement daily trial balance generation logic here
    }

    public function generateMemberStatements($date)
    {
        // Implement member statements generation logic here
    }

    public function generateRegulatoryReports($date)
    {
        // Implement regulatory reports generation logic here
    }

    public function generateAssetReports($date)
    {
        // Implement asset reports generation logic here
    }

    public function generateInvestmentReports($date)
    {
        // Implement investment reports generation logic here
    }

    public function generateInsuranceReports($date)
    {
        // Implement insurance reports generation logic here
    }

    public function generateDocumentReports($date)
    {
        // Implement document reports generation logic here
    }

    public function generatePerformanceReports($date)
    {
        // Implement performance reports generation logic here
    }

    public function generateDailyReports($date)
    {
        // Implement daily reports generation logic here
    }

    public function generateDividendReport($date, $data)
    {
        try {
            $report = new Report([
                'type' => 'dividend',
                'date' => $date,
                'data' => json_encode($data),
                'status' => 'completed'
            ]);

            $report->save();

            // Generate PDF report
            $this->generatePDFReport($report);

            Log::info("Dividend report generated successfully for {$date->format('Y-m-d')}");
            return $report;

        } catch (\Exception $e) {
            Log::error("Dividend report generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateInterestReport($date, $data)
    {
        try {
            $report = new Report([
                'type' => 'interest',
                'date' => $date,
                'data' => json_encode($data),
                'status' => 'completed'
            ]);

            $report->save();

            // Generate PDF report
            $this->generatePDFReport($report);

            Log::info("Interest report generated successfully for {$date->format('Y-m-d')}");
            return $report;

        } catch (\Exception $e) {
            Log::error("Interest report generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateShareValueReport($share, $date)
    {
        try {
            $data = [
                'share_id' => $share->id,
                'member_id' => $share->member_id,
                'previous_value' => $share->getOriginal('price_per_share'),
                'new_value' => $share->price_per_share,
                'change_percentage' => $this->calculateChangePercentage(
                    $share->getOriginal('price_per_share'),
                    $share->price_per_share
                )
            ];

            $report = new Report([
                'type' => 'share_value',
                'date' => $date,
                'data' => json_encode($data),
                'status' => 'completed'
            ]);

            $report->save();

            // Generate PDF report
            $this->generatePDFReport($report);

            Log::info("Share value report generated successfully for share ID {$share->id}");
            return $report;

        } catch (\Exception $e) {
            Log::error("Share value report generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function generatePDFReport($report)
    {
        // Implementation for PDF generation
        // This could use a library like DomPDF or TCPDF
        // For now, we'll just create a placeholder
        $pdfPath = "reports/{$report->type}/{$report->date->format('Y-m-d')}.pdf";
        Storage::put($pdfPath, "PDF content for report {$report->id}");
    }

    protected function calculateChangePercentage($oldValue, $newValue)
    {
        if ($oldValue == 0) return 0;
        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    public function generateDailyTransactionReport($date)
    {
        try {
            $transactions = Transaction::whereDate('created_at', $date)->get();
            
            $data = [
                'total_transactions' => $transactions->count(),
                'total_credits' => $transactions->where('type', 'credit')->sum('amount'),
                'total_debits' => $transactions->where('type', 'debit')->sum('amount'),
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'narration' => $transaction->narration,
                        'status' => $transaction->status
                    ];
                })
            ];

            $report = new Report([
                'type' => 'daily_transactions',
                'date' => $date,
                'data' => json_encode($data),
                'status' => 'completed'
            ]);

            $report->save();

            // Generate PDF report
            $this->generatePDFReport($report);

            Log::info("Daily transaction report generated successfully for {$date->format('Y-m-d')}");
            return $report;

        } catch (\Exception $e) {
            Log::error("Daily transaction report generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateAccountStatement($account, $startDate, $endDate)
    {
        try {
            $transactions = Transaction::where('account_id', $account->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at')
                ->get();

            $data = [
                'account_number' => $account->account_number,
                'account_type' => $account->type,
                'member_name' => $account->member->name,
                'opening_balance' => $this->getOpeningBalance($account, $startDate),
                'closing_balance' => $this->getClosingBalance($account, $endDate),
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'narration' => $transaction->narration,
                        'balance' => $transaction->balance_after
                    ];
                })
            ];

            $report = new Report([
                'type' => 'account_statement',
                'date' => Carbon::now(),
                'data' => json_encode($data),
                'status' => 'completed'
            ]);

            $report->save();

            // Generate PDF report
            $this->generatePDFReport($report);

            Log::info("Account statement generated successfully for account {$account->account_number}");
            return $report;

        } catch (\Exception $e) {
            Log::error("Account statement generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getOpeningBalance($account, $date)
    {
        $lastTransaction = Transaction::where('account_id', $account->id)
            ->where('created_at', '<', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : $account->opening_balance;
    }

    protected function getClosingBalance($account, $date)
    {
        $lastTransaction = Transaction::where('account_id', $account->id)
            ->where('created_at', '<=', $date)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : $account->balance;
    }
} 