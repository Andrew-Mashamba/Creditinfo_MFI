<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoanLossProvisionService
{
    protected $provisionRates;
    protected $reportDate;
    
    public function __construct()
    {
        $this->reportDate = Carbon::now();
        $this->loadProvisionRates();
    }
    
    /**
     * Load provision rates from configuration
     */
    private function loadProvisionRates()
    {
        $this->provisionRates = Cache::remember('provision_rates', 3600, function () {
            return DB::table('provision_rates_config')
                ->where('is_active', true)
                ->pluck('provision_rate', 'classification')
                ->toArray();
        });
    }
    
    /**
     * Calculate and update daily loan loss provisions
     */
    public function calculateDailyProvisions($date = null)
    {
        $this->reportDate = $date ? Carbon::parse($date) : Carbon::now();
        
        try {
            DB::beginTransaction();
            
            Log::info("📊 Starting loan loss provisions calculation for " . $this->reportDate->format('Y-m-d'));
            
            // Clear today's provisions to recalculate
            $this->clearExistingProvisions();
            
            // Calculate provisions for all active loans
            $statistics = $this->calculateProvisionsByClassification();
            
            // Store individual loan provisions
            $this->storeIndividualProvisions();
            
            // Generate summary report
            $summary = $this->generateProvisionSummary($statistics);
            
            // Post provisions to general ledger
            $this->postProvisionsToGL($summary);
            
            DB::commit();
            
            Log::info("✅ Loan loss provisions calculated successfully");
            Log::info("📈 Total provisions: TZS " . number_format($summary['total_provisions'], 2));
            
            return $summary;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error calculating loan loss provisions: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Clear existing provisions for the date
     */
    private function clearExistingProvisions()
    {
        DB::table('loan_loss_provisions')
            ->where('provision_date', $this->reportDate->format('Y-m-d'))
            ->delete();
    }
    
    /**
     * Calculate provisions by loan classification
     */
    private function calculateProvisionsByClassification()
    {
        $statistics = [
            'total_loans' => 0,
            'total_outstanding' => 0,
            'classifications' => [],
            'provisions' => []
        ];
        
        // Get all active loans with their classifications
        $loans = DB::table('loans')
            ->select([
                'loan_id',
                'client_number',
                'loan_classification',
                'days_in_arrears',
                DB::raw('principle - COALESCE(total_principal_paid, 0) as outstanding_balance')
            ])
            ->where('loan_status', 'active')
            ->whereNotNull('disbursement_date')
            ->get();
        
        foreach ($loans as $loan) {
            $statistics['total_loans']++;
            $statistics['total_outstanding'] += $loan->outstanding_balance;
            
            // Get provision rate for this classification
            $provisionRate = $this->provisionRates[$loan->loan_classification] ?? 1.0;
            
            // Calculate provision amount
            $provisionAmount = ($loan->outstanding_balance * $provisionRate) / 100;
            
            // Track by classification
            if (!isset($statistics['classifications'][$loan->loan_classification])) {
                $statistics['classifications'][$loan->loan_classification] = [
                    'count' => 0,
                    'balance' => 0,
                    'provision' => 0
                ];
            }
            
            $statistics['classifications'][$loan->loan_classification]['count']++;
            $statistics['classifications'][$loan->loan_classification]['balance'] += $loan->outstanding_balance;
            $statistics['classifications'][$loan->loan_classification]['provision'] += $provisionAmount;
            
            // Store provision details
            $statistics['provisions'][] = [
                'loan_id' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'classification' => $loan->loan_classification,
                'outstanding' => $loan->outstanding_balance,
                'rate' => $provisionRate,
                'amount' => $provisionAmount,
                'days_in_arrears' => $loan->days_in_arrears
            ];
        }
        
        return $statistics;
    }
    
    /**
     * Store individual loan provisions
     */
    private function storeIndividualProvisions()
    {
        // Process in chunks for better performance
        DB::table('loans')
            ->select([
                'loan_id',
                'client_number',
                'loan_classification',
                'days_in_arrears',
                DB::raw('principle - COALESCE(total_principal_paid, 0) as outstanding_balance')
            ])
            ->where('loan_status', 'active')
            ->whereNotNull('disbursement_date')
            ->orderBy('days_in_arrears', 'desc')
            ->chunk(500, function ($loans) {
                $provisions = [];
                
                foreach ($loans as $loan) {
                    // Get previous provision for this loan
                    $previousProvision = DB::table('loan_loss_provisions')
                        ->where('loan_id', $loan->loan_id)
                        ->where('provision_date', '<', $this->reportDate)
                        ->orderBy('provision_date', 'desc')
                        ->value('provision_amount') ?? 0;
                    
                    $provisionRate = $this->provisionRates[$loan->loan_classification] ?? 1.0;
                    $provisionAmount = ($loan->outstanding_balance * $provisionRate) / 100;
                    $adjustment = $provisionAmount - $previousProvision;
                    
                    $provisions[] = [
                        'provision_date' => $this->reportDate->format('Y-m-d'),
                        'loan_id' => $loan->loan_id,
                        'client_number' => $loan->client_number,
                        'loan_classification' => $loan->loan_classification,
                        'outstanding_balance' => $loan->outstanding_balance,
                        'provision_rate' => $provisionRate,
                        'provision_amount' => $provisionAmount,
                        'previous_provision' => $previousProvision,
                        'provision_adjustment' => $adjustment,
                        'provision_type' => $loan->loan_classification === 'PERFORMING' ? 'general' : 'specific',
                        'days_in_arrears' => $loan->days_in_arrears,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    // Insert in batches of 100
                    if (count($provisions) >= 100) {
                        DB::table('loan_loss_provisions')->insert($provisions);
                        $provisions = [];
                    }
                }
                
                // Insert remaining provisions
                if (!empty($provisions)) {
                    DB::table('loan_loss_provisions')->insert($provisions);
                }
            });
    }
    
    /**
     * Generate provision summary
     */
    private function generateProvisionSummary($statistics)
    {
        $summary = [
            'summary_date' => $this->reportDate->format('Y-m-d'),
            'total_loans' => $statistics['total_loans'],
            'total_outstanding' => $statistics['total_outstanding']
        ];
        
        // Initialize classification balances
        $classifications = ['PERFORMING', 'WATCH', 'SUBSTANDARD', 'DOUBTFUL', 'LOSS'];
        foreach ($classifications as $class) {
            $key = strtolower($class) . '_balance';
            $summary[$key] = $statistics['classifications'][$class]['balance'] ?? 0;
        }
        
        // Calculate provisions
        $summary['general_provisions'] = $statistics['classifications']['PERFORMING']['provision'] ?? 0;
        $summary['specific_provisions'] = 0;
        
        foreach (['WATCH', 'SUBSTANDARD', 'DOUBTFUL', 'LOSS'] as $class) {
            if (isset($statistics['classifications'][$class])) {
                $summary['specific_provisions'] += $statistics['classifications'][$class]['provision'];
            }
        }
        
        $summary['total_provisions'] = $summary['general_provisions'] + $summary['specific_provisions'];
        
        // Calculate NPL (Non-Performing Loans) - loans over 90 days
        $nplBalance = ($summary['doubtful_balance'] ?? 0) + ($summary['loss_balance'] ?? 0);
        $summary['npl_ratio'] = $summary['total_outstanding'] > 0 
            ? ($nplBalance / $summary['total_outstanding'] * 100) 
            : 0;
        
        // Calculate provision coverage ratio
        $summary['provision_coverage_ratio'] = $nplBalance > 0 
            ? ($summary['total_provisions'] / $nplBalance * 100) 
            : 100;
        
        // Store additional statistics as JSON
        $summary['statistics'] = json_encode([
            'by_classification' => $statistics['classifications'],
            'calculation_date' => now()->toISOString(),
            'provision_rates' => $this->provisionRates
        ]);
        
        $summary['created_at'] = now();
        $summary['updated_at'] = now();
        
        // Save summary to database
        DB::table('loan_loss_provision_summary')
            ->updateOrInsert(
                ['summary_date' => $summary['summary_date']],
                $summary
            );
        
        return $summary;
    }
    
    /**
     * Advise on provisions to post to general ledger
     */
    private function postProvisionsToGL($summary)
    {
        try {
            // Get today's provision adjustment
            $adjustment = DB::table('loan_loss_provisions')
                ->where('provision_date', $this->reportDate->format('Y-m-d'))
                ->sum('provision_adjustment');
            
            if (abs($adjustment) > 0.01) { // Only advise if there's a significant change
                $description = "Loan loss provision adjustment for " . $this->reportDate->format('Y-m-d');
                
                // Store the journal entry advice for review
                $journalAdvice = [
                    'date' => $this->reportDate->format('Y-m-d'),
                    'type' => $adjustment > 0 ? 'PROVISION_INCREASE' : 'PROVISION_REVERSAL',
                    'amount' => abs($adjustment),
                    'description' => $description,
                    'status' => 'pending_approval',
                    'created_at' => now()
                ];
                
                if ($adjustment > 0) {
                    // Advise: Increase in provisions (expense)
                    $journalAdvice['debit_account'] = 'loan_loss_provision_expense';
                    $journalAdvice['credit_account'] = 'loan_loss_provision_reserve';
                    $journalAdvice['narrative'] = "ADVICE: Debit Provision Expense Account and Credit Provision Reserve Account";
                    
                    Log::info("📋 JOURNAL ADVICE: Provision increase of TZS " . number_format($adjustment, 2) . " requires posting");
                    Log::info("   Debit: Loan Loss Provision Expense - TZS " . number_format($adjustment, 2));
                    Log::info("   Credit: Loan Loss Provision Reserve - TZS " . number_format($adjustment, 2));
                } else {
                    // Advise: Decrease in provisions (reversal)
                    $journalAdvice['debit_account'] = 'loan_loss_provision_reserve';
                    $journalAdvice['credit_account'] = 'loan_loss_provision_expense';
                    $journalAdvice['narrative'] = "ADVICE: Debit Provision Reserve Account and Credit Provision Expense Account";
                    
                    Log::info("📋 JOURNAL ADVICE: Provision reversal of TZS " . number_format(abs($adjustment), 2) . " requires posting");
                    Log::info("   Debit: Loan Loss Provision Reserve - TZS " . number_format(abs($adjustment), 2));
                    Log::info("   Credit: Loan Loss Provision Expense - TZS " . number_format(abs($adjustment), 2));
                }
                
                // Store the journal advice in the summary for user review
                DB::table('loan_loss_provision_summary')
                    ->where('summary_date', $this->reportDate->format('Y-m-d'))
                    ->update([
                        'statistics' => DB::raw("jsonb_set(COALESCE(statistics, '{}')::jsonb, '{journal_advice}', '" . json_encode($journalAdvice) . "'::jsonb)")
                    ]);
                
                // Send notification to finance team
                $this->notifyFinanceTeam($journalAdvice);
            }
            
        } catch (\Exception $e) {
            Log::error("Error preparing provision journal advice: " . $e->getMessage());
            // Don't throw - allow provisions to be calculated even if advice fails
        }
    }
    
    /**
     * Notify finance team about pending journal entries
     */
    private function notifyFinanceTeam($journalAdvice)
    {
        try {
            $financeUsers = DB::table('users')
                ->whereIn('role', ['admin', 'finance_manager', 'accountant'])
                ->where('status', 'active')
                ->get();
            
            foreach ($financeUsers as $user) {
                if (class_exists(\App\Services\NotificationService::class)) {
                    $notificationService = app(\App\Services\NotificationService::class);
                    
                    $message = "Journal Entry Required for Loan Loss Provisions\n\n";
                    $message .= "Date: " . $journalAdvice['date'] . "\n";
                    $message .= "Amount: TZS " . number_format($journalAdvice['amount'], 2) . "\n";
                    $message .= "Type: " . str_replace('_', ' ', $journalAdvice['type']) . "\n\n";
                    $message .= $journalAdvice['narrative'] . "\n\n";
                    $message .= "Please review and post this journal entry in the general ledger.";
                    
                    $notificationService->createNotification(
                        $user->id,
                        'journal_advice',
                        'Loan Loss Provision Journal Entry Required',
                        $message
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Error notifying finance team: " . $e->getMessage());
        }
    }
    
    /**
     * Get provision trends for reporting
     */
    public function getProvisionTrends($days = 30)
    {
        return DB::table('loan_loss_provision_summary')
            ->where('summary_date', '>=', Carbon::now()->subDays($days))
            ->orderBy('summary_date', 'asc')
            ->get();
    }
    
    /**
     * Get current provision status
     */
    public function getCurrentProvisionStatus()
    {
        $latest = DB::table('loan_loss_provision_summary')
            ->orderBy('summary_date', 'desc')
            ->first();
        
        if (!$latest) {
            return null;
        }
        
        // Add formatted values
        $latest->formatted_total = 'TZS ' . number_format($latest->total_provisions, 2);
        $latest->formatted_outstanding = 'TZS ' . number_format($latest->total_outstanding, 2);
        $latest->formatted_npl_ratio = number_format($latest->npl_ratio, 2) . '%';
        $latest->formatted_coverage = number_format($latest->provision_coverage_ratio, 2) . '%';
        
        return $latest;
    }
    
    /**
     * Release provisions for closed loans
     */
    public function releaseProvisions($loanId)
    {
        try {
            // Get the latest provision for this loan
            $provision = DB::table('loan_loss_provisions')
                ->where('loan_id', $loanId)
                ->where('status', 'active')
                ->orderBy('provision_date', 'desc')
                ->first();
            
            if ($provision && $provision->provision_amount > 0) {
                // Mark provision as released
                DB::table('loan_loss_provisions')
                    ->where('id', $provision->id)
                    ->update([
                        'status' => 'released',
                        'notes' => 'Provision released - loan closed',
                        'updated_at' => now()
                    ]);
                
                // Post reversal to GL
                $transactionService = app(\App\Services\TransactionPostingService::class);
                $transactionService->postDoubleEntry(
                    'loan_loss_provision_reserve',
                    'loan_loss_provision_recovery',
                    $provision->provision_amount,
                    'PROVISION_RELEASE',
                    "Provision release for closed loan {$loanId}",
                    'loan_provision',
                    1,
                    'HQ'
                );
                
                Log::info("📝 Released provision for loan {$loanId}: TZS " . number_format($provision->provision_amount, 2));
            }
            
        } catch (\Exception $e) {
            Log::error("Error releasing provisions for loan {$loanId}: " . $e->getMessage());
        }
    }
    
    /**
     * Write off loan and utilize provisions
     */
    public function writeOffLoan($loanId, $writeOffAmount)
    {
        try {
            // Get provisions for this loan
            $provision = DB::table('loan_loss_provisions')
                ->where('loan_id', $loanId)
                ->where('status', 'active')
                ->orderBy('provision_date', 'desc')
                ->first();
            
            if ($provision) {
                // Mark as written off
                DB::table('loan_loss_provisions')
                    ->where('id', $provision->id)
                    ->update([
                        'status' => 'written_off',
                        'notes' => 'Loan written off',
                        'updated_at' => now()
                    ]);
                
                // Calculate the amount to write off against provisions
                $coveredAmount = min($writeOffAmount, $provision->provision_amount);
                $uncoveredAmount = $writeOffAmount - $coveredAmount;
                
                $transactionService = app(\App\Services\TransactionPostingService::class);
                
                // Use provisions to cover write-off
                if ($coveredAmount > 0) {
                    $transactionService->postDoubleEntry(
                        'loan_loss_provision_reserve',
                        'loans_control_account',
                        $coveredAmount,
                        'WRITE_OFF_PROVISION',
                        "Write-off covered by provisions for loan {$loanId}",
                        'loan_writeoff',
                        1,
                        'HQ'
                    );
                }
                
                // Direct write-off for uncovered amount
                if ($uncoveredAmount > 0) {
                    $transactionService->postDoubleEntry(
                        'bad_debt_expense',
                        'loans_control_account',
                        $uncoveredAmount,
                        'WRITE_OFF_DIRECT',
                        "Direct write-off for loan {$loanId}",
                        'loan_writeoff',
                        1,
                        'HQ'
                    );
                }
                
                Log::info("📝 Wrote off loan {$loanId}: Total: " . number_format($writeOffAmount, 2) . 
                         ", Covered: " . number_format($coveredAmount, 2) . 
                         ", Direct: " . number_format($uncoveredAmount, 2));
            }
            
        } catch (\Exception $e) {
            Log::error("Error writing off loan {$loanId}: " . $e->getMessage());
            throw $e;
        }
    }
}