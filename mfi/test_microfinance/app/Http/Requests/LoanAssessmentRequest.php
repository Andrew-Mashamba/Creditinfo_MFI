<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\LoansModel;
use App\Models\Loan_sub_products;

class LoanAssessmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $loanId = $this->route('loan');
        $loan = $loanId ? LoansModel::find($loanId) : null;
        
        return [
            'principle' => [
                'required',
                'numeric',
                'min:0',
                'max:' . $this->getMaxLoanAmount($loan)
            ],
            'tenure' => [
                'required',
                'integer',
                'min:1',
                'max:' . $this->getMaxTenure($loan)
            ],
            'collateral_value' => [
                'required_if:collateral_required,true',
                'numeric',
                'min:0'
            ],
            'collateral_type' => [
                'required_if:collateral_value,>,0',
                'string',
                'in:REAL_ESTATE,VEHICLE,EQUIPMENT,INVENTORY,CASH,SECURITIES'
            ],
            'business_income' => [
                'required',
                'numeric',
                'min:0'
            ],
            'monthly_expenses' => [
                'required',
                'numeric',
                'min:0'
            ],
            'business_age' => [
                'required',
                'integer',
                'min:0'
            ],
            'daily_sales' => [
                'required',
                'numeric',
                'min:0'
            ],
            'cost_of_goods_sold' => [
                'required',
                'numeric',
                'min:0'
            ],
            'operating_expenses' => [
                'required',
                'numeric',
                'min:0'
            ],
            'monthly_taxes' => [
                'required',
                'numeric',
                'min:0'
            ],
            'other_expenses' => [
                'required',
                'numeric',
                'min:0'
            ],
            'available_funds' => [
                'required',
                'numeric',
                'min:0'
            ],
            'business_name' => [
                'required',
                'string',
                'max:255'
            ],
            'business_category' => [
                'required',
                'string',
                'in:RETAIL,WHOLESALE,MANUFACTURING,SERVICES,AGRICULTURE,CONSTRUCTION'
            ],
            'business_type' => [
                'required',
                'string',
                'max:255'
            ],
            'business_licence_number' => [
                'nullable',
                'string',
                'max:255'
            ],
            'business_tin_number' => [
                'nullable',
                'string',
                'max:255'
            ],
            'guarantor' => [
                'nullable',
                'string',
                'max:255'
            ],
            'interest_method' => [
                'required',
                'string',
                'in:flat,reducing,compound'
            ],
            'amortization_method' => [
                'required',
                'string',
                'in:equal_installments,equal_principal,balloon'
            ]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAffordability($validator);
            $this->validateCollateralCoverage($validator);
            $this->validateCreditHistory($validator);
            $this->validateBusinessViability($validator);
        });
    }

    protected function validateAffordability($validator)
    {
        $principle = $this->input('principle', 0);
        $tenure = $this->input('tenure', 12);
        $interest = $this->getInterestRate();
        $availableFunds = $this->input('available_funds', 0);

        if ($principle > 0 && $tenure > 0 && $interest > 0) {
            $monthlyRate = $interest / 12 / 100;
            $numerator = $principle * $monthlyRate * pow(1 + $monthlyRate, $tenure);
            $denominator = pow(1 + $monthlyRate, $tenure) - 1;
            
            if ($denominator > 0) {
                $monthlyPayment = $numerator / $denominator;
                $affordabilityRatio = $availableFunds > 0 ? ($monthlyPayment / $availableFunds) * 100 : 100;

                if ($affordabilityRatio > 70) {
                    $validator->errors()->add('principle', 'Loan amount exceeds affordability limit. Consider reducing the amount or extending the term.');
                }
            }
        }
    }

    protected function validateCollateralCoverage($validator)
    {
        $principle = $this->input('principle', 0);
        $collateralValue = $this->input('collateral_value', 0);

        if ($principle > 0 && $collateralValue > 0) {
            $ltv = ($principle / $collateralValue) * 100;

            if ($ltv > 80) {
                $validator->errors()->add('collateral_value', 'Loan-to-Value ratio exceeds 80%. Additional collateral or reduced loan amount required.');
            }
        }
    }

    protected function validateCreditHistory($validator)
    {
        $clientNumber = $this->route('loan') ? 
            LoansModel::find($this->route('loan'))->client_number : 
            $this->input('client_number');

        if ($clientNumber) {
            $defaultedLoans = LoansModel::where('client_number', $clientNumber)
                ->where('status', 'DEFAULTED')
                ->count();

            if ($defaultedLoans > 0) {
                $validator->errors()->add('client_number', 'Client has defaulted loans in the past. Additional review required.');
            }

            $activeLoans = LoansModel::where('client_number', $clientNumber)
                ->where('status', 'ACTIVE')
                ->sum('principle');

            $totalExposure = $activeLoans + $this->input('principle', 0);
            
            if ($totalExposure > 5000000) { // 5M limit
                $validator->errors()->add('principle', 'Total loan exposure exceeds limit. Additional approval required.');
            }
        }
    }

    protected function validateBusinessViability($validator)
    {
        $dailySales = $this->input('daily_sales', 0);
        $costOfGoods = $this->input('cost_of_goods_sold', 0);
        $operatingExpenses = $this->input('operating_expenses', 0);
        $businessAge = $this->input('business_age', 0);

        if ($dailySales > 0 && $costOfGoods > 0) {
            $monthlySales = $dailySales * 30;
            $grossProfit = $monthlySales - $costOfGoods;
            $netProfit = $grossProfit - $operatingExpenses;
            $profitMargin = $monthlySales > 0 ? ($netProfit / $monthlySales) * 100 : 0;

            if ($profitMargin < 10) {
                $validator->errors()->add('daily_sales', 'Business profitability is below minimum threshold. Additional review required.');
            }
        }

        if ($businessAge < 1) {
            $validator->errors()->add('business_age', 'Business is less than 1 year old. Additional documentation and review required.');
        }
    }

    protected function getMaxLoanAmount($loan)
    {
        if (!$loan || !$loan->loan_sub_product) {
            return 1000000; // Default 1M
        }

        $product = Loan_sub_products::where('sub_product_id', $loan->loan_sub_product)->first();
        return $product ? $product->principle_max_value : 1000000;
    }

    protected function getMaxTenure($loan)
    {
        if (!$loan || !$loan->loan_sub_product) {
            return 60; // Default 60 months
        }

        $product = Loan_sub_products::where('sub_product_id', $loan->loan_sub_product)->first();
        return $product ? $product->max_term : 60;
    }

    protected function getInterestRate()
    {
        $loanId = $this->route('loan');
        if ($loanId) {
            $loan = LoansModel::find($loanId);
            return $loan ? $loan->interest : 12; // Default 12%
        }

        return 12; // Default 12%
    }

    public function messages()
    {
        return [
            'principle.required' => 'Loan amount is required.',
            'principle.numeric' => 'Loan amount must be a valid number.',
            'principle.min' => 'Loan amount must be greater than zero.',
            'principle.max' => 'Loan amount exceeds maximum allowed.',
            'tenure.required' => 'Loan term is required.',
            'tenure.integer' => 'Loan term must be a whole number.',
            'tenure.min' => 'Loan term must be at least 1 month.',
            'tenure.max' => 'Loan term exceeds maximum allowed.',
            'collateral_value.required_if' => 'Collateral value is required when collateral is provided.',
            'collateral_value.numeric' => 'Collateral value must be a valid number.',
            'business_income.required' => 'Business income is required.',
            'business_income.numeric' => 'Business income must be a valid number.',
            'monthly_expenses.required' => 'Monthly expenses are required.',
            'monthly_expenses.numeric' => 'Monthly expenses must be a valid number.',
            'business_age.required' => 'Business age is required.',
            'business_age.integer' => 'Business age must be a whole number.',
            'daily_sales.required' => 'Daily sales are required.',
            'daily_sales.numeric' => 'Daily sales must be a valid number.',
            'available_funds.required' => 'Available funds are required.',
            'available_funds.numeric' => 'Available funds must be a valid number.',
            'business_name.required' => 'Business name is required.',
            'business_category.required' => 'Business category is required.',
            'business_category.in' => 'Invalid business category selected.',
            'business_type.required' => 'Business type is required.',
            'interest_method.required' => 'Interest method is required.',
            'interest_method.in' => 'Invalid interest method selected.',
            'amortization_method.required' => 'Amortization method is required.',
            'amortization_method.in' => 'Invalid amortization method selected.'
        ];
    }
} 