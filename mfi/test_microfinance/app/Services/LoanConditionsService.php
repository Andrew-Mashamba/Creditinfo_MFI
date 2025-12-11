<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\ClientsModel;

class LoanConditionsService
{
    public function determine(LoansModel $loan)
    {
        $conditions = [];

        // Standard conditions
        $conditions = array_merge($conditions, $this->getStandardConditions());

        // Risk-based conditions
        $conditions = array_merge($conditions, $this->getRiskBasedConditions($loan));

        // Collateral-based conditions
        $conditions = array_merge($conditions, $this->getCollateralConditions($loan));

        // Business-based conditions
        $conditions = array_merge($conditions, $this->getBusinessConditions($loan));

        // Client-based conditions
        $conditions = array_merge($conditions, $this->getClientConditions($loan));

        // Regulatory conditions
        $conditions = array_merge($conditions, $this->getRegulatoryConditions($loan));

        return $conditions;
    }

    protected function getStandardConditions()
    {
        return [
            [
                'type' => 'PAYMENT',
                'description' => 'Regular monthly payments must be made on or before the due date',
                'priority' => 'HIGH',
                'mandatory' => true
            ],
            [
                'type' => 'INSURANCE',
                'description' => 'Comprehensive insurance coverage is mandatory for the loan duration',
                'priority' => 'HIGH',
                'mandatory' => true
            ],
            [
                'type' => 'REVIEW',
                'description' => 'Annual loan review and assessment required',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ],
            [
                'type' => 'NOTIFICATION',
                'description' => 'Borrower must notify lender of any material changes in financial circumstances',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ]
        ];
    }

    protected function getRiskBasedConditions(LoansModel $loan)
    {
        $conditions = [];
        $riskLevel = $this->determineRiskLevel($loan);

        if ($riskLevel === 'HIGH') {
            $conditions[] = [
                'type' => 'COLLATERAL',
                'description' => 'Additional collateral or security required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'GUARANTOR',
                'description' => 'Personal guarantor with adequate income required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'MONITORING',
                'description' => 'Monthly income and expense verification required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'REVIEW',
                'description' => 'Quarterly business and financial review required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
        } elseif ($riskLevel === 'MEDIUM') {
            $conditions[] = [
                'type' => 'GUARANTOR',
                'description' => 'Personal guarantor recommended',
                'priority' => 'MEDIUM',
                'mandatory' => false
            ];
            $conditions[] = [
                'type' => 'REVIEW',
                'description' => 'Semi-annual business review required',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ];
        }

        return $conditions;
    }

    protected function getCollateralConditions(LoansModel $loan)
    {
        $conditions = [];

        if ($loan->collateral_value > 0) {
            $conditions[] = [
                'type' => 'VALUATION',
                'description' => 'Professional collateral valuation required before disbursement',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'INSURANCE',
                'description' => 'Collateral insurance with lender as beneficiary required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'MAINTENANCE',
                'description' => 'Collateral must be maintained in good condition',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'ACCESS',
                'description' => 'Lender must have access to inspect collateral when required',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ];
        }

        return $conditions;
    }

    protected function getBusinessConditions(LoansModel $loan)
    {
        $conditions = [];

        if ($loan->business_age < 2) {
            $conditions[] = [
                'type' => 'BUSINESS_PLAN',
                'description' => 'Detailed business plan and financial projections required',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'FINANCIAL_STATEMENTS',
                'description' => 'Quarterly financial statements must be submitted',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
            $conditions[] = [
                'type' => 'MENTORSHIP',
                'description' => 'Business mentorship program participation required',
                'priority' => 'MEDIUM',
                'mandatory' => false
            ];
        }

        if ($loan->business_age < 1) {
            $conditions[] = [
                'type' => 'GRADUAL_DISBURSEMENT',
                'description' => 'Loan disbursement in phases based on business milestones',
                'priority' => 'HIGH',
                'mandatory' => true
            ];
        }

        // Industry-specific conditions
        $industryConditions = $this->getIndustrySpecificConditions($loan->business_category);
        $conditions = array_merge($conditions, $industryConditions);

        return $conditions;
    }

    protected function getClientConditions(LoansModel $loan)
    {
        $conditions = [];
        $client = ClientsModel::where('client_number', $loan->client_number)->first();

        if (!$client) {
            return $conditions;
        }

        // Age-based conditions
        if ($client->date_of_birth) {
            $age = now()->diffInYears($client->date_of_birth);
            if ($age > 60) {
                $conditions[] = [
                    'type' => 'RETIREMENT_PLAN',
                    'description' => 'Retirement plan and exit strategy required',
                    'priority' => 'MEDIUM',
                    'mandatory' => true
                ];
            }
        }

        // Employment-based conditions
        if ($client->membership_type === 'Individual' && !empty($client->employer_name)) {
            $conditions[] = [
                'type' => 'EMPLOYMENT_VERIFICATION',
                'description' => 'Employment verification and salary confirmation required',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ];
        }

        return $conditions;
    }

    protected function getRegulatoryConditions(LoansModel $loan)
    {
        return [
            [
                'type' => 'COMPLIANCE',
                'description' => 'All regulatory and compliance requirements must be met',
                'priority' => 'HIGH',
                'mandatory' => true
            ],
            [
                'type' => 'REPORTING',
                'description' => 'Regular reporting to regulatory authorities as required',
                'priority' => 'HIGH',
                'mandatory' => true
            ],
            [
                'type' => 'DOCUMENTATION',
                'description' => 'All required documentation must be maintained and updated',
                'priority' => 'MEDIUM',
                'mandatory' => true
            ]
        ];
    }

    protected function getIndustrySpecificConditions($businessCategory)
    {
        $conditions = [];

        switch ($businessCategory) {
            case 'AGRICULTURE':
                $conditions[] = [
                    'type' => 'SEASONAL_PLANNING',
                    'description' => 'Seasonal cash flow planning and management required',
                    'priority' => 'MEDIUM',
                    'mandatory' => true
                ];
                $conditions[] = [
                    'type' => 'CROP_INSURANCE',
                    'description' => 'Crop insurance coverage recommended',
                    'priority' => 'MEDIUM',
                    'mandatory' => false
                ];
                break;

            case 'MANUFACTURING':
                $conditions[] = [
                    'type' => 'QUALITY_CONTROL',
                    'description' => 'Quality control and certification requirements',
                    'priority' => 'MEDIUM',
                    'mandatory' => true
                ];
                break;

            case 'CONSTRUCTION':
                $conditions[] = [
                    'type' => 'PROJECT_MANAGEMENT',
                    'description' => 'Project management and timeline adherence required',
                    'priority' => 'HIGH',
                    'mandatory' => true
                ];
                break;

            case 'RETAIL':
                $conditions[] = [
                    'type' => 'INVENTORY_MANAGEMENT',
                    'description' => 'Inventory management system and reporting required',
                    'priority' => 'MEDIUM',
                    'mandatory' => true
                ];
                break;
        }

        return $conditions;
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
} 