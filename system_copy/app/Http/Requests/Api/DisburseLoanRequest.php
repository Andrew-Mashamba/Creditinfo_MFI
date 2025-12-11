<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request validation for loan disbursement API
 * 
 * @package App\Http\Requests\Api
 * @version 1.0
 */
class DisburseLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization is handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'loan_id' => 'required|string|max:50',
            'payment_method' => 'required|string|in:CASH,NBC_ACCOUNT,TIPS_MNO,TIPS_BANK',
            'narration' => 'nullable|string|max:500',
            'validate_only' => 'nullable|boolean',
            'payment_details' => 'nullable|array',
        ];

        // Add payment method specific validation
        if ($this->payment_method === 'NBC_ACCOUNT') {
            $rules['payment_details.account_number'] = 'required|string|max:50';
            $rules['payment_details.account_holder_name'] = 'nullable|string|max:255';
        }

        if ($this->payment_method === 'TIPS_MNO') {
            $rules['payment_details.phone_number'] = [
                'required',
                'string',
                'regex:/^(255|0)[0-9]{9}$/'
            ];
            $rules['payment_details.mno_provider'] = 'required|string|in:MPESA,TIGOPESA,AIRTELMONEY,HALOPESA';
            $rules['payment_details.wallet_holder_name'] = 'nullable|string|max:255';
        }

        if ($this->payment_method === 'TIPS_BANK') {
            $rules['payment_details.bank_code'] = 'required|string|max:20';
            $rules['payment_details.bank_account'] = 'required|string|max:50';
            $rules['payment_details.bank_account_holder_name'] = 'nullable|string|max:255';
            $rules['payment_details.swift_code'] = 'nullable|string|max:20';
        }

        if ($this->payment_method === 'CASH') {
            $rules['payment_details.deposit_account'] = 'nullable|string|max:50';
            $rules['payment_details.cashier_id'] = 'nullable|string|max:50';
            $rules['payment_details.branch_code'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    /**
     * Get custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'loan_id.required' => 'Loan ID is required',
            'loan_id.string' => 'Loan ID must be a string',
            'loan_id.max' => 'Loan ID cannot exceed 50 characters',
            
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method. Allowed: CASH, NBC_ACCOUNT, TIPS_MNO, TIPS_BANK',
            
            'payment_details.account_number.required' => 'NBC account number is required for internal transfers',
            'payment_details.phone_number.required' => 'Phone number is required for mobile money transfers',
            'payment_details.phone_number.regex' => 'Invalid phone number format. Use 255XXXXXXXXX or 0XXXXXXXXX',
            'payment_details.mno_provider.required' => 'Mobile network operator is required',
            'payment_details.mno_provider.in' => 'Invalid MNO provider. Allowed: MPESA, TIGOPESA, AIRTELMONEY, HALOPESA',
            
            'payment_details.bank_code.required' => 'Bank code is required for bank transfers',
            'payment_details.bank_account.required' => 'Bank account number is required for bank transfers',
            
            'narration.max' => 'Narration cannot exceed 500 characters',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'loan_id' => 'loan identifier',
            'payment_method' => 'payment method',
            'payment_details.account_number' => 'account number',
            'payment_details.phone_number' => 'phone number',
            'payment_details.mno_provider' => 'mobile network operator',
            'payment_details.bank_code' => 'bank code',
            'payment_details.bank_account' => 'bank account number',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors->toArray(),
            'meta' => [
                'timestamp' => now()->toISOString()
            ]
        ], 422));
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Normalize phone number if provided
        if ($this->has('payment_details.phone_number')) {
            $phone = $this->input('payment_details.phone_number');
            
            // Remove any non-numeric characters
            $phone = preg_replace('/[^0-9]/', '', $phone);
            
            // Add country code if not present
            if (strlen($phone) === 9) {
                $phone = '255' . $phone;
            } elseif (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
                $phone = '255' . substr($phone, 1);
            }
            
            $this->merge([
                'payment_details' => array_merge(
                    $this->input('payment_details', []),
                    ['phone_number' => $phone]
                )
            ]);
        }

        // Convert payment_method to uppercase
        if ($this->has('payment_method')) {
            $this->merge([
                'payment_method' => strtoupper($this->input('payment_method'))
            ]);
        }

        // Convert MNO provider to uppercase
        if ($this->has('payment_details.mno_provider')) {
            $this->merge([
                'payment_details' => array_merge(
                    $this->input('payment_details', []),
                    ['mno_provider' => strtoupper($this->input('payment_details.mno_provider'))]
                )
            ]);
        }
    }
}