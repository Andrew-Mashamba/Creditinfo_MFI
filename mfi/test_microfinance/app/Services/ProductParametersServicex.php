<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductParametersServicex
{
    /**
     * Get comprehensive product parameters
     */
    public function getProductParameters($productId, $approvedLoanValue = 0)
    {
        try {
            $product = DB::table('loan_sub_products')->where('sub_product_id', $productId)->first();
            
            if (!$product) {
                return $this->getDefaultProductInfo();
            }

            return [
                'basic_info' => $this->getBasicInfo($product),
                'loan_limits' => $this->getLoanLimits($product),
                'interest_info' => $this->getInterestInfo($product),
                'grace_periods' => $this->getGracePeriods($product),
                'fees_and_charges' => $this->getFeesAndCharges($product, $approvedLoanValue),
                'insurance_info' => $this->getInsuranceInfo($product, $approvedLoanValue),
                'repayment_info' => $this->getRepaymentInfo($product),
                'account_info' => $this->getAccountInfo($product),
                'requirements' => $this->getRequirements($product),
                'validation' => $this->validateParameters($product, $approvedLoanValue)
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching product parameters: ' . $e->getMessage());
            return $this->getDefaultProductInfo();
        }
    }

    /**
     * Get basic product information
     */
    private function getBasicInfo($product)
    {
        return [
            'sub_product_id' => $product->sub_product_id ?? 'N/A',
            'sub_product_name' => $product->sub_product_name ?? 'N/A',
            'product_id' => $product->product_id ?? 'N/A',
            'prefix' => $product->prefix ?? 'N/A',
            'status' => $this->mapProductStatus($product->sub_product_status),
            'currency' => $product->currency ?? 'TZS',
            'notes' => $product->notes ?? 'N/A'
        ];
    }

    /**
     * Map product status to human-readable format
     */
    private function mapProductStatus($status)
    {
        if (empty($status)) return 'N/A';
        
        $statusMap = [
            '1' => 'ACTIVE',
            '0' => 'INACTIVE',
            'ACTIVE' => 'ACTIVE',
            'INACTIVE' => 'INACTIVE'
        ];
        
        return $statusMap[$status] ?? $status;
    }

    /**
     * Map boolean field to Yes/No
     */
    private function mapBooleanField($value)
    {
        if (empty($value)) return 'No';
        
        $booleanMap = [
            '1' => 'Yes',
            '0' => 'No',
            'true' => 'Yes',
            'false' => 'No',
            'yes' => 'Yes',
            'no' => 'No'
        ];
        
        return $booleanMap[strtolower($value)] ?? $value;
    }

    /**
     * Get loan limits
     */
    private function getLoanLimits($product)
    {
        return [
            'min_amount' => $product->principle_min_value ?? 0,
            'max_amount' => $product->principle_max_value ?? 0,
            'min_term' => $product->min_term ?? 0,
            'max_term' => $product->max_term ?? 0,
            'loan_multiplier' => $product->loan_multiplier ?? 0,
            'ltv' => $product->ltv ?? 0,
            'score_limit' => $product->score_limit ?? 0
        ];
    }

    /**
     * Get interest information
     */
    private function getInterestInfo($product)
    {
        return [
            'interest_rate' => $product->interest_value ?? 0,
            'interest_tenure' => $product->interest_tenure ?? 'N/A',
            'interest_method' => $product->interest_method ?? 'N/A',
            'amortization_method' => $product->amortization_method ?? 'N/A',
            'days_in_year' => $product->days_in_a_year ?? 365,
            'days_in_month' => $product->days_in_a_month ?? 30,
            'monthly_rate' => $this->calculateMonthlyRate($product->interest_value ?? 0)
        ];
    }

    /**
     * Get grace periods
     */
    private function getGracePeriods($product)
    {
        return [
            'principle_grace_period' => $product->principle_grace_period ?? 0,
            'interest_grace_period' => $product->interest_grace_period ?? 0,
            'has_grace_period' => !empty($product->principle_grace_period) || !empty($product->interest_grace_period)
        ];
    }

    /**
     * Get fees and charges
     */
    private function getFeesAndCharges($product, $approvedLoanValue)
    {
        $charges = [];
        $totalCharges = 0;

        try {
            // Get charges for this product
            $chargeIds = DB::table('product_has_charges')
                ->where('product_id', $product->id)
                ->pluck('charge_id');

            if ($chargeIds->count() > 0) {
                $chargeList = DB::table('chargeslist')
                    ->whereIn('id', $chargeIds)
                    ->get();

                foreach ($chargeList as $charge) {
                    $chargeAmount = $this->calculateChargeAmount($charge, $approvedLoanValue);
                    $charges[] = [
                        'name' => $charge->name ?? 'N/A',
                        'type' => $charge->calculating_type ?? 'Fixed',
                        'value' => $charge->value ?? 0,
                        'amount' => $chargeAmount,
                        'description' => $charge->description ?? ''
                    ];
                    $totalCharges += $chargeAmount;
                }
            }

            // Add maintenance fees
            if ($product->maintenance_fees_value) {
                $maintenanceFeesValue = (float)$product->maintenance_fees_value;
                $charges[] = [
                    'name' => 'Maintenance Fees',
                    'type' => 'Fixed',
                    'value' => $maintenanceFeesValue,
                    'amount' => $maintenanceFeesValue,
                    'description' => 'Monthly maintenance fees'
                ];
                $totalCharges += $maintenanceFeesValue;
            }

            // Add ledger fees
            if ($product->ledger_fees_value) {
                $ledgerFeesValue = (float)$product->ledger_fees_value;
                $charges[] = [
                    'name' => 'Ledger Fees',
                    'type' => 'Fixed',
                    'value' => $ledgerFeesValue,
                    'amount' => $ledgerFeesValue,
                    'description' => 'Ledger maintenance fees'
                ];
                $totalCharges += $ledgerFeesValue;
            }

        } catch (\Exception $e) {
            Log::error('Error fetching charges: ' . $e->getMessage());
        }

        return [
            'charges' => $charges,
            'total_charges' => $totalCharges,
            'penalty_value' => (float)($product->penalty_value ?? 0)
        ];
    }

    /**
     * Get insurance information
     */
    private function getInsuranceInfo($product, $approvedLoanValue)
    {
        $insurances = [];
        $totalInsurance = 0;

        try {
            // Get insurance for this product
            $insuranceIds = DB::table('product_has_insurance')
                ->where('product_id', $product->id)
                ->pluck('insurance_id');

            if ($insuranceIds->count() > 0) {
                $insuranceList = DB::table('insurancelist')
                    ->whereIn('id', $insuranceIds)
                    ->get();

                foreach ($insuranceList as $insurance) {
                    $insuranceAmount = $this->calculateInsuranceAmount($insurance, $approvedLoanValue);
                    $insurances[] = [
                        'name' => $insurance->name ?? 'N/A',
                        'type' => $insurance->calculating_type ?? 'Fixed',
                        'value' => $insurance->value ?? 0,
                        'amount' => $insuranceAmount,
                        'description' => $insurance->description ?? ''
                    ];
                    $totalInsurance += $insuranceAmount;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fetching insurance: ' . $e->getMessage());
        }

        return [
            'insurances' => $insurances,
            'total_insurance' => $totalInsurance
        ];
    }

    /**
     * Get repayment information
     */
    private function getRepaymentInfo($product)
    {
        return [
            'repayment_strategy' => $product->repayment_strategy ?? 'N/A',
            'repayment_frequency' => $product->repayment_frequency ?? 'Monthly',
            'inactivity_period' => $product->inactivity ?? 0
        ];
    }

    /**
     * Get account information
     */
    private function getAccountInfo($product)
    {
        return [
            'disbursement_account' => $product->disbursement_account ?? 'N/A',
            'collection_account_loan_interest' => $product->collection_account_loan_interest ?? 'N/A',
            'collection_account_loan_principle' => $product->collection_account_loan_principle ?? 'N/A',
            'collection_account_loan_charges' => $product->collection_account_loan_charges ?? 'N/A',
            'collection_account_loan_penalties' => $product->collection_account_loan_penalties ?? 'N/A',
            'loan_product_account' => $product->loan_product_account ?? 'N/A',
            'interest_account' => $product->interest_account ?? 'N/A',
            'fees_account' => $product->fees_account ?? 'N/A',
            'payable_account' => $product->payable_account ?? 'N/A',
            'insurance_account' => $product->insurance_account ?? 'N/A'
        ];
    }

    /**
     * Get product requirements
     */
    private function getRequirements($product)
    {
        return [
            'requires_approval' => $this->mapBooleanField($product->requires_approval),
            'allow_statement_generation' => $this->mapBooleanField($product->allow_statement_generation),
            'send_notifications' => $this->mapBooleanField($product->send_notifications),
            'require_image_member' => $this->mapBooleanField($product->require_image_member),
            'require_image_id' => $this->mapBooleanField($product->require_image_id),
            'require_mobile_number' => $this->mapBooleanField($product->require_mobile_number),
            'lock_guarantee_funds' => $this->mapBooleanField($product->lock_guarantee_funds)
        ];
    }

    /**
     * Validate parameters against current loan
     */
    private function validateParameters($product, $approvedLoanValue)
    {
        $validation = [];

        // Validate loan amount
        if ($approvedLoanValue > 0) {
            if ($approvedLoanValue < ($product->principle_min_value ?? 0)) {
                $validation[] = [
                    'type' => 'amount',
                    'level' => 'error',
                    'message' => 'Loan amount below minimum requirement',
                    'required' => $product->principle_min_value,
                    'provided' => $approvedLoanValue
                ];
            } elseif ($approvedLoanValue > ($product->principle_max_value ?? 0)) {
                $validation[] = [
                    'type' => 'amount',
                    'level' => 'error',
                    'message' => 'Loan amount exceeds maximum limit',
                    'required' => $product->principle_max_value,
                    'provided' => $approvedLoanValue
                ];
            } else {
                $validation[] = [
                    'type' => 'amount',
                    'level' => 'success',
                    'message' => 'Loan amount within acceptable range',
                    'required' => $product->principle_max_value,
                    'provided' => $approvedLoanValue
                ];
            }
        }

        // Validate product status
        if (($product->sub_product_status ?? '') !== 'ACTIVE') {
            $validation[] = [
                'type' => 'status',
                'level' => 'warning',
                'message' => 'Product is not active',
                'status' => $product->sub_product_status
            ];
        }

        return $validation;
    }

    /**
     * Calculate monthly interest rate
     */
    private function calculateMonthlyRate($annualRate)
    {
        if (!$annualRate) return 0;
        return (float)$annualRate / 12;
    }

    /**
     * Calculate charge amount
     */
    private function calculateChargeAmount($charge, $loanAmount)
    {
        if ($charge->calculating_type === 'Percent') {
            $amount = ($loanAmount * $charge->value) / 100;
            
            // Apply min cap if set
            if (!empty($charge->min_cap) && $amount < (float)$charge->min_cap) {
                $amount = (float)$charge->min_cap;
            }
            
            // Apply max cap if set
            if (!empty($charge->max_cap) && $amount > (float)$charge->max_cap) {
                $amount = (float)$charge->max_cap;
            }
            
            return $amount;
        }
        return $charge->value ?? 0;
    }

    /**
     * Calculate insurance amount
     */
    private function calculateInsuranceAmount($insurance, $loanAmount)
    {
        if ($insurance->calculating_type === 'Percent') {
            $amount = ($loanAmount * $insurance->value) / 100;
            
            // Apply min cap if set
            if (!empty($insurance->min_cap) && $amount < (float)$insurance->min_cap) {
                $amount = (float)$insurance->min_cap;
            }
            
            // Apply max cap if set
            if (!empty($insurance->max_cap) && $amount > (float)$insurance->max_cap) {
                $amount = (float)$insurance->max_cap;
            }
            
            return $amount;
        }
        return $insurance->value ?? 0;
    }

    /**
     * Get default product information
     */
    private function getDefaultProductInfo()
    {
        return [
            'basic_info' => [
                'sub_product_id' => 'N/A',
                'sub_product_name' => 'N/A',
                'product_id' => 'N/A',
                'prefix' => 'N/A',
                'status' => 'N/A',
                'currency' => 'TZS',
                'notes' => 'N/A'
            ],
            'loan_limits' => [
                'min_amount' => 0,
                'max_amount' => 0,
                'min_term' => 0,
                'max_term' => 0,
                'loan_multiplier' => 0,
                'ltv' => 0,
                'score_limit' => 0
            ],
            'interest_info' => [
                'interest_rate' => 0,
                'interest_tenure' => 'N/A',
                'interest_method' => 'N/A',
                'amortization_method' => 'N/A',
                'days_in_year' => 365,
                'days_in_month' => 30,
                'monthly_rate' => 0
            ],
            'grace_periods' => [
                'principle_grace_period' => 0,
                'interest_grace_period' => 0,
                'has_grace_period' => false
            ],
            'fees_and_charges' => [
                'charges' => [],
                'total_charges' => 0,
                'penalty_value' => 0
            ],
            'insurance_info' => [
                'insurances' => [],
                'total_insurance' => 0
            ],
            'repayment_info' => [
                'repayment_strategy' => 'N/A',
                'repayment_frequency' => 'Monthly',
                'inactivity_period' => 0
            ],
            'account_info' => [
                'disbursement_account' => 'N/A',
                'collection_account_loan_interest' => 'N/A',
                'collection_account_loan_principle' => 'N/A',
                'collection_account_loan_charges' => 'N/A',
                'collection_account_loan_penalties' => 'N/A',
                'loan_product_account' => 'N/A',
                'interest_account' => 'N/A',
                'fees_account' => 'N/A',
                'payable_account' => 'N/A',
                'insurance_account' => 'N/A'
            ],
            'requirements' => [
                'requires_approval' => 'No',
                'allow_statement_generation' => 'No',
                'send_notifications' => 'No',
                'require_image_member' => 'No',
                'require_image_id' => 'No',
                'require_mobile_number' => 'No',
                'lock_guarantee_funds' => 'No'
            ],
            'validation' => []
        ];
    }
}
