<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExpenseReportService
{
    /**
     * Generate comprehensive expense report
     *
     * @param array $filters
     * @return array
     */
    public function generateExpenseReport(array $filters = [])
    {
        try {
            Log::channel('budget_management')->info('📊 GENERATING EXPENSE REPORT', $filters);

            $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            // Build query
            $query = Expense::query()
                ->with(['account', 'user', 'budgetItem', 'paidByUser'])
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['account_id'])) {
                $query->where('account_id', $filters['account_id']);
            }

            if (!empty($filters['budget_item_id'])) {
                $query->where('budget_item_id', $filters['budget_item_id']);
            }

            if (!empty($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (!empty($filters['budget_status'])) {
                $query->where('budget_status', $filters['budget_status']);
            }

            if (!empty($filters['payment_type'])) {
                $query->where('payment_type', $filters['payment_type']);
            }

            $expenses = $query->orderBy('created_at', 'desc')->get();

            // Calculate statistics
            $statistics = $this->calculateExpenseStatistics($expenses, $dateFrom, $dateTo);

            // Generate Excel file
            $fileName = 'expense_report_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = $this->generateExcelReport($expenses, $statistics, $dateFrom, $dateTo, $fileName);

            Log::channel('budget_management')->info('✅ EXPENSE REPORT GENERATED', [
                'file_name' => $fileName,
                'records_count' => $expenses->count(),
                'total_amount' => $statistics['total_expenses']
            ]);

            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'statistics' => $statistics,
                'records_count' => $expenses->count()
            ];

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('❌ ERROR GENERATING EXPENSE REPORT', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate expense statistics
     */
    private function calculateExpenseStatistics($expenses, $dateFrom, $dateTo)
    {
        $totalExpenses = $expenses->sum('amount');
        $totalPaid = $expenses->where('status', 'PAID')->sum('amount');
        $totalPending = $expenses->whereIn('status', ['PENDING_APPROVAL', 'APPROVED'])->sum('amount');

        // Group by account
        $byAccount = $expenses->groupBy('account_id')->map(function ($items) {
            $account = $items->first()->account;
            return [
                'account_name' => $account ? $account->account_name : 'N/A',
                'account_number' => $account ? $account->account_number : 'N/A',
                'count' => $items->count(),
                'total' => $items->sum('amount'),
                'paid' => $items->where('status', 'PAID')->sum('amount'),
                'pending' => $items->whereIn('status', ['PENDING_APPROVAL', 'APPROVED'])->sum('amount')
            ];
        })->values()->toArray();

        // Group by status
        $byStatus = $expenses->groupBy('status')->map(function ($items, $status) {
            return [
                'status' => $status,
                'count' => $items->count(),
                'total' => $items->sum('amount')
            ];
        })->values()->toArray();

        // Group by payment type
        $byPaymentType = $expenses->groupBy('payment_type')->map(function ($items, $type) {
            return [
                'payment_type' => $type,
                'count' => $items->count(),
                'total' => $items->sum('amount')
            ];
        })->values()->toArray();

        // Group by budget status
        $byBudgetStatus = $expenses->groupBy('budget_status')->map(function ($items, $status) {
            return [
                'budget_status' => $status,
                'count' => $items->count(),
                'total' => $items->sum('amount')
            ];
        })->values()->toArray();

        // Group by user
        $byUser = $expenses->groupBy('user_id')->map(function ($items) {
            $user = $items->first()->user;
            return [
                'user_name' => $user ? $user->name : 'N/A',
                'count' => $items->count(),
                'total' => $items->sum('amount')
            ];
        })->values()->toArray();

        return [
            'total_expenses' => $totalExpenses,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'total_count' => $expenses->count(),
            'paid_count' => $expenses->where('status', 'PAID')->count(),
            'pending_count' => $expenses->whereIn('status', ['PENDING_APPROVAL', 'APPROVED'])->count(),
            'average_expense' => $expenses->count() > 0 ? $totalExpenses / $expenses->count() : 0,
            'by_account' => $byAccount,
            'by_status' => $byStatus,
            'by_payment_type' => $byPaymentType,
            'by_budget_status' => $byBudgetStatus,
            'by_user' => $byUser,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($expenses, $statistics, $dateFrom, $dateTo, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expense Report');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ];

        // Title
        $sheet->setCellValue('A1', 'EXPENSE REPORT');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report metadata
        $sheet->setCellValue('A2', 'Period: ' . $dateFrom . ' to ' . $dateTo);
        $sheet->setCellValue('A3', 'Generated: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('A4', 'Total Expenses: TZS ' . number_format($statistics['total_expenses'], 2));

        // Summary section
        $sheet->setCellValue('A6', 'SUMMARY');
        $sheet->mergeCells('A6:D6');
        $sheet->getStyle('A6')->applyFromArray($headerStyle);

        $summaryData = [
            ['Total Amount', 'TZS ' . number_format($statistics['total_expenses'], 2)],
            ['Total Paid', 'TZS ' . number_format($statistics['total_paid'], 2)],
            ['Total Pending', 'TZS ' . number_format($statistics['total_pending'], 2)],
            ['Total Count', $statistics['total_count']],
            ['Average Expense', 'TZS ' . number_format($statistics['average_expense'], 2)]
        ];

        $row = 7;
        foreach ($summaryData as $data) {
            $sheet->setCellValue('A' . $row, $data[0]);
            $sheet->setCellValue('B' . $row, $data[1]);
            $row++;
        }

        // Detail section - Column headers
        $row = $row + 2;
        $sheet->setCellValue('A' . $row, 'EXPENSE DETAILS');
        $sheet->mergeCells('A' . $row . ':L' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);

        $row++;
        $headers = [
            'A' => 'Date',
            'B' => 'Expense #',
            'C' => 'Description',
            'D' => 'Account',
            'E' => 'Amount',
            'F' => 'Payment Type',
            'G' => 'Requester',
            'H' => 'Status',
            'I' => 'Budget Status',
            'J' => 'Payment Date',
            'K' => 'Payment Ref',
            'L' => 'Paid By'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
        }

        // Data rows
        $row++;
        foreach ($expenses as $expense) {
            $sheet->setCellValue('A' . $row, Carbon::parse($expense->created_at)->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, 'EXP-' . str_pad($expense->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('C' . $row, $expense->description);
            $sheet->setCellValue('D' . $row, $expense->account ? $expense->account->account_name : 'N/A');
            $sheet->setCellValue('E' . $row, $expense->amount);
            $sheet->setCellValue('F' . $row, $expense->payment_type);
            $sheet->setCellValue('G' . $row, $expense->user ? $expense->user->name : 'N/A');
            $sheet->setCellValue('H' . $row, $expense->status);
            $sheet->setCellValue('I' . $row, $expense->budget_status);
            $sheet->setCellValue('J' . $row, $expense->payment_date ? Carbon::parse($expense->payment_date)->format('Y-m-d') : '');
            $sheet->setCellValue('K' . $row, $expense->payment_reference ?? '');
            $sheet->setCellValue('L' . $row, $expense->paidByUser ? $expense->paidByUser->name : '');

            // Add borders to data rows
            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save file
        $filePath = storage_path('app/reports/' . $fileName);

        // Ensure directory exists
        if (!file_exists(storage_path('app/reports'))) {
            mkdir(storage_path('app/reports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Generate expense summary by category
     */
    public function generateCategorySummary(array $filters = [])
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $summary = Expense::query()
            ->join('accounts', 'expenses.account_id', '=', 'accounts.id')
            ->whereBetween('expenses.created_at', [$dateFrom, $dateTo])
            ->select([
                'accounts.account_name',
                'accounts.account_number',
                'accounts.major_category_code',
                DB::raw('COUNT(expenses.id) as expense_count'),
                DB::raw('SUM(expenses.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN expenses.status = \'PAID\' THEN expenses.amount ELSE 0 END) as paid_amount'),
                DB::raw('SUM(CASE WHEN expenses.status IN (\'PENDING_APPROVAL\', \'APPROVED\') THEN expenses.amount ELSE 0 END) as pending_amount')
            ])
            ->groupBy('accounts.id', 'accounts.account_name', 'accounts.account_number', 'accounts.major_category_code')
            ->orderBy('total_amount', 'desc')
            ->get();

        return $summary;
    }

    /**
     * Generate monthly expense trend
     */
    public function generateMonthlyTrend($year = null)
    {
        $year = $year ?? Carbon::now()->year;

        $trend = Expense::query()
            ->whereYear('created_at', $year)
            ->select([
                DB::raw('EXTRACT(MONTH FROM created_at) as month'),
                DB::raw('COUNT(id) as expense_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = \'PAID\' THEN amount ELSE 0 END) as paid_amount')
            ])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy('month')
            ->get();

        return $trend;
    }
}
