<?php

namespace App\Http\Controllers;

use App\Services\LoanFullReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoanFullReportController extends Controller
{
    protected $reportService;

    public function __construct(LoanFullReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate and download the full loan report
     */
    public function generate(Request $request)
    {
        Log::info('Loan Full Report generation requested', $request->all());

        try {
            // Validate request
            $validated = $request->validate([
                'format' => 'sometimes|string|in:xlsx,csv',
                'branch_id' => 'sometimes|string',
                'loan_status' => 'sometimes|string',
                'product_type' => 'sometimes|string',
                'classification' => 'sometimes|string|in:PERFORMING,WATCH,SUBSTANDARD,DOUBTFUL,LOSS',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date',
                'include_all' => 'sometimes|boolean'
            ]);

            // Prepare filters
            $filters = $this->prepareFilters($validated);

            // Generate report
            $format = $validated['format'] ?? 'xlsx';

            if ($format === 'csv') {
                $result = $this->reportService->exportAsCSV($filters);
            } else {
                $result = $this->reportService->generateFullLoanReport($filters);
            }

            if ($result['success']) {
                // Return file download
                return response()->download(
                    $result['file_path'],
                    $result['file_name'],
                    [
                        'Content-Type' => $format === 'csv'
                            ? 'text/csv'
                            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ]
                )->deleteFileAfterSend(true);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate report'
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating loan full report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report statistics without generating the full report
     */
    public function statistics(Request $request)
    {
        Log::info('Loan Full Report statistics requested', $request->all());

        try {
            // Validate request
            $validated = $request->validate([
                'branch_id' => 'sometimes|string',
                'loan_status' => 'sometimes|string',
                'product_type' => 'sometimes|string',
                'classification' => 'sometimes|string|in:PERFORMING,WATCH,SUBSTANDARD,DOUBTFUL,LOSS',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date',
                'include_all' => 'sometimes|boolean'
            ]);

            // Prepare filters
            $filters = $this->prepareFilters($validated);

            // Get statistics
            $stats = $this->reportService->getReportStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error getting loan report statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream download the report (for large datasets)
     */
    public function stream(Request $request)
    {
        Log::info('Loan Full Report stream download requested', $request->all());

        try {
            // Validate request
            $validated = $request->validate([
                'format' => 'sometimes|string|in:csv',
                'branch_id' => 'sometimes|string',
                'loan_status' => 'sometimes|string',
                'product_type' => 'sometimes|string',
                'classification' => 'sometimes|string',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date',
                'include_all' => 'sometimes|boolean'
            ]);

            // Prepare filters
            $filters = $this->prepareFilters($validated);

            // Stream CSV
            $fileName = 'loan_full_report_' . Carbon::now()->format('Y_m_d_His') . '.csv';

            return response()->streamDownload(function () use ($filters) {
                $handle = fopen('php://output', 'w');

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
                fputcsv($handle, $headers);

                // Stream data in chunks
                $chunkSize = 1000;
                $query = $this->reportService->getLoanData($filters);

                foreach ($query->chunk($chunkSize) as $loans) {
                    foreach ($loans as $loan) {
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
                            $loan->next_due_date,
                            $loan->outstanding_emi,
                            $loan->overdue_emi,
                            $loan->open_date,
                            $loan->limit_disbursed_date,
                            $loan->limit_maturity_date,
                            $loan->last_paid_date,
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
                            $loan->end_date,
                            $loan->net_exposure_tzs,
                            $loan->ntd,
                            $loan->straight_roller,
                            $loan->bucket_jump,
                            $loan->movement,
                            $loan->flow,
                            $loan->current_int_rate,
                            $loan->final_instalment_date,
                            $loan->current_instalment_tzs
                        ];
                        fputcsv($handle, $row);
                    }
                }

                fclose($handle);
            }, $fileName, [
                'Content-Type' => 'text/csv'
            ]);

        } catch (\Exception $e) {
            Log::error('Error streaming loan report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error streaming report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare filters from validated input
     */
    private function prepareFilters($validated)
    {
        $filters = [
            'report_date' => Carbon::now()
        ];

        if (isset($validated['branch_id'])) {
            $filters['branch_id'] = $validated['branch_id'];
        }

        if (isset($validated['loan_status'])) {
            $filters['loan_status'] = $validated['loan_status'];
        }

        if (isset($validated['product_type'])) {
            $filters['product_type'] = $validated['product_type'];
        }

        if (isset($validated['classification'])) {
            $filters['classification'] = $validated['classification'];
        }

        if (isset($validated['date_from'])) {
            $filters['date_from'] = $validated['date_from'];
        }

        if (isset($validated['date_to'])) {
            $filters['date_to'] = $validated['date_to'];
        }

        if (isset($validated['include_all'])) {
            $filters['include_all'] = $validated['include_all'];
        }

        return $filters;
    }
}
