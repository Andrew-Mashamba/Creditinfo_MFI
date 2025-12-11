<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Share;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\Asset;
use App\Models\Investment;
use App\Models\Insurance;
use App\Models\Document;
use App\Models\PerformanceMetric;
use App\Services\DividendCalculationService;
use App\Services\InterestCalculationService;
use App\Services\NotificationService;
use App\Services\ReportGenerationService;
use App\Services\SecurityService;
use App\Services\BackupService;
use App\Services\MandatorySavingsService;
use App\Services\SimpleDailyLoanReportsService;
use App\Services\OptimizedDailyLoanService;
use App\Services\PaymentNotificationService;
use App\Services\SavingsInterestAccrualService;
use App\Services\LoanInterestAccrualService;
use Illuminate\Support\Facades\Cache;
use App\Traits\TracksActivityProgress;
use App\Traits\LogsEndOfDayActivities;
use App\Models\DailyActivityStatus;
use App\Jobs\ProcessTradeReceivableInvoice;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

class DailySystemActivitiesService
{
    use TracksActivityProgress, LogsEndOfDayActivities;
    protected $previousDay;
    protected $dividendService;
    protected $interestService;
    protected $notificationService;
    protected $reportService;
    protected $securityService;
    protected $backupService;
    protected $mandatorySavingsService;

    public function __construct(
        // DividendCalculationService $dividendService,
        InterestCalculationService $interestService,
        // NotificationService $notificationService,
        ReportGenerationService $reportService,
        SecurityService $securityService,
        BackupService $backupService,
        MandatorySavingsService $mandatorySavingsService
    ) {
        $this->previousDay = Carbon::now()->subDay();
        // $this->dividendService = $dividendService;
        $this->interestService = $interestService;
        // $this->notificationService = $notificationService;
        $this->reportService = $reportService;
        $this->securityService = $securityService;
        $this->backupService = $backupService;
        $this->mandatorySavingsService = $mandatorySavingsService;
    }

    public function executeDailyActivities($triggeredBy = 'scheduled')
    {
        // Initialize logger
        $this->initializeEodLogger();
        $this->logEodStart($triggeredBy);
        
        try {
            DB::beginTransaction();
            
            // Cache the last run time
            Cache::put('last_daily_activities_run', now(), 86400);
            $this->logActivityProgress('Cache updated with last run time');

            // 1. Financial Core Activities
            $this->logActivityProgress('Starting Financial Core Activities...');
            $this->processLoanActivities($triggeredBy);
            $this->processSavingsAndDeposits($triggeredBy);
            $this->processShareManagement($triggeredBy);
            $this->processFinancialReconciliation($triggeredBy);

            // 2. Member and Compliance Activities
            $this->logActivityProgress('Starting Member and Compliance Activities...');
            $this->processMemberServices($triggeredBy);
            $this->processComplianceAndReporting($triggeredBy);

            // 3. System and Security Activities
            $this->logActivityProgress('Starting System and Security Activities...');
            $this->processSystemMaintenance($triggeredBy);
            $this->processSecurityAndAccessControl($triggeredBy);

            // 4. Asset and Investment Activities
            $this->logActivityProgress('Starting Asset and Investment Activities...');
            $this->processAssetManagement($triggeredBy);
            $this->processInvestmentManagement($triggeredBy);
            $this->processInsuranceActivities($triggeredBy);

            // 5. Document and Performance Activities
            $this->logActivityProgress('Starting Document and Performance Activities...');
            $this->processDocumentManagement($triggeredBy);
            $this->processPerformanceMonitoring($triggeredBy);

            // 6. Communication and Notifications
            $this->logActivityProgress('Starting Communication and Notifications...');
            $this->processCommunicationAndNotifications($triggeredBy);

            // 7. Payment Notifications for Payables and Receivables
            $this->logActivityProgress('Processing Payment Notifications...');
            $this->processPaymentNotifications($triggeredBy);

            // 8. Monthly Payroll Reset (on 1st of each month)
            if (Carbon::now()->day === 1) {
                $this->logActivityProgress('Resetting payroll payment status for new month...');
                $this->resetMonthlyPayrollStatus();
            }

            // Temporarily disabled due to error
            // $this->processMandatorySavings();

            DB::commit();
            
            // Log summary
            $activities = DailyActivityStatus::getTodayActivities()->toArray();
            $this->logEodSummary($activities);
            $this->logEodComplete('success', 'All activities completed successfully');
            
            Log::info('Daily activities completed successfully for ' . $this->previousDay->format('Y-m-d'));
            return ['status' => 'success', 'date' => $this->previousDay->format('Y-m-d')];

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->logActivityError($e->getMessage(), ['exception' => get_class($e)]);
            $this->logEodComplete('failed', $e->getMessage());
            
            Log::error('Daily activities failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function processLoanActivities($triggeredBy = 'system')
    {
        $this->logActivityStart('Loan Activities', [
            'Process' => 'Loan repayments and notifications',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('repayments_collection', 'Repayments collection', $triggeredBy);
        
        try {
            $this->logActivityProgress('Initializing Optimized Daily Loan Service');
            
            // Use the optimized service for high-volume processing
            $optimizedService = new OptimizedDailyLoanService();
            $optimizedService->processDailyActivities();
            $statistics = $optimizedService->getStatistics();
            
            $this->logActivityProgress('Loan processing completed', 
                $statistics['total_loans'] ?? 0, 
                $statistics['total_loans'] ?? 0
            );
            
            // Log statistics
            $this->logActivityStatistics([
                'total_loans' => $statistics['total_loans'] ?? 0,
                'repayments_processed' => $statistics['repayments_processed'] ?? 0,
                'amount_collected' => $statistics['amount_collected'] ?? 0,
                'schedules_updated' => $statistics['schedules_updated'] ?? 0,
                'error_count' => $statistics['error_count'] ?? 0
            ]);
            
            // Cache the statistics for monitoring
            Cache::put('daily_loan_processing_stats', $statistics, 86400); // 24 hours
            $this->logActivityProgress('Statistics cached for monitoring');
            
            // Update progress based on statistics
            if (isset($statistics['total_loans']) && $statistics['total_loans'] > 0) {
                $this->updateActivityProgress(
                    $statistics['total_loans'],
                    $statistics['total_loans'],
                    $statistics['error_count'] ?? 0
                );
            }
            
            $this->completeActivity();
            
            // Track loan notifications separately
            $this->logActivityProgress('Processing loan notifications');
            $this->trackSimpleActivity('loan_notifications', 'Loan repayment notifications', function() use ($statistics) {
                $this->logActivityProgress('Generating arrears report');
                $this->reportService->generateLoanArrearsReport($this->previousDay);
                
                $this->logActivityProgress('Updating loan loss provisions');
                $this->updateLoanLossProvisions();
                
                $this->logActivityProgress('Generating and sending daily loan reports');
                $this->generateAndSendDailyLoanReportsWithStats($statistics);
            }, $triggeredBy);

            $this->logActivityComplete('success', [
                'Total Loans Processed' => $statistics['total_loans'] ?? 0,
                'Total Amount Collected' => number_format($statistics['amount_collected'] ?? 0, 2)
            ]);

            Log::info('Loan activities completed for ' . $this->previousDay->format('Y-m-d'));
            Log::info('Processing statistics: ' . json_encode($statistics));

            // Process daily loan interest accrual
            $this->logActivityProgress('Processing daily loan interest accrual');
            $this->processLoanInterestAccrual($triggeredBy);

        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Loan activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process daily loan interest accrual
     * Recognizes interest income on accrual basis per IFRS/GAAP standards
     */
    protected function processLoanInterestAccrual($triggeredBy = 'system')
    {
        $this->logActivityStart('Loan Interest Accrual', [
            'Process' => 'Accrual-based interest income recognition',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);

        try {
            $this->logActivityProgress('Initializing Loan Interest Accrual Service');

            $loanInterestService = new LoanInterestAccrualService();
            $accrualResult = $loanInterestService->processDailyAccrual($this->previousDay);

            if ($accrualResult['status'] === 'success') {
                $stats = $accrualResult['statistics'] ?? [];

                $this->logActivityProgress('Loan interest accrual completed',
                    $stats['performing_loans'] ?? 0,
                    $stats['total_loans'] ?? 0
                );

                Log::info('Loan interest accrual summary', [
                    'date' => $accrualResult['date'],
                    'total_loans' => $stats['total_loans'] ?? 0,
                    'performing_loans' => $stats['performing_loans'] ?? 0,
                    'npl_loans' => $stats['npl_loans'] ?? 0,
                    'total_interest_accrued' => number_format($stats['total_interest_accrued'] ?? 0, 2),
                    'performing_interest' => number_format($stats['performing_interest'] ?? 0, 2),
                    'suspended_interest' => number_format($stats['suspended_interest'] ?? 0, 2),
                    'journal_entries' => $stats['journal_entries_created'] ?? 0,
                    'errors' => $stats['errors'] ?? 0
                ]);

                // Cache statistics for reporting
                Cache::put('loan_interest_accrual_stats_' . $this->previousDay->format('Ymd'), $stats, 86400);

            } elseif ($accrualResult['status'] === 'skipped') {
                $this->logActivityProgress('Loan interest accrual skipped - already processed');
                Log::info('Loan interest accrual skipped: ' . ($accrualResult['message'] ?? 'Already processed'));
            } else {
                $this->logActivityProgress('Loan interest accrual had errors');
                Log::warning('Loan interest accrual completed with issues', $accrualResult);
            }

        } catch (\Exception $e) {
            // Log error but don't fail the entire EOD process
            Log::error('Loan interest accrual failed: ' . $e->getMessage(), [
                'date' => $this->previousDay->format('Y-m-d'),
                'trace' => $e->getTraceAsString()
            ]);
            // Continue with other activities
        }
    }

    protected function processSavingsAndDeposits($triggeredBy = 'system')
    {
        $this->logActivityStart('Savings and Deposits', [
            'Process' => 'Interest calculation and deposit processing',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);

        $this->startActivity('interest_calculation', 'Interest calculation', $triggeredBy);

        try {
            // Calculate daily interest accrual for savings accounts
            $this->logActivityProgress('Processing daily savings interest accrual');
            $savingsInterestService = new SavingsInterestAccrualService();
            $accrualResult = $savingsInterestService->processDailyAccrual($this->previousDay);

            if ($accrualResult['status'] === 'success') {
                $this->logActivityProgress('Daily interest accrual completed',
                    $accrualResult['processed_accounts'] ?? 0,
                    $accrualResult['total_accounts'] ?? 0
                );

                Log::info('💰 Savings interest accrual summary', [
                    'date' => $accrualResult['date'],
                    'accounts_processed' => $accrualResult['processed_accounts'],
                    'total_interest' => number_format($accrualResult['total_interest_accrued'] ?? 0, 2),
                    'errors' => $accrualResult['errors_count'] ?? 0
                ]);
            } elseif ($accrualResult['status'] === 'skipped') {
                $this->logActivityProgress('Interest accrual skipped - already processed for this date');
                Log::info('⏭️ Savings interest accrual skipped: ' . $accrualResult['message']);
            }

            // Process fixed deposit maturities
            $this->logActivityProgress('Processing fixed deposit maturities');
            $maturedDeposits = $this->processFixedDepositMaturities();
            $this->logActivityProgress('Fixed deposits processed', $maturedDeposits, $maturedDeposits);

            // Update deposit balances
            $this->logActivityProgress('Updating deposit balances');
            $updatedBalances = $this->updateDepositBalances();
            $this->logActivityProgress('Deposit balances updated', $updatedBalances, $updatedBalances);

            // Generate interest reports
            $this->logActivityProgress('Generating deposit interest report');
            $this->reportService->generateDepositInterestReport($this->previousDay);

            // Process recurring deposits
            $this->logActivityProgress('Processing recurring deposits');
            $recurringProcessed = $this->processRecurringDeposits();

            $this->logActivityStatistics([
                'interest_accounts_processed' => $accrualResult['processed_accounts'] ?? 0,
                'total_interest_accrued' => $accrualResult['total_interest_accrued'] ?? 0,
                'matured_deposits' => $maturedDeposits,
                'balances_updated' => $updatedBalances,
                'recurring_processed' => $recurringProcessed
            ]);

            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Interest Accounts' => $accrualResult['processed_accounts'] ?? 0,
                'Total Interest' => 'TZS ' . number_format($accrualResult['total_interest_accrued'] ?? 0, 2),
                'Other Deposits' => $maturedDeposits + $recurringProcessed
            ]);

            Log::info('Savings and deposits activities completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Savings and deposits activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processShareManagement($triggeredBy = 'system')
    {
        $this->logActivityStart('Share Management', [
            'Process' => 'Share transactions and dividend calculation',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('share_transactions', 'Share transactions', $triggeredBy);
        
        try {
            // Update share values
            $this->logActivityProgress('Updating share values');
            // $this->dividendService->updateShareValues($this->previousDay);

            // Process share transactions
            $this->logActivityProgress('Processing share transactions');
            $shareTransactions = $this->processShareTransactions();
            $this->logActivityProgress('Share transactions processed', $shareTransactions, $shareTransactions);

            // Calculate dividends
            $this->logActivityProgress('Calculating daily dividends');
            // $this->dividendService->calculateDailyDividends($this->previousDay);

            // Update member share balances
            $this->logActivityProgress('Updating member share balances');
            $balancesUpdated = $this->updateMemberShareBalances();
            $this->logActivityProgress('Share balances updated', $balancesUpdated, $balancesUpdated);

            // Generate share movement reports
            $this->logActivityProgress('Generating share movement report');
            $this->reportService->generateShareMovementReport($this->previousDay);
            
            $this->logActivityStatistics([
                'share_transactions' => $shareTransactions,
                'balances_updated' => $balancesUpdated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Total Transactions' => $shareTransactions,
                'Balances Updated' => $balancesUpdated
            ]);

            Log::info('Share management activities completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Share management activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processFinancialReconciliation($triggeredBy = 'system')
    {
        $this->logActivityStart('Financial Reconciliation', [
            'Process' => 'Bank reconciliation and ledger updates',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('bank_reconciliation', 'Bank reconciliation', $triggeredBy);
        
        try {
            // Process standing instructions
            $this->logActivityProgress('Processing standing instructions');
            $standingInstructionsProcessed = $this->processStandingInstructions();
            $this->logActivityProgress('Standing instructions processed', $standingInstructionsProcessed, $standingInstructionsProcessed);

            // Reconcile bank accounts
            $this->logActivityProgress('Reconciling bank accounts');
            $reconciledAccounts = $this->reconcileBankAccounts();
            $this->logActivityProgress('Bank accounts reconciled', $reconciledAccounts, $reconciledAccounts);

            // Match bank transactions
            $this->logActivityProgress('Matching bank transactions');
            $matchedTransactions = $this->matchBankTransactions();
            $this->logActivityProgress('Transactions matched', $matchedTransactions, $matchedTransactions);

            // Update general ledger
            $this->logActivityProgress('Updating general ledger');
            $ledgerEntries = $this->updateGeneralLedger();
            $this->logActivityProgress('Ledger entries updated', $ledgerEntries, $ledgerEntries);

            // Generate trial balance
            $this->logActivityProgress('Generating daily trial balance');
            $this->reportService->generateDailyTrialBalance($this->previousDay);

            // Process bank charges
            $this->logActivityProgress('Processing bank charges');
            $bankCharges = $this->processBankCharges();
            
            $this->logActivityStatistics([
                'standing_instructions' => $standingInstructionsProcessed,
                'accounts_reconciled' => $reconciledAccounts,
                'transactions_matched' => $matchedTransactions,
                'ledger_entries' => $ledgerEntries,
                'bank_charges' => $bankCharges
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Accounts Reconciled' => $reconciledAccounts,
                'Transactions Matched' => $matchedTransactions
            ]);

            Log::info('Financial reconciliation completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Financial reconciliation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMemberServices($triggeredBy = 'system')
    {
        $this->logActivityStart('Member Services', [
            'Process' => 'Member withdrawals and account management',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('member_withdrawals', 'Member withdrawals', $triggeredBy);
        
        try {
            // Process withdrawals
            $this->logActivityProgress('Processing member withdrawals');
            $withdrawalsProcessed = $this->processMemberWithdrawals();
            $this->logActivityProgress('Withdrawals processed', $withdrawalsProcessed, $withdrawalsProcessed);

            // Update account statuses
            $this->logActivityProgress('Updating member account statuses');
            $statusesUpdated = $this->updateMemberAccountStatuses();
            $this->logActivityProgress('Account statuses updated', $statusesUpdated, $statusesUpdated);

            // Generate statements
            $this->logActivityProgress('Generating member statements');
            $this->reportService->generateMemberStatements($this->previousDay);

            // Process benefits
            $this->logActivityProgress('Processing member benefits');
            $benefitsProcessed = $this->processMemberBenefits();
            $this->logActivityProgress('Benefits processed', $benefitsProcessed, $benefitsProcessed);

            // Update eligibility
            $this->logActivityProgress('Updating member eligibility');
            $eligibilityUpdated = $this->updateMemberEligibility();
            $this->logActivityProgress('Eligibility updated', $eligibilityUpdated, $eligibilityUpdated);
            
            $this->logActivityStatistics([
                'withdrawals_processed' => $withdrawalsProcessed,
                'statuses_updated' => $statusesUpdated,
                'benefits_processed' => $benefitsProcessed,
                'eligibility_updated' => $eligibilityUpdated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Total Withdrawals' => $withdrawalsProcessed,
                'Members Updated' => $statusesUpdated
            ]);

            Log::info('Member services completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Member services failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processComplianceAndReporting($triggeredBy = 'system')
    {
        $this->logActivityStart('Compliance and Reporting', [
            'Process' => 'Regulatory reports and compliance checks',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('regulatory_reports', 'Regulatory reports', $triggeredBy);
        
        try {
            // Generate regulatory reports
            $this->logActivityProgress('Generating regulatory reports');
            $this->reportService->generateRegulatoryReports($this->previousDay);

            // Update compliance status
            $this->logActivityProgress('Updating compliance status');
            $complianceUpdates = $this->updateComplianceStatus();
            $this->logActivityProgress('Compliance status updated', $complianceUpdates, $complianceUpdates);

            // Process tax calculations
            $this->logActivityProgress('Processing tax calculations');
            $taxCalculations = $this->processTaxCalculations();
            $this->logActivityProgress('Tax calculations completed', $taxCalculations, $taxCalculations);

            // Generate audit trails
            $this->logActivityProgress('Generating audit trails');
            $auditTrails = $this->generateAuditTrails();
            $this->logActivityProgress('Audit trails generated', $auditTrails, $auditTrails);

            // Update risk assessments
            $this->logActivityProgress('Updating risk assessments');
            $riskAssessments = $this->updateRiskAssessments();
            
            $this->logActivityStatistics([
                'compliance_updates' => $complianceUpdates,
                'tax_calculations' => $taxCalculations,
                'audit_trails' => $auditTrails,
                'risk_assessments' => $riskAssessments
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Reports Generated' => 'All regulatory reports',
                'Compliance Items' => $complianceUpdates
            ]);

            Log::info('Compliance and reporting completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Compliance and reporting failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processSystemMaintenance($triggeredBy = 'system')
    {
        $this->logActivityStart('System Maintenance', [
            'Process' => 'Database backup and system optimization',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('database_backup', 'Database backup', $triggeredBy);
        
        try {
            // Database backup
            $this->logActivityProgress('Creating database backup');
            $backupSize = $this->backupService->createDatabaseBackup();
            $this->logActivityProgress('Database backup completed', 1, 1);

            // Clean system logs
            $this->logActivityProgress('Cleaning system logs');
            $logsCleared = $this->cleanSystemLogs();
            $this->logActivityProgress('System logs cleaned', $logsCleared, $logsCleared);

            // Clear cache
            $this->logActivityProgress('Clearing system cache');
            $cacheCleared = $this->clearSystemCache();
            $this->logActivityProgress('Cache cleared', $cacheCleared, $cacheCleared);

            // Clean temporary files
            $this->logActivityProgress('Cleaning temporary files');
            $tempFilesCleaned = $this->cleanTemporaryFiles();
            $this->logActivityProgress('Temporary files cleaned', $tempFilesCleaned, $tempFilesCleaned);

            // Optimize performance
            $this->logActivityProgress('Optimizing system performance');
            $this->optimizeSystemPerformance();
            
            $this->logActivityStatistics([
                'backup_size' => $backupSize ?? 'N/A',
                'logs_cleared' => $logsCleared,
                'cache_entries_cleared' => $cacheCleared,
                'temp_files_cleaned' => $tempFilesCleaned
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Backup Created' => 'Yes',
                'Files Cleaned' => $logsCleared + $tempFilesCleaned
            ]);

            Log::info('System maintenance completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('System maintenance failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processSecurityAndAccessControl($triggeredBy = 'system')
    {
        $this->logActivityStart('Security and Access Control', [
            'Process' => 'Security audit and access management',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('security_audit', 'Security audit', $triggeredBy);
        
        try {
            // Audit user activities
            $this->logActivityProgress('Auditing user activities');
            $auditedActivities = $this->securityService->auditUserActivities($this->previousDay);
            $this->logActivityProgress('User activities audited', $auditedActivities, $auditedActivities);

            // Update access logs
            $this->logActivityProgress('Updating access logs');
            $accessLogsUpdated = $this->securityService->updateAccessLogs();
            $this->logActivityProgress('Access logs updated', $accessLogsUpdated, $accessLogsUpdated);

            // Check suspicious activities
            $this->logActivityProgress('Checking for suspicious activities');
            $suspiciousFound = $this->securityService->checkSuspiciousActivities();
            if ($suspiciousFound > 0) {
                $this->logActivityWarning('Suspicious activities detected', ['count' => $suspiciousFound]);
            }

            // Rotate security keys
            $this->logActivityProgress('Rotating security keys');
            $keysRotated = $this->securityService->rotateSecurityKeys();
            $this->logActivityProgress('Security keys rotated', $keysRotated, $keysRotated);

            // Update session records
            $this->logActivityProgress('Updating session records');
            $sessionsUpdated = $this->securityService->updateSessionRecords();
            
            $this->logActivityStatistics([
                'activities_audited' => $auditedActivities,
                'access_logs_updated' => $accessLogsUpdated,
                'suspicious_activities' => $suspiciousFound,
                'keys_rotated' => $keysRotated,
                'sessions_updated' => $sessionsUpdated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Activities Audited' => $auditedActivities,
                'Suspicious Found' => $suspiciousFound
            ]);

            Log::info('Security and access control completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Security and access control failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processAssetManagement($triggeredBy = 'system')
    {
        $this->logActivityStart('Asset Management', [
            'Process' => 'Asset depreciation and maintenance',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('asset_depreciation', 'Asset depreciation', $triggeredBy);
        
        try {
            // Update depreciation
            $this->logActivityProgress('Updating asset depreciation');
            $assetsDepreciated = $this->updateAssetDepreciation();
            $this->logActivityProgress('Assets depreciated', $assetsDepreciated, $assetsDepreciated);

            // Process maintenance schedules
            $this->logActivityProgress('Processing asset maintenance schedules');
            $maintenanceProcessed = $this->processAssetMaintenance();
            $this->logActivityProgress('Maintenance schedules processed', $maintenanceProcessed, $maintenanceProcessed);

            // Update inventory
            $this->logActivityProgress('Updating inventory records');
            $inventoryUpdated = $this->updateInventoryRecords();
            $this->logActivityProgress('Inventory records updated', $inventoryUpdated, $inventoryUpdated);

            // Generate asset reports
            $this->logActivityProgress('Generating asset reports');
            $this->reportService->generateAssetReports($this->previousDay);

            // Process insurance updates
            $this->logActivityProgress('Processing asset insurance updates');
            $insuranceUpdated = $this->processAssetInsurance();
            
            $this->logActivityStatistics([
                'assets_depreciated' => $assetsDepreciated,
                'maintenance_processed' => $maintenanceProcessed,
                'inventory_updated' => $inventoryUpdated,
                'insurance_updated' => $insuranceUpdated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Assets Processed' => $assetsDepreciated,
                'Maintenance Items' => $maintenanceProcessed
            ]);

            Log::info('Asset management completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Asset management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processInvestmentManagement($triggeredBy = 'system')
    {
        $this->logActivityStart('Investment Management', [
            'Process' => 'Investment values and portfolio management',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('investment_valuation', 'Investment valuation', $triggeredBy);
        
        try {
            // Update investment values
            $this->logActivityProgress('Updating investment values');
            $investmentsUpdated = $this->updateInvestmentValues();
            $this->logActivityProgress('Investment values updated', $investmentsUpdated, $investmentsUpdated);

            // Process investment returns
            $this->logActivityProgress('Processing investment returns');
            $returnsProcessed = $this->processInvestmentReturns();
            $this->logActivityProgress('Investment returns processed', $returnsProcessed, $returnsProcessed);

            // Generate investment reports
            $this->logActivityProgress('Generating investment reports');
            $this->reportService->generateInvestmentReports($this->previousDay);

            // Update portfolio allocations
            $this->logActivityProgress('Updating portfolio allocations');
            $portfoliosUpdated = $this->updatePortfolioAllocations();
            $this->logActivityProgress('Portfolio allocations updated', $portfoliosUpdated, $portfoliosUpdated);

            // Process investment maturities
            $this->logActivityProgress('Processing investment maturities');
            $maturitiesProcessed = $this->processInvestmentMaturities();
            
            $this->logActivityStatistics([
                'investments_updated' => $investmentsUpdated,
                'returns_processed' => $returnsProcessed,
                'portfolios_updated' => $portfoliosUpdated,
                'maturities_processed' => $maturitiesProcessed
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Investments Updated' => $investmentsUpdated,
                'Returns Processed' => $returnsProcessed
            ]);

            Log::info('Investment management completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Investment management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processInsuranceActivities($triggeredBy = 'system')
    {
        $this->logActivityStart('Insurance Activities', [
            'Process' => 'Insurance policies and claims processing',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('insurance_policies', 'Insurance policies', $triggeredBy);
        
        try {
            // Update insurance policies
            $this->logActivityProgress('Updating insurance policies');
            $policiesUpdated = $this->updateInsurancePolicies();
            $this->logActivityProgress('Insurance policies updated', $policiesUpdated, $policiesUpdated);

            // Process insurance claims
            $this->logActivityProgress('Processing insurance claims');
            $claimsProcessed = $this->processInsuranceClaims();
            $this->logActivityProgress('Insurance claims processed', $claimsProcessed, $claimsProcessed);

            // Generate insurance reports
            $this->logActivityProgress('Generating insurance reports');
            $this->reportService->generateInsuranceReports($this->previousDay);

            // Update coverage status
            $this->logActivityProgress('Updating coverage status');
            $coverageUpdated = $this->updateCoverageStatus();
            $this->logActivityProgress('Coverage status updated', $coverageUpdated, $coverageUpdated);

            // Process premium payments
            $this->logActivityProgress('Processing premium payments');
            $premiumsProcessed = $this->processPremiumPayments();
            
            $this->logActivityStatistics([
                'policies_updated' => $policiesUpdated,
                'claims_processed' => $claimsProcessed,
                'coverage_updated' => $coverageUpdated,
                'premiums_processed' => $premiumsProcessed
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Policies Updated' => $policiesUpdated,
                'Claims Processed' => $claimsProcessed
            ]);

            Log::info('Insurance activities completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Insurance activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processDocumentManagement($triggeredBy = 'system')
    {
        $this->logActivityStart('Document Management', [
            'Process' => 'Document archiving and status updates',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('document_archiving', 'Document archiving', $triggeredBy);
        
        try {
            // Archive old documents
            $this->logActivityProgress('Archiving old documents');
            $documentsArchived = $this->archiveOldDocuments();
            $this->logActivityProgress('Documents archived', $documentsArchived, $documentsArchived);

            // Update document statuses
            $this->logActivityProgress('Updating document statuses');
            $statusesUpdated = $this->updateDocumentStatuses();
            $this->logActivityProgress('Document statuses updated', $statusesUpdated, $statusesUpdated);

            // Generate document reports
            $this->logActivityProgress('Generating document reports');
            $this->reportService->generateDocumentReports($this->previousDay);

            // Process document expiries
            $this->logActivityProgress('Processing document expiries');
            $expiriesProcessed = $this->processDocumentExpiries();
            $this->logActivityProgress('Document expiries processed', $expiriesProcessed, $expiriesProcessed);

            // Update document metadata
            $this->logActivityProgress('Updating document metadata');
            $metadataUpdated = $this->updateDocumentMetadata();
            
            $this->logActivityStatistics([
                'documents_archived' => $documentsArchived,
                'statuses_updated' => $statusesUpdated,
                'expiries_processed' => $expiriesProcessed,
                'metadata_updated' => $metadataUpdated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Documents Archived' => $documentsArchived,
                'Expiries Processed' => $expiriesProcessed
            ]);

            Log::info('Document management completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Document management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processPerformanceMonitoring($triggeredBy = 'system')
    {
        $this->logActivityStart('Performance Monitoring', [
            'Process' => 'Performance metrics and KPI calculations',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('performance_metrics', 'Performance metrics', $triggeredBy);
        
        try {
            // Update performance metrics
            $this->logActivityProgress('Updating performance metrics');
            $metricsUpdated = $this->updatePerformanceMetrics();
            $this->logActivityProgress('Performance metrics updated', $metricsUpdated, $metricsUpdated);

            // Generate performance reports
            $this->logActivityProgress('Generating performance reports');
            $this->reportService->generatePerformanceReports($this->previousDay);

            // Process KPI calculations
            $this->logActivityProgress('Processing KPI calculations');
            $kpisCalculated = $this->processKPICalculations();
            $this->logActivityProgress('KPIs calculated', $kpisCalculated, $kpisCalculated);

            // Update benchmark comparisons
            $this->logActivityProgress('Updating benchmark comparisons');
            $benchmarksUpdated = $this->updateBenchmarkComparisons();
            $this->logActivityProgress('Benchmarks updated', $benchmarksUpdated, $benchmarksUpdated);

            // Generate trend analysis
            $this->logActivityProgress('Generating trend analysis');
            $trendsGenerated = $this->generateTrendAnalysis();
            
            $this->logActivityStatistics([
                'metrics_updated' => $metricsUpdated,
                'kpis_calculated' => $kpisCalculated,
                'benchmarks_updated' => $benchmarksUpdated,
                'trends_generated' => $trendsGenerated
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Metrics Updated' => $metricsUpdated,
                'KPIs Calculated' => $kpisCalculated
            ]);

            Log::info('Performance monitoring completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Performance monitoring failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processCommunicationAndNotifications($triggeredBy = 'system')
    {
        $this->logActivityStart('Communication and Notifications', [
            'Process' => 'Daily notifications and communication',
            'Date' => $this->previousDay->format('Y-m-d')
        ]);
        
        $this->startActivity('daily_notifications', 'Daily notifications', $triggeredBy);
        
        try {
            $this->logActivityProgress('Preparing daily notifications');
            
            // Process Trade Receivables Reminders
            $this->logActivityProgress('Processing trade receivables reminders');
            $tradeReceivablesProcessed = $this->processTradeReceivablesReminders();
            $this->logActivityProgress('Trade receivables reminders processed', $tradeReceivablesProcessed, $tradeReceivablesProcessed);
            
            // Send daily notifications (commented out for now)
            // $this->logActivityProgress('Sending daily notifications');
            // $notificationsSent = $this->notificationService->sendDailyNotifications($this->previousDay);
            // $this->logActivityProgress('Daily notifications sent', $notificationsSent, $notificationsSent);

            // Process email notifications (commented out for now)
            // $this->logActivityProgress('Processing email notifications');
            // $emailsSent = $this->processEmailNotifications();
            // $this->logActivityProgress('Email notifications processed', $emailsSent, $emailsSent);

            // Process SMS notifications (commented out for now)
            // $this->logActivityProgress('Processing SMS notifications');
            // $smsSent = $this->processSMSNotifications();
            // $this->logActivityProgress('SMS notifications processed', $smsSent, $smsSent);

            // Update notification statuses (commented out for now)
            // $this->logActivityProgress('Updating notification statuses');
            // $statusesUpdated = $this->updateNotificationStatuses();
            // $this->logActivityProgress('Notification statuses updated', $statusesUpdated, $statusesUpdated);

            // Generate communication reports (commented out for now)
            // $this->logActivityProgress('Generating communication reports');
            // $this->reportService->generateCommunicationReport($this->previousDay);
            
            $this->logActivityStatistics([
                'notifications_sent' => $tradeReceivablesProcessed, // Trade receivables reminders
                'emails_processed' => 0,
                'sms_processed' => 0,
                'trade_receivables_reminders' => $tradeReceivablesProcessed
            ]);
            
            $this->completeActivity();
            $this->logActivityComplete('success', [
                'Status' => 'Placeholder - Notifications disabled'
            ]);

            Log::info('Communication and notifications completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            $this->failActivity($e->getMessage());
            $this->logActivityError($e);
            $this->logActivityComplete('failed');
            Log::error('Communication and notifications failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMandatorySavings()
    {
        try {
            $year = now()->year;
            $month = now()->month;
            
            // Generate tracking records for current month if needed
            $this->mandatorySavingsService->generateTrackingRecords($year, $month);
            
            // Update tracking from new payments
            $this->mandatorySavingsService->updateTrackingFromPayments($year, $month);
            
            // Process overdue records
            $this->mandatorySavingsService->processOverdueRecords();
            
            // Generate notifications for upcoming payments
            $this->mandatorySavingsService->generateNotifications($year, $month);
            
            // Calculate and process arrears
            $this->mandatorySavingsService->calculateArrears();
            
            Log::info('Mandatory savings daily processing completed for ' . $this->previousDay->format('Y-m-d'));
        } catch (\Exception $e) {
            Log::error('Mandatory savings daily processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Helper methods for specific tasks
    private function processLoanRepayments()
    {
        try {
            Log::info('🔄 Starting automatic loan repayment processing for ' . $this->previousDay->format('Y-m-d'));
            
            // Get all active loans with overdue schedules
            $activeLoans = DB::table('loans')
                ->where('loan_status', 'active')
                ->whereNotNull('disbursement_date')
                ->get();
            
            $totalProcessed = 0;
            $totalAmount = 0;
            
            foreach ($activeLoans as $loan) {
                // Get member with deposit account (product_number = '3000')
                $depositAccount = DB::table('accounts')
                    ->where('member_id', $loan->client_id)
                    ->where('product_number', '3000')
                    ->where('status', 'active')
                    ->first();
                
                if (!$depositAccount || $depositAccount->balance <= 0) {
                    continue;
                }
                
                // Get overdue and current schedules
                $schedules = DB::table('loans_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->whereIn('status', ['overdue', 'pending'])
                    ->where('installment_date', '<=', now())
                    ->orderBy('installment_date', 'asc')
                    ->get();
                
                if ($schedules->isEmpty()) {
                    continue;
                }
                
                $availableBalance = $depositAccount->balance;
                $transactionService = app(\App\Services\TransactionPostingService::class);
                $repaymentService = app(\App\Services\LoanRepaymentService::class);
                
                foreach ($schedules as $schedule) {
                    if ($availableBalance <= 0) {
                        break;
                    }
                    
                    // Calculate arrears
                    $daysOverdue = now()->diffInDays($schedule->installment_date);
                    $amountDue = ($schedule->interest ?? 0) + ($schedule->principle ?? 0) - ($schedule->interest_paid ?? 0) - ($schedule->principle_paid ?? 0);
                    
                    if ($amountDue <= 0) {
                        continue;
                    }
                    
                    // Update arrears information
                    DB::table('loans_schedules')
                        ->where('id', $schedule->id)
                        ->update([
                            'amount_in_arrears' => $amountDue,
                            'days_in_arrears' => $daysOverdue > 0 ? $daysOverdue : 0,
                            'updated_at' => now()
                        ]);
                    
                    // Process payment based on available balance
                    $paymentAmount = min($availableBalance, $amountDue);
                    
                    // Priority: Interest Arrears → Principal Arrears → Current Interest → Current Principal
                    $interestDue = ($schedule->interest ?? 0) - ($schedule->interest_paid ?? 0);
                    $principalDue = ($schedule->principle ?? 0) - ($schedule->principle_paid ?? 0);
                    
                    $interestPayment = min($paymentAmount, $interestDue);
                    $principalPayment = min($paymentAmount - $interestPayment, $principalDue);
                    
                    if ($paymentAmount > 0) {
                        Log::info("💰 Processing automatic repayment for Loan: {$loan->loan_id}, Schedule: {$schedule->id}, Amount: {$paymentAmount}");
                        
                        // Debit deposit account
                        $transactionService->postDoubleEntry(
                            $depositAccount->account_number,
                            'loans_control_account',
                            $paymentAmount,
                            'AUTO_REPAY',
                            "Automatic loan repayment for {$loan->loan_id}",
                            'loan_repayment',
                            auth()->id() ?? 1,
                            $loan->branch_id
                        );
                        
                        // Update deposit account balance
                        DB::table('accounts')
                            ->where('id', $depositAccount->id)
                            ->decrement('balance', $paymentAmount);
                        
                        // Update loan schedule
                        DB::table('loans_schedules')
                            ->where('id', $schedule->id)
                            ->update([
                                'interest_paid' => DB::raw("interest_paid + {$interestPayment}"),
                                'principle_paid' => DB::raw("principle_paid + {$principalPayment}"),
                                'amount_in_arrears' => DB::raw("GREATEST(0, amount_in_arrears - {$paymentAmount})"),
                                'days_in_arrears' => $amountDue <= $paymentAmount ? 0 : $daysOverdue,
                                'status' => $amountDue <= $paymentAmount ? 'paid' : 'partially_paid',
                                'updated_at' => now()
                            ]);
                        
                        // Update loan balance
                        DB::table('loans')
                            ->where('id', $loan->id)
                            ->update([
                                'total_interest_paid' => DB::raw("total_interest_paid + {$interestPayment}"),
                                'total_principal_paid' => DB::raw("total_principal_paid + {$principalPayment}"),
                                'balance' => DB::raw("balance - {$principalPayment}"),
                                'updated_at' => now()
                            ]);
                        
                        // Create payment record
                        DB::table('loan_payments')->insert([
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
                            'notes' => "Automatic repayment from deposit account {$depositAccount->account_number}",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        $availableBalance -= $paymentAmount;
                        $totalAmount += $paymentAmount;
                        $totalProcessed++;
                    }
                }
                
                // Check if loan is fully paid
                $remainingBalance = DB::table('loans')
                    ->where('id', $loan->id)
                    ->value('balance');
                
                if ($remainingBalance <= 0) {
                    DB::table('loans')
                        ->where('id', $loan->id)
                        ->update([
                            'loan_status' => 'closed',
                            'closed_date' => now(),
                            'updated_at' => now()
                        ]);
                    
                    Log::info("✅ Loan {$loan->loan_id} fully paid and closed");
                }
            }
            
            // Process member notifications for automatic repayments
            $memberNotifications = [];
            
            // Send notifications to members whose accounts were debited
            foreach ($activeLoans as $loan) {
                // Get member details
                $member = DB::table('clients')
                    ->where('id', $loan->client_id)
                    ->first();
                    
                if (!$member) continue;
                
                // Check if this member had any repayments processed
                $memberRepayments = DB::table('loan_payments')
                    ->where('loan_id', $loan->loan_id)
                    ->where('payment_method', 'auto_deduction')
                    ->whereDate('payment_date', now()->toDateString())
                    ->get();
                
                if ($memberRepayments->count() > 0) {
                    $totalMemberAmount = $memberRepayments->sum('amount');
                    
                    // Send notification to member
                    $memberMessage = "Dear {$member->first_name} {$member->last_name},\n\n";
                    $memberMessage .= "Automatic loan repayment has been processed from your deposit account.\n\n";
                    $memberMessage .= "Loan ID: {$loan->loan_id}\n";
                    $memberMessage .= "Amount Deducted: TZS " . number_format($totalMemberAmount, 2) . "\n";
                    $memberMessage .= "Date: " . now()->format('Y-m-d') . "\n";
                    $memberMessage .= "Remaining Loan Balance: TZS " . number_format($loan->balance, 2) . "\n\n";
                    $memberMessage .= "Thank you for maintaining your loan repayments.";
                    
                    // Get user account for this member
                    $userAccount = DB::table('users')
                        ->where('institution_id', $member->client_number)
                        ->first();
                    
                    if ($userAccount) {
                        $notificationService = app(\App\Services\NotificationService::class);
                        $notificationService->createNotification(
                            $userAccount->id,
                            'loan_repayment',
                            'Automatic Loan Repayment Processed',
                            $memberMessage
                        );
                        
                        // Also send email if available
                        if ($member->email) {
                            try {
                                \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($member, $memberMessage) {
                                    $message->to($member->email)
                                            ->subject('Automatic Loan Repayment Notification')
                                            ->html(nl2br($memberMessage));
                                });
                            } catch (\Exception $e) {
                                Log::error("Failed to send email to {$member->email}: " . $e->getMessage());
                            }
                        }
                        
                        $memberNotifications[] = $member->client_number;
                    }
                }
            }
            
            Log::info("📊 Automatic loan repayment summary: Processed {$totalProcessed} repayments totaling " . number_format($totalAmount, 2));
            Log::info("📧 Notifications sent to " . count($memberNotifications) . " members: " . implode(', ', $memberNotifications));
            
        } catch (\Exception $e) {
            Log::error('❌ Error processing automatic loan repayments: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateLoanStatuses()
    {
        try {
            Log::info('📊 Updating loan statuses and arrears for ' . $this->previousDay->format('Y-m-d'));
            
            // Update all overdue schedules with arrears information
            $overdueSchedules = DB::table('loans_schedules')
                ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
                ->where('installment_date', '<', now())
                ->get();
            
            foreach ($overdueSchedules as $schedule) {
                $daysOverdue = now()->diffInDays($schedule->installment_date);
                $amountDue = ($schedule->interest ?? 0) + ($schedule->principle ?? 0) 
                            - ($schedule->interest_paid ?? 0) - ($schedule->principle_paid ?? 0);
                
                // Update arrears and status
                DB::table('loans_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'amount_in_arrears' => max(0, $amountDue),
                        'days_in_arrears' => $daysOverdue > 0 ? $daysOverdue : 0,
                        'status' => $amountDue > 0 ? 'overdue' : 'paid',
                        'updated_at' => now()
                    ]);
            }
            
            // Update loan statuses based on arrears
            $loansWithArrears = DB::table('loans')
                ->where('loan_status', 'active')
                ->whereNotNull('disbursement_date')
                ->get();
            
            foreach ($loansWithArrears as $loan) {
                $totalArrears = DB::table('loans_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->where('status', 'overdue')
                    ->sum('amount_in_arrears');
                
                $maxDaysInArrears = DB::table('loans_schedules')
                    ->where('loan_id', $loan->loan_id)
                    ->where('status', 'overdue')
                    ->max('days_in_arrears');
                
                // Update loan with arrears summary
                DB::table('loans')
                    ->where('id', $loan->id)
                    ->update([
                        'total_arrears' => $totalArrears,
                        'days_in_arrears' => $maxDaysInArrears ?? 0,
                        'updated_at' => now()
                    ]);
                
                // Update loan classification based on days in arrears
                $classification = 'PERFORMING';
                if ($maxDaysInArrears > 180) {
                    $classification = 'LOSS';
                } elseif ($maxDaysInArrears > 90) {
                    $classification = 'DOUBTFUL';
                } elseif ($maxDaysInArrears > 30) {
                    $classification = 'SUBSTANDARD';
                } elseif ($maxDaysInArrears > 0) {
                    $classification = 'WATCH';
                }
                
                DB::table('loans')
                    ->where('id', $loan->id)
                    ->update([
                        'loan_classification' => $classification,
                        'updated_at' => now()
                    ]);
            }
            
            Log::info('✅ Loan statuses and arrears updated successfully');
            
        } catch (\Exception $e) {
            Log::error('❌ Error updating loan statuses: ' . $e->getMessage());
            throw $e;
        }
    }

    private function updateLoanLossProvisions()
    {
        try {
            Log::info('💰 Calculating loan loss provisions...');
            
            // Initialize the provision service
            $provisionService = new \App\Services\LoanLossProvisionService();
            
            // Calculate provisions for the previous day
            $summary = $provisionService->calculateDailyProvisions($this->previousDay);
            
            // Log summary statistics
            Log::info('📊 Loan Loss Provisions Summary:');
            Log::info('  - Total Outstanding: TZS ' . number_format($summary['total_outstanding'], 2));
            Log::info('  - General Provisions: TZS ' . number_format($summary['general_provisions'], 2));
            Log::info('  - Specific Provisions: TZS ' . number_format($summary['specific_provisions'], 2));
            Log::info('  - Total Provisions: TZS ' . number_format($summary['total_provisions'], 2));
            Log::info('  - NPL Ratio: ' . number_format($summary['npl_ratio'], 2) . '%');
            Log::info('  - Coverage Ratio: ' . number_format($summary['provision_coverage_ratio'], 2) . '%');
            
            // Alert if NPL ratio exceeds threshold
            if ($summary['npl_ratio'] > 5.0) {
                Log::warning('⚠️ NPL Ratio exceeds 5% threshold: ' . number_format($summary['npl_ratio'], 2) . '%');
                
                // Send alert to management
                $this->sendNPLAlert($summary);
            }
            
            // Alert if coverage ratio is below minimum
            if ($summary['provision_coverage_ratio'] < 100.0) {
                Log::warning('⚠️ Provision coverage below 100%: ' . number_format($summary['provision_coverage_ratio'], 2) . '%');
            }
            
            Log::info('✅ Loan loss provisions updated successfully');
            
        } catch (\Exception $e) {
            Log::error('❌ Error updating loan loss provisions: ' . $e->getMessage());
            // Don't throw - allow other activities to continue
        }
    }
    
    private function sendNPLAlert($summary)
    {
        try {
            // Get management users
            $managers = DB::table('users')
                ->whereIn('role', ['admin', 'manager', 'credit_manager'])
                ->where('status', 'active')
                ->get();
            
            foreach ($managers as $manager) {
                $message = "ALERT: Non-Performing Loans ratio has exceeded the 5% threshold.\n\n";
                $message .= "Current NPL Ratio: " . number_format($summary['npl_ratio'], 2) . "%\n";
                $message .= "Total Outstanding: TZS " . number_format($summary['total_outstanding'], 2) . "\n";
                $message .= "NPL Amount: TZS " . number_format($summary['doubtful_balance'] + $summary['loss_balance'], 2) . "\n";
                $message .= "Provision Coverage: " . number_format($summary['provision_coverage_ratio'], 2) . "%\n\n";
                $message .= "Immediate action required to review and manage non-performing loans.";
                
                // Create notification
                if (class_exists(\App\Services\NotificationService::class)) {
                    $notificationService = app(\App\Services\NotificationService::class);
                    $notificationService->createNotification(
                        $manager->id,
                        'npl_alert',
                        'High NPL Ratio Alert',
                        $message
                    );
                }
                
                // Send email if available
                if ($manager->email) {
                    \Illuminate\Support\Facades\Mail::send([], [], function ($mail) use ($manager, $message, $summary) {
                        $htmlContent = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <div style='background-color: #ff4444; color: white; padding: 20px;'>
                                <h2>⚠️ High NPL Ratio Alert</h2>
                            </div>
                            <div style='padding: 20px;'>
                                <p>Dear {$manager->name},</p>
                                <p><strong>The Non-Performing Loans ratio has exceeded the 5% threshold.</strong></p>
                                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                                    <tr>
                                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>NPL Ratio:</strong></td>
                                        <td style='padding: 10px; border: 1px solid #ddd; color: red;'>" . number_format($summary['npl_ratio'], 2) . "%</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Total Outstanding:</strong></td>
                                        <td style='padding: 10px; border: 1px solid #ddd;'>TZS " . number_format($summary['total_outstanding'], 2) . "</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>NPL Amount:</strong></td>
                                        <td style='padding: 10px; border: 1px solid #ddd;'>TZS " . number_format($summary['doubtful_balance'] + $summary['loss_balance'], 2) . "</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Provision Coverage:</strong></td>
                                        <td style='padding: 10px; border: 1px solid #ddd;'>" . number_format($summary['provision_coverage_ratio'], 2) . "%</td>
                                    </tr>
                                </table>
                                <p style='color: red;'><strong>Immediate action required to review and manage non-performing loans.</strong></p>
                                <p>Best regards,<br>SACCOS System</p>
                            </div>
                        </body>
                        </html>";
                        
                        $mail->to($manager->email)
                             ->subject('URGENT: High NPL Ratio Alert - ' . number_format($summary['npl_ratio'], 2) . '%')
                             ->html($htmlContent);
                    });
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error sending NPL alerts: ' . $e->getMessage());
        }
    }

    private function processFixedDepositMaturities()
    {
        // Implementation for processing fixed deposit maturities
    }

    private function updateDepositBalances()
    {
        // Implementation for updating deposit balances
    }

    private function processRecurringDeposits()
    {
        // Implementation for processing recurring deposits
    }

    private function processShareTransactions()
    {
        // Implementation for processing share transactions
    }

    private function updateMemberShareBalances()
    {
        // Implementation for updating member share balances
    }

    private function reconcileBankAccounts()
    {
        // Implementation for reconciling bank accounts
    }

    private function matchBankTransactions()
    {
        // Implementation for matching bank transactions
    }

    private function updateGeneralLedger()
    {
        // Implementation for updating general ledger
    }

    private function processBankCharges()
    {
        // Implementation for processing bank charges
    }
    
    private function processStandingInstructions()
    {
        try {
            // Execute standing instructions using Artisan command
            \Artisan::call('standing-instructions:execute', [
                '--dry-run' => false
            ]);
            
            // Get the count of successfully processed instructions
            $successCount = DB::table('standing_instructions_executions')
                ->whereDate('executed_at', Carbon::today())
                ->where('status', 'SUCCESS')
                ->count();
            
            Log::info("Standing instructions processed: {$successCount}");
            
            return $successCount;
        } catch (\Exception $e) {
            Log::error('Failed to process standing instructions: ' . $e->getMessage());
            return 0;
        }
    }

    private function processMemberWithdrawals()
    {
        // Implementation for processing member withdrawals
    }

    private function updateMemberAccountStatuses()
    {
        // Implementation for updating member account statuses
    }

    private function processMemberBenefits()
    {
        // Implementation for processing member benefits
    }

    private function updateMemberEligibility()
    {
        // Implementation for updating member eligibility
    }

    private function updateComplianceStatus()
    {
        // Implementation for updating compliance status
    }

    private function processTaxCalculations()
    {
        // Implementation for processing tax calculations
    }

    private function generateAuditTrails()
    {
        // Implementation for generating audit trails
    }

    private function updateRiskAssessments()
    {
        // Implementation for updating risk assessments
    }

    private function cleanSystemLogs()
    {
        // Implementation for cleaning system logs
    }

    private function clearSystemCache()
    {
        // Implementation for clearing system cache
    }

    private function cleanTemporaryFiles()
    {
        // Implementation for cleaning temporary files
    }

    private function optimizeSystemPerformance()
    {
        // Implementation for optimizing system performance
    }

    private function updateAssetDepreciation()
    {
        // Implementation for updating asset depreciation
    }

    private function processAssetMaintenance()
    {
        // Implementation for processing asset maintenance
    }

    private function updateInventoryRecords()
    {
        // Implementation for updating inventory records
    }

    private function processAssetInsurance()
    {
        // Implementation for processing asset insurance
    }

    private function updateInvestmentValues()
    {
        // Implementation for updating investment values
    }

    private function processInvestmentReturns()
    {
        // Implementation for processing investment returns
    }

    private function updatePortfolioAllocations()
    {
        // Implementation for updating portfolio allocations
    }

    private function processInvestmentMaturities()
    {
        // Implementation for processing investment maturities
    }

    private function updateInsurancePolicies()
    {
        // Implementation for updating insurance policies
    }

    private function processInsuranceClaims()
    {
        // Implementation for processing insurance claims
    }

    private function updateCoverageStatus()
    {
        // Implementation for updating coverage status
    }

    private function processPremiumPayments()
    {
        // Implementation for processing premium payments
    }

    private function archiveOldDocuments()
    {
        // Implementation for archiving old documents
    }

    private function updateDocumentStatuses()
    {
        // Implementation for updating document statuses
    }

    private function processDocumentExpiries()
    {
        // Implementation for processing document expiries
    }

    private function updateDocumentMetadata()
    {
        // Implementation for updating document metadata
    }

    private function updatePerformanceMetrics()
    {
        // Implementation for updating performance metrics
    }

    private function processKPICalculations()
    {
        // Implementation for processing KPI calculations
    }

    private function updateBenchmarkComparisons()
    {
        // Implementation for updating benchmark comparisons
    }

    private function generateTrendAnalysis()
    {
        // Implementation for generating trend analysis
    }
    
    private function generateAndSendDailyLoanReports()
    {
        try {
            Log::info('📊 Generating daily loan reports...');
            
            $reportsService = new SimpleDailyLoanReportsService();
            $reportsService->generateAndSendReports($this->previousDay);
            
            Log::info('📧 Daily loan reports generated and sent to all system users');
            
        } catch (\Exception $e) {
            Log::error('❌ Error generating/sending daily loan reports: ' . $e->getMessage());
            // Don't throw - allow other activities to continue
        }
    }
    
    private function generateAndSendDailyLoanReportsWithStats($statistics)
    {
        try {
            Log::info('📊 Generating optimized daily loan reports with statistics...');
            
            // Dispatch the report generation job with statistics
            dispatch(new \App\Jobs\GenerateDailyLoanReports($this->previousDay, $statistics));
            
            Log::info('📧 Daily loan reports job dispatched with processing statistics');
            
        } catch (\Exception $e) {
            Log::error('❌ Error dispatching daily loan reports job: ' . $e->getMessage());
            // Don't throw - allow other activities to continue
        }
    }
    
    /**
     * Process trade receivables reminders for unpaid invoices
     * 
     * @return int Number of reminders sent
     */
    protected function processTradeReceivablesReminders()
    {
        try {
            $this->logActivityProgress('Starting trade receivables reminder processing');
            
            // Get unpaid invoices that need reminders
            $unpaidReceivables = DB::table('trade_receivables')
                ->where('status', '!=', 'paid')
                ->where('status', '!=', 'written_off')
                ->where('balance', '>', 0)
                ->whereNotNull('customer_email') // Must have email or phone
                ->where(function($query) {
                    // Reminder logic based on days overdue
                    $query->where(function($q) {
                        // First reminder: 3 days before due date
                        $q->whereRaw('due_date::date = ?', [Carbon::now()->addDays(3)->format('Y-m-d')])
                          ->where(function($q2) {
                              $q2->whereNull('last_reminder_sent_at')
                                 ->orWhereRaw('last_reminder_sent_at::date < ?', [Carbon::now()->subDays(7)->format('Y-m-d')]);
                          });
                    })->orWhere(function($q) {
                        // Second reminder: On due date
                        $q->whereRaw('due_date::date = ?', [Carbon::now()->format('Y-m-d')])
                          ->where(function($q2) {
                              $q2->whereNull('last_reminder_sent_at')
                                 ->orWhereRaw('last_reminder_sent_at::date < ?', [Carbon::now()->subDays(7)->format('Y-m-d')]);
                          });
                    })->orWhere(function($q) {
                        // Third reminder: 7 days overdue
                        $q->whereRaw('due_date::date = ?', [Carbon::now()->subDays(7)->format('Y-m-d')])
                          ->where(function($q2) {
                              $q2->whereNull('last_reminder_sent_at')
                                 ->orWhereRaw('last_reminder_sent_at::date < ?', [Carbon::now()->subDays(7)->format('Y-m-d')]);
                          });
                    })->orWhere(function($q) {
                        // Fourth reminder: 14 days overdue
                        $q->whereRaw('due_date::date = ?', [Carbon::now()->subDays(14)->format('Y-m-d')])
                          ->where(function($q2) {
                              $q2->whereNull('last_reminder_sent_at')
                                 ->orWhereRaw('last_reminder_sent_at::date < ?', [Carbon::now()->subDays(7)->format('Y-m-d')]);
                          });
                    })->orWhere(function($q) {
                        // Monthly reminders: Every 30 days after 30 days overdue
                        $q->whereRaw('due_date::date < ?', [Carbon::now()->subDays(30)->format('Y-m-d')])
                          ->where(function($q2) {
                              $q2->whereNull('last_reminder_sent_at')
                                 ->orWhereRaw('last_reminder_sent_at::date < ?', [Carbon::now()->subDays(30)->format('Y-m-d')]);
                          });
                    });
                })
                ->get();
            
            $this->logActivityProgress('Found ' . $unpaidReceivables->count() . ' invoices needing reminders');
            
            $processedCount = 0;
            $failedCount = 0;
            
            foreach ($unpaidReceivables as $receivable) {
                try {
                    // Calculate days overdue
                    $daysOverdue = Carbon::now()->diffInDays(Carbon::parse($receivable->due_date), false);
                    $reminderType = $this->getReminderType($daysOverdue);
                    
                    $this->logActivityProgress('Processing reminder for invoice ' . $receivable->invoice_number . ' (' . $reminderType . ')');
                    
                    // Check if invoice has been generated
                    if ($receivable->processing_status === 'completed' || $receivable->invoice_file_path) {
                        // Invoice already generated, just send reminder
                        $this->sendTradeReceivableReminder($receivable, $reminderType);
                    } else {
                        // Generate invoice first, then send reminder
                        ProcessTradeReceivableInvoice::dispatch($receivable->id, 1)
                            ->onQueue('invoices');
                        
                        $this->logActivityProgress('Dispatched invoice generation job for ' . $receivable->invoice_number);
                    }
                    
                    // Update last reminder sent timestamp
                    DB::table('trade_receivables')
                        ->where('id', $receivable->id)
                        ->update([
                            'last_reminder_sent_at' => now(),
                            'reminder_count' => DB::raw('COALESCE(reminder_count, 0) + 1'),
                            'updated_at' => now()
                        ]);
                    
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to process reminder for receivable ID ' . $receivable->id, [
                        'error' => $e->getMessage(),
                        'invoice_number' => $receivable->invoice_number
                    ]);
                }
            }
            
            $this->logActivityProgress('Trade receivables reminder processing completed', [
                'total_found' => $unpaidReceivables->count(),
                'processed' => $processedCount,
                'failed' => $failedCount
            ]);
            
            return $processedCount;
            
        } catch (\Exception $e) {
            Log::error('Error in processTradeReceivablesReminders: ' . $e->getMessage());
            $this->logActivityError('Trade receivables reminder processing failed: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Send reminder for a trade receivable
     */
    protected function sendTradeReceivableReminder($receivable, $reminderType)
    {
        try {
            // Prepare reminder data
            $daysOverdue = Carbon::now()->diffInDays(Carbon::parse($receivable->due_date), false);
            $institution = DB::table('institutions')->where('id', 1)->first();
            
            // Send email reminder if email exists
            if ($receivable->customer_email) {
                $this->sendEmailReminder($receivable, $reminderType, $daysOverdue, $institution);
            }
            
            // Send SMS reminder if phone exists
            if ($receivable->customer_phone) {
                $this->sendSmsReminder($receivable, $reminderType, $daysOverdue);
            }
            
            Log::info('Reminder sent for invoice ' . $receivable->invoice_number, [
                'type' => $reminderType,
                'days_overdue' => $daysOverdue,
                'customer' => $receivable->customer_name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send reminder for invoice ' . $receivable->invoice_number, [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Send email reminder
     */
    protected function sendEmailReminder($receivable, $reminderType, $daysOverdue, $institution)
    {
        $data = [
            'invoice' => $receivable,
            'reminderType' => $reminderType,
            'daysOverdue' => abs($daysOverdue),
            'paymentUrl' => $receivable->payment_link,
            'institution' => $institution,
            'customerName' => $receivable->customer_name
        ];
        
        // Attach invoice PDF if it exists
        $attachments = [];
        if ($receivable->invoice_file_path && file_exists(storage_path('app/' . $receivable->invoice_file_path))) {
            $attachments[] = storage_path('app/' . $receivable->invoice_file_path);
        }
        
        Mail::send('emails.invoice-reminder', $data, function ($message) use ($receivable, $reminderType, $attachments) {
            $message->to($receivable->customer_email, $receivable->customer_name)
                    ->subject($reminderType . ': Invoice ' . $receivable->invoice_number . ' - Payment Reminder');
            
            foreach ($attachments as $attachment) {
                $message->attach($attachment, [
                    'as' => 'invoice_' . $receivable->invoice_number . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        });
    }
    
    /**
     * Send SMS reminder
     */
    protected function sendSmsReminder($receivable, $reminderType, $daysOverdue)
    {
        $smsService = new SmsService();
        
        // Format amount
        $amount = ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2);
        
        // Prepare SMS message based on reminder type
        $message = "Dear {$receivable->customer_name},\n";
        
        if ($daysOverdue > 0) {
            $message .= "REMINDER: Invoice {$receivable->invoice_number} for {$amount} is due in " . abs($daysOverdue) . " days.\n";
        } elseif ($daysOverdue == 0) {
            $message .= "Invoice {$receivable->invoice_number} for {$amount} is due TODAY.\n";
        } else {
            $message .= "OVERDUE: Invoice {$receivable->invoice_number} for {$amount} is " . abs($daysOverdue) . " days overdue.\n";
        }
        
        if ($receivable->control_number) {
            $message .= "Control No: {$receivable->control_number}\n";
        }
        
        if ($receivable->payment_link) {
            $message .= "Pay online: {$receivable->payment_link}\n";
        }
        
        $message .= "Please pay promptly to avoid penalties.";
        
        $smsService->send($receivable->customer_phone, $message);
    }
    
    /**
     * Determine reminder type based on days overdue
     */
    protected function getReminderType($daysOverdue)
    {
        if ($daysOverdue > 0) {
            return 'Pre-Due Reminder';
        } elseif ($daysOverdue == 0) {
            return 'Due Date Reminder';
        } elseif ($daysOverdue >= -7) {
            return 'First Overdue Notice';
        } elseif ($daysOverdue >= -14) {
            return 'Second Overdue Notice';
        } elseif ($daysOverdue >= -30) {
            return 'Third Overdue Notice';
        } else {
            return 'Final Demand Notice';
        }
    }
    
    /**
     * Process payment notifications for payables and receivables
     */
    protected function processPaymentNotifications($triggeredBy = 'system')
    {
        $this->logActivityStart('Payment Notifications', [
            'Process' => 'Payment notifications for payables and receivables',
            'Date' => Carbon::today()->format('Y-m-d')
        ]);

        $this->startActivity('payment_notifications', 'Payment notifications', $triggeredBy);

        try {
            $this->logActivityProgress('Initializing Payment Notification Service');

            $paymentNotificationService = new PaymentNotificationService();
            $result = $paymentNotificationService->processDailyNotifications();

            if ($result['status'] === 'success') {
                $this->completeActivity('payment_notifications', 'Payment notifications completed successfully');
                $this->logActivityComplete('Payment Notifications completed successfully');
            } else {
                throw new \Exception($result['message']);
            }

        } catch (\Exception $e) {
            $this->failActivity('payment_notifications', $e->getMessage());
            $this->logActivityError('Payment Notifications failed: ' . $e->getMessage());
            Log::error('Payment notifications processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - allow other activities to continue
        }
    }

    /**
     * Reset payroll payment status at the start of each month
     * This ensures the payroll payment buttons and flags are reset for the new month
     */
    protected function resetMonthlyPayrollStatus()
    {
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $previousMonth = Carbon::now()->subMonth()->month;
            $previousYear = Carbon::now()->subMonth()->year;

            Log::info('🔄 RESETTING PAYROLL STATUS FOR NEW MONTH', [
                'new_month' => $currentMonth,
                'new_year' => $currentYear,
                'previous_month' => $previousMonth,
                'previous_year' => $previousYear
            ]);

            // Clear any payroll-related caches
            Cache::forget("payroll_status_{$currentMonth}_{$currentYear}");
            Cache::forget("payroll_approval_status_{$currentMonth}_{$currentYear}");
            Cache::forget("payroll_budget_check_{$currentMonth}_{$currentYear}");

            // Log summary of previous month's payroll
            $previousMonthStats = DB::table('pay_rolls')
                ->where('month', $previousMonth)
                ->where('year', $previousYear)
                ->selectRaw('
                    COUNT(*) as total_payrolls,
                    SUM(CASE WHEN status = \'paid\' THEN 1 ELSE 0 END) as paid_count,
                    SUM(CASE WHEN status = \'paid\' THEN gross_salary ELSE 0 END) as total_paid,
                    SUM(CASE WHEN status = \'approved\' THEN 1 ELSE 0 END) as approved_pending,
                    SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending_count
                ')
                ->first();

            Log::info('📊 PREVIOUS MONTH PAYROLL SUMMARY', [
                'month' => $previousMonth,
                'year' => $previousYear,
                'total_payrolls' => $previousMonthStats->total_payrolls ?? 0,
                'paid' => $previousMonthStats->paid_count ?? 0,
                'approved_pending' => $previousMonthStats->approved_pending ?? 0,
                'pending' => $previousMonthStats->pending_count ?? 0,
                'total_amount_paid' => 'TZS ' . number_format($previousMonthStats->total_paid ?? 0, 2)
            ]);

            // Check if there are any existing payrolls for current month
            $currentMonthPayrolls = DB::table('pay_rolls')
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->count();

            if ($currentMonthPayrolls > 0) {
                Log::warning('⚠️ CURRENT MONTH ALREADY HAS PAYROLL ENTRIES', [
                    'month' => $currentMonth,
                    'year' => $currentYear,
                    'count' => $currentMonthPayrolls
                ]);
            }

            Log::info('✅ PAYROLL STATUS RESET COMPLETE FOR NEW MONTH', [
                'new_month' => $currentMonth,
                'new_year' => $currentYear,
                'ready_for_generation' => $currentMonthPayrolls === 0
            ]);

            // Log activity
            $this->logActivityProgress('Monthly payroll status reset completed', 1, 1);

        } catch (\Exception $e) {
            Log::error('❌ ERROR RESETTING MONTHLY PAYROLL STATUS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - allow other activities to continue
        }
    }
} 