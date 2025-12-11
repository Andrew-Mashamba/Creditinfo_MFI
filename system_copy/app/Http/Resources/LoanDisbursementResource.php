<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Loan Disbursement Response
 * 
 * Formats the disbursement response data for API consumers
 * 
 * @package App\Http\Resources
 * @version 1.0
 */
class LoanDisbursementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'transaction_id' => $this['transaction_id'],
            'loan_id' => $this['loan_id'],
            'status' => $this['status'],
            'disbursement_details' => [
                'total_loan_amount' => $this->formatAmount($this['total_loan_amount']),
                'disbursed_amount' => $this->formatAmount($this['disbursed_amount']),
                'currency' => 'TZS',
            ],
            'deductions' => $this->formatDeductions($this['deductions']),
            'payment_info' => [
                'method' => $this['payment_method'],
                'reference' => $this['payment_reference'],
                'status' => $this->getPaymentStatus($this['payment_method'], $this['payment_reference']),
            ],
            'control_numbers' => $this->formatControlNumbers($this['control_numbers']),
            'loan_account' => $this['loan_account'],
            'disbursement_date' => $this['disbursement_date'],
            'next_payment_date' => $this->getNextPaymentDate(),
            'repayment_info' => $this->getRepaymentInfo(),
        ];
    }

    /**
     * Format amount with proper decimal places
     *
     * @param float $amount
     * @return array
     */
    private function formatAmount($amount)
    {
        return [
            'value' => round($amount, 2),
            'formatted' => number_format($amount, 2, '.', ','),
            'display' => 'TZS ' . number_format($amount, 2, '.', ',')
        ];
    }

    /**
     * Format deductions breakdown
     *
     * @param array $deductions
     * @return array
     */
    private function formatDeductions($deductions)
    {
        if (empty($deductions)) {
            return [
                'total' => $this->formatAmount(0),
                'breakdown' => []
            ];
        }

        $formattedBreakdown = [];
        
        if (isset($deductions['breakdown']) && is_array($deductions['breakdown'])) {
            foreach ($deductions['breakdown'] as $deduction) {
                $formattedBreakdown[] = [
                    'type' => $deduction['type'],
                    'name' => $deduction['name'],
                    'amount' => $this->formatAmount($deduction['amount'])
                ];
            }
        }

        return [
            'total' => $this->formatAmount($deductions['total'] ?? 0),
            'charges' => $this->formatAmount($deductions['charges'] ?? 0),
            'insurance' => $this->formatAmount($deductions['insurance'] ?? 0),
            'first_interest' => $this->formatAmount($deductions['first_interest'] ?? 0),
            'top_up_settlement' => $this->formatAmount($deductions['top_up_amount'] ?? 0),
            'top_up_penalty' => $this->formatAmount($deductions['top_up_penalty'] ?? 0),
            'breakdown' => $formattedBreakdown
        ];
    }

    /**
     * Format control numbers
     *
     * @param array $controlNumbers
     * @return array
     */
    private function formatControlNumbers($controlNumbers)
    {
        if (empty($controlNumbers)) {
            return [];
        }

        $formatted = [];
        foreach ($controlNumbers as $control) {
            $formatted[] = [
                'type' => $control['type'] ?? 'REPAYMENT',
                'number' => $control['control_number'] ?? null,
                'description' => $control['description'] ?? 'Loan Repayment',
                'valid_until' => $this->getControlNumberExpiry(),
            ];
        }

        return $formatted;
    }

    /**
     * Get payment status based on method and reference
     *
     * @param string $method
     * @param string|null $reference
     * @return string
     */
    private function getPaymentStatus($method, $reference)
    {
        if (empty($reference)) {
            return 'PENDING';
        }

        // For cash and internal transfers, payment is immediate
        if (in_array($method, ['CASH', 'NBC_ACCOUNT'])) {
            return 'COMPLETED';
        }

        // For external transfers, status may be pending
        return 'PROCESSING';
    }

    /**
     * Get next payment date (first day of next month)
     *
     * @return string
     */
    private function getNextPaymentDate()
    {
        return now()->addMonth()->startOfMonth()->format('Y-m-d');
    }

    /**
     * Get control number expiry date (30 days from now)
     *
     * @return string
     */
    private function getControlNumberExpiry()
    {
        return now()->addDays(30)->format('Y-m-d');
    }

    /**
     * Get repayment information
     *
     * @return array
     */
    private function getRepaymentInfo()
    {
        // This would typically fetch from the repayment schedule
        // For now, return basic info
        return [
            'frequency' => 'MONTHLY',
            'installment_amount' => $this->formatAmount($this['monthly_installment'] ?? 0),
            'total_installments' => $this['tenure'] ?? 12,
            'first_payment_date' => $this->getNextPaymentDate(),
        ];
    }

    /**
     * Add additional metadata
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'api_version' => '1.0',
                'response_time' => round(microtime(true) - LARAVEL_START, 3) . 's',
            ],
            'links' => [
                'self' => route('api.v1.loans.disbursement.status', ['transactionId' => $this['transaction_id']]),
                'loan' => '/api/v1/loans/' . $this['loan_id'],
            ]
        ];
    }
}