<?php

namespace App\Services;

use App\Models\LoanSubProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling all loan-related calculations
 */
class LoanCalculationService
{
    /**
     * Calculate complete loan assessment
     */
    public function calculateAssessment(LoanApplicationData $data): array
    {
        // Use caching for expensive calculations
        $cacheKey = $this->getAssessmentCacheKey($data);
        
        return Cache::remember($cacheKey, 300, function () use ($data) {
            return $this->performAssessmentCalculations($data);
        });
    }
    
    /**
     * Perform actual assessment calculations
     */
    private function performAssessmentCalculations(LoanApplicationData $data): array
    {
        $product = $data->selectedProduct;
        $principal = $data->loanAmount;
        $term = $data->repaymentPeriod;
        
        // Calculate interest
        $interestCalculation = $this->calculateInterest($principal, $term, $product);
        
        // Calculate charges
        $charges = $this->calculateCharges($principal, $product);
        
        // Calculate monthly installment
        $monthlyInstallment = $this->calculateMonthlyInstallment($principal, $term, $product);
        
        // Calculate affordability metrics
        $affordability = $this->calculateAffordability($data, $monthlyInstallment);
        
        // Generate repayment schedule
        $schedule = $this->generateRepaymentSchedule($principal, $term, $product);
        
        // Calculate net disbursement
        $netDisbursement = $this->calculateNetDisbursement($principal, $charges);
        
        return [
            'approved_loan_value' => $principal,
            'approved_term' => $term,
            'interest_rate' => $product->interest_value,
            'interest_method' => $product->interest_method,
            'total_interest' => $interestCalculation['total_interest'],
            'monthly_installment' => $monthlyInstallment,
            'total_amount_payable' => $principal + $interestCalculation['total_interest'],
            'charges' => $charges,
            'total_charges' => array_sum(array_column($charges, 'amount')),
            'net_disbursement' => $netDisbursement,
            'affordability' => $affordability,
            'repayment_schedule' => $schedule,
            'calculation_date' => now()->toDateTimeString(),
        ];
    }
    
    /**
     * Calculate interest based on method
     */
    public function calculateInterest(float $principal, int $term, LoanSubProduct $product): array
    {
        $rate = $product->interest_value / 100;
        $method = $product->interest_method;
        
        if ($method === 'REDUCING_BALANCE') {
            return $this->calculateReducingBalanceInterest($principal, $term, $rate);
        } else {
            return $this->calculateFlatRateInterest($principal, $term, $rate);
        }
    }
    
    /**
     * Calculate reducing balance interest
     */
    private function calculateReducingBalanceInterest(float $principal, int $term, float $annualRate): array
    {
        $monthlyRate = $annualRate / 12;
        $totalInterest = 0;
        $balance = $principal;
        $monthlyPayment = $this->calculatePMT($principal, $monthlyRate, $term);
        
        for ($month = 1; $month <= $term; $month++) {
            $interestComponent = $balance * $monthlyRate;
            $principalComponent = $monthlyPayment - $interestComponent;
            $balance -= $principalComponent;
            $totalInterest += $interestComponent;
        }
        
        return [
            'total_interest' => round($totalInterest, 2),
            'effective_rate' => $annualRate,
            'method' => 'REDUCING_BALANCE',
        ];
    }
    
    /**
     * Calculate flat rate interest
     */
    private function calculateFlatRateInterest(float $principal, int $term, float $annualRate): array
    {
        $years = $term / 12;
        $totalInterest = $principal * $annualRate * $years;
        
        return [
            'total_interest' => round($totalInterest, 2),
            'effective_rate' => $this->calculateEffectiveRate($principal, $totalInterest, $term),
            'method' => 'FLAT_RATE',
        ];
    }
    
    /**
     * Calculate PMT (Payment) for reducing balance loans
     */
    private function calculatePMT(float $principal, float $monthlyRate, int $term): float
    {
        if ($monthlyRate == 0) {
            return $principal / $term;
        }
        
        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $term)) / (pow(1 + $monthlyRate, $term) - 1);
    }
    
    /**
     * Calculate effective interest rate for flat rate loans
     */
    private function calculateEffectiveRate(float $principal, float $totalInterest, int $term): float
    {
        // Approximate effective rate calculation
        $totalAmount = $principal + $totalInterest;
        $monthlyPayment = $totalAmount / $term;
        
        // Use Newton-Raphson method to find effective rate
        $guess = 0.1 / 12; // Start with 10% annual
        
        for ($i = 0; $i < 100; $i++) {
            $pv = $this->calculatePV($monthlyPayment, $guess, $term);
            $diff = $pv - $principal;
            
            if (abs($diff) < 0.01) {
                break;
            }
            
            $dpv = $this->calculatePVDerivative($monthlyPayment, $guess, $term);
            $guess = $guess - $diff / $dpv;
        }
        
        return round($guess * 12 * 100, 2); // Convert to annual percentage
    }
    
    /**
     * Calculate present value
     */
    private function calculatePV(float $payment, float $rate, int $periods): float
    {
        if ($rate == 0) {
            return $payment * $periods;
        }
        
        return $payment * (1 - pow(1 + $rate, -$periods)) / $rate;
    }
    
    /**
     * Calculate present value derivative
     */
    private function calculatePVDerivative(float $payment, float $rate, int $periods): float
    {
        if ($rate == 0) {
            return -$payment * $periods * ($periods + 1) / 2;
        }
        
        $term1 = $periods * pow(1 + $rate, -$periods - 1) / $rate;
        $term2 = (1 - pow(1 + $rate, -$periods)) / ($rate * $rate);
        
        return $payment * ($term1 + $term2);
    }
    
    /**
     * Calculate loan charges
     */
    public function calculateCharges(float $principal, LoanSubProduct $product): array
    {
        $charges = [];
        
        // Processing fee
        if ($product->processing_fee_rate > 0) {
            $charges[] = [
                'name' => 'Processing Fee',
                'rate' => $product->processing_fee_rate,
                'amount' => round($principal * $product->processing_fee_rate / 100, 2),
                'type' => 'PERCENTAGE',
            ];
        }
        
        // Insurance
        if ($product->insurance_rate > 0) {
            $charges[] = [
                'name' => 'Loan Insurance',
                'rate' => $product->insurance_rate,
                'amount' => round($principal * $product->insurance_rate / 100, 2),
                'type' => 'PERCENTAGE',
            ];
        }
        
        // Fixed charges
        if ($product->application_fee > 0) {
            $charges[] = [
                'name' => 'Application Fee',
                'rate' => null,
                'amount' => $product->application_fee,
                'type' => 'FIXED',
            ];
        }
        
        return $charges;
    }
    
    /**
     * Calculate monthly installment
     */
    public function calculateMonthlyInstallment(float $principal, int $term, LoanSubProduct $product): float
    {
        if ($product->interest_method === 'REDUCING_BALANCE') {
            $monthlyRate = $product->interest_value / 100 / 12;
            return round($this->calculatePMT($principal, $monthlyRate, $term), 2);
        } else {
            $totalInterest = $this->calculateFlatRateInterest($principal, $term, $product->interest_value / 100)['total_interest'];
            return round(($principal + $totalInterest) / $term, 2);
        }
    }
    
    /**
     * Calculate affordability metrics
     */
    public function calculateAffordability(LoanApplicationData $data, float $monthlyInstallment): array
    {
        $totalIncome = $data->salaryTakeHome + ($data->monthlyIncome ?? 0);
        $existingObligations = $data->otherLoansAmount ?? 0;
        $totalObligations = $monthlyInstallment + $existingObligations;
        
        $debtToIncomeRatio = $totalIncome > 0 ? $totalObligations / $totalIncome : 1;
        $disposableIncome = $totalIncome - $totalObligations;
        
        // Determine affordability status
        if ($debtToIncomeRatio <= 0.3) {
            $status = 'EXCELLENT';
            $risk = 'LOW';
        } elseif ($debtToIncomeRatio <= 0.4) {
            $status = 'GOOD';
            $risk = 'MEDIUM';
        } elseif ($debtToIncomeRatio <= 0.5) {
            $status = 'FAIR';
            $risk = 'MEDIUM-HIGH';
        } else {
            $status = 'POOR';
            $risk = 'HIGH';
        }
        
        return [
            'total_income' => $totalIncome,
            'existing_obligations' => $existingObligations,
            'new_obligation' => $monthlyInstallment,
            'total_obligations' => $totalObligations,
            'debt_to_income_ratio' => round($debtToIncomeRatio * 100, 2),
            'disposable_income' => $disposableIncome,
            'affordability_status' => $status,
            'risk_level' => $risk,
        ];
    }
    
    /**
     * Generate repayment schedule
     */
    public function generateRepaymentSchedule(float $principal, int $term, LoanSubProduct $product): array
    {
        $schedule = [];
        $balance = $principal;
        $cumulativeInterest = 0;
        $cumulativePrincipal = 0;
        
        if ($product->interest_method === 'REDUCING_BALANCE') {
            $monthlyRate = $product->interest_value / 100 / 12;
            $monthlyPayment = $this->calculatePMT($principal, $monthlyRate, $term);
            
            for ($month = 1; $month <= $term; $month++) {
                $interestComponent = round($balance * $monthlyRate, 2);
                $principalComponent = round($monthlyPayment - $interestComponent, 2);
                $balance = round($balance - $principalComponent, 2);
                
                $cumulativeInterest += $interestComponent;
                $cumulativePrincipal += $principalComponent;
                
                $schedule[] = [
                    'payment_number' => $month,
                    'payment_date' => Carbon::now()->addMonths($month)->format('Y-m-d'),
                    'principal' => $principalComponent,
                    'interest' => $interestComponent,
                    'total_payment' => round($monthlyPayment, 2),
                    'balance' => max(0, $balance),
                    'cumulative_principal' => $cumulativePrincipal,
                    'cumulative_interest' => $cumulativeInterest,
                ];
            }
        } else {
            // Flat rate calculation
            $totalInterest = $this->calculateFlatRateInterest($principal, $term, $product->interest_value / 100)['total_interest'];
            $monthlyInterest = round($totalInterest / $term, 2);
            $monthlyPrincipal = round($principal / $term, 2);
            $monthlyPayment = $monthlyInterest + $monthlyPrincipal;
            
            for ($month = 1; $month <= $term; $month++) {
                $balance = round($balance - $monthlyPrincipal, 2);
                $cumulativeInterest += $monthlyInterest;
                $cumulativePrincipal += $monthlyPrincipal;
                
                $schedule[] = [
                    'payment_number' => $month,
                    'payment_date' => Carbon::now()->addMonths($month)->format('Y-m-d'),
                    'principal' => $monthlyPrincipal,
                    'interest' => $monthlyInterest,
                    'total_payment' => $monthlyPayment,
                    'balance' => max(0, $balance),
                    'cumulative_principal' => $cumulativePrincipal,
                    'cumulative_interest' => $cumulativeInterest,
                ];
            }
        }
        
        return $schedule;
    }
    
    /**
     * Calculate net disbursement amount
     */
    public function calculateNetDisbursement(float $principal, array $charges): float
    {
        $totalCharges = array_sum(array_column($charges, 'amount'));
        return $principal - $totalCharges;
    }
    
    /**
     * Get cache key for assessment
     */
    private function getAssessmentCacheKey(LoanApplicationData $data): string
    {
        return sprintf(
            'loan_assessment_%s_%s_%s_%s',
            $data->member_id,
            $data->selectedProductId,
            $data->loanAmount,
            $data->repaymentPeriod
        );
    }
    
    /**
     * Clear assessment cache
     */
    public function clearAssessmentCache(LoanApplicationData $data): void
    {
        $cacheKey = $this->getAssessmentCacheKey($data);
        Cache::forget($cacheKey);
    }
    
    /**
     * Calculate loan details for the Livewire component
     * This is a simplified version for basic loan calculations
     */
    public function calculateLoanDetails($loanAmount, $interestRate, $repaymentPeriod, $managementFee = 0, $majangaFee = 0)
    {
        try {
            // Convert to proper types
            $principal = (float) $loanAmount;
            $annualRate = (float) $interestRate;
            $termMonths = (int) $repaymentPeriod;
            $mgmtFee = (float) $managementFee;
            $mjFee = (float) $majangaFee;
            
            // Calculate monthly interest rate
            $monthlyRate = $annualRate / (100 * 12);
            
            // Calculate monthly installment using PMT formula
            if ($monthlyRate > 0) {
                $monthlyInstallment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / 
                                    (pow(1 + $monthlyRate, $termMonths) - 1);
            } else {
                $monthlyInstallment = $principal / $termMonths;
            }
            
            // Calculate total amount payable
            $totalAmountPayable = $monthlyInstallment * $termMonths;
            
            // Calculate fees
            $managementFeeAmount = ($mgmtFee / 100) * $principal;
            $majangaFeeAmount = ($mjFee / 100) * $principal;
            
            // Add fees to total amount
            $totalAmountPayable += $managementFeeAmount + $majangaFeeAmount;
            
            // Calculate eligible amount (simple affordability check)
            // Assuming 30% debt-to-income ratio as maximum
            $eligibleAmount = $principal; // For now, return the requested amount
            
            return [
                'monthly_installment' => round($monthlyInstallment, 2),
                'total_amount_payable' => round($totalAmountPayable, 2),
                'eligible_amount' => round($eligibleAmount, 2),
                'total_interest' => round($totalAmountPayable - $principal - $managementFeeAmount - $majangaFeeAmount, 2),
                'management_fee_amount' => round($managementFeeAmount, 2),
                'majanga_fee_amount' => round($majangaFeeAmount, 2)
            ];
            
        } catch (\Exception $e) {
            Log::error('LoanCalculationService: Error calculating loan details', [
                'error' => $e->getMessage(),
                'loan_amount' => $loanAmount,
                'interest_rate' => $interestRate,
                'repayment_period' => $repaymentPeriod
            ]);
            
            // Return default values on error
            return [
                'monthly_installment' => 0,
                'total_amount_payable' => 0,
                'eligible_amount' => 0,
                'total_interest' => 0,
                'management_fee_amount' => 0,
                'majanga_fee_amount' => 0
            ];
        }
    }
}