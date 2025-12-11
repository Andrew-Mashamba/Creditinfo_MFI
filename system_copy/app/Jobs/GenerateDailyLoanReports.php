<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class GenerateDailyLoanReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportDate;
    protected $statistics;
    public $timeout = 300; // 5 minutes
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct($reportDate, $statistics = [])
    {
        $this->reportDate = $reportDate;
        $this->statistics = $statistics;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('📊 Starting optimized report generation for ' . $this->reportDate->format('Y-m-d'));
        
        try {
            // Generate reports with memory optimization
            $arrearsPath = $this->generateOptimizedArrearsReport();
            $summaryPath = $this->generateOptimizedSummaryReport();
            
            // Send reports in batches to avoid memory issues
            $this->sendReportsInBatches($arrearsPath, $summaryPath);
            
            Log::info('✅ Reports generated and distributed successfully');
            
        } catch (\Exception $e) {
            Log::error('❌ Report generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate optimized arrears report using streaming
     */
    private function generateOptimizedArrearsReport()
    {
        $spreadsheet = new Spreadsheet();
        $fileName = 'arrears_report_' . $this->reportDate->format('Y_m_d') . '.xlsx';
        $filePath = storage_path('app/reports/' . $fileName);
        
        if (!file_exists(storage_path('app/reports'))) {
            mkdir(storage_path('app/reports'), 0755, true);
        }
        
        // Summary Sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');
        $this->writeArrearsSummary($sheet);
        
        // PAR Analysis Sheet
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('PAR Analysis');
        $this->writePARAnalysis($sheet);
        
        // Detailed List Sheet - Use chunking for large datasets
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Detailed List');
        $this->writeDetailedListOptimized($sheet);
        
        // Classification Sheets
        $classifications = ['WATCH', 'SUBSTANDARD', 'DOUBTFUL', 'LOSS'];
        foreach ($classifications as $index => $classification) {
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(3 + $index);
            $sheet->setTitle($classification);
            $this->writeClassificationSheet($sheet, $classification);
        }
        
        // Save with memory optimization
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        // Clear memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return $filePath;
    }
    
    /**
     * Write arrears summary with cached data
     */
    private function writeArrearsSummary($sheet)
    {
        $sheet->setCellValue('A1', 'LOAN ARREARS SUMMARY');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A2', 'Report Date: ' . $this->reportDate->format('Y-m-d'));
        $sheet->setCellValue('A3', 'Processing Statistics:');
        
        $row = 4;
        if (!empty($this->statistics)) {
            foreach ($this->statistics as $key => $value) {
                if (!in_array($key, ['start_time', 'end_time'])) {
                    $sheet->setCellValue('A' . $row, ucwords(str_replace('_', ' ', $key)) . ':');
                    $sheet->setCellValue('B' . $row, is_numeric($value) ? number_format($value, 0) : $value);
                    $row++;
                }
            }
        }
        
        // Get summary using optimized query
        $summary = DB::table('loans')
            ->selectRaw("
                COUNT(CASE WHEN loan_status = 'active' THEN 1 END) as active_loans,
                COUNT(CASE WHEN days_in_arrears > 0 THEN 1 END) as loans_in_arrears,
                SUM(CASE WHEN loan_status = 'active' THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as portfolio,
                SUM(CASE WHEN days_in_arrears > 0 THEN total_arrears ELSE 0 END) as total_arrears,
                COUNT(CASE WHEN loan_classification = 'PERFORMING' THEN 1 END) as performing,
                COUNT(CASE WHEN loan_classification = 'WATCH' THEN 1 END) as watch,
                COUNT(CASE WHEN loan_classification = 'SUBSTANDARD' THEN 1 END) as substandard,
                COUNT(CASE WHEN loan_classification = 'DOUBTFUL' THEN 1 END) as doubtful,
                COUNT(CASE WHEN loan_classification = 'LOSS' THEN 1 END) as loss
            ")
            ->first();
        
        $row += 2;
        $sheet->setCellValue('A' . $row, 'PORTFOLIO OVERVIEW');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Active Loans:');
        $sheet->setCellValue('B' . $row, $summary->active_loans);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Loans in Arrears:');
        $sheet->setCellValue('B' . $row, $summary->loans_in_arrears);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Portfolio Outstanding:');
        $sheet->setCellValue('B' . $row, number_format($summary->portfolio ?? 0, 2));
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Arrears:');
        $sheet->setCellValue('B' . $row, number_format($summary->total_arrears ?? 0, 2));
        
        $row++;
        $sheet->setCellValue('A' . $row, 'PAR %:');
        $par = $summary->portfolio > 0 ? ($summary->total_arrears / $summary->portfolio * 100) : 0;
        $sheet->setCellValue('B' . $row, number_format($par, 2) . '%');
        
        // Classification breakdown
        $row += 2;
        $sheet->setCellValue('A' . $row, 'CLASSIFICATION');
        $sheet->setCellValue('B' . $row, 'COUNT');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        
        $classifications = [
            'PERFORMING' => $summary->performing,
            'WATCH' => $summary->watch,
            'SUBSTANDARD' => $summary->substandard,
            'DOUBTFUL' => $summary->doubtful,
            'LOSS' => $summary->loss
        ];
        
        foreach ($classifications as $class => $count) {
            $row++;
            $sheet->setCellValue('A' . $row, $class);
            $sheet->setCellValue('B' . $row, $count);
        }
        
        // Auto-size columns
        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Write PAR analysis
     */
    private function writePARAnalysis($sheet)
    {
        $sheet->setCellValue('A1', 'PORTFOLIO AT RISK ANALYSIS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        // PAR bands with optimized query - use subquery to allow ordering by alias
        $parData = DB::table('loans')
            ->selectRaw("
                CASE
                    WHEN days_in_arrears = 0 THEN 'Current'
                    WHEN days_in_arrears BETWEEN 1 AND 30 THEN 'PAR 1-30'
                    WHEN days_in_arrears BETWEEN 31 AND 60 THEN 'PAR 31-60'
                    WHEN days_in_arrears BETWEEN 61 AND 90 THEN 'PAR 61-90'
                    WHEN days_in_arrears BETWEEN 91 AND 180 THEN 'PAR 91-180'
                    WHEN days_in_arrears > 180 THEN 'PAR >180'
                END as par_band,
                COUNT(*) as count,
                SUM(principle - COALESCE(total_principal_paid, 0)) as outstanding,
                SUM(total_arrears) as arrears,
                CASE
                    WHEN days_in_arrears = 0 THEN 0
                    WHEN days_in_arrears BETWEEN 1 AND 30 THEN 1
                    WHEN days_in_arrears BETWEEN 31 AND 60 THEN 2
                    WHEN days_in_arrears BETWEEN 61 AND 90 THEN 3
                    WHEN days_in_arrears BETWEEN 91 AND 180 THEN 4
                    WHEN days_in_arrears > 180 THEN 5
                END as sort_order
            ")
            ->where('loan_status', 'active')
            ->groupByRaw("
                CASE
                    WHEN days_in_arrears = 0 THEN 'Current'
                    WHEN days_in_arrears BETWEEN 1 AND 30 THEN 'PAR 1-30'
                    WHEN days_in_arrears BETWEEN 31 AND 60 THEN 'PAR 31-60'
                    WHEN days_in_arrears BETWEEN 61 AND 90 THEN 'PAR 61-90'
                    WHEN days_in_arrears BETWEEN 91 AND 180 THEN 'PAR 91-180'
                    WHEN days_in_arrears > 180 THEN 'PAR >180'
                END,
                CASE
                    WHEN days_in_arrears = 0 THEN 0
                    WHEN days_in_arrears BETWEEN 1 AND 30 THEN 1
                    WHEN days_in_arrears BETWEEN 31 AND 60 THEN 2
                    WHEN days_in_arrears BETWEEN 61 AND 90 THEN 3
                    WHEN days_in_arrears BETWEEN 91 AND 180 THEN 4
                    WHEN days_in_arrears > 180 THEN 5
                END
            ")
            ->orderBy('sort_order')
            ->get();
        
        $row = 3;
        $headers = ['PAR Band', 'Count', 'Outstanding', 'Arrears', '% of Portfolio'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        $totalOutstanding = $parData->sum('outstanding');
        
        foreach ($parData as $band) {
            $row++;
            $sheet->setCellValue('A' . $row, $band->par_band);
            $sheet->setCellValue('B' . $row, $band->count);
            $sheet->setCellValue('C' . $row, number_format($band->outstanding ?? 0, 2));
            $sheet->setCellValue('D' . $row, number_format($band->arrears ?? 0, 2));
            $percentage = $totalOutstanding > 0 ? ($band->outstanding / $totalOutstanding * 100) : 0;
            $sheet->setCellValue('E' . $row, number_format($percentage, 2) . '%');
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Write detailed list using chunking for large datasets
     */
    private function writeDetailedListOptimized($sheet)
    {
        $sheet->setCellValue('A1', 'LOANS IN ARREARS - DETAILED LIST');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $headers = ['Loan ID', 'Client No', 'Product', 'Disbursed', 'Outstanding', 
                   'Arrears', 'Days', 'Classification', 'Branch', 'Status'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        
        // Process in chunks to avoid memory issues
        DB::table('loans')
            ->where('days_in_arrears', '>', 0)
            ->where('loan_status', 'active')
            ->select([
                'loan_id',
                'client_number',
                'loan_sub_product',
                'principle',
                DB::raw('principle - COALESCE(total_principal_paid, 0) as outstanding'),
                'total_arrears',
                'days_in_arrears',
                'loan_classification',
                'branch_id',
                'loan_status'
            ])
            ->orderBy('days_in_arrears', 'desc')
            ->chunk(500, function ($loans) use ($sheet, &$row) {
                foreach ($loans as $loan) {
                    $row++;
                    if ($row > 10000) break; // Limit to 10,000 rows for Excel
                    
                    $sheet->setCellValue('A' . $row, $loan->loan_id);
                    $sheet->setCellValue('B' . $row, $loan->client_number);
                    $sheet->setCellValue('C' . $row, $loan->loan_sub_product ?? 'N/A');
                    $sheet->setCellValue('D' . $row, number_format($loan->principle ?? 0, 2));
                    $sheet->setCellValue('E' . $row, number_format($loan->outstanding ?? 0, 2));
                    $sheet->setCellValue('F' . $row, number_format($loan->total_arrears ?? 0, 2));
                    $sheet->setCellValue('G' . $row, $loan->days_in_arrears);
                    $sheet->setCellValue('H' . $row, $loan->loan_classification);
                    $sheet->setCellValue('I' . $row, $loan->branch_id ?? 'HEAD');
                    $sheet->setCellValue('J' . $row, $loan->loan_status);
                }
            });
        
        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Write classification sheet
     */
    private function writeClassificationSheet($sheet, $classification)
    {
        $sheet->setCellValue('A1', $classification . ' LOANS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $headers = ['Loan ID', 'Client', 'Product', 'Outstanding', 'Arrears', 'Days', 'Last Payment', 'Phone'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        
        // Get loans for this classification
        DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_id', '=', 'c.id')
            ->leftJoin(DB::raw('(SELECT loan_id, MAX(payment_date) as last_payment FROM loan_payments GROUP BY loan_id) as lp'), 
                      'l.loan_id', '=', 'lp.loan_id')
            ->where('l.loan_classification', $classification)
            ->where('l.loan_status', 'active')
            ->select([
                'l.loan_id',
                'l.client_number',
                'l.loan_sub_product',
                DB::raw('l.principle - COALESCE(l.total_principal_paid, 0) as outstanding'),
                'l.total_arrears',
                'l.days_in_arrears',
                'lp.last_payment',
                'c.mobile_phone_number'
            ])
            ->orderBy('l.days_in_arrears', 'desc')
            ->chunk(500, function ($loans) use ($sheet, &$row) {
                foreach ($loans as $loan) {
                    $row++;
                    if ($row > 5000) break; // Limit per sheet
                    
                    $sheet->setCellValue('A' . $row, $loan->loan_id);
                    $sheet->setCellValue('B' . $row, $loan->client_number);
                    $sheet->setCellValue('C' . $row, $loan->loan_sub_product ?? 'N/A');
                    $sheet->setCellValue('D' . $row, number_format($loan->outstanding ?? 0, 2));
                    $sheet->setCellValue('E' . $row, number_format($loan->total_arrears ?? 0, 2));
                    $sheet->setCellValue('F' . $row, $loan->days_in_arrears);
                    $sheet->setCellValue('G' . $row, $loan->last_payment ? Carbon::parse($loan->last_payment)->format('Y-m-d') : 'Never');
                    $sheet->setCellValue('H' . $row, $loan->mobile_phone_number ?? 'N/A');
                }
            });
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Generate optimized summary report
     */
    private function generateOptimizedSummaryReport()
    {
        $spreadsheet = new Spreadsheet();
        $fileName = 'loan_summary_' . $this->reportDate->format('Y_m_d') . '.xlsx';
        $filePath = storage_path('app/reports/' . $fileName);
        
        // Portfolio Summary
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Portfolio');
        $this->writePortfolioSummary($sheet);
        
        // Product Performance
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Products');
        $this->writeProductPerformance($sheet);
        
        // Collections Summary
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Collections');
        $this->writeCollectionsSummary($sheet);
        
        // Save
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        // Clear memory
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        
        return $filePath;
    }
    
    /**
     * Write portfolio summary
     */
    private function writePortfolioSummary($sheet)
    {
        $sheet->setCellValue('A1', 'LOAN PORTFOLIO SUMMARY');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A2', 'Report Date: ' . $this->reportDate->format('Y-m-d'));
        
        // Get metrics using optimized query
        $metrics = DB::table('loans')
            ->selectRaw("
                COUNT(*) as total_loans,
                COUNT(CASE WHEN loan_status = 'active' THEN 1 END) as active_loans,
                COUNT(CASE WHEN loan_status = 'closed' THEN 1 END) as closed_loans,
                SUM(principle) as total_disbursed,
                SUM(CASE WHEN loan_status = 'active' THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as outstanding,
                SUM(total_interest_paid) as interest_collected,
                SUM(total_principal_paid) as principal_collected,
                AVG(CASE WHEN loan_status = 'active' THEN interest_rate END) as avg_rate,
                COUNT(CASE WHEN days_in_arrears > 0 THEN 1 END) as loans_in_arrears,
                SUM(CASE WHEN days_in_arrears > 0 THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as par
            ")
            ->first();
        
        $row = 4;
        $sheet->setCellValue('A' . $row, 'Metric');
        $sheet->setCellValue('B' . $row, 'Value');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        
        $displayMetrics = [
            'Total Loans' => number_format($metrics->total_loans ?? 0),
            'Active Loans' => number_format($metrics->active_loans ?? 0),
            'Closed Loans' => number_format($metrics->closed_loans ?? 0),
            'Total Disbursed' => 'TZS ' . number_format($metrics->total_disbursed ?? 0, 2),
            'Outstanding Portfolio' => 'TZS ' . number_format($metrics->outstanding ?? 0, 2),
            'Interest Collected' => 'TZS ' . number_format($metrics->interest_collected ?? 0, 2),
            'Principal Collected' => 'TZS ' . number_format($metrics->principal_collected ?? 0, 2),
            'Average Interest Rate' => number_format($metrics->avg_rate ?? 0, 2) . '%',
            'Loans in Arrears' => number_format($metrics->loans_in_arrears ?? 0),
            'Portfolio at Risk' => 'TZS ' . number_format($metrics->par ?? 0, 2),
            'PAR Ratio' => $metrics->outstanding > 0 ? number_format(($metrics->par / $metrics->outstanding * 100), 2) . '%' : '0%'
        ];
        
        foreach ($displayMetrics as $label => $value) {
            $row++;
            $sheet->setCellValue('A' . $row, $label);
            $sheet->setCellValue('B' . $row, $value);
        }
        
        // Auto-size columns
        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Write product performance
     */
    private function writeProductPerformance($sheet)
    {
        $sheet->setCellValue('A1', 'PRODUCT PERFORMANCE');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $row = 3;
        $headers = ['Product', 'Count', 'Active', 'Disbursed', 'Outstanding', 'Collections', 'PAR', 'PAR %'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        
        $products = DB::table('loans')
            ->selectRaw("
                loan_sub_product,
                COUNT(*) as total,
                COUNT(CASE WHEN loan_status = 'active' THEN 1 END) as active,
                SUM(principle) as disbursed,
                SUM(CASE WHEN loan_status = 'active' THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as outstanding,
                SUM(total_interest_paid + total_principal_paid) as collections,
                SUM(CASE WHEN days_in_arrears > 0 THEN principle - COALESCE(total_principal_paid, 0) ELSE 0 END) as par
            ")
            ->groupBy('loan_sub_product')
            ->get();
        
        foreach ($products as $product) {
            $row++;
            $sheet->setCellValue('A' . $row, $product->loan_sub_product ?? 'Unknown');
            $sheet->setCellValue('B' . $row, $product->total);
            $sheet->setCellValue('C' . $row, $product->active);
            $sheet->setCellValue('D' . $row, number_format($product->disbursed ?? 0, 2));
            $sheet->setCellValue('E' . $row, number_format($product->outstanding ?? 0, 2));
            $sheet->setCellValue('F' . $row, number_format($product->collections ?? 0, 2));
            $sheet->setCellValue('G' . $row, number_format($product->par ?? 0, 2));
            $parRatio = $product->outstanding > 0 ? ($product->par / $product->outstanding * 100) : 0;
            $sheet->setCellValue('H' . $row, number_format($parRatio, 2) . '%');
        }
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Write collections summary
     */
    private function writeCollectionsSummary($sheet)
    {
        $sheet->setCellValue('A1', 'COLLECTIONS SUMMARY');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A2', 'Collections for: ' . $this->reportDate->format('Y-m-d'));
        
        $row = 4;
        $headers = ['Payment Method', 'Count', 'Principal', 'Interest', 'Total'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $header);
        }
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        $collections = DB::table('loan_payments')
            ->whereDate('payment_date', $this->reportDate->toDateString())
            ->where('status', 'completed')
            ->selectRaw("
                payment_method,
                COUNT(*) as count,
                SUM(principal_amount) as principal,
                SUM(interest_amount) as interest,
                SUM(amount) as total
            ")
            ->groupBy('payment_method')
            ->get();
        
        $totalCount = 0;
        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalAmount = 0;
        
        foreach ($collections as $collection) {
            $row++;
            $sheet->setCellValue('A' . $row, $collection->payment_method);
            $sheet->setCellValue('B' . $row, $collection->count);
            $sheet->setCellValue('C' . $row, number_format($collection->principal ?? 0, 2));
            $sheet->setCellValue('D' . $row, number_format($collection->interest ?? 0, 2));
            $sheet->setCellValue('E' . $row, number_format($collection->total ?? 0, 2));
            
            $totalCount += $collection->count;
            $totalPrincipal += $collection->principal;
            $totalInterest += $collection->interest;
            $totalAmount += $collection->total;
        }
        
        // Add totals row
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, $totalCount);
        $sheet->setCellValue('C' . $row, number_format($totalPrincipal, 2));
        $sheet->setCellValue('D' . $row, number_format($totalInterest, 2));
        $sheet->setCellValue('E' . $row, number_format($totalAmount, 2));
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    /**
     * Send reports in batches to avoid memory issues
     */
    private function sendReportsInBatches($arrearsPath, $summaryPath)
    {
        // Process users in chunks
        DB::table('users')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->chunk(50, function ($users) use ($arrearsPath, $summaryPath) {
                foreach ($users as $user) {
                    try {
                        Mail::send([], [], function ($message) use ($user, $arrearsPath, $summaryPath) {
                            $htmlContent = "
                            <html>
                            <body>
                                <h2>Daily Loan Reports - " . $this->reportDate->format('F d, Y') . "</h2>
                                <p>Dear {$user->name},</p>
                                <p>Please find attached the daily loan reports.</p>
                                <p>Report Date: " . $this->reportDate->format('Y-m-d') . "</p>
                                <p>Best regards,<br>SACCOS System</p>
                            </body>
                            </html>";
                            
                            $message->to($user->email)
                                    ->subject('Daily Loan Reports - ' . $this->reportDate->format('Y-m-d'))
                                    ->html($htmlContent)
                                    ->attach($arrearsPath)
                                    ->attach($summaryPath);
                        });
                        
                        Log::info("Report sent to {$user->email}");
                        
                    } catch (\Exception $e) {
                        Log::error("Failed to send report to {$user->email}: " . $e->getMessage());
                    }
                }
            });
    }
}