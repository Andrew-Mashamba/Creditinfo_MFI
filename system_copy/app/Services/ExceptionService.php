<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExceptionService
{
    protected $loan;
    protected $product;
    protected $member;
    protected $creditScoreData;
    protected $approvedLoanValue;
    protected $approvedTerm;
    protected $takeHome;
    protected $monthlyInstallmentValue;
    protected $collateralValue;
    protected $isPhysicalCollateral;

    public function __construct($loan, $product, $member, $creditScoreData, $approvedLoanValue, $approvedTerm, $takeHome, $monthlyInstallmentValue, $collateralValue, $isPhysicalCollateral)
    {
        $this->loan = $loan;
        $this->product = $product;
        $this->member = $member;
        $this->creditScoreData = $creditScoreData;
        
        // Validate and sanitize input values to prevent unrealistic values
        $this->approvedLoanValue = $this->validateNumericValue($approvedLoanValue, 'approved_loan_value', 0, 1000000000);
        $this->approvedTerm = $this->validateNumericValue($approvedTerm, 'approved_term', 1, 360);
        $this->takeHome = $this->validateNumericValue($takeHome, 'take_home', 0, 100000000);
        $this->monthlyInstallmentValue = $this->validateNumericValue($monthlyInstallmentValue, 'monthly_installment_value', 0, 100000000);
        $this->collateralValue = $this->validateNumericValue($collateralValue, 'collateral_value', 0, 1000000000);
        $this->isPhysicalCollateral = (bool)$isPhysicalCollateral;

        Log::info('ExceptionService: Initializing with validated parameters', [
            'loan_id' => $loan->id ?? 'N/A',
            'product_id' => $product->id ?? 'N/A',
            'member_id' => $member->id ?? 'N/A',
            'approved_loan_value' => $this->approvedLoanValue,
            'approved_term' => $this->approvedTerm,
            'take_home' => $this->takeHome,
            'monthly_installment_value' => $this->monthlyInstallmentValue,
            'collateral_value' => $this->collateralValue,
            'is_physical_collateral' => $this->isPhysicalCollateral,
            'credit_score' => $creditScoreData['score'] ?? 'N/A',
            'credit_grade' => $creditScoreData['grade'] ?? 'N/A'
        ]);
    }

    /**
     * Validate numeric values to prevent unrealistic values
     */
    private function validateNumericValue($value, $fieldName, $min, $max)
    {
        $numericValue = (float)$value;
        
        if ($numericValue < $min || $numericValue > $max) {
            Log::error("ExceptionService: Invalid {$fieldName} value", [
                'value' => $numericValue,
                'min' => $min,
                'max' => $max
            ]);
            return 0; // Return 0 for invalid values
        }
        
        return $numericValue;
    }

    public function getExceptions()
    {
        Log::info('ExceptionService: Starting exception validation process');
        
        try {
            $exceptions = [
                'loan_amount' => $this->validateLoanAmount(),
                'term' => $this->validateTerm(),
                'credit_score' => $this->validateCreditScore(),
                'salary_installment' => $this->validateSalaryInstallment(),
                'collateral' => $this->validateCollateral(),
            ];
            
            // Add LTV check separately if product has LTV limit and there's collateral value
            $ltvLimit = (float)($this->product->ltv ?? 0);
            $collateralValue = (float)$this->collateralValue;
            if ($ltvLimit > 0 && $collateralValue > 0) {
                $exceptions['ltv'] = $this->getLTVForDisplay();
            }
            
            $exceptions['summary'] = $this->getSummary();

            Log::info('ExceptionService: Exception validation completed successfully', [
                'total_exceptions' => count($exceptions),
                'summary_status' => $exceptions['summary']['overall_status'] ?? 'N/A',
                'can_approve' => $exceptions['summary']['can_approve'] ?? false,
                'requires_exception' => $exceptions['summary']['requires_exception'] ?? false
            ]);

            return $exceptions;
        } catch (\Exception $e) {
            Log::error('ExceptionService: Error during exception validation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function validateLoanAmount()
    {
        Log::info('ExceptionService: Starting loan amount validation');
        
        $limit = (float)$this->product->principle_max_value;
        $given = (float)$this->approvedLoanValue;
        $isExceeded = $given > $limit;
        $percentage = $limit > 0 ? round(($given / $limit) * 100, 2) : 0;
        
        Log::info('ExceptionService: Loan amount validation details', [
            'product_max_value' => $limit,
            'approved_loan_value' => $given,
            'is_exceeded' => $isExceeded,
            'percentage_of_limit' => $percentage,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none'
        ]);
        
        $result = [
            'name' => 'Maximum Loan Amount',
            'description' => $isExceeded ? 'Loan amount exceeds product maximum limit' : 'Loan amount within product limits',
            'limit' => $limit,
            'given' => $given,
            'unit' => 'TZS',
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isExceeded ? 'Reduce loan amount or consider alternative product' : null
        ];

        Log::info('ExceptionService: Loan amount validation result', $result);
        return $result;
    }

    protected function validateTerm()
    {
        Log::info('ExceptionService: Starting term validation');
        
        $limit = (int)$this->product->max_term;
        $given = (int)$this->approvedTerm;
        $isExceeded = $given > $limit;
        $percentage = $limit > 0 ? round(($given / $limit) * 100, 2) : 0;
        
        Log::info('ExceptionService: Term validation details', [
            'product_max_term' => $limit,
            'approved_term' => $given,
            'is_exceeded' => $isExceeded,
            'percentage_of_limit' => $percentage,
            'status' => $isExceeded ? 'EXCIN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none'
        ]);
        
        $result = [
            'name' => 'Maximum Term',
            'description' => $isExceeded ? 'Loan term exceeds product maximum limit' : 'Loan term within product limits',
            'limit' => $limit,
            'given' => $given,
            'unit' => 'months',
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isExceeded ? 'Reduce loan term or consider alternative product' : null
        ];

        Log::info('ExceptionService: Term validation result', $result);
        return $result;
    }

    protected function validateCreditScore()
    {
        Log::info('ExceptionService: Starting credit score validation');
        
        $limit = (int)($this->product->score_limit ?? 0);
        $given = (int)($this->creditScoreData['score'] ?? 500);
        $grade = $this->creditScoreData['grade'] ?? 'E';
        $isBelow = $given < $limit;
        $percentage = $limit > 0 ? round(($given / $limit) * 100, 2) : 0;
        
        Log::info('ExceptionService: Credit score validation details', [
            'product_score_limit' => $limit,
            'client_score' => $given,
            'client_grade' => $grade,
            'is_below_limit' => $isBelow,
            'percentage_of_limit' => $percentage,
            'status' => $isBelow ? 'BELOW LIMIT' : 'ACCEPTED',
            'severity' => $isBelow ? 'high' : 'none'
        ]);
        
        $result = [
            'name' => 'Credit Score',
            'description' => $isBelow ? 'Credit score below product minimum requirement' : 'Credit score meets product requirements',
            'limit' => $limit,
            'given' => $given,
            'grade' => $grade,
            'unit' => 'score',
            'is_exceeded' => $isBelow,
            'status' => $isBelow ? 'BELOW LIMIT' : 'ACCEPTED',
            'severity' => $isBelow ? 'high' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isBelow ? 'Improve credit score or consider alternative product' : null
        ];

        Log::info('ExceptionService: Credit score validation result', $result);
        return $result;
    }

    protected function validateSalaryInstallment()
    {
        Log::info('ExceptionService: Starting salary/installment validation');
        
        // Validate inputs to prevent unrealistic values
        $takeHome = (float)$this->takeHome;
        $monthlyInstallment = (float)$this->monthlyInstallmentValue;
        
        // Validate take home salary
        if ($takeHome < 0 || $takeHome > 100000000) {
            Log::error('ExceptionService: Invalid take home salary', ['take_home' => $takeHome]);
            $takeHome = 0;
        }
        
        // Validate monthly installment
        if ($monthlyInstallment < 0 || $monthlyInstallment > 100000000) {
            Log::error('ExceptionService: Invalid monthly installment', ['monthly_installment' => $monthlyInstallment]);
            $monthlyInstallment = 0;
        }
        
        $limit = $takeHome > 0 ? $takeHome / 2 : 0;
        $given = $monthlyInstallment;
        $isExceeded = $takeHome > 0 ? $given > $limit : true;
        $percentage = $limit > 0 ? round(($given / $limit) * 100, 2) : 0;
        
        // Additional validation for percentage to prevent unrealistic display
        if ($percentage > 10000) {
            Log::error('ExceptionService: Unrealistic percentage calculated', [
                'percentage' => $percentage,
                'given' => $given,
                'limit' => $limit
            ]);
            $percentage = 10000; // Cap at 10000% for display purposes
        }
        
        Log::info('ExceptionService: Salary/installment validation details', [
            'take_home_salary' => $takeHome,
            'monthly_installment' => $given,
            'limit_50_percent' => $limit,
            'is_exceeded' => $isExceeded,
            'percentage_of_limit' => $percentage,
            'status' => $isExceeded ? 'ABOVE LIMIT' : 'ACCEPTED',
            'severity' => $isExceeded ? 'medium' : 'none'
        ]);
        
        $result = [
            'name' => 'Salary/Installment Limit',
            'description' => $isExceeded ? 'Monthly installment exceeds 50% of take-home salary' : 'Monthly installment within acceptable limits',
            'limit' => $limit,
            'given' => $given,
            'take_home' => $takeHome,
            'unit' => 'TZS',
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'ABOVE LIMIT' : 'ACCEPTED',
            'severity' => $isExceeded ? 'medium' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isExceeded ? 'Reduce loan amount or increase loan term' : null
        ];

        Log::info('ExceptionService: Salary/installment validation result', $result);
        return $result;
    }

    protected function validateCollateral()
    {
        Log::info('ExceptionService: Starting collateral validation', [
            'is_physical_collateral' => $this->isPhysicalCollateral,
            'collateral_value' => $this->collateralValue,
            'loan_value' => $this->approvedLoanValue
        ]);
        
        if ($this->isPhysicalCollateral) {
            Log::info('ExceptionService: Using LTV validation for physical collateral');
            return $this->validateLTV();
        } else {
            Log::info('ExceptionService: Using loan multiplier validation for non-physical collateral');
            return $this->validateLoanMultiplier();
        }
    }

    protected function validateLTV()
    {
        Log::info('ExceptionService: Starting LTV validation');
        
        $ltvLimit = (float)($this->product->ltv ?? 70);
        $collateralValue = (float)$this->collateralValue;
        $loanValue = (float)$this->approvedLoanValue;
        
        $percent = $collateralValue > 0 ? ($loanValue / $collateralValue) * 100 : 0;
        $isExceeded = $percent > $ltvLimit;
        $percentage = $ltvLimit > 0 ? round(($percent / $ltvLimit) * 100, 2) : 0;
        
        Log::info('ExceptionService: LTV validation details', [
            'ltv_limit' => $ltvLimit,
            'collateral_value' => $collateralValue,
            'loan_value' => $loanValue,
            'calculated_ltv_percent' => $percent,
            'is_exceeded' => $isExceeded,
            'percentage_of_limit' => $percentage,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none'
        ]);
        
        $result = [
            'name' => 'Loan-to-Value (LTV)',
            'description' => $isExceeded ? 'Loan amount exceeds maximum LTV ratio for collateral' : 'Loan amount within LTV ratio limits',
            'limit' => $ltvLimit,
            'given' => $percent,
            'collateral_value' => $collateralValue,
            'loan_value' => $loanValue,
            'unit' => '%',
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isExceeded ? 'Reduce loan amount or provide additional collateral' : null
        ];

        Log::info('ExceptionService: LTV validation result', $result);
        return $result;
    }
    
    /**
     * Get LTV data specifically for display in the exceptions table
     */
    protected function getLTVForDisplay()
    {
        Log::info('ExceptionService: Getting LTV for display');
        
        $ltvLimit = (float)($this->product->ltv ?? 70);
        $collateralValue = (float)$this->collateralValue;
        $loanValue = (float)$this->approvedLoanValue;
        
        $calculatedLTV = $collateralValue > 0 ? round(($loanValue / $collateralValue) * 100, 2) : 0;
        $isExceeded = $calculatedLTV > $ltvLimit;
        
        Log::info('ExceptionService: LTV display data', [
            'product_ltv_limit' => $ltvLimit,
            'calculated_ltv' => $calculatedLTV,
            'is_exceeded' => $isExceeded
        ]);
        
        return [
            'name' => "Loan-to-Value (LTV) - Max {$ltvLimit}%",
            'product_ltv' => $ltvLimit,
            'calculated_ltv' => $calculatedLTV,
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none',
            'description' => "LTV: {$calculatedLTV}% (Limit: {$ltvLimit}%)"
        ];
    }

    protected function validateLoanMultiplier()
    {
        Log::info('ExceptionService: Starting loan multiplier validation');
        
        $multiplier = (float)($this->product->loan_multiplier ?? 1);
        $collateralValue = (float)$this->collateralValue;
        $loanValue = (float)$this->approvedLoanValue;
        
        // If no collateral value, set multiplier to 0 (no collateral-based lending)
        if ($collateralValue == 0) {
            $multiplier = 0;
            Log::info('ExceptionService: No collateral value found, setting multiplier to 0');
        }
        
        $limit = $collateralValue * $multiplier;
        $isExceeded = $loanValue > $limit;
        $percentage = $limit > 0 ? round(($loanValue / $limit) * 100, 2) : 0;
        
        Log::info('ExceptionService: Loan multiplier validation details', [
            'loan_multiplier' => $multiplier,
            'collateral_value' => $collateralValue,
            'loan_value' => $loanValue,
            'calculated_limit' => $limit,
            'is_exceeded' => $isExceeded,
            'percentage_of_limit' => $percentage,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none'
        ]);
        
        $result = [
            'name' => 'Breach of total savings or collateral value',
            'description' => $isExceeded ? 'Loan amount exceeds collateral-based multiplier limit' : 'Loan amount within multiplier limits',
            'limit' => $limit,
            'given' => $loanValue,
            'multiplier' => $multiplier,
            'collateral_value' => $collateralValue,
            'unit' => 'TZS',
            'is_exceeded' => $isExceeded,
            'status' => $isExceeded ? 'IN BREACH' : 'ACCEPTED',
            'severity' => $isExceeded ? 'high' : 'none',
            'percentage' => $percentage,
            'recommendation' => $isExceeded ? 'Reduce loan amount or provide additional collateral' : null
        ];

        Log::info('ExceptionService: Loan multiplier validation result', $result);
        return $result;
    }

    protected function getSummary()
    {
        Log::info('ExceptionService: Starting summary generation');
        
        $exceptions = [
            $this->validateLoanAmount(),
            $this->validateTerm(),
            $this->validateCreditScore(),
            $this->validateSalaryInstallment(),
            $this->validateCollateral()
        ];
        
        // Add LTV to summary if applicable
        $ltvLimit = (float)($this->product->ltv ?? 0);
        $collateralValue = (float)$this->collateralValue;
        if ($ltvLimit > 0 && $collateralValue > 0) {
            $exceptions[] = $this->getLTVForDisplay();
        }

        $totalExceptions = count($exceptions);
        $exceededExceptions = count(array_filter($exceptions, fn($e) => $e['is_exceeded']));
        $highSeverityExceptions = count(array_filter($exceptions, fn($e) => $e['severity'] === 'high' && $e['is_exceeded']));
        $mediumSeverityExceptions = count(array_filter($exceptions, fn($e) => $e['severity'] === 'medium' && $e['is_exceeded']));

        $overallStatus = $exceededExceptions === 0 ? 'APPROVED' : ($highSeverityExceptions > 0 ? 'REJECTED' : 'REVIEW_REQUIRED');
        $canApprove = $exceededExceptions === 0;
        $requiresException = $exceededExceptions > 0;

        Log::info('ExceptionService: Summary calculation details', [
            'total_exceptions' => $totalExceptions,
            'exceeded_exceptions' => $exceededExceptions,
            'high_severity_exceptions' => $highSeverityExceptions,
            'medium_severity_exceptions' => $mediumSeverityExceptions,
            'passed_checks' => $totalExceptions - $exceededExceptions,
            'failed_checks' => $exceededExceptions,
            'overall_status' => $overallStatus,
            'can_approve' => $canApprove,
            'requires_exception' => $requiresException
        ]);

        $result = [
            'total_checks' => $totalExceptions,
            'passed_checks' => $totalExceptions - $exceededExceptions,
            'failed_checks' => $exceededExceptions,
            'high_severity' => $highSeverityExceptions,
            'medium_severity' => $mediumSeverityExceptions,
            'overall_status' => $overallStatus,
            'can_approve' => $canApprove,
            'requires_exception' => $requiresException
        ];

        Log::info('ExceptionService: Summary generation completed', $result);
        return $result;
    }
} 