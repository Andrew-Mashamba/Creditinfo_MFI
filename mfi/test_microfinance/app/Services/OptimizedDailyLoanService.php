<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class OptimizedDailyLoanService
{
    private $chunkSize = 500; // Process 500 records at a time
    private $batchSize = 100; // Insert/Update 100 records at a time
    private $processDate;
    private $statistics = [
        'loans_processed' => 0,
        'repayments_processed' => 0,
        'total_amount_collected' => 0,
        'schedules_updated' => 0,
        'notifications_queued' => 0,
        'errors' => 0,
        'start_time' => null,
        'end_time' => null,
    ];
    
    public function __construct()
    {
        $this->processDate = Carbon::now();
        $this->statistics['start_time'] = microtime(true);
    }
    
    /**
     * Main method to process daily loan activities
     */
    public function processDailyActivities()
    {
        Log::info('🚀 Starting optimized daily loan processing for ' . $this->processDate->format('Y-m-d'));
        
        try {
            // Process in separate transactions for better performance
            $this->updateLoanArrearsInBatches();
            $this->processAutomaticRepaymentsInBatches();
            $this->updateLoanClassificationsInBatches();
            $this->queueNotifications();
            $this->generateOptimizedReports();
            
            $this->statistics['end_time'] = microtime(true);
            $duration = round($this->statistics['end_time'] - $this->statistics['start_time'], 2);
            
            Log::info('✅ Daily loan processing completed', [
                'duration_seconds' => $duration,
                'statistics' => $this->statistics
            ]);
            
            return $this->statistics;
            
        } catch (\Exception $e) {
            Log::error('❌ Error in daily loan processing: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update loan arrears using batch operations
     */
    private function updateLoanArrearsInBatches()
    {
        Log::info('📊 Updating loan arrears in batches...');
        
        $today = Carbon::now()->startOfDay();
        $updatedCount = 0;
        
        // Use raw query for better performance on large datasets
        DB::statement("
            UPDATE loans_schedules 
            SET 
                days_in_arrears = GREATEST(0, DATE_PART('day', ?::timestamp - installment_date)),
                amount_in_arrears = GREATEST(0, 
                    (COALESCE(interest, 0) + COALESCE(principle, 0)) - 
                    (COALESCE(interest_payment, 0) + COALESCE(principle_payment, 0))
                ),
                status = CASE 
                    WHEN (COALESCE(interest, 0) + COALESCE(principle, 0)) <= 
                         (COALESCE(interest_payment, 0) + COALESCE(principle_payment, 0)) THEN 'paid'
                    WHEN installment_date < ?::timestamp THEN 'overdue'
                    ELSE status
                END,
                updated_at = NOW()
            WHERE 
                installment_date <= ?::timestamp
                AND status IN ('pending', 'overdue', 'partially_paid')
        ", [$today, $today, $today]);
        
        // Update loan master records with aggregated arrears
        DB::statement("
            UPDATE loans 
            SET 
                total_arrears = subquery.total_arrears,
                days_in_arrears = subquery.max_days
            FROM (
                SELECT 
                    loan_id,
                    SUM(amount_in_arrears) as total_arrears,
                    MAX(days_in_arrears) as max_days
                FROM loans_schedules
                WHERE status IN ('overdue', 'partially_paid')
                GROUP BY loan_id
            ) AS subquery
            WHERE loans.loan_id = subquery.loan_id
                AND loans.loan_status = 'active'
        ");
        
        $this->statistics['schedules_updated'] = DB::table('loans_schedules')
            ->where('updated_at', '>=', $today)
            ->count();
        
        Log::info("✅ Updated {$this->statistics['schedules_updated']} loan schedules");
    }
    
    /**
     * Process automatic repayments in chunks
     */
    private function processAutomaticRepaymentsInBatches()
    {
        Log::info('💰 Processing automatic repayments in chunks...');
        
        $totalProcessed = 0;
        $totalAmount = 0;
        $batchInserts = [];
        $batchUpdates = [];
        
        // Get loans with overdue schedules using efficient query
        DB::table('loans')
            ->select([
                'loans.id',
                'loans.loan_id',
                'loans.client_id',
                'loans.branch_id',
                'loans.principle',
                DB::raw('(loans.principle - COALESCE(loans.total_principal_paid, 0)) as balance'),
                'accounts.id as account_id',
                'accounts.account_number',
                'accounts.balance as account_balance'
            ])
            ->join('accounts', function($join) {
                $join->on('accounts.client_number', '=', 'loans.client_number')
                     ->where('accounts.product_number', '=', '3000')
                     ->where('accounts.status', '=', 'active')
                     ->where('accounts.balance', '>', 0);
            })
            ->where('loans.loan_status', 'active')
            ->whereNotNull('loans.disbursement_date')
            ->where('loans.days_in_arrears', '>', 0)
            ->orderBy('loans.days_in_arrears', 'desc')
            ->chunk($this->chunkSize, function ($loans) use (&$totalProcessed, &$totalAmount, &$batchInserts, &$batchUpdates) {
                
                foreach ($loans as $loan) {
                    $availableBalance = $loan->account_balance;
                    
                    // Get overdue schedules for this loan
                    $schedules = DB::table('loans_schedules')
                        ->where('loan_id', $loan->loan_id)
                        ->whereIn('status', ['overdue', 'partially_paid'])
                        ->where('amount_in_arrears', '>', 0)
                        ->orderBy('installment_date', 'asc')
                        ->get();
                    
                    foreach ($schedules as $schedule) {
                        if ($availableBalance <= 0) break;
                        
                        $paymentAmount = min($availableBalance, $schedule->amount_in_arrears);
                        $interestDue = ($schedule->interest ?? 0) - ($schedule->interest_paid ?? 0);
                        $principalDue = ($schedule->principle ?? 0) - ($schedule->principle_paid ?? 0);
                        
                        $interestPayment = min($paymentAmount, $interestDue);
                        $principalPayment = min($paymentAmount - $interestPayment, $principalDue);
                        
                        if ($paymentAmount > 0) {
                            // Collect payment record for batch insert
                            $batchInserts[] = [
                                'loan_id' => $loan->loan_id,
                                'member_id' => $loan->client_id,
                                'payment_date' => now(),
                                'amount' => $paymentAmount,
                                'principal_amount' => $principalPayment,
                                'interest_amount' => $interestPayment,
                                'payment_method' => 'auto_deduction',
                                'payment_reference' => 'AUTO_' . now()->format('YmdHis') . '_' . $loan->loan_id,
                                'status' => 'completed',
                                'processed_by' => 'SYSTEM',
                                'notes' => "Automatic repayment from account {$loan->account_number}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            
                            // Update schedule
                            DB::table('loans_schedules')
                                ->where('id', $schedule->id)
                                ->update([
                                    'interest_payment' => DB::raw("interest_payment + {$interestPayment}"),
                                    'principle_payment' => DB::raw("principle_payment + {$principalPayment}"),
                                    'amount_in_arrears' => DB::raw("GREATEST(0, amount_in_arrears - {$paymentAmount})"),
                                    'status' => DB::raw("CASE WHEN amount_in_arrears <= {$paymentAmount} THEN 'paid' ELSE 'partially_paid' END"),
                                    'updated_at' => now()
                                ]);
                            
                            $availableBalance -= $paymentAmount;
                            $totalAmount += $paymentAmount;
                            $totalProcessed++;
                        }
                    }
                    
                    // Update account balance
                    if ($loan->account_balance > $availableBalance) {
                        $deductedAmount = $loan->account_balance - $availableBalance;
                        
                        DB::table('accounts')
                            ->where('id', $loan->account_id)
                            ->decrement('balance', $deductedAmount);
                        
                        // Update loan totals
                        DB::table('loans')
                            ->where('id', $loan->id)
                            ->update([
                                'total_interest_paid' => DB::raw("total_interest_paid + {$deductedAmount}"),
                                'total_principal_paid' => DB::raw("total_principal_paid + {$deductedAmount}"),
                                'updated_at' => now()
                            ]);
                    }
                    
                    // Insert payments in batches
                    if (count($batchInserts) >= $this->batchSize) {
                        DB::table('loan_payments')->insert($batchInserts);
                        $batchInserts = [];
                    }
                }
            });
        
        // Insert remaining payment records
        if (!empty($batchInserts)) {
            DB::table('loan_payments')->insert($batchInserts);
        }
        
        $this->statistics['repayments_processed'] = $totalProcessed;
        $this->statistics['total_amount_collected'] = $totalAmount;
        
        Log::info("✅ Processed {$totalProcessed} automatic repayments totaling " . number_format($totalAmount, 2));
    }
    
    /**
     * Update loan classifications in bulk
     */
    private function updateLoanClassificationsInBatches()
    {
        Log::info('🏷️ Updating loan classifications...');
        
        // Use CASE statement for bulk update
        DB::statement("
            UPDATE loans 
            SET loan_classification = CASE
                WHEN days_in_arrears = 0 THEN 'PERFORMING'
                WHEN days_in_arrears BETWEEN 1 AND 30 THEN 'WATCH'
                WHEN days_in_arrears BETWEEN 31 AND 90 THEN 'SUBSTANDARD'
                WHEN days_in_arrears BETWEEN 91 AND 180 THEN 'DOUBTFUL'
                WHEN days_in_arrears > 180 THEN 'LOSS'
                ELSE loan_classification
            END,
            loan_status = CASE
                WHEN (principle - COALESCE(total_principal_paid, 0)) <= 0 THEN 'closed'
                ELSE loan_status
            END,
            closure_date = CASE
                WHEN (principle - COALESCE(total_principal_paid, 0)) <= 0 
                    AND closure_date IS NULL THEN NOW()
                ELSE closure_date
            END,
            updated_at = NOW()
            WHERE loan_status = 'active'
        ");
        
        $this->statistics['loans_processed'] = DB::table('loans')
            ->where('loan_status', 'active')
            ->count();
        
        Log::info("✅ Updated classifications for {$this->statistics['loans_processed']} loans");
    }
    
    /**
     * Queue notifications for batch processing
     */
    private function queueNotifications()
    {
        Log::info('📧 Queueing member notifications...');
        
        // Get all members who had automatic repayments today
        $notifications = DB::table('loan_payments as lp')
            ->join('loans as l', 'lp.loan_id', '=', 'l.loan_id')
            ->join('clients as c', 'l.client_id', '=', 'c.id')
            ->leftJoin('users as u', 'c.client_number', '=', 'u.institution_user_id')
            ->select([
                'c.id as client_id',
                'c.client_number',
                'c.first_name',
                'c.last_name',
                'c.email',
                'c.mobile_phone_number',
                'u.id as user_id',
                'l.loan_id',
                DB::raw('SUM(lp.amount) as total_amount'),
                DB::raw('COUNT(lp.id) as payment_count'),
                DB::raw('(l.principle - COALESCE(l.total_principal_paid, 0)) as remaining_balance')
            ])
            ->whereDate('lp.payment_date', Carbon::today())
            ->where('lp.payment_method', 'auto_deduction')
            ->groupBy('c.id', 'c.client_number', 'c.first_name', 'c.last_name', 
                     'c.email', 'c.mobile_phone_number', 'u.id', 'l.loan_id', 'l.principle', 
                     'l.total_principal_paid')
            ->orderBy('c.id')
            ->chunk($this->chunkSize, function ($members) {
                $notificationBatch = [];
                
                foreach ($members as $member) {
                    if ($member->user_id) {
                        $message = "Dear {$member->first_name} {$member->last_name},\n\n";
                        $message .= "Automatic loan repayment processed:\n";
                        $message .= "Loan ID: {$member->loan_id}\n";
                        $message .= "Amount: TZS " . number_format($member->total_amount, 2) . "\n";
                        $message .= "Remaining Balance: TZS " . number_format($member->remaining_balance, 2) . "\n";
                        $message .= "Date: " . Carbon::now()->format('Y-m-d') . "\n\n";
                        $message .= "Thank you for maintaining your loan repayments.";
                        
                        $notificationBatch[] = [
                            'user_id' => $member->user_id,
                            'type' => 'loan_repayment',
                            'title' => 'Automatic Loan Repayment',
                            'message' => $message,
                            'status' => 'unread',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    
                    // Queue email notification if email exists
                    if ($member->email) {
                        dispatch(new \App\Jobs\SendRepaymentEmail($member));
                    }
                    
                    // Queue SMS notification if mobile exists
                    if ($member->mobile_phone_number) {
                        dispatch(new \App\Jobs\SendRepaymentSMS($member));
                    }
                }
                
                // Batch insert notifications
                if (!empty($notificationBatch)) {
                    DB::table('notifications')->insert($notificationBatch);
                    $this->statistics['notifications_queued'] += count($notificationBatch);
                }
            });
        
        Log::info("✅ Queued {$this->statistics['notifications_queued']} notifications");
    }
    
    /**
     * Generate optimized reports using data streaming
     */
    private function generateOptimizedReports()
    {
        Log::info('📊 Generating optimized reports...');
        
        // Dispatch report generation to queue for background processing
        dispatch(new \App\Jobs\GenerateDailyLoanReports($this->processDate, $this->statistics));
        
        Log::info('✅ Report generation queued for background processing');
    }
    
    /**
     * Get processing statistics
     */
    public function getStatistics()
    {
        return $this->statistics;
    }
}