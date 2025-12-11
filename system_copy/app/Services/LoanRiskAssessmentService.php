<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\DB;

class LoanRiskAssessmentService
{
    public function calculate(LoansModel $loan)
    {
        $riskFactors = [
            'income_risk' => $this->calculateIncomeRisk($loan),
            'collateral_risk' => $this->calculateCollateralRisk($loan),
            'credit_history_risk' => $this->calculateCreditHistoryRisk($loan),
            'business_risk' => $this->calculateBusinessRisk($loan),
            'market_risk' => $this->calculateMarketRisk($loan)
        ];

        $totalRiskScore = array_sum($riskFactors);
        $riskLevel = $this->determineRiskLevel($totalRiskScore);

        return [
            'total_score' => $totalRiskScore,
            'level' => $riskLevel,
            'factors' => $riskFactors,
            'recommendations' => $this->getRiskRecommendations($riskLevel, $riskFactors)
        ];
    }

    protected function calculateIncomeRisk(LoansModel $loan)
    {
        $riskScore = 0;

        // Affordability ratio risk
        $affordabilityRatio = $this->calculateAffordabilityRatio($loan);
        
        if ($affordabilityRatio > 70) {
            $riskScore += 5; // High risk
        } elseif ($affordabilityRatio > 50) {
            $riskScore += 3; // Medium risk
        } elseif ($affordabilityRatio > 30) {
            $riskScore += 1; // Low risk
        }

        // Income stability risk
        if ($loan->business_age < 1) {
            $riskScore += 3;
        } elseif ($loan->business_age < 2) {
            $riskScore += 2;
        } elseif ($loan->business_age < 3) {
            $riskScore += 1;
        }

        // Income source diversification
        $incomeSources = $this->countIncomeSources($loan);
        if ($incomeSources <= 1) {
            $riskScore += 2;
        }

        return $riskScore;
    }

    protected function calculateCollateralRisk(LoansModel $loan)
    {
        $riskScore = 0;

        if ($loan->collateral_value <= 0) {
            $riskScore += 5; // No collateral
            return $riskScore;
        }

        // Loan-to-Value ratio
        $ltv = ($loan->principle / $loan->collateral_value) * 100;
        
        if ($ltv > 80) {
            $riskScore += 4;
        } elseif ($ltv > 70) {
            $riskScore += 3;
        } elseif ($ltv > 60) {
            $riskScore += 2;
        } elseif ($ltv > 50) {
            $riskScore += 1;
        }

        // Collateral type risk
        $collateralTypeRisk = $this->getCollateralTypeRisk($loan->collateral_type);
        $riskScore += $collateralTypeRisk;

        // Collateral liquidity risk
        $liquidityRisk = $this->getCollateralLiquidityRisk($loan->collateral_type);
        $riskScore += $liquidityRisk;

        return $riskScore;
    }

    protected function calculateCreditHistoryRisk(LoansModel $loan)
    {
        $riskScore = 0;

        // Days in arrears
        if ($loan->days_in_arrears > 90) {
            $riskScore += 5;
        } elseif ($loan->days_in_arrears > 60) {
            $riskScore += 4;
        } elseif ($loan->days_in_arrears > 30) {
            $riskScore += 3;
        } elseif ($loan->days_in_arrears > 15) {
            $riskScore += 2;
        } elseif ($loan->days_in_arrears > 0) {
            $riskScore += 1;
        }

        // Previous loan performance
        $previousLoans = $this->getPreviousLoans($loan->client_number);
        $defaultRate = $this->calculateDefaultRate($previousLoans);
        
        if ($defaultRate > 0.5) {
            $riskScore += 4;
        } elseif ($defaultRate > 0.3) {
            $riskScore += 3;
        } elseif ($defaultRate > 0.1) {
            $riskScore += 2;
        }

        // Credit utilization
        $creditUtilization = $this->calculateCreditUtilization($loan->client_number);
        if ($creditUtilization > 0.8) {
            $riskScore += 3;
        } elseif ($creditUtilization > 0.6) {
            $riskScore += 2;
        } elseif ($creditUtilization > 0.4) {
            $riskScore += 1;
        }

        return $riskScore;
    }

    protected function calculateBusinessRisk(LoansModel $loan)
    {
        $riskScore = 0;

        // Business age
        if ($loan->business_age < 1) {
            $riskScore += 4;
        } elseif ($loan->business_age < 2) {
            $riskScore += 3;
        } elseif ($loan->business_age < 3) {
            $riskScore += 2;
        } elseif ($loan->business_age < 5) {
            $riskScore += 1;
        }

        // Business type risk
        $businessTypeRisk = $this->getBusinessTypeRisk($loan->business_type);
        $riskScore += $businessTypeRisk;

        // Business profitability
        $profitability = $this->calculateProfitability($loan);
        if ($profitability < 0.1) {
            $riskScore += 3;
        } elseif ($profitability < 0.2) {
            $riskScore += 2;
        } elseif ($profitability < 0.3) {
            $riskScore += 1;
        }

        return $riskScore;
    }

    protected function calculateMarketRisk(LoansModel $loan)
    {
        $riskScore = 0;

        // Industry risk (simplified)
        $industryRisk = $this->getIndustryRisk($loan->business_category);
        $riskScore += $industryRisk;

        // Economic conditions (simplified)
        $economicRisk = $this->getEconomicRisk();
        $riskScore += $economicRisk;

        return $riskScore;
    }

    protected function determineRiskLevel($totalScore)
    {
        if ($totalScore >= 15) {
            return 'HIGH';
        } elseif ($totalScore >= 10) {
            return 'MEDIUM';
        } elseif ($totalScore >= 5) {
            return 'LOW';
        } else {
            return 'VERY_LOW';
        }
    }

    protected function getRiskRecommendations($riskLevel, $riskFactors)
    {
        $recommendations = [];

        if ($riskLevel === 'HIGH') {
            $recommendations[] = 'Require additional collateral';
            $recommendations[] = 'Reduce loan amount';
            $recommendations[] = 'Increase interest rate';
            $recommendations[] = 'Require guarantor';
        } elseif ($riskLevel === 'MEDIUM') {
            $recommendations[] = 'Monitor closely';
            $recommendations[] = 'Consider additional security';
        } elseif ($riskLevel === 'LOW') {
            $recommendations[] = 'Standard terms apply';
        }

        // Specific recommendations based on risk factors
        if ($riskFactors['income_risk'] > 3) {
            $recommendations[] = 'Verify income sources';
        }

        if ($riskFactors['collateral_risk'] > 3) {
            $recommendations[] = 'Reassess collateral value';
        }

        if ($riskFactors['credit_history_risk'] > 3) {
            $recommendations[] = 'Review payment history';
        }

        return $recommendations;
    }

    // Helper methods
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

    protected function countIncomeSources(LoansModel $loan)
    {
        $sources = 0;
        
        if ($loan->daily_sales > 0) $sources++;
        if ($loan->cash_at_hand > 0) $sources++;
        if ($loan->business_inventory > 0) $sources++;
        
        return $sources;
    }

    protected function getCollateralTypeRisk($collateralType)
    {
        $riskMap = [
            'REAL_ESTATE' => 1,
            'VEHICLE' => 2,
            'EQUIPMENT' => 3,
            'INVENTORY' => 4,
            'CASH' => 0,
            'SECURITIES' => 1
        ];

        return $riskMap[$collateralType] ?? 3;
    }

    protected function getCollateralLiquidityRisk($collateralType)
    {
        $liquidityMap = [
            'CASH' => 0,
            'SECURITIES' => 1,
            'REAL_ESTATE' => 3,
            'VEHICLE' => 2,
            'EQUIPMENT' => 4,
            'INVENTORY' => 3
        ];

        return $liquidityMap[$collateralType] ?? 3;
    }

    protected function getPreviousLoans($clientNumber)
    {
        return DB::table('loans')
            ->where('client_number', $clientNumber)
            ->where('id', '!=', $this->id ?? 0)
            ->get();
    }

    protected function calculateDefaultRate($loans)
    {
        if ($loans->isEmpty()) {
            return 0;
        }

        $defaulted = $loans->where('status', 'DEFAULTED')->count();
        return $defaulted / $loans->count();
    }

    protected function calculateCreditUtilization($clientNumber)
    {
        $activeLoans = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->where('status', 'ACTIVE')
            ->sum('principle');

        $totalApproved = DB::table('loans')
            ->where('client_number', $clientNumber)
            ->sum('approved_loan_value');

        return $totalApproved > 0 ? $activeLoans / $totalApproved : 0;
    }

    protected function getBusinessTypeRisk($businessType)
    {
        $riskMap = [
            'RETAIL' => 2,
            'WHOLESALE' => 2,
            'MANUFACTURING' => 3,
            'SERVICES' => 1,
            'AGRICULTURE' => 4,
            'CONSTRUCTION' => 3
        ];

        return $riskMap[$businessType] ?? 2;
    }

    protected function calculateProfitability(LoansModel $loan)
    {
        if ($loan->daily_sales <= 0) {
            return 0;
        }

        $monthlySales = $loan->daily_sales * 30;
        $monthlyCosts = $loan->cost_of_goods_sold + $loan->operating_expenses;
        
        if ($monthlyCosts <= 0) {
            return 0;
        }

        return ($monthlySales - $monthlyCosts) / $monthlySales;
    }

    protected function getIndustryRisk($businessCategory)
    {
        // Simplified industry risk assessment
        $riskMap = [
            'AGRICULTURE' => 3,
            'MANUFACTURING' => 2,
            'SERVICES' => 1,
            'RETAIL' => 2,
            'CONSTRUCTION' => 3
        ];

        return $riskMap[$businessCategory] ?? 2;
    }

    protected function getEconomicRisk()
    {
        // Simplified economic risk (could be enhanced with real economic data)
        return 1; // Base economic risk
    }
} 