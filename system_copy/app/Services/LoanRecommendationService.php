<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\Loan_sub_products;

class LoanRecommendationService
{
    public function generate(LoansModel $loan)
    {
        $riskLevel = $this->determineRiskLevel($loan);
        $affordability = $this->calculateAffordability($loan);
        $productConstraints = $this->getProductConstraints($loan);

        $recommendation = [
            'approved_amount' => $this->calculateApprovedAmount($loan, $riskLevel, $affordability, $productConstraints),
            'approved_term' => $this->calculateApprovedTerm($loan, $riskLevel, $productConstraints),
            'interest_rate' => $this->calculateInterestRate($loan, $riskLevel),
            'conditions' => $this->generateConditions($loan, $riskLevel),
            'reasoning' => $this->generateReasoning($loan, $riskLevel, $affordability),
            'risk_level' => $riskLevel,
            'confidence_score' => $this->calculateConfidenceScore($loan)
        ];

        return $recommendation;
    }

    protected function determineRiskLevel(LoansModel $loan)
    {
        $riskScore = 0;

        // Income risk
        $affordabilityRatio = $this->calculateAffordabilityRatio($loan);
        if ($affordabilityRatio > 70) $riskScore += 3;
        elseif ($affordabilityRatio > 50) $riskScore += 2;
        elseif ($affordabilityRatio > 30) $riskScore += 1;

        // Collateral risk
        $ltv = $this->calculateLoanToValue($loan);
        if ($ltv > 80) $riskScore += 3;
        elseif ($ltv > 70) $riskScore += 2;
        elseif ($ltv > 60) $riskScore += 1;

        // Business risk
        if ($loan->business_age < 1) $riskScore += 2;
        elseif ($loan->business_age < 2) $riskScore += 1;

        // Credit history risk
        if ($loan->days_in_arrears > 30) $riskScore += 3;
        elseif ($loan->days_in_arrears > 15) $riskScore += 2;
        elseif ($loan->days_in_arrears > 0) $riskScore += 1;

        if ($riskScore >= 8) return 'HIGH';
        if ($riskScore >= 5) return 'MEDIUM';
        if ($riskScore >= 2) return 'LOW';
        return 'VERY_LOW';
    }

    protected function calculateAffordabilityRatio(LoansModel $loan)
    {
        if ($loan->available_funds <= 0) {
            return 100;
        }

        $monthlyPayment = $this->calculateMonthlyPayment($loan);
        return ($monthlyPayment / $loan->available_funds) * 100;
    }

    protected function calculateMonthlyPayment(LoansModel $loan)
    {
        if ($loan->principle <= 0 || $loan->interest <= 0 || $loan->tenure <= 0) {
            return 0;
        }

        $monthlyRate = $loan->interest / 12 / 100;
        $numerator = $loan->principle * $monthlyRate * pow(1 + $monthlyRate, $loan->tenure);
        $denominator = pow(1 + $monthlyRate, $loan->tenure) - 1;

        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    protected function calculateLoanToValue(LoansModel $loan)
    {
        if ($loan->collateral_value <= 0) {
            return 100;
        }

        return ($loan->principle / $loan->collateral_value) * 100;
    }

    protected function calculateAffordability(LoansModel $loan)
    {
        $monthlyIncome = $loan->available_funds * 2; // Estimate
        $monthlyPayment = $this->calculateMonthlyPayment($loan);
        
        return $monthlyIncome > 0 ? ($monthlyPayment / $monthlyIncome) * 100 : 100;
    }

    protected function getProductConstraints(LoansModel $loan)
    {
        $product = Loan_sub_products::where('sub_product_id', $loan->loan_sub_product)->first();
        
        if (!$product) {
            return [
                'min_amount' => 0,
                'max_amount' => 1000000,
                'min_term' => 1,
                'max_term' => 60,
                'min_interest' => 5,
                'max_interest' => 30
            ];
        }

        return [
            'min_amount' => $product->principle_min_value ?? 0,
            'max_amount' => $product->principle_max_value ?? 1000000,
            'min_term' => $product->min_term ?? 1,
            'max_term' => $product->max_term ?? 60,
            'min_interest' => 5,
            'max_interest' => 30
        ];
    }

    protected function calculateApprovedAmount(LoansModel $loan, $riskLevel, $affordability, $constraints)
    {
        $requestedAmount = $loan->principle;
        $maxAmount = $constraints['max_amount'];
        $minAmount = $constraints['min_amount'];

        // Risk-based adjustments
        $riskAdjustment = $this->getRiskAdjustment($riskLevel);
        $adjustedAmount = $requestedAmount * $riskAdjustment;

        // Affordability-based adjustments
        if ($affordability > 70) {
            $adjustedAmount *= 0.7; // Reduce by 30% if affordability is poor
        } elseif ($affordability > 50) {
            $adjustedAmount *= 0.85; // Reduce by 15% if affordability is moderate
        }

        // Ensure within product constraints
        $approvedAmount = max($minAmount, min($maxAmount, $adjustedAmount));

        return round($approvedAmount, 2);
    }

    protected function calculateApprovedTerm(LoansModel $loan, $riskLevel, $constraints)
    {
        $requestedTerm = $loan->tenure;
        $maxTerm = $constraints['max_term'];
        $minTerm = $constraints['min_term'];

        // Risk-based adjustments
        if ($riskLevel === 'HIGH') {
            $requestedTerm = min($requestedTerm, 24); // Limit high-risk loans to 24 months
        } elseif ($riskLevel === 'MEDIUM') {
            $requestedTerm = min($requestedTerm, 36); // Limit medium-risk loans to 36 months
        }

        return max($minTerm, min($maxTerm, $requestedTerm));
    }

    protected function calculateInterestRate(LoansModel $loan, $riskLevel)
    {
        $baseRate = $loan->interest;

        // Risk-based adjustments
        $riskAdjustments = [
            'VERY_LOW' => 0,
            'LOW' => 1,
            'MEDIUM' => 2,
            'HIGH' => 4
        ];

        $adjustment = $riskAdjustments[$riskLevel] ?? 0;
        $adjustedRate = $baseRate + $adjustment;

        // Ensure within reasonable bounds
        return max(5, min(30, $adjustedRate));
    }

    protected function generateConditions(LoansModel $loan, $riskLevel)
    {
        $conditions = [];

        // Standard conditions
        $conditions[] = 'Regular monthly payments required';
        $conditions[] = 'Insurance coverage mandatory';
        $conditions[] = 'Annual review required';

        // Risk-based conditions
        if ($riskLevel === 'HIGH') {
            $conditions[] = 'Additional collateral required';
            $conditions[] = 'Guarantor mandatory';
            $conditions[] = 'Monthly income verification required';
            $conditions[] = 'Quarterly business review required';
        } elseif ($riskLevel === 'MEDIUM') {
            $conditions[] = 'Guarantor recommended';
            $conditions[] = 'Semi-annual review required';
        }

        // Collateral-based conditions
        if ($loan->collateral_value > 0) {
            $conditions[] = 'Collateral valuation required';
            $conditions[] = 'Collateral insurance mandatory';
        }

        // Business-based conditions
        if ($loan->business_age < 2) {
            $conditions[] = 'Business plan review required';
            $conditions[] = 'Quarterly financial statements required';
        }

        return $conditions;
    }

    protected function generateReasoning(LoansModel $loan, $riskLevel, $affordability)
    {
        $reasoning = [];

        $reasoning[] = "Risk Level: {$riskLevel}";
        $reasoning[] = "Affordability Ratio: " . round($affordability, 1) . "%";

        if ($loan->collateral_value > 0) {
            $ltv = $this->calculateLoanToValue($loan);
            $reasoning[] = "Loan-to-Value Ratio: " . round($ltv, 1) . "%";
        }

        if ($loan->business_age > 0) {
            $reasoning[] = "Business Age: {$loan->business_age} years";
        }

        if ($loan->days_in_arrears > 0) {
            $reasoning[] = "Days in Arrears: {$loan->days_in_arrears} days";
        }

        return $reasoning;
    }

    protected function calculateConfidenceScore(LoansModel $loan)
    {
        $score = 100;

        // Reduce confidence based on missing information
        if (empty($loan->collateral_value)) $score -= 20;
        if (empty($loan->business_age)) $score -= 15;
        if (empty($loan->daily_sales)) $score -= 15;
        if (empty($loan->available_funds)) $score -= 10;

        // Reduce confidence based on risk factors
        $affordabilityRatio = $this->calculateAffordabilityRatio($loan);
        if ($affordabilityRatio > 70) $score -= 20;
        elseif ($affordabilityRatio > 50) $score -= 10;

        $ltv = $this->calculateLoanToValue($loan);
        if ($ltv > 80) $score -= 15;
        elseif ($ltv > 70) $score -= 10;

        return max(0, $score);
    }

    protected function getRiskAdjustment($riskLevel)
    {
        $adjustments = [
            'VERY_LOW' => 1.0,
            'LOW' => 0.95,
            'MEDIUM' => 0.85,
            'HIGH' => 0.7
        ];

        return $adjustments[$riskLevel] ?? 0.85;
    }
} 