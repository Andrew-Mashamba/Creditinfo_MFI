<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SimpleDailyLoanReportsService
{
    /**
     * Generate simplified daily reports
     */
    public function generateAndSendReports($date = null)
    {
        $reportDate = $date ?? Carbon::now();
        
        try {
            // Generate Arrears Report
            $arrearsSpreadsheet = new Spreadsheet();
            $this->createSimpleArrearsReport($arrearsSpreadsheet, $reportDate);
            
            $arrearsFileName = 'arrears_report_' . $reportDate->format('Y_m_d') . '.xlsx';
            $arrearsPath = storage_path('app/reports/' . $arrearsFileName);
            
            if (!file_exists(storage_path('app/reports'))) {
                mkdir(storage_path('app/reports'), 0755, true);
            }
            
            $writer = new Xlsx($arrearsSpreadsheet);
            $writer->save($arrearsPath);
            
            // Generate Loan Summary Report
            $summarySpreadsheet = new Spreadsheet();
            $this->createSimpleLoanSummary($summarySpreadsheet, $reportDate);
            
            $summaryFileName = 'loan_summary_' . $reportDate->format('Y_m_d') . '.xlsx';
            $summaryPath = storage_path('app/reports/' . $summaryFileName);
            
            $writer = new Xlsx($summarySpreadsheet);
            $writer->save($summaryPath);
            
            // Send to all users
            $this->sendReportsToUsers($arrearsPath, $summaryPath);
            
            Log::info('✅ Daily loan reports generated and sent successfully');
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('❌ Error generating daily loan reports: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function createSimpleArrearsReport($spreadsheet, $date)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arrears Summary');
        
        // Title
        $sheet->setCellValue('A1', 'LOAN ARREARS REPORT - ' . $date->format('Y-m-d'));
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        // Summary Section
        $row = 3;
        $sheet->setCellValue('A' . $row, 'SUMMARY');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $totalLoans = DB::table('loans')->where('loan_status', 'active')->count();
        $sheet->setCellValue('A' . $row, 'Total Active Loans:');
        $sheet->setCellValue('B' . $row, $totalLoans);
        
        $row++;
        $loansInArrears = DB::table('loans')->where('days_in_arrears', '>', 0)->count();
        $sheet->setCellValue('A' . $row, 'Loans in Arrears:');
        $sheet->setCellValue('B' . $row, $loansInArrears);
        
        $row++;
        $totalArrears = DB::table('loans')->where('days_in_arrears', '>', 0)->sum('total_arrears');
        $sheet->setCellValue('A' . $row, 'Total Arrears Amount:');
        $sheet->setCellValue('B' . $row, number_format($totalArrears ?? 0, 2));
        
        // PAR Analysis
        $row += 2;
        $sheet->setCellValue('A' . $row, 'PAR ANALYSIS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Category');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Amount');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        
        $parCategories = [
            ['name' => 'PAR 1-30', 'min' => 1, 'max' => 30],
            ['name' => 'PAR 31-60', 'min' => 31, 'max' => 60],
            ['name' => 'PAR 61-90', 'min' => 61, 'max' => 90],
            ['name' => 'PAR >90', 'min' => 91, 'max' => 9999],
        ];
        
        foreach ($parCategories as $category) {
            $row++;
            $count = DB::table('loans')
                ->whereBetween('days_in_arrears', [$category['min'], $category['max']])
                ->count();
            $amount = DB::table('loans')
                ->whereBetween('days_in_arrears', [$category['min'], $category['max']])
                ->sum('total_arrears');
            
            $sheet->setCellValue('A' . $row, $category['name']);
            $sheet->setCellValue('B' . $row, $count);
            $sheet->setCellValue('C' . $row, number_format($amount ?? 0, 2));
        }
        
        // Classification Summary
        $row += 2;
        $sheet->setCellValue('A' . $row, 'CLASSIFICATION');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Classification');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Total Outstanding');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        
        $classifications = ['PERFORMING', 'WATCH', 'SUBSTANDARD', 'DOUBTFUL', 'LOSS'];
        foreach ($classifications as $class) {
            $row++;
            $count = DB::table('loans')
                ->where('loan_classification', $class)
                ->where('loan_status', 'active')
                ->count();
            $amount = DB::table('loans')
                ->where('loan_classification', $class)
                ->where('loan_status', 'active')
                ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
            
            $sheet->setCellValue('A' . $row, $class);
            $sheet->setCellValue('B' . $row, $count);
            $sheet->setCellValue('C' . $row, number_format($amount ?? 0, 2));
        }
        
        // Detailed List Sheet
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Detailed List');
        
        $sheet->setCellValue('A1', 'LOANS IN ARREARS - DETAILED LIST');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $headers = ['Loan ID', 'Client No', 'Product', 'Disbursed', 'Outstanding', 'Arrears', 'Days', 'Classification'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        
        $arrearsLoans = DB::table('loans')
            ->where('days_in_arrears', '>', 0)
            ->select(
                'loan_id',
                'client_number',
                'loan_sub_product',
                'principle',
                DB::raw('principle - COALESCE(total_principal_paid, 0) as outstanding'),
                'total_arrears',
                'days_in_arrears',
                'loan_classification'
            )
            ->orderBy('days_in_arrears', 'desc')
            ->limit(1000)
            ->get();
        
        foreach ($arrearsLoans as $loan) {
            $row++;
            $sheet->setCellValue('A' . $row, $loan->loan_id);
            $sheet->setCellValue('B' . $row, $loan->client_number);
            $sheet->setCellValue('C' . $row, $loan->loan_sub_product ?? 'N/A');
            $sheet->setCellValue('D' . $row, number_format($loan->principle ?? 0, 2));
            $sheet->setCellValue('E' . $row, number_format($loan->outstanding ?? 0, 2));
            $sheet->setCellValue('F' . $row, number_format($loan->total_arrears ?? 0, 2));
            $sheet->setCellValue('G' . $row, $loan->days_in_arrears);
            $sheet->setCellValue('H' . $row, $loan->loan_classification);
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function createSimpleLoanSummary($spreadsheet, $date)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Portfolio Summary');
        
        // Title
        $sheet->setCellValue('A1', 'LOAN PORTFOLIO SUMMARY - ' . $date->format('Y-m-d'));
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $sheet->setCellValue('A' . $row, 'PORTFOLIO METRICS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $metrics = [];
        $metrics['Total Loans'] = DB::table('loans')->count();
        $metrics['Active Loans'] = DB::table('loans')->where('loan_status', 'active')->count();
        $metrics['Total Disbursed (All Time)'] = DB::table('loans')->sum('principle');
        $metrics['Outstanding Portfolio'] = DB::table('loans')
            ->where('loan_status', 'active')
            ->sum(DB::raw('principle - COALESCE(total_principal_paid, 0)'));
        $metrics['Total Arrears'] = DB::table('loans')->sum('total_arrears');
        $metrics['Loans in Arrears'] = DB::table('loans')->where('days_in_arrears', '>', 0)->count();
        
        foreach ($metrics as $label => $value) {
            $row++;
            $sheet->setCellValue('A' . $row, $label);
            if (strpos($label, 'Disbursed') !== false || strpos($label, 'Portfolio') !== false || strpos($label, 'Arrears') !== false) {
                $sheet->setCellValue('B' . $row, number_format($value ?? 0, 2));
            } else {
                $sheet->setCellValue('B' . $row, $value ?? 0);
            }
        }
        
        // Today's Activity
        $row += 2;
        $sheet->setCellValue('A' . $row, "TODAY'S ACTIVITY");
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $todayDisbursed = DB::table('loans')
            ->whereDate('disbursement_date', $date->toDateString())
            ->sum('principle');
        $sheet->setCellValue('A' . $row, 'Disbursed Today:');
        $sheet->setCellValue('B' . $row, number_format($todayDisbursed ?? 0, 2));
        
        $row++;
        $todayCollections = DB::table('loan_payments')
            ->whereDate('payment_date', $date->toDateString())
            ->where('status', 'completed')
            ->sum('amount');
        $sheet->setCellValue('A' . $row, 'Collected Today:');
        $sheet->setCellValue('B' . $row, number_format($todayCollections ?? 0, 2));
        
        // Product Performance Sheet
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Product Performance');
        
        $sheet->setCellValue('A1', 'PRODUCT PERFORMANCE');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $headers = ['Product', 'Count', 'Disbursed', 'Outstanding', 'In Arrears'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        $products = DB::table('loans')
            ->select(
                'loan_sub_product',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(principle) as disbursed'),
                DB::raw('SUM(CASE WHEN loan_status = \'active\' THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as outstanding'),
                DB::raw('COUNT(CASE WHEN days_in_arrears > 0 THEN 1 END) as arrears_count')
            )
            ->groupBy('loan_sub_product')
            ->get();
        
        foreach ($products as $product) {
            $row++;
            $sheet->setCellValue('A' . $row, $product->loan_sub_product ?? 'Unknown');
            $sheet->setCellValue('B' . $row, $product->count);
            $sheet->setCellValue('C' . $row, number_format($product->disbursed ?? 0, 2));
            $sheet->setCellValue('D' . $row, number_format($product->outstanding ?? 0, 2));
            $sheet->setCellValue('E' . $row, $product->arrears_count ?? 0);
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    private function sendReportsToUsers($arrearsPath, $summaryPath)
    {
        try {
            $users = DB::table('users')
                ->where('status', 'active')
                ->whereNotNull('email')
                ->get();
            
            foreach ($users as $user) {
                try {
                    Mail::send([], [], function ($message) use ($user, $arrearsPath, $summaryPath) {
                        $htmlContent = "
                        <html>
                        <body>
                            <h2>Daily Loan Reports - " . Carbon::now()->format('F d, Y') . "</h2>
                            <p>Dear {$user->name},</p>
                            <p>Please find attached the daily loan reports:</p>
                            <ul>
                                <li>Arrears Report</li>
                                <li>Loan Summary Report</li>
                            </ul>
                            <p>Best regards,<br>SACCOS System</p>
                        </body>
                        </html>";
                        
                        $message->to($user->email)
                                ->subject('Daily Loan Reports - ' . Carbon::now()->format('Y-m-d'))
                                ->html($htmlContent)
                                ->attach($arrearsPath)
                                ->attach($summaryPath);
                    });
                    
                    Log::info("📧 Reports sent to {$user->email}");
                } catch (\Exception $e) {
                    Log::error("Failed to send to {$user->email}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending reports: ' . $e->getMessage());
        }
    }
}