<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;
use App\Services\NbcPayments\GepgGatewayService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SimpleGepgPayment extends Component
{
    public $controlNumber = '';
    public $amount = '';
    public $accountNumber = '';
    
    public $verificationResult = null;
    public $paymentResult = null;
    public $errorMessage = null;
    public $successMessage = null;
    public $isProcessing = false;
    public $showPaymentForm = false;
    
    // Bill details from verification
    public $billStatus = null;
    public $billStatusCode = null;
    public $billDescription = null;
    public $billAmount = null;
    public $serviceProvider = null;
    public $expiryDate = null;
    public $paidAmount = null;
    public $canPay = false;
    
    protected $rules = [
        'controlNumber' => 'required|string',
        'amount' => 'required|numeric|min:1',
        'accountNumber' => 'required|string'
    ];

    public function mount()
    {
        Log::info('Simple GEPG Payment Component Mounted');
    }
    
    protected function getGepgGateway()
    {
        return app(GepgGatewayService::class);
    }
    
    protected function interpretStatusCode($code)
    {
        $statuses = [
            '7336' => ['status' => 'ACTIVE', 'message' => 'Bill is active and can be paid', 'can_pay' => true],
            '7101' => ['status' => 'SUCCESSFUL_BUT_NOT_PAYABLE', 'message' => 'Bill verified but cannot be paid (may be already paid or expired)', 'can_pay' => false],
            '7204' => ['status' => 'NOT_FOUND', 'message' => 'Control number not found', 'can_pay' => false],
            '7205' => ['status' => 'ALREADY_PAID', 'message' => 'Bill has already been paid', 'can_pay' => false],
            '7206' => ['status' => 'EXPIRED', 'message' => 'Bill has expired', 'can_pay' => false],
            '7207' => ['status' => 'CANCELLED', 'message' => 'Bill has been cancelled', 'can_pay' => false],
            '7208' => ['status' => 'INACTIVE', 'message' => 'Bill is not active', 'can_pay' => false],
        ];
        
        return $statuses[$code] ?? ['status' => 'UNKNOWN', 'message' => 'Unknown status', 'can_pay' => false];
    }

    public function verifyControlNumber()
    {
        $this->validate(['controlNumber' => 'required|string']);
        
        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->successMessage = null;
        
        try {
            Log::info('=== GEPG CONTROL NUMBER VERIFICATION ===', [
                'control_number' => $this->controlNumber,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Call the GEPG Gateway service to verify the control number
            $gateway = $this->getGepgGateway();
            if (!$gateway) {
                throw new \Exception('GEPG Gateway Service not available');
            }
            
            $response = $gateway->verifyControlNumber(
                $this->controlNumber,
                $this->accountNumber,
                'TZS'
            );
            
            Log::info('GEPG Verification Response', ['response' => $response]);
            
            // Check if verification was successful
            if (isset($response['GepgGatewayBillQryResp'])) {
                $billResp = $response['GepgGatewayBillQryResp'];
                $this->verificationResult = $billResp;
                
                // Extract header information
                if (isset($billResp['BillHdr'])) {
                    $this->billStatusCode = $billResp['BillHdr']['BillStsCode'] ?? null;
                    $statusInfo = $this->interpretStatusCode($this->billStatusCode);
                    $this->billStatus = $statusInfo['status'];
                    $this->canPay = $statusInfo['can_pay'];
                    
                    // Extract bill details
                    if (isset($billResp['BillDtls'])) {
                        $billDtl = isset($billResp['BillDtls']['BillDtl']) ? $billResp['BillDtls']['BillDtl'] : $billResp['BillDtls'];
                        
                        $this->billDescription = $billDtl['BillDesc'] ?? 'N/A';
                        $this->serviceProvider = $billDtl['SpName'] ?? 'N/A';
                        $this->billAmount = floatval($billDtl['BillAmt'] ?? 0);
                        $this->paidAmount = floatval($billDtl['PaidAmt'] ?? 0);
                        $this->expiryDate = $billDtl['BillExpDt'] ?? $billDtl['BillExprDt'] ?? 'N/A';
                        
                        // Set default amount to bill amount if not already set
                        if ($this->billAmount > 0 && !$this->amount) {
                            $this->amount = $this->billAmount;
                        }
                    }
                    
                    // Set appropriate message based on status
                    if ($this->canPay) {
                        $this->showPaymentForm = true;
                        $this->successMessage = "✓ Control number verified successfully. {$statusInfo['message']}";
                    } else {
                        $this->errorMessage = "✗ {$statusInfo['message']} (Status code: {$this->billStatusCode})";
                        $this->showPaymentForm = false;
                    }
                }
            } else {
                $this->errorMessage = 'Failed to verify control number. Please check and try again.';
            }
            
        } catch (\Exception $e) {
            Log::error('GEPG Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error verifying control number: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }
    
    public function processPayment()
    {
        $this->validate();
        
        $this->isProcessing = true;
        $this->errorMessage = null;
        $this->successMessage = null;
        
        try {
            Log::info('=== GEPG PAYMENT PROCESSING ===', [
                'control_number' => $this->controlNumber,
                'amount' => $this->amount,
                'account' => $this->accountNumber,
                'status_code' => $this->billStatusCode,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Only process if bill is payable
            if (!$this->canPay) {
                $this->errorMessage = 'This bill cannot be paid. Please verify the control number status.';
                return;
            }
            
            // Prepare payment data
            $paymentData = [
                'channel_ref' => 'GEPG_' . now()->timestamp,
                'cbp_gw_ref' => 'CBPGW_' . now()->timestamp,
                'control_number' => $this->controlNumber,
                'pay_type' => '1',
                'status_code' => $this->billStatusCode,
                'items' => [
                    [
                        'channel_trx_id' => 'TRX_' . now()->timestamp,
                        'sp_code' => 'SP99106', // From verification
                        'pay_ref_id' => $this->verificationResult['BillDtls']['PayRefId'] ?? '',
                        'bill_ctr_num' => $this->controlNumber,
                        'bill_amt' => $this->amount,
                        'paid_amt' => $this->amount,
                        'ccy' => 'TZS',
                        'pay_opt' => 'Part',
                        'trx_dt_tm' => now()->format('Y-m-d\TH:i:s'),
                        'min_pay_amt' => '0.01'
                    ]
                ],
                'debit_account_no' => $this->accountNumber,
                'debit_account_type' => 'CASA',
                'debit_account_currency' => 'TZS',
                'bank_type' => 'ONUS',
                'forex' => 'N'
            ];
            
            // Process real payment through GEPG Gateway
            $gateway = $this->getGepgGateway();
            if (!$gateway) {
                throw new \Exception('GEPG Gateway Service not available');
            }
            
            $response = $gateway->processPayment($paymentData, false);
            
            Log::info('GEPG Payment Response', ['response' => $response]);
            
            if (isset($response['success']) && $response['success']) {
                $this->paymentResult = $response;
                $this->successMessage = "✓ Payment of TZS {$this->amount} processed successfully for control number {$this->controlNumber}";
                
                // Record transaction in database
                $this->recordTransaction($paymentData, $response);
                
                // Reset form
                $this->showPaymentForm = false;
                $this->verificationResult = null;
            } else {
                $errorMsg = $response['message'] ?? 'Payment processing failed';
                $this->errorMessage = $errorMsg;
            }
            
        } catch (\Exception $e) {
            Log::error('GEPG Payment Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error processing payment: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }
    
    protected function recordTransaction($paymentData, $response)
    {
        try {
            DB::table('transactions')->insert([
                'branch_id' => auth()->user()->branch_id ?? 1,
                'transaction_uuid' => \Str::uuid(),
                'service_name' => 'GEPG',
                'amount' => $paymentData['amount'] ?? $this->amount,
                'currency' => 'TZS',
                'type' => 'debit',
                'transaction_category' => 'payment',
                'transaction_subcategory' => 'bill_payment',
                'source' => 'web',
                'channel_id' => 'GEPG',
                'sp_code' => 'SP99106',
                'gateway_ref' => $response['transaction_id'] ?? 'GEPG_' . now()->timestamp,
                'payment_type' => 'GEPG',
                'payer_name' => auth()->user()->name ?? 'System',
                'description' => 'GEPG Payment - ' . $this->billDescription,
                'reference' => $paymentData['control_number'] ?? $this->controlNumber,
                'external_reference' => $this->serviceProvider,
                'status' => 'completed',
                'initiated_at' => now(),
                'completed_at' => now(),
                'initiated_by' => auth()->id() ?? 1,
                'metadata' => json_encode([
                    'control_number' => $this->controlNumber,
                    'bill_status_code' => $this->billStatusCode,
                    'service_provider' => $this->serviceProvider,
                    'bill_amount' => $this->billAmount,
                    'paid_amount' => $this->amount
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('GEPG transaction recorded in database', [
                'control_number' => $this->controlNumber,
                'amount' => $this->amount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record GEPG transaction', ['error' => $e->getMessage()]);
        }
    }
    
    public function resetForm()
    {
        $this->controlNumber = '';
        $this->amount = '';
        $this->accountNumber = '';
        $this->verificationResult = null;
        $this->paymentResult = null;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->showPaymentForm = false;
        $this->billStatus = null;
        $this->billStatusCode = null;
        $this->billDescription = null;
        $this->billAmount = null;
        $this->serviceProvider = null;
        $this->expiryDate = null;
        $this->paidAmount = null;
        $this->canPay = false;
    }

    public function render()
    {
        return view('livewire.payments.simple-gepg-payment');
    }
}