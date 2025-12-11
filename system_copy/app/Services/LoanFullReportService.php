<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LoanFullReportService
{
    /**
     * Generate comprehensive loan full report
     */
    public function generateFullLoanReport($filters = [])
    {
        $reportDate = $filters['report_date'] ?? Carbon::now();
        Log::info('📊 Generating Full Loan Report for ' . $reportDate->format('Y-m-d'));

        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Loan Full Report');

            // Set headers
            $this->setReportHeaders($sheet);

            // Get loan data
            $loanData = $this->getLoanData($filters);

            // Populate data
            $this->populateReportData($sheet, $loanData, $reportDate);

            // Save the file
            $fileName = 'loan_full_report_' . $reportDate->format('Y_m_d') . '.xlsx';
            $filePath = storage_path('app/reports/' . $fileName);

            // Create directory if it doesn't exist
            if (!file_exists(storage_path('app/reports'))) {
                mkdir(storage_path('app/reports'), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            Log::info('✅ Full loan report generated: ' . $fileName);

            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'record_count' => count($loanData)
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error generating full loan report: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Set report headers
     */
    private function setReportHeaders($sheet)
    {
        $headers = [
            'A' => 'PRODUCT_TYPE',
            'B' => 'LOAN_NUMBER',
            'C' => 'CONTROL_NUMBER',
            'D' => 'NIDA_NUMBER',
            'E' => 'PHONE_NUMBER',
            'F' => 'LOAN_ACCOUNT',
            'G' => 'BANK_SAVING_ACCOUNT',
            'H' => 'CUSTOMER_ID',
            'I' => 'CUSTOMER_NAME',
            'J' => 'ACCRUAL_INTEREST_STATUS',
            'K' => 'BRANCH_CODE',
            'L' => 'BRANCH_BANK_NAME',
            'M' => 'TOTAL_MORATORIUM_DAYS',
            'N' => 'NEXT_DUE_DATE',
            'O' => 'OUTSTANDING_EMI',
            'P' => 'OVERDUE_EMI',
            'Q' => 'OPEN_DATE',
            'R' => 'LIMIT_DISBURSED_DATE',
            'S' => 'LIMIT_MATURITY_DATE',
            'T' => 'LAST_PAID_DATE',
            'U' => 'LAST_PAID_AMT_TZS',
            'V' => 'PAYMENT_FREQUENCY',
            'W' => 'LOAN_OD_TENURE',
            'X' => 'MONTH_ON_BOOK',
            'Y' => 'DAYS_OVERLINE',
            'Z' => 'PAYMENT_CYCLE',
            'AA' => 'CYCLE_LASTMONTH_2',
            'AB' => 'CYCLE_LASTMONTH_1',
            'AC' => 'DEL_CYCLE',
            'AD' => 'FX_RATE',
            'AE' => 'CURRENCY',
            'AF' => 'LIMIT_DISBURSED_TZS',
            'AG' => 'AMT_PROFIT_SHARE_TZS',
            'AH' => 'BALANCE_TZS',
            'AI' => 'INTEREST_IN_SUSPENSE_TZS',
            'AJ' => 'PRINCIPAL_BALANCE_TZS',
            'AK' => 'CR_TURNOVER_MTD_TZS',
            'AL' => 'DR_TURNOVER_MTD_TZS',
            'AM' => 'INSTALMENT_AMOUNT_TZS',
            'AN' => 'ARREARS_EXCESS_TZS',
            'AO' => 'INTEREST_RATE',
            'AP' => 'PROFIT_RATE',
            'AQ' => 'PREV_SEGMENT_CODE',
            'AR' => 'ACCOUNT_SEGMENT_CODE',
            'AS' => 'SEGMENT_NAMING',
            'AT' => 'END_DATE',
            'AU' => 'NET_EXPOSURE_TZS',
            'AV' => 'NTD',
            'AW' => 'STRAIGHT_ROLLER',
            'AX' => 'BUCKET_JUMP',
            'AY' => 'MOVEMENT',
            'AZ' => 'FLOW',
            'BA' => 'CURRENT_INT_RATE',
            'BB' => 'FINAL_INSTALMENT_DATE',
            'BC' => 'CURRENT_INSTALMENT_TZS'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Apply header styling
        $sheet->getStyle('A1:BC1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4CAF50');
        $sheet->getStyle('A1:BC1')->getFont()->getColor()->setARGB('FFFFFFFF');
    }

    /**
     * Get loan data from database
     */
    private function getLoanData($filters = [])
    {
        $query = DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_id', '=', 'c.id')
            ->leftJoin('branches as b', DB::raw('CAST(l.branch_id AS BIGINT)'), '=', 'b.id')
            ->leftJoin(DB::raw('(SELECT loan_id, MIN(installment_date) as next_due_date
                FROM loans_schedules
                WHERE completion_status != \'PAID\'
                AND installment_date >= CURRENT_DATE
                GROUP BY loan_id) as next_schedule'), 'l.loan_id', '=', 'next_schedule.loan_id')
            ->leftJoin(DB::raw('(SELECT loan_id, MAX(payment_date) as last_payment_date,
                MAX(amount) as last_payment_amount
                FROM loan_repayments
                WHERE status = \'COMPLETED\'
                GROUP BY loan_id) as last_payment'), 'l.loan_id', '=', 'last_payment.loan_id')
            ->leftJoin(DB::raw('(SELECT loan_id,
                SUM(CASE WHEN installment_date < CURRENT_DATE AND completion_status != \'PAID\' THEN installment ELSE 0 END) as overdue_emi,
                SUM(CASE WHEN completion_status != \'PAID\' THEN installment ELSE 0 END) as outstanding_emi
                FROM loans_schedules
                GROUP BY loan_id) as schedules'), 'l.loan_id', '=', 'schedules.loan_id')
            ->select([
                // Basic loan information
                'l.loan_type_2 as product_type',
                'l.loan_id as loan_number',
                'l.loan_account_number as control_number',
                'c.nida_number',
                DB::raw('COALESCE(c.mobile_phone_number, c.phone_number) as phone_number'),
                'l.loan_account_number as loan_account',
                'l.bank_account_number as bank_saving_account',
                'c.client_number as customer_id',
                DB::raw('COALESCE(c.full_name, CONCAT(c.first_name, \' \', c.last_name)) as customer_name'),
                'l.loan_status as accrual_interest_status',
                'b.branch_number as branch_code',
                'b.name as branch_bank_name',

                // Dates and schedules
                DB::raw('0 as total_moratorium_days'),
                'next_schedule.next_due_date',
                DB::raw('COALESCE(schedules.outstanding_emi, 0) as outstanding_emi'),
                DB::raw('COALESCE(schedules.overdue_emi, 0) as overdue_emi'),
                'l.created_at as open_date',
                'l.disbursement_date as limit_disbursed_date',
                DB::raw("CASE
                    WHEN l.tenure IS NOT NULL AND l.disbursement_date IS NOT NULL
                    THEN l.disbursement_date + (l.tenure || ' months')::INTERVAL
                    ELSE NULL
                END as limit_maturity_date"),
                'last_payment.last_payment_date as last_paid_date',
                DB::raw('COALESCE(last_payment.last_payment_amount, 0) as last_paid_amt_tzs'),

                // Loan terms
                DB::raw("CASE
                    WHEN l.tenure <= 6 THEN 'MONTHLY'
                    WHEN l.tenure > 6 AND l.tenure <= 12 THEN 'MONTHLY'
                    WHEN l.tenure > 12 AND l.tenure <= 24 THEN 'MONTHLY'
                    WHEN l.tenure > 24 THEN 'SEMI ANNUAL'
                    ELSE 'MONTHLY'
                END as payment_frequency"),
                'l.tenure as loan_od_tenure',
                DB::raw("CASE
                    WHEN l.disbursement_date IS NOT NULL
                    THEN EXTRACT(YEAR FROM AGE(CURRENT_DATE, l.disbursement_date)) * 12 +
                         EXTRACT(MONTH FROM AGE(CURRENT_DATE, l.disbursement_date))
                    ELSE 0
                END as month_on_book"),
                DB::raw('COALESCE(l.days_in_arrears, 0) as days_overline'),

                // Classification tracking
                DB::raw('0 as payment_cycle'),
                DB::raw('0 as cycle_lastmonth_2'),
                DB::raw('0 as cycle_lastmonth_1'),
                DB::raw('0 as del_cycle'),

                // Financial details
                DB::raw('1 as fx_rate'),
                DB::raw('\'TZS\' as currency'),
                'l.principle as limit_disbursed_tzs',
                DB::raw('COALESCE(l.total_interest, 0) as amt_profit_share_tzs'),
                DB::raw('(l.principle - COALESCE(l.total_principal_paid, 0) +
                    COALESCE(l.total_interest, 0) - COALESCE(l.total_interest_paid, 0)) as balance_tzs'),
                DB::raw("CASE
                    WHEN l.loan_status = 'SUSPENDED'
                    THEN COALESCE(l.total_interest, 0) - COALESCE(l.total_interest_paid, 0)
                    ELSE 0
                END as interest_in_suspense_tzs"),
                DB::raw('(l.principle - COALESCE(l.total_principal_paid, 0)) as principal_balance_tzs'),
                DB::raw('COALESCE(l.total_principal_paid, 0) + COALESCE(l.total_interest_paid, 0) as cr_turnover_mtd_tzs'),
                DB::raw('COALESCE(l.principle, 0) as dr_turnover_mtd_tzs'),
                DB::raw('COALESCE(CAST(l.monthly_installment AS NUMERIC), 0) as instalment_amount_tzs'),
                DB::raw('COALESCE(l.total_arrears, 0) as arrears_excess_tzs'),

                // Interest and rates
                DB::raw('COALESCE(l.interest, 0) as interest_rate'),
                DB::raw('COALESCE(l.interest, 0) as profit_rate'),

                // Segment codes
                DB::raw($this->getSegmentCodeSQL() . ' as prev_segment_code'),
                DB::raw($this->getSegmentCodeSQL() . ' as account_segment_code'),
                'l.loan_classification as segment_naming',

                // Additional fields
                DB::raw('CURRENT_DATE as end_date'),
                DB::raw('(l.principle - COALESCE(l.total_principal_paid, 0)) as net_exposure_tzs'),
                DB::raw('0 as ntd'),
                DB::raw("CASE WHEN l.days_in_arrears > 0 THEN 1 ELSE 0 END as straight_roller"),
                DB::raw('0 as bucket_jump'),
                DB::raw("CASE
                    WHEN l.days_in_arrears = 0 THEN '0_0'
                    WHEN l.days_in_arrears > 0 THEN CONCAT(l.loan_classification, '_', l.loan_classification)
                    ELSE '0_0'
                END as movement"),
                DB::raw("CASE
                    WHEN l.days_in_arrears = 0 THEN 'Miller'
                    WHEN l.disbursement_date >= CURRENT_DATE - INTERVAL '30 days' THEN 'New-to-Book'
                    ELSE 'Miller'
                END as flow"),

                // Final fields
                DB::raw('COALESCE(l.interest, 0) as current_int_rate'),
                DB::raw("CASE
                    WHEN l.tenure IS NOT NULL AND l.disbursement_date IS NOT NULL
                    THEN l.disbursement_date + (l.tenure || ' months')::INTERVAL
                    ELSE NULL
                END as final_instalment_date"),
                DB::raw('COALESCE(CAST(l.monthly_installment AS NUMERIC), 0) as current_instalment_tzs')
            ]);

        // Apply filters
        if (!empty($filters['branch_id'])) {
            $query->where('l.branch_id', $filters['branch_id']);
        }

        if (!empty($filters['loan_status'])) {
            $query->where('l.loan_status', $filters['loan_status']);
        }

        if (!empty($filters['product_type'])) {
            $query->where('l.loan_type_2', $filters['product_type']);
        }

        if (!empty($filters['classification'])) {
            $query->where('l.loan_classification', $filters['classification']);
        }

        // Only apply date filters if user explicitly sets them
        if (!empty($filters['date_from'])) {
            $query->where(function($q) use ($filters) {
                $q->whereDate('l.disbursement_date', '>=', $filters['date_from'])
                  ->orWhereNull('l.disbursement_date');
            });
        }

        if (!empty($filters['date_to'])) {
            $query->where(function($q) use ($filters) {
                $q->whereDate('l.disbursement_date', '<=', $filters['date_to'])
                  ->orWhereNull('l.disbursement_date');
            });
        }

        // Default to active loans only unless specified
        if (!isset($filters['include_all']) || !$filters['include_all']) {
            $query->whereIn('l.status', ['ACTIVE', 'DISBURSED']);
        }

        return $query->get();
    }

    /**
     * Populate report data
     */
    private function populateReportData($sheet, $loanData, $reportDate)
    {
        $row = 2;

        foreach ($loanData as $loan) {
            $sheet->setCellValue('A' . $row, $loan->product_type);
            $sheet->setCellValue('B' . $row, $loan->loan_number);
            $sheet->setCellValue('C' . $row, $loan->control_number);
            $sheet->setCellValue('D' . $row, $loan->nida_number);
            $sheet->setCellValue('E' . $row, $loan->phone_number);
            $sheet->setCellValue('F' . $row, $loan->loan_account);
            $sheet->setCellValue('G' . $row, $loan->bank_saving_account);
            $sheet->setCellValue('H' . $row, $loan->customer_id);
            $sheet->setCellValue('I' . $row, $loan->customer_name);
            $sheet->setCellValue('J' . $row, $loan->accrual_interest_status);
            $sheet->setCellValue('K' . $row, $loan->branch_code);
            $sheet->setCellValue('L' . $row, $loan->branch_bank_name);
            $sheet->setCellValue('M' . $row, $loan->total_moratorium_days);
            $sheet->setCellValue('N' . $row, $loan->next_due_date ? Carbon::parse($loan->next_due_date)->format('n/j/y') : '');
            $sheet->setCellValue('O' . $row, $loan->outstanding_emi);
            $sheet->setCellValue('P' . $row, $loan->overdue_emi);
            $sheet->setCellValue('Q' . $row, $loan->open_date ? Carbon::parse($loan->open_date)->format('n/j/Y') : '');
            $sheet->setCellValue('R' . $row, $loan->limit_disbursed_date ? Carbon::parse($loan->limit_disbursed_date)->format('n/j/Y') : '');
            $sheet->setCellValue('S' . $row, $loan->limit_maturity_date ? Carbon::parse($loan->limit_maturity_date)->format('n/j/Y') : '');
            $sheet->setCellValue('T' . $row, $loan->last_paid_date ? Carbon::parse($loan->last_paid_date)->format('n/j/y') : '');
            $sheet->setCellValue('U' . $row, $loan->last_paid_amt_tzs);
            $sheet->setCellValue('V' . $row, $loan->payment_frequency);
            $sheet->setCellValue('W' . $row, $loan->loan_od_tenure);
            $sheet->setCellValue('X' . $row, $loan->month_on_book);
            $sheet->setCellValue('Y' . $row, $loan->days_overline);
            $sheet->setCellValue('Z' . $row, $loan->payment_cycle);
            $sheet->setCellValue('AA' . $row, $loan->cycle_lastmonth_2);
            $sheet->setCellValue('AB' . $row, $loan->cycle_lastmonth_1);
            $sheet->setCellValue('AC' . $row, $loan->del_cycle);
            $sheet->setCellValue('AD' . $row, $loan->fx_rate);
            $sheet->setCellValue('AE' . $row, $loan->currency);
            $sheet->setCellValue('AF' . $row, $loan->limit_disbursed_tzs);
            $sheet->setCellValue('AG' . $row, $loan->amt_profit_share_tzs);
            $sheet->setCellValue('AH' . $row, $loan->balance_tzs);
            $sheet->setCellValue('AI' . $row, $loan->interest_in_suspense_tzs);
            $sheet->setCellValue('AJ' . $row, $loan->principal_balance_tzs);
            $sheet->setCellValue('AK' . $row, $loan->cr_turnover_mtd_tzs);
            $sheet->setCellValue('AL' . $row, $loan->dr_turnover_mtd_tzs);
            $sheet->setCellValue('AM' . $row, $loan->instalment_amount_tzs);
            $sheet->setCellValue('AN' . $row, $loan->arrears_excess_tzs);
            $sheet->setCellValue('AO' . $row, $loan->interest_rate);
            $sheet->setCellValue('AP' . $row, $loan->profit_rate);
            $sheet->setCellValue('AQ' . $row, $loan->prev_segment_code);
            $sheet->setCellValue('AR' . $row, $loan->account_segment_code);
            $sheet->setCellValue('AS' . $row, $loan->segment_naming);
            $sheet->setCellValue('AT' . $row, $loan->end_date ? Carbon::parse($loan->end_date)->format('n/j/y') : '');
            $sheet->setCellValue('AU' . $row, $loan->net_exposure_tzs);
            $sheet->setCellValue('AV' . $row, $loan->ntd);
            $sheet->setCellValue('AW' . $row, $loan->straight_roller);
            $sheet->setCellValue('AX' . $row, $loan->bucket_jump);
            $sheet->setCellValue('AY' . $row, $loan->movement);
            $sheet->setCellValue('AZ' . $row, $loan->flow);
            $sheet->setCellValue('BA' . $row, $loan->current_int_rate);
            $sheet->setCellValue('BB' . $row, $loan->final_instalment_date ? Carbon::parse($loan->final_instalment_date)->format('n/j/Y') : '');
            $sheet->setCellValue('BC' . $row, $loan->current_instalment_tzs);

            // Format number columns
            $numberColumns = ['O', 'P', 'U', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AU', 'BC'];
            foreach ($numberColumns as $col) {
                $sheet->getStyle($col . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }

            $row++;
        }
    }

    /**
     * Get segment code SQL based on classification
     */
    private function getSegmentCodeSQL()
    {
        return "CASE
            WHEN l.loan_classification = 'PERFORMING' THEN 300
            WHEN l.loan_classification = 'WATCH' THEN 332
            WHEN l.loan_classification = 'SUBSTANDARD' THEN 400
            WHEN l.loan_classification = 'DOUBTFUL' THEN 500
            WHEN l.loan_classification = 'LOSS' THEN 600
            ELSE 300
        END";
    }

    /**
     * Export report as CSV
     */
    public function exportAsCSV($filters = [])
    {
        $reportDate = $filters['report_date'] ?? Carbon::now();
        Log::info('📊 Generating Full Loan Report CSV for ' . $reportDate->format('Y-m-d'));

        try {
            $loanData = $this->getLoanData($filters);

            $fileName = 'loan_full_report_' . $reportDate->format('Y_m_d') . '.csv';
            $filePath = storage_path('app/reports/' . $fileName);

            // Create directory if it doesn't exist
            if (!file_exists(storage_path('app/reports'))) {
                mkdir(storage_path('app/reports'), 0755, true);
            }

            $file = fopen($filePath, 'w');

            // Write headers
            $headers = [
                'PRODUCT_TYPE', 'LOAN_NUMBER', 'CONTROL_NUMBER', 'NIDA_NUMBER', 'PHONE_NUMBER',
                'LOAN_ACCOUNT', 'BANK_SAVING_ACCOUNT', 'CUSTOMER_ID', 'CUSTOMER_NAME',
                'ACCRUAL_INTEREST_STATUS', 'BRANCH_CODE', 'BRANCH_BANK_NAME',
                'TOTAL_MORATORIUM_DAYS', 'NEXT_DUE_DATE', 'OUTSTANDING_EMI', 'OVERDUE_EMI',
                'OPEN_DATE', 'LIMIT_DISBURSED_DATE', 'LIMIT_MATURITY_DATE', 'LAST_PAID_DATE',
                'LAST_PAID_AMT_TZS', 'PAYMENT_FREQUENCY', 'LOAN_OD_TENURE', 'MONTH_ON_BOOK',
                'DAYS_OVERLINE', 'PAYMENT_CYCLE', 'CYCLE_LASTMONTH_2', 'CYCLE_LASTMONTH_1',
                'DEL_CYCLE', 'FX_RATE', 'CURRENCY', 'LIMIT_DISBURSED_TZS', 'AMT_PROFIT_SHARE_TZS',
                'BALANCE_TZS', 'INTEREST_IN_SUSPENSE_TZS', 'PRINCIPAL_BALANCE_TZS',
                'CR_TURNOVER_MTD_TZS', 'DR_TURNOVER_MTD_TZS', 'INSTALMENT_AMOUNT_TZS',
                'ARREARS_EXCESS_TZS', 'INTEREST_RATE', 'PROFIT_RATE', 'PREV_SEGMENT_CODE',
                'ACCOUNT_SEGMENT_CODE', 'SEGMENT_NAMING', 'END_DATE', 'NET_EXPOSURE_TZS',
                'NTD', 'STRAIGHT_ROLLER', 'BUCKET_JUMP', 'MOVEMENT', 'FLOW',
                'CURRENT_INT_RATE', 'FINAL_INSTALMENT_DATE', 'CURRENT_INSTALMENT_TZS'
            ];
            fputcsv($file, $headers);

            // Write data
            foreach ($loanData as $loan) {
                $row = [
                    $loan->product_type,
                    $loan->loan_number,
                    $loan->control_number,
                    $loan->nida_number,
                    $loan->phone_number,
                    $loan->loan_account,
                    $loan->bank_saving_account,
                    $loan->customer_id,
                    $loan->customer_name,
                    $loan->accrual_interest_status,
                    $loan->branch_code,
                    $loan->branch_bank_name,
                    $loan->total_moratorium_days,
                    $loan->next_due_date ? Carbon::parse($loan->next_due_date)->format('n/j/y') : '',
                    $loan->outstanding_emi,
                    $loan->overdue_emi,
                    $loan->open_date ? Carbon::parse($loan->open_date)->format('n/j/Y') : '',
                    $loan->limit_disbursed_date ? Carbon::parse($loan->limit_disbursed_date)->format('n/j/Y') : '',
                    $loan->limit_maturity_date ? Carbon::parse($loan->limit_maturity_date)->format('n/j/Y') : '',
                    $loan->last_paid_date ? Carbon::parse($loan->last_paid_date)->format('n/j/y') : '',
                    $loan->last_paid_amt_tzs,
                    $loan->payment_frequency,
                    $loan->loan_od_tenure,
                    $loan->month_on_book,
                    $loan->days_overline,
                    $loan->payment_cycle,
                    $loan->cycle_lastmonth_2,
                    $loan->cycle_lastmonth_1,
                    $loan->del_cycle,
                    $loan->fx_rate,
                    $loan->currency,
                    $loan->limit_disbursed_tzs,
                    $loan->amt_profit_share_tzs,
                    $loan->balance_tzs,
                    $loan->interest_in_suspense_tzs,
                    $loan->principal_balance_tzs,
                    $loan->cr_turnover_mtd_tzs,
                    $loan->dr_turnover_mtd_tzs,
                    $loan->instalment_amount_tzs,
                    $loan->arrears_excess_tzs,
                    $loan->interest_rate,
                    $loan->profit_rate,
                    $loan->prev_segment_code,
                    $loan->account_segment_code,
                    $loan->segment_naming,
                    $loan->end_date ? Carbon::parse($loan->end_date)->format('n/j/y') : '',
                    $loan->net_exposure_tzs,
                    $loan->ntd,
                    $loan->straight_roller,
                    $loan->bucket_jump,
                    $loan->movement,
                    $loan->flow,
                    $loan->current_int_rate,
                    $loan->final_instalment_date ? Carbon::parse($loan->final_instalment_date)->format('n/j/Y') : '',
                    $loan->current_instalment_tzs
                ];
                fputcsv($file, $row);
            }

            fclose($file);

            Log::info('✅ Full loan report CSV generated: ' . $fileName);

            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'record_count' => count($loanData)
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error generating full loan report CSV: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get report statistics
     */
    public function getReportStatistics($filters = [])
    {
        $loanData = $this->getLoanData($filters);

        return [
            'total_loans' => count($loanData),
            'total_disbursed' => $loanData->sum('limit_disbursed_tzs'),
            'total_outstanding' => $loanData->sum('balance_tzs'),
            'total_principal_outstanding' => $loanData->sum('principal_balance_tzs'),
            'total_arrears' => $loanData->sum('arrears_excess_tzs'),
            'by_product' => $loanData->groupBy('product_type')->map(function ($group) {
                return [
                    'count' => count($group),
                    'total_disbursed' => $group->sum('limit_disbursed_tzs'),
                    'total_outstanding' => $group->sum('balance_tzs')
                ];
            }),
            'by_classification' => $loanData->groupBy('segment_naming')->map(function ($group) {
                return [
                    'count' => count($group),
                    'total_outstanding' => $group->sum('balance_tzs')
                ];
            })
        ];
    }
}
