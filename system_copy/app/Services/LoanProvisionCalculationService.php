<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoanProvisionCalculationService
{
    // IFRS 9 ECL Stage definitions
    const STAGE_1_PERFORMING = 1;       // 12-month ECL
    const STAGE_2_UNDERPERFORMING = 2;  // Lifetime ECL - not credit-impaired
    const STAGE_3_NON_PERFORMING = 3;   // Lifetime ECL - credit-impaired
    
    // Default provision rates by stage (can be overridden)
    const DEFAULT_STAGE_1_RATE = 1.0;   // 1%
    const DEFAULT_STAGE_2_RATE = 10.0;  // 10%
    const DEFAULT_STAGE_3_RATE = 100.0; // 100%
    
    // Economic scenario adjustments
    const SCENARIO_ADJUSTMENTS = [
        'optimistic' => 0.8,  // 20% reduction in provisions
        'base' => 1.0,        // No adjustment
        'pessimistic' => 1.3, // 30% increase in provisions
    ];
    
    protected $calculationDate;
    protected $method;
    protected $options;
    
    /**
     * Calculate provisions for all active loans
     */
    public function calculateProvisions($date, $method = 'ifrs9', array $options = [])
    {
        $this->calculationDate = Carbon::parse($date);
        $this->method = $method;
        $this->options = $options;
        
        try {
            DB::beginTransaction();
            
            Log::info("Starting provision calculation for {$date} using {$method} method");
            
            // Clear existing provisions for this date
            $this->clearExistingProvisions();
            
            // Get all active loans
            $loans = $this->getActiveLoans();
            
            $totalProvisions = 0;
            $stageStatistics = [
                'stage1' => ['count' => 0, 'exposure' => 0, 'provision' => 0],
                'stage2' => ['count' => 0, 'exposure' => 0, 'provision' => 0],
                'stage3' => ['count' => 0, 'exposure' => 0, 'provision' => 0],
            ];
            
            foreach ($loans as $loan) {
                // Determine ECL stage
                $stage = $this->determineECLStage($loan);
                
                // Calculate provision based on method
                $provisionData = match($method) {
                    'ifrs9' => $this->calculateIFRS9Provision($loan, $stage),
                    'regulatory' => $this->calculateRegulatoryProvision($loan),
                    'hybrid' => $this->calculateHybridProvision($loan, $stage),
                    default => $this->calculateIFRS9Provision($loan, $stage),
                };
                
                // Apply economic scenario adjustment if enabled
                if ($options['include_forward_looking'] ?? false) {
                    $scenario = $options['economic_scenario'] ?? 'base';
                    $provisionData['provision_amount'] *= self::SCENARIO_ADJUSTMENTS[$scenario];
                }
                
                // Store provision record
                $this->storeProvisionRecord($loan, $stage, $provisionData);
                
                // Update statistics
                $stageKey = 'stage' . $stage;
                $stageStatistics[$stageKey]['count']++;
                $stageStatistics[$stageKey]['exposure'] += $loan->loan_balance;
                $stageStatistics[$stageKey]['provision'] += $provisionData['provision_amount'];
                $totalProvisions += $provisionData['provision_amount'];
            }
            
            // Generate provision summary
            $summary = $this->generateProvisionSummary($stageStatistics, $totalProvisions);
            
            // Store summary record
            $this->storeProvisionSummary($summary);
            
            DB::commit();
            
            Log::info("Provision calculation completed. Total provisions: " . number_format($totalProvisions, 2));
            
            return $summary;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Provision calculation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Clear existing provisions for the calculation date
     */
    private function clearExistingProvisions()
    {
        DB::table('loan_loss_provisions')
            ->where('provision_date', $this->calculationDate->format('Y-m-d'))
            ->delete();
    }
    
    /**
     * Get all active loans for provision calculation
     */
    private function getActiveLoans()
    {
        return DB::table('loans')
            ->select([
                'id',
                'loan_id',
                'client_id',
                'client_number',
                'loan_product_id',
                'loan_sub_product',
                'principle',
                'loan_balance',
                'total_arrears',
                'days_in_arrears',
                'loan_classification',
                'disbursement_date',
                'maturity_date',
                'interest_rate',
                'loan_status',
                'collateral_value',
                'guarantor_id',
            ])
            ->where('loan_status', 'active')
            ->where('loan_balance', '>', 0)
            ->get();
    }
    
    /**
     * Determine ECL stage based on loan characteristics
     */
    private function determineECLStage($loan)
    {
        // Stage 3: Credit-impaired (>90 days)
        if ($loan->days_in_arrears > 90 || $loan->loan_classification === 'LOSS') {
            return self::STAGE_3_NON_PERFORMING;
        }
        
        // Stage 2: Significant increase in credit risk (31-90 days)
        if ($loan->days_in_arrears > 30 || 
            in_array($loan->loan_classification, ['SUBSTANDARD', 'DOUBTFUL'])) {
            return self::STAGE_2_UNDERPERFORMING;
        }
        
        // Check for other indicators of significant credit risk increase
        if ($this->hasSignificantCreditRiskIncrease($loan)) {
            return self::STAGE_2_UNDERPERFORMING;
        }
        
        // Stage 1: Performing (0-30 days)
        return self::STAGE_1_PERFORMING;
    }
    
    /**
     * Check for significant increase in credit risk (SICR)
     */
    private function hasSignificantCreditRiskIncrease($loan)
    {
        // Check restructuring flag
        if ($this->isRestructured($loan->loan_id)) {
            return true;
        }
        
        // Check payment history pattern
        if ($this->hasIrregularPaymentPattern($loan->loan_id)) {
            return true;
        }
        
        // Check if loan term has been extended
        if ($this->hasTermExtension($loan)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate IFRS 9 ECL provision
     */
    private function calculateIFRS9Provision($loan, $stage)
    {
        // Get PD, LGD, and EAD
        $pd = $this->calculateProbabilityOfDefault($loan, $stage);
        $lgd = $this->calculateLossGivenDefault($loan);
        $ead = $this->calculateExposureAtDefault($loan);
        
        // Calculate ECL = PD × LGD × EAD
        $ecl = $pd * $lgd * $ead;
        
        // Apply stage-specific adjustments
        if ($stage === self::STAGE_1_PERFORMING) {
            // 12-month ECL
            $ecl = $ecl * (12 / $this->getRemainingMonths($loan));
        }
        // Stages 2 and 3 use lifetime ECL (no adjustment needed)
        
        return [
            'provision_amount' => $ecl,
            'provision_rate' => ($ecl / $loan->loan_balance) * 100,
            'pd_rate' => $pd,
            'lgd_rate' => $lgd,
            'ead_amount' => $ead,
            'calculation_method' => 'IFRS9_ECL',
        ];
    }
    
    /**
     * Calculate regulatory provision (traditional method)
     */
    private function calculateRegulatoryProvision($loan)
    {
        // Get provision rate based on classification
        $rate = $this->getRegulatoryProvisionRate($loan->loan_classification);
        
        // Calculate provision
        $provisionAmount = $loan->loan_balance * ($rate / 100);
        
        return [
            'provision_amount' => $provisionAmount,
            'provision_rate' => $rate,
            'pd_rate' => null,
            'lgd_rate' => null,
            'ead_amount' => $loan->loan_balance,
            'calculation_method' => 'REGULATORY',
        ];
    }
    
    /**
     * Calculate hybrid provision (combination of IFRS 9 and regulatory)
     */
    private function calculateHybridProvision($loan, $stage)
    {
        // Calculate both IFRS 9 and regulatory provisions
        $ifrs9Provision = $this->calculateIFRS9Provision($loan, $stage);
        $regulatoryProvision = $this->calculateRegulatoryProvision($loan);
        
        // Use the higher of the two (conservative approach)
        if ($ifrs9Provision['provision_amount'] > $regulatoryProvision['provision_amount']) {
            $ifrs9Provision['calculation_method'] = 'HYBRID_IFRS9';
            return $ifrs9Provision;
        } else {
            $regulatoryProvision['calculation_method'] = 'HYBRID_REGULATORY';
            return $regulatoryProvision;
        }
    }
    
    /**
     * Calculate Probability of Default (PD)
     */
    private function calculateProbabilityOfDefault($loan, $stage)
    {
        // Base PD rates by stage (can be calibrated based on historical data)
        $basePD = match($stage) {
            self::STAGE_1_PERFORMING => 0.02,      // 2%
            self::STAGE_2_UNDERPERFORMING => 0.15, // 15%
            self::STAGE_3_NON_PERFORMING => 1.0,   // 100%
            default => 0.02,
        };
        
        // Adjust PD based on loan-specific factors
        $adjustmentFactor = 1.0;
        
        // Days in arrears adjustment
        if ($loan->days_in_arrears > 0) {
            $adjustmentFactor += ($loan->days_in_arrears / 365);
        }
        
        // Product risk adjustment
        $productRisk = $this->getProductRiskFactor($loan->loan_sub_product);
        $adjustmentFactor *= $productRisk;
        
        // Client history adjustment
        $clientHistory = $this->getClientHistoryFactor($loan->client_id);
        $adjustmentFactor *= $clientHistory;
        
        return min($basePD * $adjustmentFactor, 1.0); // Cap at 100%
    }
    
    /**
     * Calculate Loss Given Default (LGD)
     */
    private function calculateLossGivenDefault($loan)
    {
        // Base LGD (unsecured)
        $baseLGD = 0.45; // 45% standard for unsecured loans
        
        // Adjust for collateral
        if ($loan->collateral_value > 0) {
            $collateralCoverage = min($loan->collateral_value / $loan->loan_balance, 1.0);
            $recoveryRate = $collateralCoverage * 0.7; // Assume 70% recovery on collateral
            $baseLGD = 1 - $recoveryRate;
        }
        
        // Adjust for guarantor
        if ($loan->guarantor_id) {
            $baseLGD *= 0.8; // 20% reduction for guaranteed loans
        }
        
        return max($baseLGD, 0.1); // Minimum 10% LGD
    }
    
    /**
     * Calculate Exposure at Default (EAD)
     */
    private function calculateExposureAtDefault($loan)
    {
        // For term loans, EAD is the outstanding balance
        $ead = $loan->loan_balance;
        
        // Add undrawn commitments if applicable
        $undrawnCommitments = $this->getUndrawnCommitments($loan->loan_id);
        if ($undrawnCommitments > 0) {
            // Credit Conversion Factor (CCF) for undrawn amounts
            $ccf = 0.5; // 50% standard CCF
            $ead += $undrawnCommitments * $ccf;
        }
        
        return $ead;
    }
    
    /**
     * Get regulatory provision rate based on classification
     */
    private function getRegulatoryProvisionRate($classification)
    {
        $rates = Cache::remember('regulatory_provision_rates', 3600, function () {
            return DB::table('loan_provision_settings')
                ->pluck('rate', 'provision')
                ->toArray();
        });
        
        return match($classification) {
            'PERFORMING' => $rates['PERFORMING'] ?? 1.0,
            'WATCH' => $rates['WATCH'] ?? 5.0,
            'SUBSTANDARD' => $rates['SUBSTANDARD'] ?? 25.0,
            'DOUBTFUL' => $rates['DOUBTFUL'] ?? 50.0,
            'LOSS' => $rates['LOSS'] ?? 100.0,
            default => 1.0,
        };
    }
    
    /**
     * Store provision record for a loan
     */
    private function storeProvisionRecord($loan, $stage, $provisionData)
    {
        DB::table('loan_loss_provisions')->insert([
            'loan_id' => $loan->loan_id,
            'client_id' => $loan->client_id,
            'provision_date' => $this->calculationDate->format('Y-m-d'),
            'loan_balance' => $loan->loan_balance,
            'days_in_arrears' => $loan->days_in_arrears,
            'loan_classification' => $loan->loan_classification,
            'ecl_stage' => $stage,
            'provision_amount' => $provisionData['provision_amount'],
            'provision_rate' => $provisionData['provision_rate'],
            'pd_rate' => $provisionData['pd_rate'],
            'lgd_rate' => $provisionData['lgd_rate'],
            'ead_amount' => $provisionData['ead_amount'],
            'calculation_method' => $provisionData['calculation_method'],
            'economic_scenario' => $this->options['economic_scenario'] ?? 'base',
            'posted_to_gl' => false,
            'created_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Generate provision summary
     */
    private function generateProvisionSummary($stageStatistics, $totalProvisions)
    {
        $totalExposure = array_sum(array_column($stageStatistics, 'exposure'));
        
        return [
            'calculation_date' => $this->calculationDate->format('Y-m-d'),
            'calculation_method' => $this->method,
            'total_loans' => array_sum(array_column($stageStatistics, 'count')),
            'total_exposure' => $totalExposure,
            'total_provisions' => $totalProvisions,
            'provision_coverage' => $totalExposure > 0 ? ($totalProvisions / $totalExposure) * 100 : 0,
            'stage1_statistics' => $stageStatistics['stage1'],
            'stage2_statistics' => $stageStatistics['stage2'],
            'stage3_statistics' => $stageStatistics['stage3'],
            'economic_scenario' => $this->options['economic_scenario'] ?? 'base',
            'include_forward_looking' => $this->options['include_forward_looking'] ?? false,
        ];
    }
    
    /**
     * Store provision summary
     */
    private function storeProvisionSummary($summary)
    {
        DB::table('provision_summaries')->insert([
            'provision_date' => $summary['calculation_date'],
            'calculation_method' => $summary['calculation_method'],
            'total_loans' => $summary['total_loans'],
            'total_exposure' => $summary['total_exposure'],
            'total_provisions' => $summary['total_provisions'],
            'provision_coverage' => $summary['provision_coverage'],
            'stage1_count' => $summary['stage1_statistics']['count'],
            'stage1_exposure' => $summary['stage1_statistics']['exposure'],
            'stage1_provision' => $summary['stage1_statistics']['provision'],
            'stage2_count' => $summary['stage2_statistics']['count'],
            'stage2_exposure' => $summary['stage2_statistics']['exposure'],
            'stage2_provision' => $summary['stage2_statistics']['provision'],
            'stage3_count' => $summary['stage3_statistics']['count'],
            'stage3_exposure' => $summary['stage3_statistics']['exposure'],
            'stage3_provision' => $summary['stage3_statistics']['provision'],
            'economic_scenario' => $summary['economic_scenario'],
            'forward_looking_applied' => $summary['include_forward_looking'],
            'created_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    // Helper methods
    
    private function isRestructured($loanId)
    {
        return DB::table('loan_restructures')
            ->where('loan_id', $loanId)
            ->where('status', 'approved')
            ->exists();
    }
    
    private function hasIrregularPaymentPattern($loanId)
    {
        // Check if loan has missed payments in last 3 months
        $missedPayments = DB::table('loans_schedules')
            ->where('loan_id', $loanId)
            ->where('installment_date', '>=', Carbon::now()->subMonths(3))
            ->where('completion_status', '!=', 'CLOSED')
            ->where('days_in_arrears', '>', 0)
            ->count();
        
        return $missedPayments >= 2;
    }
    
    private function hasTermExtension($loan)
    {
        $originalMaturity = Carbon::parse($loan->disbursement_date)
            ->addMonths($this->getOriginalTerm($loan->loan_product_id));
        
        return Carbon::parse($loan->maturity_date)->gt($originalMaturity);
    }
    
    private function getRemainingMonths($loan)
    {
        $remaining = Carbon::now()->diffInMonths(Carbon::parse($loan->maturity_date));
        return max($remaining, 1); // Minimum 1 month
    }
    
    private function getProductRiskFactor($productName)
    {
        // Product-specific risk factors (can be configured)
        $riskFactors = [
            'EMERGENCY' => 1.5,
            'UNSECURED' => 1.3,
            'PERSONAL' => 1.2,
            'BUSINESS' => 1.1,
            'MORTGAGE' => 0.8,
            'SECURED' => 0.7,
        ];
        
        foreach ($riskFactors as $keyword => $factor) {
            if (stripos($productName, $keyword) !== false) {
                return $factor;
            }
        }
        
        return 1.0; // Default factor
    }
    
    private function getClientHistoryFactor($clientId)
    {
        // Check client's previous loan performance
        $previousDefaults = DB::table('loans')
            ->where('client_id', $clientId)
            ->where('loan_status', 'written_off')
            ->count();
        
        if ($previousDefaults > 0) {
            return 2.0; // Double the PD for clients with previous defaults
        }
        
        // Check if client has good payment history
        $goodLoans = DB::table('loans')
            ->where('client_id', $clientId)
            ->where('loan_status', 'closed')
            ->where('days_in_arrears_at_closure', 0)
            ->count();
        
        if ($goodLoans >= 3) {
            return 0.7; // 30% reduction for good clients
        }
        
        return 1.0; // Default factor
    }
    
    private function getUndrawnCommitments($loanId)
    {
        // Check if loan has any undrawn commitments (e.g., overdraft facilities)
        $commitment = DB::table('loan_commitments')
            ->where('loan_id', $loanId)
            ->where('status', 'active')
            ->first();
        
        if ($commitment) {
            return $commitment->approved_amount - $commitment->drawn_amount;
        }
        
        return 0;
    }
    
    private function getOriginalTerm($loanProductId)
    {
        return DB::table('loan_products')
            ->where('id', $loanProductId)
            ->value('loan_duration') ?? 12; // Default 12 months
    }
}