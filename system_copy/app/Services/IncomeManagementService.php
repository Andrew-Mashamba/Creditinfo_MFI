<?php

namespace App\Services;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IncomeManagementService
{
    protected $transactionPostingService;

    public function __construct()
    {
        $this->transactionPostingService = new TransactionPostingService();
    }

    /**
     * Record a new income transaction with automatic recognition
     *
     * @param array $data
     * @return array
     */
    public function recordIncome(array $data)
    {
        DB::beginTransaction();
        try {
            Log::info('📊 Recording income transaction', [
                'income_source' => $data['income_source'] ?? 'N/A',
                'amount' => $data['amount'] ?? 0
            ]);

            // Validate input
            $this->validateIncomeData($data);

            // Prepare income record
            $incomeRecord = [
                'income_code' => $this->generateIncomeCode(),
                'income_date' => $data['income_date'] ?? now(),
                'income_category' => $data['income_category'],
                'income_source' => $data['income_source'],
                'description' => $data['description'],
                'reference_number' => $data['reference_number'] ?? $this->generateReferenceNumber(),
                'amount' => $data['amount'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'net_amount' => $data['net_amount'] ?? $data['amount'],
                'currency' => $data['currency'] ?? 'TZS',
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'bank_account_id' => $data['bank_account_id'],
                'income_account_id' => $data['income_account_id'],
                'received_from' => $data['received_from'] ?? '',
                'receipt_number' => $data['receipt_number'] ?? $this->generateReceiptNumber(),
                'notes' => $data['notes'] ?? '',
                'status' => 'received',
                'recognition_status' => 'recognized', // Automatic recognition
                'recognition_date' => now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Log::info('🔍 DEBUG: Prepared income record for insert', [
                'income_code' => $incomeRecord['income_code'],
                'income_source' => $incomeRecord['income_source'],
                'payment_method' => $incomeRecord['payment_method'],
                'net_amount' => $incomeRecord['net_amount'],
                'has_all_required_fields' => isset($incomeRecord['income_code'], $incomeRecord['income_source'], $incomeRecord['payment_method'], $incomeRecord['net_amount'])
            ]);

            // Insert income record
            $incomeId = DB::table('other_income')->insertGetId($incomeRecord);

            Log::info('🔍 DEBUG: Insert completed', [
                'income_id_generated' => $incomeId,
                'insert_successful' => $incomeId ? 'YES' : 'NO'
            ]);

            // Post to general ledger using TransactionPostingService
            $this->postIncomeToGL($incomeId, $incomeRecord);

            // Log audit trail
            $this->logAuditTrail('CREATE', $incomeId, $incomeRecord, null, 'Income transaction recorded');

            // Log before commit
            Log::info('🔍 DEBUG: About to commit transaction', [
                'income_id' => $incomeId,
                'income_code' => $incomeRecord['income_code']
            ]);

            DB::commit();

            // CRITICAL DEBUG: Check if record exists immediately after commit
            $recordExists = DB::table('other_income')->where('id', $incomeId)->exists();
            $recordCount = DB::table('other_income')->count();

            Log::info('🔍 DEBUG: Post-commit verification', [
                'income_id' => $incomeId,
                'record_exists' => $recordExists ? 'YES' : 'NO',
                'total_records_in_table' => $recordCount,
                'connection_name' => DB::connection()->getName(),
                'database_name' => DB::connection()->getDatabaseName()
            ]);

            if (!$recordExists) {
                Log::error('🚨 CRITICAL: Record does NOT exist after commit!', [
                    'income_id' => $incomeId,
                    'income_code' => $incomeRecord['income_code']
                ]);
            }

            Log::info('✅ Income transaction recorded successfully', [
                'income_id' => $incomeId,
                'reference_number' => $incomeRecord['reference_number']
            ]);

            return [
                'success' => true,
                'income_id' => $incomeId,
                'reference_number' => $incomeRecord['reference_number'],
                'message' => 'Income recorded and recognized successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error recording income', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verify automatic income recognition
     *
     * @param int $incomeId
     * @return array
     */
    public function verifyIncomeRecognition($incomeId)
    {
        try {
            $income = DB::table('other_income')->find($incomeId);

            if (!$income) {
                throw new \Exception("Income record not found: {$incomeId}");
            }

            // Check GL postings
            $glEntries = general_ledger::where('source_type', 'other_income')
                ->where('source_id', $incomeId)
                ->get();

            $isPosted = $glEntries->count() > 0;
            $isBalanced = false;

            if ($isPosted) {
                $totalDebits = $glEntries->sum('debit');
                $totalCredits = $glEntries->sum('credit');
                $isBalanced = abs($totalDebits - $totalCredits) < 0.01;
            }

            // Verify account balances match
            $bankAccount = AccountsModel::find($income->bank_account_id);
            $incomeAccount = AccountsModel::find($income->income_account_id);

            $verification = [
                'income_id' => $incomeId,
                'recognition_status' => $income->recognition_status ?? 'pending',
                'recognition_date' => $income->recognition_date,
                'is_posted_to_gl' => $isPosted,
                'is_balanced' => $isBalanced,
                'gl_entries_count' => $glEntries->count(),
                'total_debits' => $isPosted ? $totalDebits : 0,
                'total_credits' => $isPosted ? $totalCredits : 0,
                'bank_account' => [
                    'id' => $bankAccount->id ?? null,
                    'name' => $bankAccount->account_name ?? 'N/A',
                    'balance' => $bankAccount->balance ?? 0
                ],
                'income_account' => [
                    'id' => $incomeAccount->id ?? null,
                    'name' => $incomeAccount->account_name ?? 'N/A',
                    'balance' => $incomeAccount->balance ?? 0
                ],
                'verified_at' => now(),
                'status' => ($isPosted && $isBalanced) ? 'verified' : 'issues_found'
            ];

            Log::info('✅ Income recognition verified', $verification);

            return $verification;

        } catch (\Exception $e) {
            Log::error('❌ Error verifying income recognition', [
                'income_id' => $incomeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reverse/cancel an income transaction
     *
     * @param int $incomeId
     * @param string $reason
     * @return array
     */
    public function reverseIncome($incomeId, $reason)
    {
        DB::beginTransaction();
        try {
            Log::info('🔄 Reversing income transaction', [
                'income_id' => $incomeId,
                'reason' => $reason
            ]);

            $income = DB::table('other_income')->find($incomeId);

            if (!$income) {
                throw new \Exception("Income record not found: {$incomeId}");
            }

            if ($income->status === 'reversed') {
                throw new \Exception("Income transaction already reversed");
            }

            // Create reversal entry
            $reversalReference = 'REV-' . $income->reference_number . '-' . time();

            // Reverse GL entries
            $this->reverseGLEntries($incomeId, $reversalReference, $reason);

            // Update income status
            DB::table('other_income')
                ->where('id', $incomeId)
                ->update([
                    'status' => 'reversed',
                    'reversal_reason' => $reason,
                    'reversal_reference' => $reversalReference,
                    'reversed_at' => now(),
                    'reversed_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            // Log audit trail
            $this->logAuditTrail('REVERSE', $incomeId, null, $income, "Income reversed: {$reason}");

            DB::commit();

            Log::info('✅ Income transaction reversed successfully', [
                'income_id' => $incomeId,
                'reversal_reference' => $reversalReference
            ]);

            return [
                'success' => true,
                'income_id' => $incomeId,
                'reversal_reference' => $reversalReference,
                'message' => 'Income transaction reversed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error reversing income', [
                'income_id' => $incomeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record accrued income (income earned but not yet received)
     *
     * @param array $data
     * @return array
     */
    public function recordAccruedIncome(array $data)
    {
        DB::beginTransaction();
        try {
            Log::info('📋 Recording accrued income', [
                'income_source' => $data['income_source'] ?? 'N/A',
                'accrued_amount' => $data['accrued_amount'] ?? 0
            ]);

            // Prepare accrued income record
            $accruedRecord = [
                'income_code' => $this->generateIncomeCode(),
                'income_date' => $data['accrual_date'] ?? now(),
                'income_category' => $data['income_category'],
                'income_source' => $data['income_source'],
                'description' => $data['description'] . ' (Accrued)',
                'reference_number' => $data['reference_number'] ?? $this->generateReferenceNumber('ACCR'),
                'amount' => $data['accrued_amount'],
                'tax_amount' => 0,
                'net_amount' => $data['accrued_amount'],
                'currency' => $data['currency'] ?? 'TZS',
                'payment_method' => 'accrual',
                'income_account_id' => $data['income_account_id'],
                'notes' => $data['notes'] ?? '',
                'status' => 'accrued',
                'recognition_status' => 'accrued',
                'accrual_date' => $data['accrual_date'] ?? now(),
                'expected_receipt_date' => $data['expected_receipt_date'] ?? null,
                'is_accrued' => true,
                'accrued_by' => Auth::id(),
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $accruedId = DB::table('other_income')->insertGetId($accruedRecord);

            // Post accrual to GL: Debit Accrued Income (Asset), Credit Income
            $this->postAccruedIncomeToGL($accruedId, $accruedRecord);

            // Log audit trail
            $this->logAuditTrail('ACCRUE', $accruedId, $accruedRecord, null, 'Accrued income recorded');

            DB::commit();

            Log::info('✅ Accrued income recorded successfully', [
                'accrued_id' => $accruedId
            ]);

            return [
                'success' => true,
                'accrued_id' => $accruedId,
                'reference_number' => $accruedRecord['reference_number'],
                'message' => 'Accrued income recorded successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error recording accrued income', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Realize accrued income (when payment is received)
     *
     * @param int $accruedIncomeId
     * @param array $data
     * @return array
     */
    public function realizeAccruedIncome($accruedIncomeId, array $data)
    {
        DB::beginTransaction();
        try {
            $accruedIncome = DB::table('other_income')->find($accruedIncomeId);

            if (!$accruedIncome || $accruedIncome->status !== 'accrued') {
                throw new \Exception("Invalid accrued income record");
            }

            // Update status to received
            DB::table('other_income')
                ->where('id', $accruedIncomeId)
                ->update([
                    'status' => 'received',
                    'recognition_status' => 'recognized',
                    'bank_account_id' => $data['bank_account_id'],
                    'receipt_number' => $data['receipt_number'] ?? $this->generateReceiptNumber(),
                    'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                    'realization_date' => now(),
                    'updated_at' => now()
                ]);

            // Post realization: Debit Bank, Credit Accrued Income (Asset)
            $this->postAccruedIncomeRealizationToGL($accruedIncomeId, $data, $accruedIncome);

            // Log audit trail
            $this->logAuditTrail('REALIZE', $accruedIncomeId, $data, $accruedIncome, 'Accrued income realized');

            DB::commit();

            return [
                'success' => true,
                'message' => 'Accrued income realized successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error realizing accrued income', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate comprehensive income report
     *
     * @param array $filters
     * @return array
     */
    public function generateIncomeReport(array $filters = [])
    {
        try {
            Log::info('📊 Generating income report', $filters);

            $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            $query = DB::table('other_income')
                ->leftJoin('accounts as bank_account', 'other_income.bank_account_id', '=', 'bank_account.id')
                ->leftJoin('accounts as income_account', 'other_income.income_account_id', '=', 'income_account.id')
                ->whereBetween('other_income.income_date', [$dateFrom, $dateTo])
                ->select([
                    'other_income.*',
                    'bank_account.account_name as bank_account_name',
                    'bank_account.account_number as bank_account_number',
                    'income_account.account_name as income_account_name',
                    'income_account.account_number as income_account_number'
                ]);

            // Apply filters
            if (!empty($filters['income_category'])) {
                $query->where('other_income.income_category', $filters['income_category']);
            }

            if (!empty($filters['status'])) {
                $query->where('other_income.status', $filters['status']);
            }

            $incomeRecords = $query->orderBy('other_income.income_date', 'desc')->get();

            // Calculate statistics
            $statistics = $this->calculateIncomeStatistics($incomeRecords, $dateFrom, $dateTo);

            // Generate Excel file
            $fileName = 'income_report_' . Carbon::now()->format('Y_m_d_His') . '.xlsx';
            $filePath = $this->generateExcelReport($incomeRecords, $statistics, $dateFrom, $dateTo, $fileName);

            Log::info('✅ Income report generated', [
                'file_name' => $fileName,
                'records_count' => $incomeRecords->count()
            ]);

            return [
                'success' => true,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'statistics' => $statistics,
                'records_count' => $incomeRecords->count()
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error generating income report', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get income audit trail
     *
     * @param int|null $incomeId
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getAuditTrail($incomeId = null, array $filters = [])
    {
        try {
            $query = DB::table('income_audit_trail')
                ->leftJoin('users', 'income_audit_trail.user_id', '=', 'users.id')
                ->select([
                    'income_audit_trail.*',
                    'users.name as user_name',
                    'users.email as user_email'
                ]);

            if ($incomeId) {
                $query->where('income_audit_trail.income_id', $incomeId);
            }

            if (!empty($filters['action'])) {
                $query->where('income_audit_trail.action', $filters['action']);
            }

            if (!empty($filters['date_from'])) {
                $query->where('income_audit_trail.created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('income_audit_trail.created_at', '<=', $filters['date_to']);
            }

            return $query->orderBy('income_audit_trail.created_at', 'desc')->get();

        } catch (\Exception $e) {
            Log::error('❌ Error fetching audit trail', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Validate income data
     */
    private function validateIncomeData(array $data)
    {
        $required = ['income_category', 'income_source', 'amount', 'bank_account_id', 'income_account_id'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Required field missing: {$field}");
            }
        }

        if ($data['amount'] <= 0) {
            throw new \Exception("Amount must be greater than zero");
        }
    }

    /**
     * Post income to general ledger
     */
    private function postIncomeToGL($incomeId, array $incomeData)
    {
        $reference = 'INC-' . $incomeData['receipt_number'];
        $description = $incomeData['income_category'] . ' - ' . $incomeData['description'];

        // Use TransactionPostingService for proper double-entry
        $this->transactionPostingService->postTransaction([
            'first_account' => AccountsModel::find($incomeData['bank_account_id'])->account_number,
            'second_account' => AccountsModel::find($incomeData['income_account_id'])->account_number,
            'amount' => $incomeData['net_amount'],
            'narration' => $description,
            'reference_number' => $reference,
            'source_type' => 'other_income',
            'source_id' => $incomeId
        ]);

        Log::info('✅ Income posted to GL', [
            'income_id' => $incomeId,
            'reference' => $reference
        ]);
    }

    /**
     * Reverse GL entries
     */
    private function reverseGLEntries($incomeId, $reversalReference, $reason)
    {
        // Get income record to find GL entries
        $income = DB::table('other_income')->find($incomeId);
        if (!$income) {
            throw new \Exception("Income record not found for GL reversal");
        }

        // Find GL entries by narration pattern (income_category - description)
        $searchPattern = $income->income_category . ' - ' . $income->description;
        $originalEntries = general_ledger::where('narration', 'LIKE', '%' . $searchPattern . '%')
            ->orderBy('created_at', 'desc')
            ->limit(10) // Limit to recent entries
            ->get();

        Log::info('🔍 Found GL entries for reversal', [
            'income_id' => $incomeId,
            'search_pattern' => $searchPattern,
            'entries_found' => $originalEntries->count()
        ]);

        if ($originalEntries->isEmpty()) {
            Log::warning('⚠️ No GL entries found for income reversal', [
                'income_id' => $incomeId,
                'search_pattern' => $searchPattern
            ]);
            return; // No GL entries to reverse
        }

        foreach ($originalEntries as $entry) {
            // Use TransactionPostingService for proper reversal
            $this->transactionPostingService->postTransaction([
                'first_account' => $entry->record_on_account_number,
                'second_account' => $entry->sender_account_number == $entry->record_on_account_number ?
                    $entry->beneficiary_account_number : $entry->sender_account_number,
                'amount' => $entry->debit > 0 ? $entry->debit : $entry->credit,
                'narration' => "REVERSAL: {$reason} | Original Ref: {$entry->reference_number}",
                'reference_number' => $reversalReference,
                'source_account' => $entry->debit > 0 ? $entry->beneficiary_account_number : $entry->sender_account_number,
                'destination_account' => $entry->debit > 0 ? $entry->sender_account_number : $entry->beneficiary_account_number,
            ]);

            Log::info('✅ GL entry reversed', [
                'original_ref' => $entry->reference_number,
                'reversal_ref' => $reversalReference,
                'amount' => $entry->debit > 0 ? $entry->debit : $entry->credit
            ]);
        }
    }

    /**
     * Post accrued income to GL
     */
    private function postAccruedIncomeToGL($accruedId, array $accruedData)
    {
        $reference = 'ACCR-' . $accruedData['reference_number'];

        // Find or create Accrued Income Receivable account
        $accruedIncomeAccount = $this->getOrCreateAccruedIncomeAccount();

        // Debit: Accrued Income (Asset)
        // Credit: Income
        $this->transactionPostingService->postTransaction([
            'first_account' => $accruedIncomeAccount->account_number,
            'second_account' => AccountsModel::find($accruedData['income_account_id'])->account_number,
            'amount' => $accruedData['amount'],
            'narration' => $accruedData['description'],
            'reference_number' => $reference,
            'source_type' => 'accrued_income',
            'source_id' => $accruedId
        ]);
    }

    /**
     * Post accrued income realization to GL
     */
    private function postAccruedIncomeRealizationToGL($accruedId, array $data, $accruedIncome)
    {
        $reference = 'REAL-' . $data['receipt_number'];

        $accruedIncomeAccount = $this->getOrCreateAccruedIncomeAccount();

        // Debit: Bank
        // Credit: Accrued Income (Asset)
        $this->transactionPostingService->postTransaction([
            'first_account' => AccountsModel::find($data['bank_account_id'])->account_number,
            'second_account' => $accruedIncomeAccount->account_number,
            'amount' => $accruedIncome->amount,
            'narration' => "Realization of accrued income: {$accruedIncome->description}",
            'reference_number' => $reference,
            'source_type' => 'accrued_realization',
            'source_id' => $accruedId
        ]);
    }

    /**
     * Get or create Accrued Income Receivable account
     */
    private function getOrCreateAccruedIncomeAccount()
    {
        $account = AccountsModel::where('account_name', 'LIKE', '%ACCRUED%INCOME%RECEIVABLE%')
            ->where('type', 'asset_accounts')
            ->first();

        if (!$account) {
            // Create the account if it doesn't exist
            $account = AccountsModel::create([
                'account_name' => 'ACCRUED INCOME RECEIVABLE',
                'account_number' => '0101100012001210', // Adjust based on your chart of accounts
                'major_category_code' => '1000',
                'type' => 'asset_accounts',
                'account_type' => 'asset',
                'status' => 'ACTIVE',
                'balance' => 0,
                'account_level' => 3
            ]);
        }

        return $account;
    }

    /**
     * Log audit trail
     */
    private function logAuditTrail($action, $incomeId, $newData, $oldData, $description)
    {
        try {
            DB::table('income_audit_trail')->insert([
                'income_id' => $incomeId,
                'action' => $action,
                'description' => $description,
                'old_values' => $oldData ? json_encode($oldData) : null,
                'new_values' => $newData ? json_encode($newData) : null,
                'performed_by' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
            Log::warning('Failed to log audit trail', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate reference number
     */
    private function generateReferenceNumber($prefix = 'INC')
    {
        return $prefix . '-' . Carbon::now()->format('Ymd') . '-' . substr(uniqid(), -6);
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber()
    {
        $prefix = 'OI';
        $year = date('Y');
        $month = date('m');

        $lastReceipt = DB::table('other_income')
            ->where('receipt_number', 'like', "$prefix-$year$month-%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$prefix-$year$month-$newNumber";
    }

    /**
     * Generate unique income code
     */
    private function generateIncomeCode()
    {
        $prefix = 'INC';
        $year = date('Y');
        $month = date('m');
        $day = date('d');

        // Try to find the last income code for today
        $lastIncome = DB::table('other_income')
            ->where('income_code', 'like', "$prefix-$year$month$day-%")
            ->orderBy('income_code', 'desc')
            ->first();

        if ($lastIncome) {
            // Extract the sequential number and increment
            $lastNumber = intval(substr($lastIncome->income_code, -5));
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$prefix-$year$month$day-$newNumber";
    }

    /**
     * Calculate income statistics
     */
    private function calculateIncomeStatistics($incomeRecords, $dateFrom, $dateTo)
    {
        return [
            'total_income' => $incomeRecords->sum('net_amount'),
            'total_transactions' => $incomeRecords->count(),
            'average_income' => $incomeRecords->count() > 0 ? $incomeRecords->avg('net_amount') : 0,
            'by_category' => $incomeRecords->groupBy('income_category')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('net_amount')
                ];
            })->toArray(),
            'by_status' => $incomeRecords->groupBy('status')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('net_amount')
                ];
            })->toArray(),
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($incomeRecords, $statistics, $dateFrom, $dateTo, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income Report');

        // Header
        $sheet->setCellValue('A1', 'INCOME REPORT');
        $sheet->setCellValue('A2', 'Period: ' . $dateFrom . ' to ' . $dateTo);
        $sheet->setCellValue('A3', 'Generated: ' . Carbon::now()->format('Y-m-d H:i:s'));

        // Column headers
        $headers = [
            'A5' => 'Date',
            'B5' => 'Receipt #',
            'C5' => 'Reference',
            'D5' => 'Category',
            'E5' => 'Source',
            'F5' => 'Description',
            'G5' => 'Amount',
            'H5' => 'Tax',
            'I5' => 'Net Amount',
            'J5' => 'Status',
            'K5' => 'Bank Account',
            'L5' => 'Income Account'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Data rows
        $row = 6;
        foreach ($incomeRecords as $record) {
            $sheet->setCellValue('A' . $row, Carbon::parse($record->income_date)->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $record->receipt_number);
            $sheet->setCellValue('C' . $row, $record->reference_number);
            $sheet->setCellValue('D' . $row, $record->income_category);
            $sheet->setCellValue('E' . $row, $record->income_source);
            $sheet->setCellValue('F' . $row, $record->description);
            $sheet->setCellValue('G' . $row, $record->amount);
            $sheet->setCellValue('H' . $row, $record->tax_amount);
            $sheet->setCellValue('I' . $row, $record->net_amount);
            $sheet->setCellValue('J' . $row, $record->status);
            $sheet->setCellValue('K' . $row, $record->bank_account_name);
            $sheet->setCellValue('L' . $row, $record->income_account_name);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save file
        $filePath = storage_path('app/reports/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
