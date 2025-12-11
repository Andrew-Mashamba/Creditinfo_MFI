<?php

namespace App\Http\Livewire\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class StatementOfComprehensiveIncome extends Component
{
    // Report Properties
    public $reportStartDate;
    public $reportEndDate;
    public $reportFormat = 'pdf';
    
    // Report Viewing Properties
    public $showStatementView = false;
    public $statementData = null;
    
    // Loading States
    public $isGenerating = false;
    public $isExporting = false;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'reportStartDate' => 'required|date|before_or_equal:reportEndDate',
        'reportEndDate' => 'required|date|after_or_equal:reportStartDate|before_or_equal:today',
    ];

    protected $messages = [
        'reportStartDate.required' => 'Start date is required.',
        'reportStartDate.date' => 'Start date must be a valid date.',
        'reportStartDate.before_or_equal' => 'Start date must be before or equal to end date.',
        'reportEndDate.required' => 'End date is required.',
        'reportEndDate.date' => 'End date must be a valid date.',
        'reportEndDate.after_or_equal' => 'End date must be after or equal to start date.',
        'reportEndDate.before_or_equal' => 'End date cannot be in the future.',
    ];

    public function mount()
    {
        $this->initializeDates();
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        
        // Clear any existing statement view when dates change
        if (in_array($propertyName, ['reportStartDate', 'reportEndDate'])) {
            $this->showStatementView = false;
            $this->statementData = null;
            $this->successMessage = '';
            $this->errorMessage = '';
        }
    }

    private function initializeDates()
    {
        $this->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = Carbon::now()->format('Y-m-d');
    }

    public function generateStatement()
    {
        try {
            $this->isGenerating = true;
            $this->errorMessage = '';
            $this->successMessage = '';
            
            // Validate the form data
            $this->validate();
            
            // Check if we need to add sample data for demonstration
            // $this->addSampleIncomeDataIfNeeded();
            
            // Get the statement data
            $statementData = $this->getStatementData();
            
            // Store the data for display
            $this->statementData = $statementData;
            $this->showStatementView = true;
            
            $this->successMessage = 'Statement of Comprehensive Income generated successfully!';
            
            // Log the report generation
            Log::info('Statement of Comprehensive Income generated', [
                'user_id' => auth()->id(),
                'period_start' => $this->reportStartDate,
                'period_end' => $this->reportEndDate
            ]);
            
        } catch (Exception $e) {
            Log::error('Error generating Statement of Comprehensive Income: ' . $e->getMessage());
            $this->errorMessage = 'Failed to generate Statement of Comprehensive Income. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }


    public function getStatementData()
    {
        $startDate = $this->reportStartDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->reportEndDate ?: Carbon::now()->format('Y-m-d');
        
        // Get all active income and expense accounts with their balances filtered by date range
        $accounts = DB::table('accounts')
            ->select(
                'accounts.account_number',
                'accounts.account_name',
                'accounts.type',
                'accounts.major_category_code',
                'accounts.category_code',
                'accounts.sub_category_code',
                'accounts.account_level',
                DB::raw("COALESCE(SUM(CASE WHEN gl.created_at BETWEEN '{$startDate}' AND '{$endDate}' THEN gl.debit ELSE 0 END), 0) as debit_balance"),
                DB::raw("COALESCE(SUM(CASE WHEN gl.created_at BETWEEN '{$startDate}' AND '{$endDate}' THEN gl.credit ELSE 0 END), 0) as credit_balance"),
                DB::raw("COALESCE(SUM(CASE WHEN gl.created_at BETWEEN '{$startDate}' AND '{$endDate}' THEN (gl.debit - gl.credit) ELSE 0 END), 0) as current_balance")
            )
            ->leftJoin('general_ledger as gl', 'accounts.account_number', '=', 'gl.record_on_account_number')
            ->where('accounts.status', 'ACTIVE')
            ->whereIn('accounts.type', ['income_accounts', 'expense_accounts'])
            ->whereNull('accounts.deleted_at')
            ->groupBy('accounts.account_number', 'accounts.account_name', 'accounts.type', 'accounts.major_category_code', 'accounts.category_code', 'accounts.sub_category_code', 'accounts.account_level')
            ->orderBy('accounts.type')
            ->orderBy('accounts.major_category_code')
            ->orderBy('accounts.category_code')
            ->orderBy('accounts.sub_category_code')
            ->orderBy('accounts.account_number')
            ->get();

        // Group accounts by type and calculate totals
        $statementData = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'income' => $this->groupAccountsByType($accounts, ['income_accounts']),
            'expenses' => $this->groupAccountsByType($accounts, ['expense_accounts']),
            'totals' => []
        ];

        // Calculate totals
        $totalIncome = $statementData['income']['total'];
        $totalExpenses = $statementData['expenses']['total'];
        $netIncome = $totalIncome - $totalExpenses;

        $statementData['totals'] = [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'is_profitable' => $netIncome > 0
        ];

        return $statementData;
    }

    private function groupAccountsByType($accounts, $types)
    {
        $grouped = [
            'categories' => [],
            'total' => 0
        ];

        foreach ($accounts as $account) {
            // Ensure account is an object for consistent access
            $accountObj = is_array($account) ? (object) $account : $account;
            
            if (in_array($accountObj->type, $types)) {
                // Use the combination of major_category_code and category_code for grouping
                $categoryCode = $accountObj->major_category_code . '-' . $accountObj->category_code;
                $categoryName = $this->getCategoryName($accountObj->type, $categoryCode);
                
                // If no specific category name found, use the major category
                if ($categoryName === "Category {$categoryCode}") {
                    $categoryCode = $accountObj->major_category_code;
                    $categoryName = $this->getCategoryName($accountObj->type, $categoryCode);
                }
                
                if (!isset($grouped['categories'][$categoryCode])) {
                    $grouped['categories'][$categoryCode] = [
                        'name' => $categoryName,
                        'accounts' => [],
                        'subtotal' => 0
                    ];
                }

                $grouped['categories'][$categoryCode]['accounts'][] = $accountObj;
                $grouped['categories'][$categoryCode]['subtotal'] += $accountObj->current_balance;
                $grouped['total'] += $accountObj->current_balance;
            }
        }

        return $grouped;
    }

    private function getCategoryName($type, $categoryCode)
    {
        $categoryNames = [
            'income_accounts' => [
                '4000' => 'Revenue',
                '4000-4000' => 'Interest Income',
                '4000-4100' => 'Loan Fees and Charges',
                '4000-4200' => 'Service Fees',
                '4000-4300' => 'Investment Income',
                '4000-4400' => 'Grants and Donations',
                '4000-4500' => 'Other Income',
                '4000-4600' => 'Investment Gains',
                '4000-4700' => 'Gains on Disposal',
                '4000-4800' => 'Exchange Gains'
            ],
            'expense_accounts' => [
                '5000' => 'Expenses',
                '5000-5000' => 'Financial Expenses',
                '5000-5100' => 'Personnel Expenses',
                '5000-5200' => 'Administrative Expenses',
                '5000-5300' => 'Operational Expenses',
                '5000-5600' => 'Office Expenses',
                '5000-5700' => 'Facility Expenses',
                '5000-5800' => 'Travel Expenses',
                '5000-5900' => 'Information Technology',
                '5000-6000' => 'Training and Development'
            ]
        ];

        return $categoryNames[$type][$categoryCode] ?? "Category {$categoryCode}";
    }

    private function addSampleIncomeDataIfNeeded()
    {
        // Check if we need to add sample income/expense data
        $incomeAccounts = DB::table('accounts')->where('type', 'income_accounts')->where('status', 'ACTIVE')->get();
        $expenseAccounts = DB::table('accounts')->where('type', 'expense_accounts')->where('status', 'ACTIVE')->get();
        
        $hasIncomeData = $incomeAccounts->where('balance', '>', 0)->count() > 0;
        $hasExpenseData = $expenseAccounts->where('balance', '>', 0)->count() > 0;
        
        if (!$hasIncomeData || !$hasExpenseData) {
            // Add sample income and expense transactions
            $sampleTransactions = [
                // Interest Income
                [
                    'record_on_account_number' => '010140004000', // INTEREST INCOME
                    'debit' => 0.00,
                    'credit' => 150000.00,
                    'record_on_account_number_balance' => 150000.00,
                    'narration' => 'Interest income from loans',
                    'reference_number' => 'INT001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 150000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 850000.00,
                    'narration' => 'Interest received in cash',
                    'reference_number' => 'INT001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Loan Fees
                [
                    'record_on_account_number' => '010140004100', // LOAN FEES AND CHARGES
                    'debit' => 0.00,
                    'credit' => 50000.00,
                    'record_on_account_number_balance' => 50000.00,
                    'narration' => 'Loan processing fees',
                    'reference_number' => 'FEE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 50000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 900000.00,
                    'narration' => 'Fees received in cash',
                    'reference_number' => 'FEE001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Personnel Expenses
                [
                    'record_on_account_number' => '010150005100', // PERSONNEL EXPENSES
                    'debit' => 80000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 80000.00,
                    'narration' => 'Staff salaries and benefits',
                    'reference_number' => 'SAL001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 0.00,
                    'credit' => 80000.00,
                    'record_on_account_number_balance' => 820000.00,
                    'narration' => 'Salary payments',
                    'reference_number' => 'SAL001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                // Administrative Expenses
                [
                    'record_on_account_number' => '010150005200', // ADMINISTRATIVE EXPENSES
                    'debit' => 30000.00,
                    'credit' => 0.00,
                    'record_on_account_number_balance' => 30000.00,
                    'narration' => 'Office supplies and utilities',
                    'reference_number' => 'ADM001',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'record_on_account_number' => '010110001000', // CASH AND CASH EQUIVALENTS
                    'debit' => 0.00,
                    'credit' => 30000.00,
                    'record_on_account_number_balance' => 790000.00,
                    'narration' => 'Administrative expenses paid',
                    'reference_number' => 'ADM001',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            // Insert sample transactions
            DB::table('general_ledger')->insert($sampleTransactions);
            
            // Update account balances
            $this->updateAccountBalances();
        }
    }

    private function updateAccountBalances()
    {
        // Update account balances based on general ledger entries
        $accounts = DB::table('accounts')->where('status', 'ACTIVE')->get();
        
        foreach ($accounts as $account) {
            $debitTotal = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->sum('debit');
                
            $creditTotal = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->sum('credit');
                
            $balance = $debitTotal - $creditTotal;
            
            DB::table('accounts')
                ->where('account_number', $account->account_number)
                ->update([
                    'debit' => $debitTotal,
                    'credit' => $creditTotal,
                    'balance' => $balance,
                    'updated_at' => now()
                ]);
        }
    }

    public function exportStatement($format = 'pdf')
    {
        try {
            $this->isExporting = true;
            $this->errorMessage = '';
            $this->successMessage = '';
            
            // Validate the form data
            $this->validate();
            
            if (!$this->statementData) {
                $this->statementData = $this->getStatementData();
            }
            
            $filename = 'statement_of_comprehensive_income_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
            
            if ($format === 'pdf') {
                return $this->exportToPDF($filename);
            } elseif ($format === 'excel') {
                return $this->exportToExcel($filename);
            } else {
                throw new Exception('Unsupported export format: ' . $format);
            }
            
        } catch (Exception $e) {
            Log::error('Error exporting Statement of Comprehensive Income: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export Statement of Comprehensive Income. Please try again.';
            $this->isExporting = false;
        }
    }

    public function exportPDF()
    {
        try {
            $this->isExporting = true;
            $this->errorMessage = '';
            $this->successMessage = '';
            
            // Validate the form data
            $this->validate();
            
            if (!$this->statementData) {
                $this->statementData = $this->getStatementData();
            }
            
            $filename = 'statement_of_comprehensive_income_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            return $this->exportToPDF($filename);
            
        } catch (Exception $e) {
            Log::error('Error exporting Statement of Comprehensive Income as PDF: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export Statement of Comprehensive Income as PDF. Please try again.';
            $this->isExporting = false;
        }
    }

    public function exportExcel()
    {
        try {
            $this->isExporting = true;
            $this->errorMessage = '';
            $this->successMessage = '';
            
            // Validate the form data
            $this->validate();
            
            if (!$this->statementData) {
                $this->statementData = $this->getStatementData();
            }
            
            $filename = 'statement_of_comprehensive_income_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            return $this->exportToExcel($filename);
            
        } catch (Exception $e) {
            Log::error('Error exporting Statement of Comprehensive Income as Excel: ' . $e->getMessage());
            $this->errorMessage = 'Failed to export Statement of Comprehensive Income as Excel. Please try again.';
            $this->isExporting = false;
        }
    }

    private function exportToPDF($filename)
    {
        try {
            // Prepare data for PDF template
            $pdfData = [
                'statementData' => $this->statementData,
                'startDate' => $this->reportStartDate,
                'endDate' => $this->reportEndDate,
                'currency' => 'TZS',
                'reportDate' => now()->format('Y-m-d H:i:s'),
                'totalIncome' => $this->statementData['income']['total'],
                'totalExpenses' => $this->statementData['expenses']['total'],
                'netIncome' => $this->statementData['totals']['net_income'],
                'income' => $this->prepareIncomeForPDF(),
                'expenses' => $this->prepareExpensesForPDF()
            ];
            
            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('pdf.statement-of-comprehensive-income', $pdfData);
            
            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Log the export
            Log::info('Statement of Comprehensive Income exported as PDF', [
                'format' => 'pdf',
                'user_id' => auth()->id(),
                'period_start' => $this->statementData['period_start'],
                'period_end' => $this->statementData['period_end']
            ]);
            
            // Download the PDF
            $pdfOutput = $pdf->output();
            return response()->streamDownload(function () use ($pdfOutput) {
                echo $pdfOutput;
            }, $filename);
            
        } catch (Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    private function exportToExcel($filename)
    {
        try {
            // Ensure filename has .xlsx extension
            if (!str_ends_with($filename, '.xlsx')) {
                $filename = str_replace('.excel', '.xlsx', $filename);
            }
            
            // Download the Excel file
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\StatementOfComprehensiveIncomeExport($this->statementData, $this->reportStartDate, $this->reportEndDate),
                $filename
            );
            
        } catch (Exception $e) {
            Log::error('Error generating Excel: ' . $e->getMessage());
            throw $e;
        }
    }

    private function prepareIncomeForPDF()
    {
        return $this->statementData['income']['categories'];
    }

    private function prepareExpensesForPDF()
    {
        return $this->statementData['expenses']['categories'];
    }

    public function render()
    {
        return view('livewire.reports.statement-of-comprehensive-income');
    }
}