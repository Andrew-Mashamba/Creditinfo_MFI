<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class DailyOperationsReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            'Summary' => new DailySummarySheet($this->data),
            'Transactions' => new TransactionSummarySheet($this->data),
            'Loan Operations' => new LoanOperationsSheet($this->data),
            'Deposits' => new DepositOperationsSheet($this->data),
            'Withdrawals' => new WithdrawalOperationsSheet($this->data),
            'New Loans' => new NewLoansSheet($this->data),
            'Disbursements' => new LoanDisbursementsSheet($this->data),
            'Repayments' => new LoanRepaymentsSheet($this->data),
            'New Clients' => new NewClientsSheet($this->data),
            'Staff Activities' => new StaffActivitiesSheet($this->data),
        ];
    }
}

class DailySummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $summary = $this->data['dailySummary'];
        $statistics = $this->data['statistics'];
        
        return [
            ['Daily Operations Report - Summary'],
            ['Report Date:', $summary['date'] ?? 'N/A'],
            ['Day of Week:', $summary['day_of_week'] ?? 'N/A'],
            ['Branch:', $this->data['branchName']],
            [''],
            ['OPERATIONAL METRICS'],
            ['Total Clients Served:', $summary['total_clients_served'] ?? 0],
            ['Total Transactions:', $statistics['totalTransactions']],
            ['Total Transaction Value:', number_format($statistics['totalTransactionValue'], 2) . ' TZS'],
            ['Average Transaction Value:', number_format($statistics['averageTransactionValue'], 2) . ' TZS'],
            ['Staff on Duty:', $summary['staff_on_duty'] ?? 0],
            ['Average Transaction Time:', $summary['average_transaction_time'] ?? 'N/A'],
            ['Peak Hours:', $summary['peak_hours'] ?? 'N/A'],
            ['System Uptime:', $summary['system_uptime'] ?? 'N/A'],
            [''],
            ['LOAN ACTIVITIES'],
            ['New Loans Processed:', $summary['new_loans_processed'] ?? 0],
            ['Loan Disbursements:', $summary['loan_disbursements'] ?? 0],
            ['Loan Repayments:', $summary['loan_repayments'] ?? 0],
            ['New Clients Registered:', $summary['new_clients_registered'] ?? 0],
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            6 => ['font' => ['bold' => true, 'size' => 12]],
            16 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

class TransactionSummarySheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $transactionSummary = $this->data['transactionSummary'];
        
        return [
            [
                'Deposits',
                $transactionSummary['deposits']['count'] ?? 0,
                number_format($transactionSummary['deposits']['total_amount'] ?? 0, 2) . ' TZS',
                number_format($transactionSummary['deposits']['average_amount'] ?? 0, 2) . ' TZS'
            ],
            [
                'Withdrawals',
                $transactionSummary['withdrawals']['count'] ?? 0,
                number_format($transactionSummary['withdrawals']['total_amount'] ?? 0, 2) . ' TZS',
                number_format($transactionSummary['withdrawals']['average_amount'] ?? 0, 2) . ' TZS'
            ],
            [
                'Transfers',
                $transactionSummary['transfers']['count'] ?? 0,
                number_format($transactionSummary['transfers']['total_amount'] ?? 0, 2) . ' TZS',
                number_format($transactionSummary['transfers']['average_amount'] ?? 0, 2) . ' TZS'
            ],
            [
                'Loan Payments',
                $transactionSummary['loan_payments']['count'] ?? 0,
                number_format($transactionSummary['loan_payments']['total_amount'] ?? 0, 2) . ' TZS',
                number_format($transactionSummary['loan_payments']['average_amount'] ?? 0, 2) . ' TZS'
            ],
            [
                'Other Transactions',
                $transactionSummary['other_transactions']['count'] ?? 0,
                number_format($transactionSummary['other_transactions']['total_amount'] ?? 0, 2) . ' TZS',
                number_format($transactionSummary['other_transactions']['average_amount'] ?? 0, 2) . ' TZS'
            ],
        ];
    }

    public function headings(): array
    {
        return ['Transaction Type', 'Count', 'Total Amount', 'Average Amount'];
    }

    public function title(): string
    {
        return 'Transactions';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 10,
            'C' => 20,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class LoanOperationsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $loanOperations = $this->data['loanOperations'];
        
        $rows = [];
        foreach ($loanOperations as $loan) {
            $rows[] = [
                $loan['loan_id'] ?? 'N/A',
                $loan['client_name'] ?? 'N/A',
                number_format($loan['loan_amount'] ?? 0, 2) . ' TZS',
                $loan['loan_type'] ?? 'N/A',
                $loan['officer'] ?? 'N/A',
                $loan['status'] ?? 'N/A',
                $loan['processing_time'] ?? 'N/A',
                $loan['timestamp'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Loan ID', 'Client Name', 'Amount', 'Type', 'Officer', 'Status', 'Processing Time', 'Timestamp'];
    }

    public function title(): string
    {
        return 'Loan Operations';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 20,
            'H' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class DepositOperationsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $depositOperations = $this->data['depositOperations'];
        
        $rows = [];
        foreach ($depositOperations as $deposit) {
            $rows[] = [
                $deposit['transaction_id'] ?? 'N/A',
                $deposit['client_name'] ?? 'N/A',
                number_format($deposit['amount'] ?? 0, 2) . ' TZS',
                $deposit['account_type'] ?? 'N/A',
                $deposit['officer'] ?? 'N/A',
                $deposit['timestamp'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Transaction ID', 'Client Name', 'Amount', 'Account Type', 'Officer', 'Timestamp'];
    }

    public function title(): string
    {
        return 'Deposits';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 20,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class WithdrawalOperationsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $withdrawalOperations = $this->data['withdrawalOperations'];
        
        $rows = [];
        foreach ($withdrawalOperations as $withdrawal) {
            $rows[] = [
                $withdrawal['transaction_id'] ?? 'N/A',
                $withdrawal['client_name'] ?? 'N/A',
                number_format($withdrawal['amount'] ?? 0, 2) . ' TZS',
                $withdrawal['account_type'] ?? 'N/A',
                $withdrawal['officer'] ?? 'N/A',
                $withdrawal['timestamp'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Transaction ID', 'Client Name', 'Amount', 'Account Type', 'Officer', 'Timestamp'];
    }

    public function title(): string
    {
        return 'Withdrawals';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 20,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class NewLoansSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $newLoans = $this->data['newLoans'];
        
        $rows = [];
        foreach ($newLoans as $loan) {
            $rows[] = [
                $loan['loan_id'] ?? 'N/A',
                $loan['client_name'] ?? 'N/A',
                number_format($loan['loan_amount'] ?? 0, 2) . ' TZS',
                $loan['loan_type'] ?? 'N/A',
                ($loan['interest_rate'] ?? 0) . '%',
                $loan['term_months'] ?? 0 . ' months',
                $loan['officer'] ?? 'N/A',
                $loan['approval_date'] ?? 'N/A',
                $loan['status'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Loan ID', 'Client Name', 'Amount', 'Type', 'Interest Rate', 'Term', 'Officer', 'Approval Date', 'Status'];
    }

    public function title(): string
    {
        return 'New Loans';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 15,
            'I' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class LoanDisbursementsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $loanDisbursements = $this->data['loanDisbursements'];
        
        $rows = [];
        foreach ($loanDisbursements as $disbursement) {
            $rows[] = [
                $disbursement['loan_id'] ?? 'N/A',
                $disbursement['client_name'] ?? 'N/A',
                number_format($disbursement['disbursed_amount'] ?? 0, 2) . ' TZS',
                $disbursement['disbursement_date'] ?? 'N/A',
                $disbursement['officer'] ?? 'N/A',
                $disbursement['method'] ?? 'N/A',
                $disbursement['status'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Loan ID', 'Client Name', 'Disbursed Amount', 'Disbursement Date', 'Officer', 'Method', 'Status'];
    }

    public function title(): string
    {
        return 'Disbursements';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class LoanRepaymentsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $loanRepayments = $this->data['loanRepayments'];
        
        $rows = [];
        foreach ($loanRepayments as $repayment) {
            $rows[] = [
                $repayment['loan_id'] ?? 'N/A',
                $repayment['client_name'] ?? 'N/A',
                number_format($repayment['repayment_amount'] ?? 0, 2) . ' TZS',
                number_format($repayment['principal_amount'] ?? 0, 2) . ' TZS',
                number_format($repayment['interest_amount'] ?? 0, 2) . ' TZS',
                $repayment['repayment_date'] ?? 'N/A',
                $repayment['officer'] ?? 'N/A',
                $repayment['method'] ?? 'N/A',
                $repayment['status'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Loan ID', 'Client Name', 'Total Amount', 'Principal', 'Interest', 'Repayment Date', 'Officer', 'Method', 'Status'];
    }

    public function title(): string
    {
        return 'Repayments';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 15,
            'I' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class NewClientsSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $newClients = $this->data['newClients'];
        
        $rows = [];
        foreach ($newClients as $client) {
            $rows[] = [
                $client['client_id'] ?? 'N/A',
                $client['client_name'] ?? 'N/A',
                $client['registration_date'] ?? 'N/A',
                $client['client_type'] ?? 'N/A',
                $client['officer'] ?? 'N/A',
                $client['status'] ?? 'N/A',
                number_format($client['initial_deposit'] ?? 0, 2) . ' TZS',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Client ID', 'Client Name', 'Registration Date', 'Client Type', 'Officer', 'Status', 'Initial Deposit'];
    }

    public function title(): string
    {
        return 'New Clients';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 20,
            'F' => 15,
            'G' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class StaffActivitiesSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $staffActivities = $this->data['staffActivities'];
        
        $rows = [];
        foreach ($staffActivities as $staff) {
            $rows[] = [
                $staff['staff_name'] ?? 'N/A',
                $staff['position'] ?? 'N/A',
                $staff['department'] ?? 'N/A',
                $staff['activities_completed'] ?? 0,
                $staff['clients_served'] ?? 0,
                $staff['transactions_processed'] ?? 0,
                $staff['efficiency_rating'] ?? 'N/A',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return ['Staff Name', 'Position', 'Department', 'Activities Completed', 'Clients Served', 'Transactions Processed', 'Efficiency Rating'];
    }

    public function title(): string
    {
        return 'Staff Activities';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 15,
            'F' => 20,
            'G' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
