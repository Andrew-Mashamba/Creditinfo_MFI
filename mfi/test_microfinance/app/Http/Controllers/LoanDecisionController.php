<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAutomaticLoans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoanDecisionController extends Controller
{
    public function processLoanDecision(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'member_number' => 'required',
            'product_id' => 'required',
            'tenure' => 'required',
            'approved_loan_value' => 'required|numeric',
            'approved_term' => 'required|integer',
            'score.score' => 'required|integer',
            'take_home' => 'required|numeric',
            'monthly_installment_value' => 'required|numeric',
            'collateral_value' => 'required|numeric',
            'is_physical_collateral' => 'required|boolean',
            'product' => 'required|array',
            'product.principle_max_value' => 'required|numeric',
            'product.max_term' => 'required|integer',
            'product.score_limit' => 'required|integer',
            'product.ltv' => 'required|numeric',
            'product.loan_multiplier' => 'required|numeric',
        ]);

        $response = $this->automaticLoanDecision(
            $validated['member_number'],
            $validated['product_id'],
            $validated['approved_loan_value'],
            $validated['approved_term'],
            (object) $validated['score'],
            $validated['take_home'],
            $validated['monthly_installment_value'],
            $validated['collateral_value'],
            $validated['is_physical_collateral'],
            (object) $validated['product'],
            $validated['tenure']
        );

        return response()->json($response);
    }

    private function checkLimitsAndExceptions($approvedLoanValue, $approvedTerm, $score, $takeHome, $monthlyInstallmentValue, $collateralValue, $isPhysicalCollateral, $product)
    {
        $exceptions = [];

        $loanAmountExceeded = (int)$approvedLoanValue > (int)$product->principle_max_value;
        $exceptions[] = [
            'exception' => 'Maximum Loan Amount',
            'limit' => number_format($product->principle_max_value, 2) . ' TZS',
            'given' => number_format($approvedLoanValue, 2) . ' TZS',
            'status' => $loanAmountExceeded ? 'LIMIT EXCEEDED' : 'ACCEPTED',
            'exceeded' => $loanAmountExceeded
        ];

        $termExceeded = $approvedTerm > $product->max_term;
        $exceptions[] = [
            'exception' => 'Maximum Term',
            'limit' => $product->max_term . ' TZS',
            'given' => $approvedTerm . ' TZS',
            'status' => $termExceeded ? 'LIMIT EXCEEDED' : 'ACCEPTED',
            'exceeded' => $termExceeded
        ];

        $scoreBelowLimit = $score->score < $product->score_limit;
        $exceptions[] = [
            'exception' => 'Credit Score',
            'limit' => $product->score_limit . ' TZS',
            'given' => $score->score . ' TZS',
            'status' => $scoreBelowLimit ? 'BELOW LIMIT' : 'ACCEPTED',
            'exceeded' => $scoreBelowLimit
        ];

        $salaryLimitExceeded = $takeHome > 0 ? $monthlyInstallmentValue > $takeHome / 2 : true;
        $limit = $takeHome > 0 ? $takeHome / 2 : $monthlyInstallmentValue * 4;
        $exceptions[] = [
            'exception' => 'Salary/Installment Limit (75%)',
            'limit' => number_format($limit, 2) . ' TZS',
            'given' => number_format($monthlyInstallmentValue, 2) . ' TZS',
            'status' => $salaryLimitExceeded ? 'ABOVE LIMIT' : 'ACCEPTED',
            'exceeded' => $salaryLimitExceeded
        ];

//        if ($isPhysicalCollateral) {
//            $percent = $collateralValue == 0 ? 0 : ($approvedLoanValue / $collateralValue) * 100;
//            $ltvExceeded = $percent > 70;
//            $exceptions[] = [
//                'exception' => 'LTV',
//                'limit' => $product->ltv . ' %',
//                'given' => number_format($percent, 3) . ' %',
//                'status' => $ltvExceeded ? 'LTV EXCEPTION' : 'LTV ACCEPTED',
//                'exceeded' => $ltvExceeded
//            ];
//        } else {
//            $loanMultiplierExceeded = ($collateralValue * $product->loan_multiplier) < $approvedLoanValue;
//            $exceptions[] = [
//                'exception' => 'Loan Multiplier',
//                'limit' => number_format($collateralValue * $product->loan_multiplier, 2) . ' TZS',
//                'given' => number_format($approvedLoanValue, 2) . ' TZS',
//                'status' => $loanMultiplierExceeded ? 'ABOVE LIMIT' : 'ACCEPTED',
//                'exceeded' => $loanMultiplierExceeded
//            ];
//        }

        return $exceptions;
    }

    private function automaticLoanDecision($member_number,$product_id,$approvedLoanValue, $approvedTerm, $score, $takeHome, $monthlyInstallmentValue, $collateralValue, $isPhysicalCollateral, $product,$tenure)
    {
        //dd($product_id);

        $exceptions = $this->checkLimitsAndExceptions($approvedLoanValue, $approvedTerm, $score, $takeHome, $monthlyInstallmentValue, $collateralValue, $isPhysicalCollateral, $product);

        foreach ($exceptions as $exception) {
            if ($exception['exceeded']) {
                return [
                    'approved' => false,
                    'message' => 'Loan declined due to exceeding limits on: ' . $exception['exception'],
                    'exceptions' => $exceptions
                ];
            }
        }


        $this->LoanCreationAndProcessing($member_number,$approvedLoanValue,$product_id,$tenure);

        return [
            'approved' => true,
            'message' => 'Loan approved. All conditions met.',
            'exceptions' => $exceptions
        ];



    }


    public function LoanCreationAndProcessing($member_number,$approvedLoanValue,$product_id,$tenure){
        //dd($member_number);
        $data = [
            'member_number1' => $member_number,
            'amount2' => $approvedLoanValue,
            'tenure' => $tenure,
            'loan_officer' => 1,
            'loan_product' => $product_id,
            'pay_method' => 'BANK',
            'account_number' => '000',
            'loan_type_2' => 'Auto',
        ];

        try {
            ProcessAutomaticLoans::dispatchSync($data);
        } catch (\Exception $e) {
            Log::error('Error processing loan: ' . $e->getMessage());
        }
    }


}
