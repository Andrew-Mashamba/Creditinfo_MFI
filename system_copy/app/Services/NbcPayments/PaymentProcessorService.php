<?php

namespace App\Services\NbcPayments;

use App\Models\GepgTransaction;
use App\Models\AccountsModel;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentProcessorService
{
    protected $gepgGateway;
    protected $logger;

    public function __construct(GepgGatewayService $gepgGateway, GepgLoggerService $logger)
    {
        $this->gepgGateway = $gepgGateway;
        $this->logger = $logger;
    }

    public function verifyBill($controlNumber, $accountNo, $currency)
    {
        $response = $this->gepgGateway->verifyControlNumber($controlNumber, $accountNo, $currency);

        if ($response['status'] === 'error') {
            return $response;
        }

        // Save verification result
        $transaction = GepgTransaction::create([
            'control_number' => $controlNumber,
            'account_no' => $accountNo,
            'currency' => $currency,
            'response_code' => $response['BillStsCode'] ?? '7201',
            'response_description' => $response['BillStsDesc'] ?? 'Unknown error',
            'payload' => json_encode($response),
            'transaction_type' => 'verification',
        ]);

        return [
            'status' => 'success',
            'transaction' => $transaction,
            'response' => $response,
        ];
    }

    public function checkTransactionStatus($transactionId)
    {
        try {
            $transaction = GepgTransaction::findOrFail($transactionId);
            
            $response = $this->gepgGateway->checkStatus(
                $transaction->channel_ref,
                $transaction->cbp_gw_ref
            );

            // Update transaction status
            $transaction->update([
                'response_code' => $response['PayStsCode'] ?? $response['QtStsCode'] ?? '7201',
                'response_description' => $response['PayStsDesc'] ?? $response['QtStsDesc'] ?? 'Status check completed',
                'status' => $this->determineStatus($response),
                'last_status_check' => now(),
            ]);

            // If payment exists, update its status too
            if ($transaction->payment_id) {
                Payment::where('id', $transaction->payment_id)->update([
                    'status' => $this->determineStatus($response)
                ]);
            }

            return [
                'status' => 'success',
                'transaction' => $transaction,
                'response' => $response
            ];

        } catch (\Exception $e) {
            $this->logger->logError('STATUS_CHECK', $e, [
                'transaction_id' => $transactionId
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function processPostpaidPayment(array $paymentData)
    {
        // Validate payment against account balance
        $account = AccountsModel::where('account_no', $paymentData['debit_account_no'])->first();

        if (!$account || $account->balance < $paymentData['total_amount']) {
            return [
                'status' => 'error',
                'message' => 'Insufficient account balance',
            ];
        }

        // Process payment with GEPG
        $response = $this->gepgGateway->processPayment($paymentData);

        if ($response['status'] === 'error') {
            return $response;
        }

        // Record transaction
        $transaction = GepgTransaction::create([
            'control_number' => $paymentData['control_number'],
            'account_no' => $paymentData['debit_account_no'],
            'amount' => $paymentData['total_amount'],
            'currency' => $paymentData['currency'],
            'response_code' => $response['PayStsCode'] ?? '7201',
            'response_description' => $response['PayStsDesc'] ?? 'Unknown error',
            'payload' => json_encode($response),
            'transaction_type' => 'postpaid',
        ]);

        return [
            'status' => 'success',
            'transaction' => $transaction,
            'response' => $response,
        ];
    }

    public function processPrepaidPayment(array $paymentData)
    {
        // Process quote first
        $quoteResponse = $this->gepgGateway->processPayment($paymentData, true);

        if ($quoteResponse['status'] === 'error') {
            return $quoteResponse;
        }

        // Process actual payment if quote is successful
        if ($quoteResponse['QtStsCode'] === '7101') {
            $paymentResponse = $this->gepgGateway->processPayment($paymentData);

            if ($paymentResponse['status'] === 'error') {
                return $paymentResponse;
            }

            // Record transaction
            $transaction = GepgTransaction::create([
                'control_number' => $paymentData['control_number'],
                'account_no' => $paymentData['debit_account_no'],
                'amount' => $paymentData['total_amount'],
                'currency' => $paymentData['currency'],
                'response_code' => $paymentResponse['PayStsCode'] ?? '7201',
                'response_description' => $paymentResponse['PayStsDesc'] ?? 'Unknown error',
                'payload' => json_encode($paymentResponse),
                'transaction_type' => 'prepaid',
                'quote_reference' => $quoteResponse['QtRefId'] ?? null,
            ]);

            return [
                'status' => 'success',
                'transaction' => $transaction,
                'response' => $paymentResponse,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Quote processing failed',
            'response' => $quoteResponse,
        ];
    }

    public function processPayment(Payment $payment)
    {
        $startTime = microtime(true);
        
        try {
            DB::beginTransaction();

            // Create GEPG transaction record
            $transaction = GepgTransaction::create([
                'payment_id' => $payment->id,
                'control_number' => $payment->control_number,
                'amount' => $payment->amount,
                'payment_type' => $payment->payment_type,
                'status' => 'PENDING',
                'channel_ref' => uniqid('pay_'),
                'cbp_gw_ref' => uniqid('cbp_')
            ]);

            $this->logger->logTransaction($transaction->id, [
                'payment' => $payment->toArray(),
                'transaction' => $transaction->toArray()
            ]);

            // Prepare payment data
            $paymentData = [
                'channel_ref' => $transaction->channel_ref,
                'cbp_gw_ref' => $transaction->cbp_gw_ref,
                'control_number' => $payment->control_number,
                'pay_type' => $payment->payment_type,
                'status_code' => '7101',
                'items' => [
                    [
                        'BillItemRef' => $payment->id,
                        'BillItemAmt' => $payment->amount,
                        'BillItemCur' => $payment->currency ?? 'TZS'
                    ]
                ]
            ];

            // Process payment
            $response = $this->gepgGateway->processPayment($paymentData, $payment->payment_type === 'PREPAID');

            // Update transaction with response
            $transaction->update([
                'response_code' => $response['PayStsCode'] ?? $response['QtStsCode'] ?? '7201',
                'response_description' => $response['PayStsDesc'] ?? $response['QtStsDesc'] ?? 'Payment processed',
                'payload' => $response,
                'status' => $this->determineStatus($response)
            ]);

            // Update payment status
            $payment->update([
                'status' => $this->determineStatus($response),
                'gepg_transaction_id' => $transaction->id
            ]);

            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->logTransaction($transaction->id, [
                'final_status' => $transaction->status,
                'duration_ms' => $duration
            ]);

            DB::commit();
            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->logger->logError('PAYMENT_PROCESSOR', $e, [
                'payment_id' => $payment->id,
                'payment_type' => $payment->payment_type,
                'amount' => $payment->amount
            ]);

            throw $e;
        }
    }

    protected function determineStatus($response)
    {
        $code = $response['PayStsCode'] ?? $response['QtStsCode'] ?? '7201';
        
        return match($code) {
            '7101' => 'SUCCESS',
            '7102' => 'PENDING',
            '7103' => 'FAILED',
            default => 'FAILED'
        };
    }
}
