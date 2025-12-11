<?php

namespace App\Services;

use Carbon\Carbon;
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

class MonthlySystemActivitiesService
{
    protected $previousMonth;
    protected $dividendService;
    protected $interestService;
    protected $notificationService;
    protected $reportService;
    protected $securityService;
    protected $backupService;

    public function __construct(
        DividendCalculationService $dividendService,
        InterestCalculationService $interestService,
        NotificationService $notificationService,
        ReportGenerationService $reportService,
        SecurityService $securityService,
        BackupService $backupService
    ) {
        $this->previousMonth = Carbon::now()->subMonth();
        $this->dividendService = $dividendService;
        $this->interestService = $interestService;
        $this->notificationService = $notificationService;
        $this->reportService = $reportService;
        $this->securityService = $securityService;
        $this->backupService = $backupService;
    }

    public function executeMonthlyActivities()
    {
        try {
            DB::beginTransaction();

            // 1. Financial Core Activities
            $this->processMonthlyLoanActivities();
            $this->processMonthlySavingsAndDeposits();
            $this->processMonthlyShareManagement();
            $this->processMonthlyFinancialReconciliation();

            // 2. Member and Compliance Activities
            $this->processMonthlyMemberServices();
            $this->processMonthlyComplianceAndReporting();

            // 3. System and Security Activities
            $this->processMonthlySystemMaintenance();
            $this->processMonthlySecurityAndAccessControl();

            // 4. Asset and Investment Activities
            $this->processMonthlyAssetManagement();
            $this->processMonthlyInvestmentManagement();
            $this->processMonthlyInsuranceActivities();

            // 5. Document and Performance Activities
            $this->processMonthlyDocumentManagement();
            $this->processMonthlyPerformanceMonitoring();

            // 6. Communication and Notifications
            $this->processMonthlyCommunicationAndNotifications();

            // 7. PPE Depreciation Calculation
            $this->processMonthlyPpeDepreciation();
            
            // 8. Budget Period Close and Versioning
            $this->processMonthlyBudgetClose();

            DB::commit();
            Log::info('Monthly activities completed successfully for ' . $this->previousMonth->format('Y-m'));
            return ['status' => 'success', 'date' => $this->previousMonth->format('Y-m')];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Monthly activities failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function processMonthlyLoanActivities()
    {
        try {
            // Generate monthly loan portfolio report
            $this->reportService->generateMonthlyLoanPortfolioReport($this->previousMonth);
            
            // Process monthly loan loss provisions
            $this->updateMonthlyLoanLossProvisions();
            
            // Generate monthly arrears report
            $this->reportService->generateMonthlyLoanArrearsReport($this->previousMonth);
            
            // Process monthly loan restructuring
            $this->processMonthlyLoanRestructuring();
            
            Log::info('Monthly loan activities completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly loan activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlySavingsAndDeposits()
    {
        try {
            // Generate monthly deposit reports
            $this->reportService->generateMonthlyDepositReport($this->previousMonth);
            
            // Process monthly fixed deposit renewals
            $this->processMonthlyFixedDepositRenewals();
            
            // Generate monthly interest reports
            $this->reportService->generateMonthlyInterestReport($this->previousMonth);
            
            // Process monthly deposit promotions
            $this->processMonthlyDepositPromotions();
            
            Log::info('Monthly savings and deposits activities completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly savings and deposits activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyShareManagement()
    {
        try {
            // Generate monthly share movement report
            $this->reportService->generateMonthlyShareMovementReport($this->previousMonth);
            
            // Process monthly share value adjustments
            $this->processMonthlyShareValueAdjustments();
            
            // Generate monthly dividend reports
            $this->reportService->generateMonthlyDividendReport($this->previousMonth);
            
            // Process monthly share transfers
            $this->processMonthlyShareTransfers();
            
            Log::info('Monthly share management activities completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly share management activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyFinancialReconciliation()
    {
        try {
            // Generate monthly financial statements
            $this->reportService->generateMonthlyFinancialStatements($this->previousMonth);
            
            // Process monthly bank reconciliation
            $this->processMonthlyBankReconciliation();
            
            // Generate monthly trial balance
            $this->reportService->generateMonthlyTrialBalance($this->previousMonth);
            
            // Process monthly general ledger closing
            $this->processMonthlyGeneralLedgerClosing();
            
            Log::info('Monthly financial reconciliation completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly financial reconciliation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyMemberServices()
    {
        try {
            // Generate monthly member statements
            $this->reportService->generateMonthlyMemberStatements($this->previousMonth);
            
            // Process monthly member benefits
            $this->processMonthlyMemberBenefits();
            
            // Generate monthly membership report
            $this->reportService->generateMonthlyMembershipReport($this->previousMonth);
            
            // Process monthly member communications
            $this->processMonthlyMemberCommunications();
            
            Log::info('Monthly member services completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly member services failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyComplianceAndReporting()
    {
        try {
            // Generate monthly regulatory reports
            $this->reportService->generateMonthlyRegulatoryReports($this->previousMonth);
            
            // Process monthly compliance checks
            $this->processMonthlyComplianceChecks();
            
            // Generate monthly audit reports
            $this->reportService->generateMonthlyAuditReports($this->previousMonth);
            
            // Process monthly risk assessments
            $this->processMonthlyRiskAssessments();
            
            Log::info('Monthly compliance and reporting completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly compliance and reporting failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlySystemMaintenance()
    {
        try {
            // Perform monthly database optimization
            $this->performMonthlyDatabaseOptimization();
            
            // Generate monthly system health report
            $this->reportService->generateMonthlySystemHealthReport($this->previousMonth);
            
            // Process monthly system updates
            $this->processMonthlySystemUpdates();
            
            // Perform monthly performance tuning
            $this->performMonthlyPerformanceTuning();
            
            Log::info('Monthly system maintenance completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly system maintenance failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlySecurityAndAccessControl()
    {
        try {
            // Generate monthly security audit report
            $this->reportService->generateMonthlySecurityAuditReport($this->previousMonth);
            
            // Process monthly access reviews
            $this->processMonthlyAccessReviews();
            
            // Generate monthly security incident report
            $this->reportService->generateMonthlySecurityIncidentReport($this->previousMonth);
            
            // Process monthly security updates
            $this->processMonthlySecurityUpdates();
            
            Log::info('Monthly security and access control completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly security and access control failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyAssetManagement()
    {
        try {
            // Generate monthly asset depreciation report
            $this->reportService->generateMonthlyAssetDepreciationReport($this->previousMonth);
            
            // Process monthly asset maintenance schedules
            $this->processMonthlyAssetMaintenanceSchedules();
            
            // Generate monthly asset inventory report
            $this->reportService->generateMonthlyAssetInventoryReport($this->previousMonth);
            
            // Process monthly asset insurance renewals
            $this->processMonthlyAssetInsuranceRenewals();
            
            Log::info('Monthly asset management completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly asset management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyInvestmentManagement()
    {
        try {
            // Generate monthly investment portfolio report
            $this->reportService->generateMonthlyInvestmentPortfolioReport($this->previousMonth);
            
            // Process monthly investment rebalancing
            $this->processMonthlyInvestmentRebalancing();
            
            // Generate monthly investment performance report
            $this->reportService->generateMonthlyInvestmentPerformanceReport($this->previousMonth);
            
            // Process monthly investment maturities
            $this->processMonthlyInvestmentMaturities();
            
            Log::info('Monthly investment management completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly investment management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyInsuranceActivities()
    {
        try {
            // Generate monthly insurance portfolio report
            $this->reportService->generateMonthlyInsurancePortfolioReport($this->previousMonth);
            
            // Process monthly insurance renewals
            $this->processMonthlyInsuranceRenewals();
            
            // Generate monthly claims report
            $this->reportService->generateMonthlyClaimsReport($this->previousMonth);
            
            // Process monthly premium collections
            $this->processMonthlyPremiumCollections();
            
            Log::info('Monthly insurance activities completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly insurance activities failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyDocumentManagement()
    {
        try {
            // Generate monthly document status report
            $this->reportService->generateMonthlyDocumentStatusReport($this->previousMonth);
            
            // Process monthly document archiving
            $this->processMonthlyDocumentArchiving();
            
            // Generate monthly document expiry report
            $this->reportService->generateMonthlyDocumentExpiryReport($this->previousMonth);
            
            // Process monthly document updates
            $this->processMonthlyDocumentUpdates();
            
            Log::info('Monthly document management completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly document management failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyPerformanceMonitoring()
    {
        try {
            // Generate monthly performance metrics report
            $this->reportService->generateMonthlyPerformanceMetricsReport($this->previousMonth);
            
            // Process monthly KPI calculations
            $this->processMonthlyKPICalculations();
            
            // Generate monthly benchmark comparison report
            $this->reportService->generateMonthlyBenchmarkComparisonReport($this->previousMonth);
            
            // Process monthly trend analysis
            $this->processMonthlyTrendAnalysis();
            
            Log::info('Monthly performance monitoring completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly performance monitoring failed: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function processMonthlyCommunicationAndNotifications()
    {
        try {
            // Generate monthly communication report
            $this->reportService->generateMonthlyCommunicationReport($this->previousMonth);
            
            // Process monthly member newsletters
            $this->processMonthlyMemberNewsletters();
            
            // Generate monthly notification report
            $this->reportService->generateMonthlyNotificationReport($this->previousMonth);
            
            // Process monthly communication campaigns
            $this->processMonthlyCommunicationCampaigns();
            
            Log::info('Monthly communication and notifications completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly communication and notifications failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // Helper methods for specific monthly tasks
    private function updateMonthlyLoanLossProvisions()
    {
        // Implementation for updating monthly loan loss provisions
    }

    private function processMonthlyLoanRestructuring()
    {
        // Implementation for processing monthly loan restructuring
    }

    private function processMonthlyFixedDepositRenewals()
    {
        // Implementation for processing monthly fixed deposit renewals
    }

    private function processMonthlyDepositPromotions()
    {
        // Implementation for processing monthly deposit promotions
    }

    private function processMonthlyShareValueAdjustments()
    {
        // Implementation for processing monthly share value adjustments
    }

    private function processMonthlyShareTransfers()
    {
        // Implementation for processing monthly share transfers
    }

    private function processMonthlyBankReconciliation()
    {
        // Implementation for processing monthly bank reconciliation
    }

    private function processMonthlyGeneralLedgerClosing()
    {
        // Implementation for processing monthly general ledger closing
    }

    private function processMonthlyMemberBenefits()
    {
        // Implementation for processing monthly member benefits
    }

    private function processMonthlyMemberCommunications()
    {
        // Implementation for processing monthly member communications
    }

    private function processMonthlyComplianceChecks()
    {
        // Implementation for processing monthly compliance checks
    }

    private function processMonthlyRiskAssessments()
    {
        // Implementation for processing monthly risk assessments
    }

    private function performMonthlyDatabaseOptimization()
    {
        // Implementation for performing monthly database optimization
    }

    private function processMonthlySystemUpdates()
    {
        // Implementation for processing monthly system updates
    }

    private function performMonthlyPerformanceTuning()
    {
        // Implementation for performing monthly performance tuning
    }

    private function processMonthlyAccessReviews()
    {
        // Implementation for processing monthly access reviews
    }

    private function processMonthlySecurityUpdates()
    {
        // Implementation for processing monthly security updates
    }

    private function processMonthlyAssetMaintenanceSchedules()
    {
        // Implementation for processing monthly asset maintenance schedules
    }

    private function processMonthlyAssetInsuranceRenewals()
    {
        // Implementation for processing monthly asset insurance renewals
    }

    private function processMonthlyInvestmentRebalancing()
    {
        // Implementation for processing monthly investment rebalancing
    }

    private function processMonthlyInvestmentMaturities()
    {
        // Implementation for processing monthly investment maturities
    }

    private function processMonthlyInsuranceRenewals()
    {
        // Implementation for processing monthly insurance renewals
    }

    private function processMonthlyPremiumCollections()
    {
        // Implementation for processing monthly premium collections
    }

    private function processMonthlyDocumentArchiving()
    {
        // Implementation for processing monthly document archiving
    }

    private function processMonthlyDocumentUpdates()
    {
        // Implementation for processing monthly document updates
    }

    private function processMonthlyKPICalculations()
    {
        // Implementation for processing monthly KPI calculations
    }

    private function processMonthlyTrendAnalysis()
    {
        // Implementation for processing monthly trend analysis
    }

    private function processMonthlyMemberNewsletters()
    {
        // Implementation for processing monthly member newsletters
    }

    private function processMonthlyCommunicationCampaigns()
    {
        // Implementation for processing monthly communication campaigns
    }

    /**
     * Process monthly PPE depreciation calculation
     */
    protected function processMonthlyPpeDepreciation()
    {
        try {
            Log::info('Starting monthly PPE depreciation calculation for ' . $this->previousMonth->format('Y-m'));
            
            // Dispatch the PPE depreciation job for all institutions
            $institutions = DB::table('institutions')->pluck('id');
            
            foreach ($institutions as $institutionId) {
                try {
                    // Dispatch the job for each institution
                    \App\Jobs\CalculatePpeDepreciation::dispatch($institutionId);
                    
                    Log::info('PPE depreciation job dispatched for institution', [
                        'institution_id' => $institutionId,
                        'period' => $this->previousMonth->format('Y-m')
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch PPE depreciation job for institution', [
                        'institution_id' => $institutionId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other institutions even if one fails
                }
            }
            
            Log::info('Monthly PPE depreciation calculation completed for ' . $this->previousMonth->format('Y-m'));
        } catch (\Exception $e) {
            Log::error('Monthly PPE depreciation calculation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process monthly budget close and create version snapshots
     */
    protected function processMonthlyBudgetClose()
    {
        try {
            Log::info('Starting monthly budget close and versioning for ' . $this->previousMonth->format('Y-m'));
            
            // Get all active budgets
            $budgets = \App\Models\BudgetManagement::where('status', 'ACTIVE')->get();
            
            if ($budgets->isEmpty()) {
                Log::info('No active budgets found for monthly close');
                return;
            }
            
            $period = $this->previousMonth->format('Y-m');
            $successCount = 0;
            $failCount = 0;
            
            foreach ($budgets as $budget) {
                try {
                    // Recalculate metrics before creating version
                    $budget->calculateBudgetMetrics();
                    
                    // Create monthly close version
                    $versionNumber = \App\Models\BudgetVersion::where('budget_id', $budget->id)->count() + 1;
                    
                    \App\Models\BudgetVersion::create([
                        'budget_id' => $budget->id,
                        'version_number' => $versionNumber,
                        'version_name' => "Version {$versionNumber} - {$period} Monthly Close",
                        'version_type' => 'MONTHLY_CLOSE',
                        'allocated_amount' => $budget->allocated_amount,
                        'spent_amount' => $budget->spent_amount,
                        'committed_amount' => $budget->committed_amount,
                        'effective_from' => $this->previousMonth->endOfMonth(),
                        'created_by' => 1, // System generated
                        'revision_reason' => "Automatic monthly close for {$period}",
                        'change_summary' => json_encode([
                            'period' => $period,
                            'period_type' => 'monthly',
                            'utilization_percentage' => $budget->utilization_percentage,
                            'variance_amount' => $budget->variance_amount,
                            'available_amount' => $budget->available_amount,
                            'closed_at' => now()->toDateTimeString(),
                            'closed_by' => 'Monthly System Activities'
                        ]),
                        'is_active' => false // Monthly close versions are snapshots
                    ]);
                    
                    // Update last period close timestamp
                    $budget->last_period_close = $this->previousMonth->endOfMonth();
                    $budget->last_closed_period = $period;
                    $budget->saveQuietly();
                    
                    $successCount++;
                    
                    Log::info('Monthly budget close version created', [
                        'budget_id' => $budget->id,
                        'budget_name' => $budget->budget_name,
                        'period' => $period,
                        'version_number' => $versionNumber
                    ]);
                    
                } catch (\Exception $e) {
                    $failCount++;
                    Log::error('Failed to create monthly close version for budget', [
                        'budget_id' => $budget->id,
                        'budget_name' => $budget->budget_name,
                        'period' => $period,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Monthly budget close completed', [
                'period' => $period,
                'total_budgets' => $budgets->count(),
                'success' => $successCount,
                'failed' => $failCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Monthly budget close and versioning failed: ' . $e->getMessage());
            // Don't throw - allow other monthly activities to continue
        }
    }
} 