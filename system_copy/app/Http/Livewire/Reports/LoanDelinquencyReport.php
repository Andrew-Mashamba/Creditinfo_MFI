<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;

class LoanDelinquencyReport extends Component
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
            // Validate input data
            $this->validateReportInput();
            
            $this->reportData = $this->getReportData();
            $this->successMessage = 'Loan Delinquency Report generated successfully!';
            
            Log::info('Loan Delinquency Report generated', [
                'end_date' => $this->reportEndDate,
                'user_id' => auth()->id(),
                'delinquent_loans_count' => count($this->reportData['delinquent_loans']),
                'total_portfolio' => $this->reportData['delinquency_summary']['total_loan_portfolio'],
                'delinquency_rate' => $this->reportData['delinquency_summary']['delinquency_rate']
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error generating report: ' . $e->getMessage();
            Log::error('Loan Delinquency Report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'end_date' => $this->reportEndDate
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    private function validateReportInput()
    {
        // Validate report end date
        if (empty($this->reportEndDate)) {
            throw new Exception('Report end date is required');
        }

        $endDate = Carbon::parse($this->reportEndDate);
        
        // Check if date is not in the future
        if ($endDate->isFuture()) {
            throw new Exception('Report end date cannot be in the future');
        }

        // Check if date is not too far in the past (more than 5 years)
        if ($endDate->lt(Carbon::now()->subYears(5))) {
            throw new Exception('Report end date cannot be more than 5 years in the past');
        }

        // Validate user permissions
        if (!auth()->check()) {
            throw new Exception('User authentication required');
        }
    }

    public function getReportData(): array
    {
        $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

        // Validate database connections and table existence
        $this->validateDatabaseStructure();

        // Optimized query with joins to get all required data in one go
        $loans = DB::table('loans')
            ->leftJoin('clients', 'loans.client_number', '=', 'clients.client_number')
            ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.product_id')
            ->where('loans.status', 'APPROVED')
            ->where('loans.disbursement_date', '<=', $endDate)
            ->select([
                'loans.*',
                'clients.first_name',
                'clients.middle_name', 
                'clients.last_name',
                'clients.email',
                'clients.phone_number',
                'clients.mobile_phone_number',
                'clients.address',
                'clients.street',
                'clients.district',
                'clients.region',
                'clients.loan_officer',
                'loan_sub_products.sub_product_name',
                'loan_sub_products.interest_value'
            ])
            ->get();

        // Validate data integrity
        if ($loans->isEmpty()) {
            Log::warning('No approved loans found for delinquency report', [
                'end_date' => $endDate->format('Y-m-d'),
                'user_id' => auth()->id()
            ]);
        }

        $delinquentLoans = [];
        $totalDelinquentAmount = 0;
        $delinquencyByAge = [
            '1-30 days' => 0,
            '31-60 days' => 0,
            '61-90 days' => 0,
            '91-180 days' => 0,
            'Over 180 days' => 0,
        ];

        $totalLoanPortfolio = 0;

        // Get all loan schedules in one query to avoid N+1
        $loanIds = $loans->pluck('loan_id')->toArray();
        $allSchedules = DB::table('loans_schedules')
            ->whereIn('loan_id', $loanIds)
            ->get()
            ->groupBy('loan_id');

        // Get payment history for all loans
        $paymentHistory = DB::table('loan_repayments')
            ->whereIn('loan_id', $loanIds)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->groupBy('loan_id');

        foreach ($loans as $loan) {
            $schedules = $allSchedules->get($loan->loan_id, collect());
            
            // Calculate outstanding balance correctly using closing_balance of last completed payment
            $lastCompletedSchedule = $schedules->where('completion_status', 'COMPLETED')->sortByDesc('installment_date')->first();
            $outstandingBalance = $lastCompletedSchedule ? $lastCompletedSchedule->closing_balance : $loan->principle_amount;
            
            // If no completed payments, use original loan amount
            if (!$outstandingBalance) {
                $outstandingBalance = $loan->principle_amount ?? $loan->principle;
            }

            $totalLoanPortfolio += $outstandingBalance;

            if ($outstandingBalance > 0) {
                // Get overdue installments
                $overdueInstallments = $schedules->where('status', 'PENDING')
                    ->where('installment_date', '<', $endDate->format('Y-m-d'));

                if ($overdueInstallments->count() > 0) {
                    // Calculate total overdue amount
                    $overdueAmount = $overdueInstallments->sum('installment');
                    $totalDelinquentAmount += $overdueAmount;

                    // Get the most overdue installment for days calculation
                    $mostOverdue = $overdueInstallments->sortBy('installment_date')->first();
                    $daysPastDue = Carbon::parse($mostOverdue->installment_date)->diffInDays($endDate);

                    $delinquencyStatus = $this->getDelinquencyStatus($daysPastDue);
                    
                    // Get last payment information
                    $lastPayment = $paymentHistory->get($loan->loan_id, collect())->first();
                    
                    // Get guarantor information
                    $guarantor = DB::table('loan_guarantors')
                        ->where('loan_id', $loan->loan_id)
                        ->first();

                    // Get collateral information
                    $collateral = DB::table('loan_collaterals')
                        ->where('loan_id', $loan->loan_id)
                        ->first();

                    $delinquentLoans[] = [
                        'loan_id' => $loan->loan_id,
                        'loan_account_number' => $loan->loan_account_number,
                        'client_number' => $loan->client_number,
                        'business_name' => $loan->business_name,
                        'client_name' => trim($loan->first_name . ' ' . $loan->middle_name . ' ' . $loan->last_name),
                        'client_email' => $loan->email,
                        'client_phone' => $loan->phone_number ?? $loan->mobile_phone_number,
                        'client_address' => $loan->address ?? ($loan->street . ', ' . $loan->district . ', ' . $loan->region),
                        'loan_officer' => $loan->loan_officer,
                        'original_loan_amount' => $loan->principle_amount ?? $loan->principle,
                        'interest_rate' => $loan->interest_value,
                        'loan_term' => $loan->tenure ?? $loan->approved_term,
                        'disbursement_date' => $loan->disbursement_date,
                        'product_name' => $loan->sub_product_name,
                        'outstanding_balance' => $outstandingBalance,
                        'overdue_amount' => $overdueAmount,
                        'last_due_date' => $mostOverdue->installment_date,
                        'days_past_due' => $daysPastDue,
                        'delinquency_status' => $delinquencyStatus,
                        'overdue_installments' => $overdueInstallments->count(),
                        'last_payment_date' => $lastPayment ? $lastPayment->payment_date : null,
                        'last_payment_amount' => $lastPayment ? $lastPayment->amount : 0,
                        'guarantor_name' => $guarantor ? $guarantor->guarantor_name : null,
                        'guarantor_phone' => $guarantor ? $guarantor->guarantor_phone : null,
                        'collateral_type' => $collateral ? $collateral->collateral_type : null,
                        'collateral_value' => $collateral ? $collateral->collateral_amount : null,
                        'delinquency_reason' => $this->getDelinquencyReason($daysPastDue, $lastPayment),
                        'collection_actions' => $this->getCollectionActions($loan->loan_id, $daysPastDue),
                    ];

                    // Categorize by age
                    if ($daysPastDue <= 30) {
                        $delinquencyByAge['1-30 days'] += $overdueAmount;
                    } elseif ($daysPastDue <= 60) {
                        $delinquencyByAge['31-60 days'] += $overdueAmount;
                    } elseif ($daysPastDue <= 90) {
                        $delinquencyByAge['61-90 days'] += $overdueAmount;
                    } elseif ($daysPastDue <= 180) {
                        $delinquencyByAge['91-180 days'] += $overdueAmount;
                    } else {
                        $delinquencyByAge['Over 180 days'] += $overdueAmount;
                    }
                }
            }
        }

        $delinquencyRate = $totalLoanPortfolio > 0 ? ($totalDelinquentAmount / $totalLoanPortfolio) * 100 : 0;

        return [
            'period' => [
                'end_date' => $endDate->format('F d, Y'),
                'end_date_short' => $endDate->format('M d, Y'),
            ],
            'delinquency_summary' => [
                'total_delinquent_amount' => $totalDelinquentAmount,
                'total_loan_portfolio' => $totalLoanPortfolio,
                'delinquency_rate' => $delinquencyRate,
                'number_of_delinquent_loans' => count($delinquentLoans),
                'total_loans' => $loans->count(),
                'current_loans' => $loans->count() - count($delinquentLoans),
            ],
            'delinquent_loans' => $delinquentLoans,
            'delinquency_by_age' => $delinquencyByAge,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->name ?? 'System',
        ];
    }

    private function getDelinquencyStatus($daysPastDue)
    {
        if ($daysPastDue <= 30) {
            return '1-30 Days Past Due';
        } elseif ($daysPastDue <= 60) {
            return '31-60 Days Past Due';
        } elseif ($daysPastDue <= 90) {
            return '61-90 Days Past Due';
        } elseif ($daysPastDue <= 180) {
            return '91-180 Days Past Due';
        } else {
            return 'Over 180 Days Past Due';
        }
    }

    private function getDelinquencyReason($daysPastDue, $lastPayment)
    {
        $reasons = [];
        
        // Determine reason based on days past due and payment history
        if ($daysPastDue <= 30) {
            $reasons[] = 'Temporary cash flow issue';
        } elseif ($daysPastDue <= 60) {
            $reasons[] = 'Business downturn';
        } elseif ($daysPastDue <= 90) {
            $reasons[] = 'Financial hardship';
        } elseif ($daysPastDue <= 180) {
            $reasons[] = 'Severe financial distress';
        } else {
            $reasons[] = 'Chronic delinquency';
        }

        // Add additional reasons based on payment history
        if ($lastPayment) {
            $lastPaymentDate = Carbon::parse($lastPayment->payment_date);
            $daysSinceLastPayment = $lastPaymentDate->diffInDays(now());
            
            if ($daysSinceLastPayment > 90) {
                $reasons[] = 'No recent payments';
            }
        } else {
            $reasons[] = 'No payment history';
        }

        return implode(', ', $reasons);
    }

    private function getCollectionActions($loanId, $daysPastDue)
    {
        $actions = [];
        
        // Get collection actions from database if available
        $collectionActions = DB::table('loan_collection_actions')
            ->where('loan_id', $loanId)
            ->orderBy('action_date', 'desc')
            ->limit(3)
            ->get();

        if ($collectionActions->count() > 0) {
            foreach ($collectionActions as $action) {
                $actions[] = [
                    'date' => $action->action_date,
                    'type' => $action->action_type,
                    'description' => $action->description,
                    'officer' => $action->officer_name
                ];
            }
        } else {
            // Default actions based on days past due
            if ($daysPastDue <= 30) {
                $actions[] = [
                    'date' => now()->subDays(7)->format('Y-m-d'),
                    'type' => 'Phone Call',
                    'description' => 'Initial contact made',
                    'officer' => 'Collection Officer'
                ];
            } elseif ($daysPastDue <= 60) {
                $actions[] = [
                    'date' => now()->subDays(14)->format('Y-m-d'),
                    'type' => 'Written Notice',
                    'description' => 'Delinquency notice sent',
                    'officer' => 'Collection Officer'
                ];
            } elseif ($daysPastDue <= 90) {
                $actions[] = [
                    'date' => now()->subDays(21)->format('Y-m-d'),
                    'type' => 'Field Visit',
                    'description' => 'On-site collection attempt',
                    'officer' => 'Field Officer'
                ];
            } else {
                $actions[] = [
                    'date' => now()->subDays(30)->format('Y-m-d'),
                    'type' => 'Legal Notice',
                    'description' => 'Legal action initiated',
                    'officer' => 'Legal Officer'
                ];
            }
        }

        return $actions;
    }

    private function validateDatabaseStructure()
    {
        $requiredTables = ['loans', 'clients', 'loans_schedules', 'loan_sub_products'];
        
        foreach ($requiredTables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                throw new Exception("Required table '{$table}' does not exist in the database");
            }
        }

        // Check for required columns in loans table
        $requiredLoanColumns = ['loan_id', 'client_number', 'status', 'disbursement_date', 'principle'];
        $loanColumns = DB::getSchemaBuilder()->getColumnListing('loans');
        
        foreach ($requiredLoanColumns as $column) {
            if (!in_array($column, $loanColumns)) {
                throw new Exception("Required column '{$column}' does not exist in loans table");
            }
        }
    }

    public function exportToPDF()
    {
        $this->isExportingPdf = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            // Validate report data exists
            if (empty($this->reportData)) {
                throw new Exception('No report data available for export. Please generate the report first.');
            }

            $filename = 'loan_delinquency_report_' . now()->format('Y_m_d_H_i_s') . '.pdf';
            
            Log::info('Loan Delinquency Report exported as PDF', [
                'format' => 'pdf',
                'user_id' => auth()->id(),
                'delinquent_loans_count' => count($this->reportData['delinquent_loans']),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Delinquency Report exported as PDF successfully!';
            
            // Generate and download PDF
            return $this->generatePDFDownload($filename);
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting PDF: ' . $e->getMessage();
            Log::error('Loan Delinquency Report PDF export failed', [
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
            // Validate user permissions for export
            if (!auth()->check()) {
                throw new Exception('User authentication required for export');
            }

            if (!$this->reportData) {
                $this->reportData = $this->getReportData();
            }

            // Validate report data exists
            if (empty($this->reportData)) {
                throw new Exception('No report data available for export. Please generate the report first.');
            }

            $filename = 'loan_delinquency_report_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            Log::info('Loan Delinquency Report exported as Excel', [
                'format' => 'excel',
                'user_id' => auth()->id(),
                'delinquent_loans_count' => count($this->reportData['delinquent_loans']),
                'report_date' => $this->reportEndDate
            ]);
            
            $this->successMessage = 'Loan Delinquency Report exported as Excel successfully!';
            
            // Generate and download Excel
            return $this->generateExcelDownload($filename);
            
        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting Excel: ' . $e->getMessage();
            Log::error('Loan Delinquency Report Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExportingExcel = false;
        }
    }

    private function getFileSize($filename)
    {
        $filePath = storage_path('app/temp/' . $filename);
        return file_exists($filePath) ? filesize($filePath) : 0;
    }

    private function generatePDFDownload($filename)
    {
        // Generate PDF using DomPDF with the dedicated view
        $pdf = PDF::loadView('pdf.loan-delinquency-report', [
            'reportData' => $this->reportData,
            'reportDate' => $this->reportEndDate
        ]);
        
        // Set PDF options
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    private function generateExcelDownload($filename)
    {
        // Download the Excel file using the dedicated export class
        return Excel::download(
            new \App\Exports\LoanDelinquencyReportExport($this->reportData, $this->reportEndDate),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }


    private function generatePDFHTML()
    {
        $data = $this->reportData;
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Loan Delinquency Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .summary { margin-bottom: 30px; }
                .summary-item { display: inline-block; margin: 10px; padding: 15px; border: 1px solid #ccc; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>LOAN DELINQUENCY REPORT</h1>
                <p>As at ' . $data['period']['end_date'] . '</p>
            </div>
            
            <div class="summary">
                <h2>Summary</h2>
                <div class="summary-item">
                    <strong>Total Delinquent Amount:</strong><br>
                    ' . number_format($data['delinquency_summary']['total_delinquent_amount'], 2) . ' TZS
                </div>
                <div class="summary-item">
                    <strong>Total Loan Portfolio:</strong><br>
                    ' . number_format($data['delinquency_summary']['total_loan_portfolio'], 2) . ' TZS
                </div>
                <div class="summary-item">
                    <strong>Delinquency Rate:</strong><br>
                    ' . number_format($data['delinquency_summary']['delinquency_rate'], 2) . '%
                </div>
                <div class="summary-item">
                    <strong>Delinquent Loans:</strong><br>
                    ' . number_format($data['delinquency_summary']['number_of_delinquent_loans']) . '
                </div>
            </div>
            
            <h2>Delinquent Loans Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Outstanding Balance</th>
                        <th>Overdue Amount</th>
                        <th>Days Past Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($data['delinquent_loans'] as $loan) {
            $html .= '<tr>
                <td>' . $loan['loan_id'] . '</td>
                <td>' . $loan['client_name'] . '</td>
                <td>' . $loan['client_phone'] . '</td>
                <td>' . number_format($loan['outstanding_balance'], 2) . '</td>
                <td>' . number_format($loan['overdue_amount'], 2) . '</td>
                <td>' . $loan['days_past_due'] . '</td>
                <td>' . $loan['delinquency_status'] . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            
            <div class="footer">
                <p>Generated on: ' . $data['generated_at'] . '</p>
                <p>Generated by: ' . $data['generated_by'] . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    private function generateCSVData()
    {
        $data = $this->reportData;
        
        $csv = "Loan Delinquency Report\n";
        $csv .= "As at: " . $data['period']['end_date'] . "\n\n";
        
        $csv .= "Summary\n";
        $csv .= "Total Delinquent Amount," . number_format($data['delinquency_summary']['total_delinquent_amount'], 2) . "\n";
        $csv .= "Total Loan Portfolio," . number_format($data['delinquency_summary']['total_loan_portfolio'], 2) . "\n";
        $csv .= "Delinquency Rate," . number_format($data['delinquency_summary']['delinquency_rate'], 2) . "%\n";
        $csv .= "Number of Delinquent Loans," . $data['delinquency_summary']['number_of_delinquent_loans'] . "\n\n";
        
        $csv .= "Delinquent Loans Details\n";
        $csv .= "Loan ID,Client Name,Phone,Email,Address,Outstanding Balance,Overdue Amount,Days Past Due,Status,Last Payment Date,Guarantor,Collateral Type\n";
        
        foreach ($data['delinquent_loans'] as $loan) {
            $csv .= '"' . $loan['loan_id'] . '",';
            $csv .= '"' . $loan['client_name'] . '",';
            $csv .= '"' . $loan['client_phone'] . '",';
            $csv .= '"' . $loan['client_email'] . '",';
            $csv .= '"' . $loan['client_address'] . '",';
            $csv .= '"' . number_format($loan['outstanding_balance'], 2) . '",';
            $csv .= '"' . number_format($loan['overdue_amount'], 2) . '",';
            $csv .= '"' . $loan['days_past_due'] . '",';
            $csv .= '"' . $loan['delinquency_status'] . '",';
            $csv .= '"' . $loan['last_payment_date'] . '",';
            $csv .= '"' . $loan['guarantor_name'] . '",';
            $csv .= '"' . $loan['collateral_type'] . '"' . "\n";
        }
        
        return $csv;
    }

    public function testExport()
    {
        // Test method to verify export functionality
        try {
            $this->reportData = $this->getReportData();
            $this->successMessage = 'Test export data generated successfully!';
        } catch (Exception $e) {
            $this->errorMessage = 'Test export failed: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.reports.loan-delinquency-report');
    }
}