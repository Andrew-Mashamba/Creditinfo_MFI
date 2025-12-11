<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountsModel;
use Exception;

class SavingsInterestPaymentService
{
    protected $taxRate;

    public function __construct()
    {
        // Tax withholding rate (Tanzania: 15%)
        $this->taxRate = config('interest.tax_rate', 15.00);
    }

    /**
     * Process annual interest payments for all savings accounts
     * This should run once per year, typically at year-end
     *
     * @param int $year The year to process payments for
     * @param Carbon|null $paymentDate The date to post the payment (defaults to today)
     * @return array Processing results
     */
    public function processAnnualPayments($year, $paymentDate = null)
    {
        try {
            $paymentDate = $paymentDate ?? Carbon::today();

            Log::info('💰 Starting annual interest payment processing', [
                'year' => $year,
                'payment_date' => $paymentDate->format('Y-m-d')
            ]);

            DB::beginTransaction();

            // Check if payments already processed for this year
            $existingPayments = DB::table('interest_payments')
                ->where('year', $year)
                ->where('status', 'PAID')
                ->count();

            if ($existingPayments > 0) {
                Log::warning('⚠️ Interest payments already processed for this year', [
                    'year' => $year,
                    'existing_records' => $existingPayments
                ]);
                DB::rollBack();
                return [
                    'status' => 'skipped',
                    'message' => 'Payments already processed for this year',
                    'year' => $year,
                    'existing_records' => $existingPayments
                ];
            }

            // Get all accounts with accrued interest for the year
            $accountsWithInterest = DB::table('interest_accruals')
                ->select('account_id', 'account_number', DB::raw('SUM(interest_amount) as total_interest'))
                ->where('year', $year)
                ->where('status', 'ACCRUED')
                ->groupBy('account_id', 'account_number')
                ->having('total_interest', '>', 0)
                ->get();

            $processedCount = 0;
            $totalGrossInterest = 0;
            $totalTaxWithheld = 0;
            $totalNetInterest = 0;
            $errors = [];

            foreach ($accountsWithInterest as $accountData) {
                try {
                    $result = $this->processPaymentForAccount(
                        $accountData->account_id,
                        $accountData->account_number,
                        $year,
                        $paymentDate
                    );

                    if ($result['status'] === 'success') {
                        $processedCount++;
                        $totalGrossInterest += $result['gross_interest'];
                        $totalTaxWithheld += $result['tax_withheld'];
                        $totalNetInterest += $result['net_interest'];
                    }
                } catch (Exception $e) {
                    $errors[] = [
                        'account_number' => $accountData->account_number,
                        'error' => $e->getMessage()
                    ];
                    Log::error('Error processing interest payment for account', [
                        'account_number' => $accountData->account_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $summary = [
                'status' => 'success',
                'year' => $year,
                'payment_date' => $paymentDate->format('Y-m-d'),
                'total_accounts' => $accountsWithInterest->count(),
                'processed_accounts' => $processedCount,
                'total_gross_interest' => round($totalGrossInterest, 2),
                'total_tax_withheld' => round($totalTaxWithheld, 2),
                'total_net_interest' => round($totalNetInterest, 2),
                'errors_count' => count($errors),
                'errors' => $errors
            ];

            Log::info('✅ Annual interest payment processing completed', $summary);

            return $summary;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Annual interest payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process interest payment for a single account
     *
     * @param int $accountId
     * @param string $accountNumber
     * @param int $year
     * @param Carbon $paymentDate
     * @return array Result of payment
     */
    protected function processPaymentForAccount($accountId, $accountNumber, $year, $paymentDate)
    {
        // Get account details
        $account = AccountsModel::find($accountId);
        if (!$account) {
            throw new Exception("Account not found: {$accountNumber}");
        }

        // Calculate period
        $periodStart = Carbon::create($year, 1, 1);
        $periodEnd = Carbon::create($year, 12, 31);

        // Get total accrued interest for the year
        $accruals = DB::table('interest_accruals')
            ->where('account_number', $accountNumber)
            ->where('year', $year)
            ->where('status', 'ACCRUED')
            ->get();

        $grossInterest = $accruals->sum('interest_amount');

        if ($grossInterest <= 0) {
            throw new Exception("No accrued interest found for account {$accountNumber} in year {$year}");
        }

        // Calculate tax withholding
        $taxWithheld = $grossInterest * ($this->taxRate / 100);
        $netInterest = $grossInterest - $taxWithheld;

        // Generate reference number
        $referenceNumber = $this->generateReferenceNumber($year, $accountNumber);

        // Create interest payment record
        $paymentId = DB::table('interest_payments')->insertGetId([
            'account_id' => $accountId,
            'account_number' => $accountNumber,
            'client_number' => $account->client_number,
            'year' => $year,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'gross_interest' => round($grossInterest, 2),
            'tax_rate' => $this->taxRate,
            'tax_withheld' => round($taxWithheld, 2),
            'net_interest' => round($netInterest, 2),
            'payment_date' => $paymentDate->format('Y-m-d'),
            'reference_number' => $referenceNumber,
            'status' => 'PAID',
            'notes' => "Annual interest payment for {$year} - {$accruals->count()} days accrued",
            'posted_by' => auth()->id() ?? 1,
            'posted_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Post transaction to account (credit the savings account with net interest)
        $transactionRef = $this->postInterestTransaction(
            $account,
            $netInterest,
            $taxWithheld,
            $referenceNumber,
            $year
        );

        // Update payment record with transaction reference
        DB::table('interest_payments')
            ->where('id', $paymentId)
            ->update([
                'transaction_reference' => $transactionRef,
                'updated_at' => now()
            ]);

        // Update account balance
        $account->balance += $netInterest;
        $account->save();

        // Mark all accruals as PAID
        DB::table('interest_accruals')
            ->where('account_number', $accountNumber)
            ->where('year', $year)
            ->where('status', 'ACCRUED')
            ->update([
                'status' => 'PAID',
                'updated_at' => now()
            ]);

        Log::info('💳 Interest payment processed', [
            'account_number' => $accountNumber,
            'gross_interest' => round($grossInterest, 2),
            'tax_withheld' => round($taxWithheld, 2),
            'net_interest' => round($netInterest, 2),
            'reference' => $referenceNumber
        ]);

        return [
            'status' => 'success',
            'account_number' => $accountNumber,
            'gross_interest' => round($grossInterest, 2),
            'tax_withheld' => round($taxWithheld, 2),
            'net_interest' => round($netInterest, 2),
            'reference_number' => $referenceNumber,
            'days_accrued' => $accruals->count()
        ];
    }

    /**
     * Post interest transaction to general ledger
     *
     * @param AccountsModel $account
     * @param float $netInterest
     * @param float $taxWithheld
     * @param string $referenceNumber
     * @param int $year
     * @return string Transaction reference
     */
    protected function postInterestTransaction($account, $netInterest, $taxWithheld, $referenceNumber, $year)
    {
        $transactionRef = 'INT-' . $referenceNumber;

        // Entry 1: Debit Interest Expense Account
        DB::table('general_ledger')->insert([
            'account_number' => 'INTEREST_EXPENSE', // Configure this in your chart of accounts
            'reference_number' => $transactionRef,
            'debit' => round($netInterest + $taxWithheld, 2),
            'credit' => 0,
            'balance' => 0, // Update with actual balance
            'narration' => "Interest payment for year {$year} - Account {$account->account_number}",
            'transaction_type' => 'INTEREST_PAYMENT',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Entry 2: Credit Customer Savings Account (Net Interest)
        DB::table('general_ledger')->insert([
            'account_number' => $account->account_number,
            'reference_number' => $transactionRef,
            'debit' => 0,
            'credit' => round($netInterest, 2),
            'balance' => $account->balance + $netInterest,
            'narration' => "Interest earned for year {$year} (after tax)",
            'transaction_type' => 'INTEREST_PAYMENT',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Entry 3: Credit Tax Payable Account (Tax Withheld)
        DB::table('general_ledger')->insert([
            'account_number' => 'TAX_PAYABLE', // Configure this in your chart of accounts
            'reference_number' => $transactionRef,
            'debit' => 0,
            'credit' => round($taxWithheld, 2),
            'balance' => 0, // Update with actual balance
            'narration' => "Withholding tax on interest - {$this->taxRate}% - Account {$account->account_number}",
            'transaction_type' => 'TAX_WITHHOLDING',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $transactionRef;
    }

    /**
     * Generate unique reference number for payment
     *
     * @param int $year
     * @param string $accountNumber
     * @return string
     */
    protected function generateReferenceNumber($year, $accountNumber)
    {
        return 'IP-' . $year . '-' . $accountNumber . '-' . time();
    }

    /**
     * Get payment history for an account
     *
     * @param string $accountNumber
     * @return array
     */
    public function getPaymentHistory($accountNumber)
    {
        $payments = DB::table('interest_payments')
            ->where('account_number', $accountNumber)
            ->orderBy('year', 'desc')
            ->get();

        return [
            'account_number' => $accountNumber,
            'total_payments' => $payments->count(),
            'total_gross_interest' => round($payments->sum('gross_interest'), 2),
            'total_tax_withheld' => round($payments->sum('tax_withheld'), 2),
            'total_net_interest' => round($payments->sum('net_interest'), 2),
            'payments' => $payments
        ];
    }

    /**
     * Get summary of payments for a year
     *
     * @param int $year
     * @return array
     */
    public function getYearSummary($year)
    {
        $summary = DB::table('interest_payments')
            ->where('year', $year)
            ->selectRaw('
                COUNT(DISTINCT account_number) as total_accounts,
                SUM(gross_interest) as total_gross,
                SUM(tax_withheld) as total_tax,
                SUM(net_interest) as total_net,
                MIN(payment_date) as first_payment_date,
                MAX(payment_date) as last_payment_date
            ')
            ->first();

        return [
            'year' => $year,
            'total_accounts' => $summary->total_accounts ?? 0,
            'total_gross_interest' => round($summary->total_gross ?? 0, 2),
            'total_tax_withheld' => round($summary->total_tax ?? 0, 2),
            'total_net_interest' => round($summary->total_net ?? 0, 2),
            'first_payment_date' => $summary->first_payment_date,
            'last_payment_date' => $summary->last_payment_date
        ];
    }

    /**
     * Reverse/cancel a payment (for corrections)
     *
     * @param string $referenceNumber
     * @return array
     */
    public function reversePayment($referenceNumber)
    {
        try {
            DB::beginTransaction();

            // Get payment record
            $payment = DB::table('interest_payments')
                ->where('reference_number', $referenceNumber)
                ->where('status', 'PAID')
                ->first();

            if (!$payment) {
                throw new Exception("Payment not found or already reversed: {$referenceNumber}");
            }

            // Reverse account balance
            $account = AccountsModel::where('account_number', $payment->account_number)->first();
            if ($account) {
                $account->balance -= $payment->net_interest;
                $account->save();
            }

            // Post reversal transactions
            $reversalRef = 'REV-' . $payment->transaction_reference;

            DB::table('general_ledger')->insert([
                'account_number' => $payment->account_number,
                'reference_number' => $reversalRef,
                'debit' => round($payment->net_interest, 2),
                'credit' => 0,
                'balance' => $account->balance ?? 0,
                'narration' => "Reversal of interest payment - {$payment->reference_number}",
                'transaction_type' => 'INTEREST_REVERSAL',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update payment status
            DB::table('interest_payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => 'REVERSED',
                    'notes' => ($payment->notes ?? '') . ' | REVERSED on ' . now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()
                ]);

            // Restore accruals to ACCRUED status
            DB::table('interest_accruals')
                ->where('account_number', $payment->account_number)
                ->where('year', $payment->year)
                ->where('status', 'PAID')
                ->update([
                    'status' => 'ACCRUED',
                    'updated_at' => now()
                ]);

            DB::commit();

            Log::info('Payment reversed successfully', [
                'reference_number' => $referenceNumber,
                'account_number' => $payment->account_number,
                'amount' => $payment->net_interest
            ]);

            return [
                'status' => 'success',
                'message' => 'Payment reversed successfully',
                'reference_number' => $referenceNumber,
                'reversal_reference' => $reversalRef
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reverse payment', [
                'reference_number' => $referenceNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
