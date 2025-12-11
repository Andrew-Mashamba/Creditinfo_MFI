<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DailyLoanReportsService
{
    /**
     * Helper to get the balance calculation SQL
     */
    private function getBalanceSQL()
    {
        return '(principle - COALESCE(total_principal_paid, 0))';
    }
    
    /**
     * Generate daily arrears report with multiple sheets
     */
    public function generateArrearsReport($date = null)
    {
        $reportDate = $date ?? Carbon::now();
        Log::info('📊 Generating Daily Arrears Report for ' . $reportDate->format('Y-m-d'));
        
        try {
            $spreadsheet = new Spreadsheet();
            
            // Sheet 1: Summary
            $this->createArrearsSummarySheet($spreadsheet, 0, $reportDate);
            
            // Sheet 2: PAR Analysis
            $this->createPARAnalysisSheet($spreadsheet, $reportDate);
            
            // Sheet 3: Detailed List
            $this->createArrearsDetailSheet($spreadsheet, $reportDate);
            
            // Sheet 4: Classification (WATCH)
            $this->createClassificationSheet($spreadsheet, 'WATCH', $reportDate);
            
            // Sheet 5: Classification (SUBSTANDARD)
            $this->createClassificationSheet($spreadsheet, 'SUBSTANDARD', $reportDate);
            
            // Sheet 6: Classification (DOUBTFUL)
            $this->createClassificationSheet($spreadsheet, 'DOUBTFUL', $reportDate);
            
            // Sheet 7: Classification (LOSS)
            $this->createClassificationSheet($spreadsheet, 'LOSS', $reportDate);
            
            // Save the file
            $fileName = 'arrears_report_' . $reportDate->format('Y_m_d') . '.xlsx';
            $filePath = storage_path('app/reports/' . $fileName);
            
            // Create directory if it doesn't exist
            if (!file_exists(storage_path('app/reports'))) {
                mkdir(storage_path('app/reports'), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            Log::info('✅ Arrears report generated: ' . $fileName);
            
            return $filePath;
            
        } catch (\Exception $e) {
            Log::error('❌ Error generating arrears report: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate daily loan summary report
     */
    public function generateLoanSummaryReport($date = null)
    {
        $reportDate = $date ?? Carbon::now();
        Log::info('📊 Generating Daily Loan Summary Report for ' . $reportDate->format('Y-m-d'));
        
        try {
            $spreadsheet = new Spreadsheet();
            
            // Sheet 1: Portfolio Summary
            $this->createPortfolioSummarySheet($spreadsheet, 0, $reportDate);
            
            // Sheet 2: Disbursement Summary
            $this->createDisbursementSummarySheet($spreadsheet, $reportDate);
            
            // Sheet 3: Collections Summary
            $this->createCollectionsSummarySheet($spreadsheet, $reportDate);
            
            // Sheet 4: Product Performance
            $this->createProductPerformanceSheet($spreadsheet, $reportDate);
            
            // Sheet 5: Branch Performance
            $this->createBranchPerformanceSheet($spreadsheet, $reportDate);
            
            // Sheet 6: Officer Performance
            $this->createOfficerPerformanceSheet($spreadsheet, $reportDate);
            
            // Save the file
            $fileName = 'loan_summary_' . $reportDate->format('Y_m_d') . '.xlsx';
            $filePath = storage_path('app/reports/' . $fileName);
            
            // Create directory if it doesn't exist
            if (!file_exists(storage_path('app/reports'))) {
                mkdir(storage_path('app/reports'), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            Log::info('✅ Loan summary report generated: ' . $fileName);
            
            return $filePath;
            
        } catch (\Exception $e) {
            Log::error('❌ Error generating loan summary report: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send reports to all system users
     */
    public function sendReportsToUsers($arrearsReportPath, $summaryReportPath)
    {
        try {
            // Get all active users
            $users = DB::table('users')
                ->where('status', 'active')
                ->whereNotNull('email')
                ->get();
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($users as $user) {
                try {
                    Mail::send([], [], function ($message) use ($user, $arrearsReportPath, $summaryReportPath) {
                        $htmlContent = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2>Daily Loan Reports - " . Carbon::now()->format('F d, Y') . "</h2>
                            <p>Dear {$user->name},</p>
                            <p>Please find attached the daily loan reports:</p>
                            <ul>
                                <li><strong>Arrears Report:</strong> Contains summary, PAR analysis, detailed list, and classification sheets</li>
                                <li><strong>Loan Summary Report:</strong> Contains portfolio, disbursement, collections, and performance summaries</li>
                            </ul>
                            <p>Report Date: " . Carbon::now()->format('Y-m-d') . "</p>
                            <p>Generated at: " . Carbon::now()->format('H:i:s') . "</p>
                            <br>
                            <p>Best regards,<br>SACCOS System</p>
                        </body>
                        </html>";
                        
                        $message->to($user->email)
                                ->subject('Daily Loan Reports - ' . Carbon::now()->format('Y-m-d'))
                                ->html($htmlContent)
                                ->attach($arrearsReportPath, [
                                    'as' => 'arrears_report_' . Carbon::now()->format('Y_m_d') . '.xlsx',
                                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ])
                                ->attach($summaryReportPath, [
                                    'as' => 'loan_summary_' . Carbon::now()->format('Y_m_d') . '.xlsx',
                                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ]);
                    });
                    
                    $successCount++;
                    Log::info("📧 Reports sent to {$user->email}");
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("❌ Failed to send reports to {$user->email}: " . $e->getMessage());
                }
            }
            
            Log::info("📊 Report distribution complete: {$successCount} successful, {$errorCount} failed");
            
        } catch (\Exception $e) {
            Log::error('❌ Error sending reports to users: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // Helper methods for Arrears Report sheets
    
    private function createArrearsSummarySheet($spreadsheet, $sheetIndex, $date)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');
        
        // Title
        $sheet->setCellValue('A1', 'LOAN ARREARS SUMMARY REPORT');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Report Date: ' . $date->format('Y-m-d'));
        $sheet->mergeCells('A2:F2');
        
        // Get summary data
        $totalLoans = DB::table('loans')->where('loan_status', 'active')->count();
        $loansInArrears = DB::table('loans')->where('days_in_arrears', '>', 0)->count();
        $totalPortfolio = DB::table('loans')
            ->where('loan_status', 'active')
            ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
        $totalArrears = DB::table('loans')->where('days_in_arrears', '>', 0)->sum('total_arrears');
        
        // Classifications
        $balanceSQL = $this->getBalanceSQL();
        $classifications = DB::table('loans')
            ->select('loan_classification', DB::raw('COUNT(*) as count'), DB::raw("SUM({$balanceSQL}) as portfolio"), DB::raw('SUM(total_arrears) as arrears'))
            ->where('loan_status', 'active')
            ->groupBy('loan_classification')
            ->get();
        
        // Summary table
        $row = 4;
        $sheet->setCellValue('A' . $row, 'PORTFOLIO OVERVIEW');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Active Loans');
        $sheet->setCellValue('B' . $row, $totalLoans);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Loans in Arrears');
        $sheet->setCellValue('B' . $row, $loansInArrears);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Portfolio');
        $sheet->setCellValue('B' . $row, $totalPortfolio);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Arrears');
        $sheet->setCellValue('B' . $row, $totalArrears);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'PAR %');
        $sheet->setCellValue('B' . $row, $totalPortfolio > 0 ? ($totalArrears / $totalPortfolio * 100) : 0);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('0.00%');
        
        // Classification breakdown
        $row += 2;
        $sheet->setCellValue('A' . $row, 'CLASSIFICATION BREAKDOWN');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Classification');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Portfolio');
        $sheet->setCellValue('D' . $row, 'Arrears');
        $sheet->setCellValue('E' . $row, '% of Total');
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        foreach ($classifications as $class) {
            $row++;
            $sheet->setCellValue('A' . $row, $class->loan_classification);
            $sheet->setCellValue('B' . $row, $class->count);
            $sheet->setCellValue('C' . $row, $class->portfolio);
            $sheet->setCellValue('D' . $row, $class->arrears);
            $sheet->setCellValue('E' . $row, $totalPortfolio > 0 ? ($class->portfolio / $totalPortfolio) : 0);
            $sheet->getStyle('C' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('0.00%');
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createPARAnalysisSheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('PAR Analysis');
        
        // Title
        $sheet->setCellValue('A1', 'PORTFOLIO AT RISK (PAR) ANALYSIS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // PAR bands
        $parBands = [
            ['band' => 'PAR 1-30', 'min' => 1, 'max' => 30],
            ['band' => 'PAR 31-60', 'min' => 31, 'max' => 60],
            ['band' => 'PAR 61-90', 'min' => 61, 'max' => 90],
            ['band' => 'PAR 91-180', 'min' => 91, 'max' => 180],
            ['band' => 'PAR >180', 'min' => 181, 'max' => 9999],
        ];
        
        $row = 3;
        $sheet->setCellValue('A' . $row, 'PAR Band');
        $sheet->setCellValue('B' . $row, 'No. of Loans');
        $sheet->setCellValue('C' . $row, 'Principal Outstanding');
        $sheet->setCellValue('D' . $row, 'Interest Outstanding');
        $sheet->setCellValue('E' . $row, 'Total Outstanding');
        $sheet->setCellValue('F' . $row, 'Total Arrears');
        $sheet->setCellValue('G' . $row, '% of Portfolio');
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        
        $balanceSQL = $this->getBalanceSQL();
        $totalPortfolio = DB::table('loans')->where('loan_status', 'active')->sum(DB::raw($balanceSQL));
        
        foreach ($parBands as $band) {
            $row++;
            
            $data = DB::table('loans')
                ->where('loan_status', 'active')
                ->whereBetween('days_in_arrears', [$band['min'], $band['max']])
                ->select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw("SUM({$balanceSQL}) as principal"),
                    DB::raw('SUM(total_interest_paid - COALESCE(total_interest_paid, 0)) as interest'),
                    DB::raw("SUM({$balanceSQL} + (total_interest_paid - COALESCE(total_interest_paid, 0))) as total"),
                    DB::raw('SUM(total_arrears) as arrears')
                )
                ->first();
            
            $sheet->setCellValue('A' . $row, $band['band']);
            $sheet->setCellValue('B' . $row, $data->count ?? 0);
            $sheet->setCellValue('C' . $row, $data->principal ?? 0);
            $sheet->setCellValue('D' . $row, $data->interest ?? 0);
            $sheet->setCellValue('E' . $row, $data->total ?? 0);
            $sheet->setCellValue('F' . $row, $data->arrears ?? 0);
            $sheet->setCellValue('G' . $row, $totalPortfolio > 0 ? (($data->principal ?? 0) / $totalPortfolio) : 0);
            
            $sheet->getStyle('C' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('0.00%');
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createArrearsDetailSheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Detailed List');
        
        // Title
        $sheet->setCellValue('A1', 'DETAILED ARREARS LIST');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $row = 3;
        $headers = [
            'Loan ID', 'Member No', 'Member Name', 'Product', 'Disbursed', 
            'Disbursement Date', 'Outstanding', 'Arrears Amount', 'Days in Arrears',
            'Classification', 'Officer', 'Branch'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':L' . $row)->getFont()->setBold(true);
        
        // Get arrears data
        $arrearsLoans = DB::table('loans')
            ->join('clients', 'loans.client_id', '=', 'clients.id')
            // ->leftJoin('users as officers', 'loans.loan_officer_id', '=', 'officers.id')
            ->leftJoin('branches', DB::raw('CAST(loans.branch_id AS BIGINT)'), '=', 'branches.id')
            ->where('loans.days_in_arrears', '>', 0)
            ->select(
                'loans.loan_id',
                'clients.client_number',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as member_name"),
                'loans.loan_product',
                'loans.principle',
                'loans.disbursement_date',
                DB::raw("{$this->getBalanceSQL()} as balance"),
                'loans.total_arrears',
                'loans.days_in_arrears',
                'loans.loan_classification',
                // 'officers.name as officer_name',
                'branches.name as branch_name'
            )
            ->orderBy('loans.days_in_arrears', 'desc')
            ->get();
        
        foreach ($arrearsLoans as $loan) {
            $row++;
            $sheet->setCellValue('A' . $row, $loan->loan_id);
            $sheet->setCellValue('B' . $row, $loan->client_number);
            $sheet->setCellValue('C' . $row, $loan->member_name);
            $sheet->setCellValue('D' . $row, $loan->loan_product);
            $sheet->setCellValue('E' . $row, $loan->principle);
            $sheet->setCellValue('F' . $row, $loan->disbursement_date);
            $sheet->setCellValue('G' . $row, $loan->balance);
            $sheet->setCellValue('H' . $row, $loan->total_arrears);
            $sheet->setCellValue('I' . $row, $loan->days_in_arrears);
            $sheet->setCellValue('J' . $row, $loan->loan_classification);
            $sheet->setCellValue('K' . $row, 'N/A');
            $sheet->setCellValue('L' . $row, $loan->branch_name ?? 'N/A');
            
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G' . $row . ':H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createClassificationSheet($spreadsheet, $classification, $date)
    {
        $spreadsheet->createSheet();
        $sheetIndex = $spreadsheet->getSheetCount() - 1;
        $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
        $sheet->setTitle($classification);
        
        // Title
        $sheet->setCellValue('A1', $classification . ' LOANS');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $row = 3;
        $headers = [
            'Loan ID', 'Member No', 'Member Name', 'Phone', 'Disbursed Amount',
            'Outstanding', 'Arrears', 'Days Overdue', 'Last Payment Date', 
            'Officer', 'Branch'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':K' . $row)->getFont()->setBold(true);
        
        // Get classification data
        $loans = DB::table('loans')
            ->join('clients', 'loans.client_id', '=', 'clients.id')
            // ->leftJoin('users as officers', 'loans.loan_officer_id', '=', 'officers.id')
            ->leftJoin('branches', DB::raw('CAST(loans.branch_id AS BIGINT)'), '=', 'branches.id')
            ->where('loans.loan_classification', $classification)
            ->select(
                'loans.loan_id',
                'clients.client_number',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as member_name"),
                'clients.mobile_phone_number',
                'loans.principle',
                DB::raw("{$this->getBalanceSQL()} as balance"),
                'loans.total_arrears',
                'loans.days_in_arrears',
                DB::raw("(SELECT MAX(payment_date) FROM loan_payments WHERE loan_id = loans.loan_id) as last_payment"),
                // 'officers.name as officer_name',
                'branches.name as branch_name'
            )
            ->orderBy('loans.days_in_arrears', 'desc')
            ->get();
        
        foreach ($loans as $loan) {
            $row++;
            $sheet->setCellValue('A' . $row, $loan->loan_id);
            $sheet->setCellValue('B' . $row, $loan->client_number);
            $sheet->setCellValue('C' . $row, $loan->member_name);
            $sheet->setCellValue('D' . $row, $loan->mobile_phone_number);
            $sheet->setCellValue('E' . $row, $loan->principle);
            $sheet->setCellValue('F' . $row, $loan->balance);
            $sheet->setCellValue('G' . $row, $loan->total_arrears);
            $sheet->setCellValue('H' . $row, $loan->days_in_arrears);
            $sheet->setCellValue('I' . $row, $loan->last_payment ?? 'Never');
            $sheet->setCellValue('J' . $row, 'N/A');
            $sheet->setCellValue('K' . $row, $loan->branch_name ?? 'N/A');
            
            $sheet->getStyle('E' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    // Helper methods for Loan Summary Report sheets
    
    private function createPortfolioSummarySheet($spreadsheet, $sheetIndex, $date)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Portfolio Summary');
        
        // Title
        $sheet->setCellValue('A1', 'LOAN PORTFOLIO SUMMARY');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Report Date: ' . $date->format('Y-m-d'));
        $sheet->mergeCells('A2:E2');
        
        // Portfolio metrics
        $metrics = [];
        $metrics['Total Loans'] = DB::table('loans')->count();
        $metrics['Active Loans'] = DB::table('loans')->where('loan_status', 'active')->count();
        $metrics['Total Disbursed'] = DB::table('loans')->sum('principle');
        $metrics['Outstanding Portfolio'] = DB::table('loans')->where('loan_status', 'active')->sum(DB::raw($this->getBalanceSQL()));
        $metrics['Total Interest Earned'] = DB::table('loans')->sum('total_interest_paid');
        $metrics['Total Principal Collected'] = DB::table('loans')->sum('total_principal_paid');
        $metrics['Average Loan Size'] = DB::table('loans')->avg('principle');
        $metrics['Portfolio at Risk'] = DB::table('loans')->where('days_in_arrears', '>', 0)->sum(DB::raw($this->getBalanceSQL()));
        
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Metric');
        $sheet->setCellValue('B' . $row, 'Value');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        
        foreach ($metrics as $metric => $value) {
            $row++;
            $sheet->setCellValue('A' . $row, $metric);
            $sheet->setCellValue('B' . $row, $value);
            if (strpos($metric, 'Total') !== false || strpos($metric, 'Outstanding') !== false || strpos($metric, 'Average') !== false || strpos($metric, 'Portfolio at Risk') !== false) {
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createDisbursementSummarySheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Disbursements');
        
        // Title
        $sheet->setCellValue('A1', 'DISBURSEMENT SUMMARY');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Today's disbursements
        $todayDisbursements = DB::table('loans')
            ->join('clients', 'loans.client_id', '=', 'clients.id')
            ->whereDate('loans.disbursement_date', $date->toDateString())
            ->select(
                'loans.loan_id',
                'clients.client_number',
                DB::raw("CONCAT(clients.first_name, ' ', clients.last_name) as member_name"),
                'loans.loan_product',
                'loans.principle',
                'loans.disbursement_method'
            )
            ->get();
        
        $row = 3;
        $sheet->setCellValue('A' . $row, "Today's Disbursements (" . $date->format('Y-m-d') . ")");
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Loan ID');
        $sheet->setCellValue('B' . $row, 'Member No');
        $sheet->setCellValue('C' . $row, 'Member Name');
        $sheet->setCellValue('D' . $row, 'Product');
        $sheet->setCellValue('E' . $row, 'Amount');
        $sheet->setCellValue('F' . $row, 'Method');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        
        $totalDisbursed = 0;
        foreach ($todayDisbursements as $loan) {
            $row++;
            $sheet->setCellValue('A' . $row, $loan->loan_id);
            $sheet->setCellValue('B' . $row, $loan->client_number);
            $sheet->setCellValue('C' . $row, $loan->member_name);
            $sheet->setCellValue('D' . $row, $loan->loan_product);
            $sheet->setCellValue('E' . $row, $loan->principle);
            $sheet->setCellValue('F' . $row, $loan->disbursement_method ?? 'N/A');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $totalDisbursed += $loan->principle;
        }
        
        $row++;
        $sheet->setCellValue('D' . $row, 'TOTAL');
        $sheet->setCellValue('E' . $row, $totalDisbursed);
        $sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createCollectionsSummarySheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Collections');
        
        // Title
        $sheet->setCellValue('A1', 'COLLECTIONS SUMMARY');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Today's collections
        $collections = DB::table('loan_payments')
            ->whereDate('payment_date', $date->toDateString())
            ->where('status', 'completed')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(principal_amount) as principal'),
                DB::raw('SUM(interest_amount) as interest'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('payment_method')
            ->get();
        
        $row = 3;
        $sheet->setCellValue('A' . $row, "Today's Collections (" . $date->format('Y-m-d') . ")");
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Payment Method');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Principal');
        $sheet->setCellValue('D' . $row, 'Interest');
        $sheet->setCellValue('E' . $row, 'Total');
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        $totalCollected = 0;
        foreach ($collections as $collection) {
            $row++;
            $sheet->setCellValue('A' . $row, $collection->payment_method);
            $sheet->setCellValue('B' . $row, $collection->count);
            $sheet->setCellValue('C' . $row, $collection->principal);
            $sheet->setCellValue('D' . $row, $collection->interest);
            $sheet->setCellValue('E' . $row, $collection->total);
            $sheet->getStyle('C' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $totalCollected += $collection->total;
        }
        
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('E' . $row, $totalCollected);
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createProductPerformanceSheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(3);
        $sheet->setTitle('Product Performance');
        
        // Title
        $sheet->setCellValue('A1', 'PRODUCT PERFORMANCE SUMMARY');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Product performance data
        $products = DB::table('loans')
            ->select(
                'loan_product',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN loan_status = "active" THEN 1 ELSE 0 END) as active_count'),
                DB::raw('SUM(principle) as total_disbursed'),
                DB::raw("SUM(CASE WHEN loan_status = 'active' THEN {$this->getBalanceSQL()} ELSE 0 END) as outstanding"),
                DB::raw('SUM(total_interest_paid) as interest_collected'),
                DB::raw("SUM(CASE WHEN days_in_arrears > 0 THEN {$this->getBalanceSQL()} ELSE 0 END) as par"),
                DB::raw('AVG(interest_rate) as avg_rate')
            )
            ->groupBy('loan_product')
            ->get();
        
        $row = 3;
        $headers = ['Product', 'Total Loans', 'Active', 'Disbursed', 'Outstanding', 'Interest Collected', 'PAR', 'Avg Rate'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        
        foreach ($products as $product) {
            $row++;
            $sheet->setCellValue('A' . $row, $product->loan_product);
            $sheet->setCellValue('B' . $row, $product->count);
            $sheet->setCellValue('C' . $row, $product->active_count);
            $sheet->setCellValue('D' . $row, $product->total_disbursed);
            $sheet->setCellValue('E' . $row, $product->outstanding);
            $sheet->setCellValue('F' . $row, $product->interest_collected);
            $sheet->setCellValue('G' . $row, $product->par);
            $sheet->setCellValue('H' . $row, $product->avg_rate . '%');
            $sheet->getStyle('D' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createBranchPerformanceSheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(4);
        $sheet->setTitle('Branch Performance');
        
        // Title
        $sheet->setCellValue('A1', 'BRANCH PERFORMANCE SUMMARY');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Branch performance data
        $branches = DB::table('loans')
            ->leftJoin('branches', DB::raw('CAST(loans.branch_id AS BIGINT)'), '=', 'branches.id')
            ->select(
                'branches.name as branch_name',
                DB::raw('COUNT(*) as total_loans'),
                DB::raw('SUM(CASE WHEN loan_status = "active" THEN 1 ELSE 0 END) as active_loans'),
                DB::raw('SUM(principle) as total_disbursed'),
                DB::raw("SUM(CASE WHEN loan_status = 'active' THEN {$this->getBalanceSQL()} ELSE 0 END) as outstanding"),
                DB::raw('SUM(total_interest_paid + total_principal_paid) as collections'),
                DB::raw("SUM(CASE WHEN days_in_arrears > 0 THEN {$this->getBalanceSQL()} ELSE 0 END) as par")
            )
            ->groupBy('branches.id', 'branches.name')
            ->get();
        
        $row = 3;
        $headers = ['Branch', 'Total Loans', 'Active', 'Disbursed', 'Outstanding', 'Collections', 'PAR'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        
        foreach ($branches as $branch) {
            $row++;
            $sheet->setCellValue('A' . $row, $branch->branch_name ?? 'Head Office');
            $sheet->setCellValue('B' . $row, $branch->total_loans);
            $sheet->setCellValue('C' . $row, $branch->active_loans);
            $sheet->setCellValue('D' . $row, $branch->total_disbursed);
            $sheet->setCellValue('E' . $row, $branch->outstanding);
            $sheet->setCellValue('F' . $row, $branch->collections);
            $sheet->setCellValue('G' . $row, $branch->par);
            $sheet->getStyle('D' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createOfficerPerformanceSheet($spreadsheet, $date)
    {
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(5);
        $sheet->setTitle('Officer Performance');
        
        // Title
        $sheet->setCellValue('A1', 'LOAN OFFICER PERFORMANCE');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Officer performance data
        $officers = DB::table('loans')
            ->leftJoin('users', 'loans.loan_officer_id', '=', 'users.id')
            ->select(
                'users.name as officer_name',
                DB::raw('COUNT(*) as total_loans'),
                DB::raw('SUM(CASE WHEN loan_status = "active" THEN 1 ELSE 0 END) as active_loans'),
                DB::raw('SUM(principle) as total_disbursed'),
                DB::raw("SUM(CASE WHEN loan_status = 'active' THEN {$this->getBalanceSQL()} ELSE 0 END) as outstanding"),
                DB::raw('SUM(total_interest_paid + total_principal_paid) as collections'),
                DB::raw('SUM(CASE WHEN days_in_arrears > 0 THEN 1 ELSE 0 END) as loans_in_arrears'),
                DB::raw("SUM(CASE WHEN days_in_arrears > 0 THEN {$this->getBalanceSQL()} ELSE 0 END) as par")
            )
            ->groupBy('users.id', 'users.name')
            ->get();
        
        $row = 3;
        $headers = ['Officer', 'Total Loans', 'Active', 'Disbursed', 'Outstanding', 'Collections', 'Arrears Count', 'PAR'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        
        foreach ($officers as $officer) {
            $row++;
            $sheet->setCellValue('A' . $row, $officer->officer_name ?? 'Unassigned');
            $sheet->setCellValue('B' . $row, $officer->total_loans);
            $sheet->setCellValue('C' . $row, $officer->active_loans);
            $sheet->setCellValue('D' . $row, $officer->total_disbursed);
            $sheet->setCellValue('E' . $row, $officer->outstanding);
            $sheet->setCellValue('F' . $row, $officer->collections);
            $sheet->setCellValue('G' . $row, $officer->loans_in_arrears);
            $sheet->setCellValue('H' . $row, $officer->par);
            $sheet->getStyle('D' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}