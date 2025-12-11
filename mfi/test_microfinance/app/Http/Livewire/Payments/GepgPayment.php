<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;
use App\Services\NbcPayments\GepgGatewayService;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;

class GepgPayment extends Component
{
    // Form properties
    public $controlNumber;
    public $amount = 0;
    public $payerName;
    public $payerMsisdn;
    public $payerEmail;
    public $payerTin;
    public $payerNin;
    public $currency = 'TZS';

    // Bill details properties
    public $billNumber;
    public $billDescription;
    public $billExpiryDate;
    public $paymentOption;
    public $paymentPlan;
    public $serviceProviderCode;
    public $serviceProviderName;
    public $paymentReferenceId;
    public $minimumPaymentAmount;

    // State properties
    public $billInfo;
    public $showPaymentForm = false;
    public $processing = false;
    public $errorMessage = null;
    public $successMessage = null;
    public $paymentType = 'POSTPAID'; // or 'PREPAID'
    public $billStatus = null;

    // Data binding properties
    public $billData = [];
    public $customerData = [];
    public $componentId;

    public $billDetails = [];
    public $billHeader = [];

    protected $gepgGateway;

    protected $rules = [
        'controlNumber' => 'required|string',
        'amount' => 'required|numeric|min:1000',
        'payerName' => 'required|string',
        'payerMsisdn' => 'required|string',
        'payerEmail' => 'nullable|email',
        'payerTin' => 'nullable|string',
        'payerNin' => 'nullable|string',
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->gepgGateway = app(GepgGatewayService::class);
    }

    public function mount()
    {
        $this->componentId = 'gepg_' . uniqid();
        $this->resetValidation();
        $this->resetErrorBag();
        
        Log::info('GEPG Payment Component Mounted', [
            'component_id' => $this->componentId,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    public function setBillData($data)
    {
        Log::info('GEPG Setting Bill Data', [
            'data' => $data,
            'component_id' => $this->componentId
        ]);

        $this->billData = $data;
        $this->controlNumber = $data['control_number'] ?? null;
        $this->amount = $data['amount'] ?? null;
        
        if ($this->controlNumber) {
            $this->verifyControlNumber();
        }
    }

    public function setCustomerData($data)
    {
        Log::info('GEPG Setting Customer Data', [
            'data' => $data,
            'component_id' => $this->componentId
        ]);

        $this->customerData = $data;
        $this->payerName = $data['name'] ?? null;
        $this->payerMsisdn = $data['phone'] ?? null;
        $this->payerEmail = $data['email'] ?? null;
        $this->payerTin = $data['tin'] ?? null;
        $this->payerNin = $data['nin'] ?? null;
    }

    protected function interpretBillStatusCode($code)
    {
        return match($code) {
            '7101' => [
                'status' => 'UNPAYABLE',
                'message' => 'This bill cannot be paid as it may be expired or already paid.',
                'can_pay' => false
            ],
            '7336' => [
                'status' => 'ACTIVE',
                'message' => 'Bill is active and can be paid.',
                'can_pay' => true
            ],
            '7204' => [
                'status' => 'NOT_FOUND',
                'message' => 'The bill you entered does not exist. Please check the control number and try again.',
                'can_pay' => false
            ],
            '7205' => [
                'status' => 'ALREADY_PAID',
                'message' => 'This bill has already been paid.',
                'can_pay' => false
            ],
            '7206' => [
                'status' => 'EXPIRED',
                'message' => 'This bill has expired.',
                'can_pay' => false
            ],
            '7207' => [
                'status' => 'CANCELLED',
                'message' => 'This bill has been cancelled.',
                'can_pay' => false
            ],
            '7208' => [
                'status' => 'INACTIVE',
                'message' => 'This bill is not active.',
                'can_pay' => false
            ],
            '7209' => [
                'status' => 'UNAVAILABLE',
                'message' => 'This bill is not available for payment.',
                'can_pay' => false
            ],
            '7210' => [
                'status' => 'INVALID_CURRENCY',
                'message' => 'This bill is not valid for the selected currency.',
                'can_pay' => false
            ],
            default => [
                'status' => 'UNKNOWN',
                'message' => 'Unknown bill status.',
                'can_pay' => false
            ]
        };
    }

    public function verifyControlNumber()
    {

        $this->validate([
            'controlNumber' => 'required|string',
            'currency' => 'required|string|size:3'
        ]);

        $componentId = uniqid('gepg_');
        Log::channel('gepg')->info('GEPG Starting Control Number Verification', [
            'component_id' => $componentId,
            'control_number' => $this->controlNumber,
            'currency' => $this->currency,
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            $accountNo = config('gepg.default_account_no', 'DEFAULT_ACCOUNT');
            $response = $this->gepgGateway->verifyControlNumber(
                $this->controlNumber,
                $accountNo,
                $this->currency
            );

 
            
            Log::channel('gepg')->info('GEPG Raw Response', [
                'component_id' => $componentId,
                'raw_response' => $response,
                'timestamp' => now()->toIso8601String()
            ]);

            if (!isset($response['GepgGatewayBillQryResp'])) {
                Log::channel('gepg')->error('GEPG Invalid Response Structure', [
                    'component_id' => $componentId,
                    'response' => $response,
                    'timestamp' => now()->toIso8601String()
                ]);
                throw new \Exception('Invalid response structure from GEPG gateway');
            }

            $billHeader = $response['GepgGatewayBillQryResp']['BillHdr'] ?? [];
            $billDetails = $response['GepgGatewayBillQryResp']['BillDtls']['BillDtl'] ?? [];
            $gepgTxn = $response['GepgGatewayBillQryResp']['GepgGatewayTxn'] ?? [];

            Log::channel('gepg')->info('GEPG Parsed Response Components', [
                'component_id' => $componentId,
                'bill_header' => [
                    'CustCtrNum' => $billHeader['CustCtrNum'] ?? null,
                    'PayType' => $billHeader['PayType'] ?? null,
                    'EntryCnt' => $billHeader['EntryCnt'] ?? null,
                    'BillStsCode' => $billHeader['BillStsCode'] ?? null,
                    'BillStsDesc' => $billHeader['BillStsDesc'] ?? null,
                    'ChannelRef' => $billHeader['ChannelRef'] ?? null,
                    'CbpGwRef' => $billHeader['CbpGwRef'] ?? null,
                ],
                'bill_detail' => [
                    'SpCode' => $billDetails['SpCode'] ?? null,
                    'SpName' => $billDetails['SpName'] ?? null,
                    'PayRefId' => $billDetails['PayRefId'] ?? null,
                    'BillCtrNum' => $billDetails['BillCtrNum'] ?? null,
                    'PayOpt' => $billDetails['PayOpt'] ?? null,
                    'BillAmt' => $billDetails['BillAmt'] ?? null,
                    'MinPayAmt' => $billDetails['MinPayAmt'] ?? null,
                    'Ccy' => $billDetails['Ccy'] ?? null,
                    'BillExprDt' => $billDetails['BillExprDt'] ?? null,
                    'BillDesc' => $billDetails['BillDesc'] ?? null,
                    'CustName' => $billDetails['CustName'] ?? null,
                    'CustCellNum' => $billDetails['CustCellNum'] ?? null,
                    'CustEmail' => $billDetails['CustEmail'] ?? null,
                    'PayPlan' => $billDetails['PayPlan'] ?? null,
                ],
                'bank_details' => [
                    'BankType' => $gepgTxn['BankType'] ?? null,
                    'Forex' => $gepgTxn['Forex'] ?? null,
                    'BankName' => $gepgTxn['BankDetails']['BankTrfDetails']['CreditBankName'] ?? null,
                    'AccountNo' => $gepgTxn['BankDetails']['BankTrfDetails']['CreditBankAccountNo'] ?? null,
                    'AccountCurrency' => $gepgTxn['BankDetails']['BankTrfDetails']['CreditAccountCurrency'] ?? null,
                    'AccountName' => $gepgTxn['BankDetails']['BankTrfDetails']['CreditAccountName'] ?? null,
                    'BicCode' => $gepgTxn['BankDetails']['BankTrfDetails']['CreditBankBenBic'] ?? null,
                ],
                'timestamp' => now()->toIso8601String()
            ]);

            $statusCode = $billHeader['BillStsCode'] ?? '';
            $statusInfo = $this->interpretBillStatusCode($statusCode);

            Log::channel('gepg')->info('GEPG Bill Status Interpretation', [
                'component_id' => $componentId,
                'status_code' => $statusCode,
                'status_info' => $statusInfo,
                'timestamp' => now()->toIso8601String()
            ]);

            if (!$statusInfo['can_pay']) {
                Log::channel('gepg')->warning('GEPG Bill Not Payable', [
                    'component_id' => $componentId,
                    'status_code' => $statusCode,
                    'status_info' => $statusInfo,
                    'timestamp' => now()->toIso8601String()
                ]);
                $this->addError('controlNumber', $statusInfo['message']);
                //return;
            }

            $this->billDetails = $billDetails;
            $this->billHeader = $billHeader;
            $this->billStatus = $statusInfo;

            // Map bill details to component properties
            $this->amount = $billDetails['BillAmt'] ?? 0;
            $this->payerName = $billDetails['CustName'] ?? '';
            $this->payerMsisdn = $billDetails['CustCellNum'] ?? '';
            $this->payerEmail = $billDetails['CustEmail'] ?? '';
            $this->billNumber = $billDetails['BillCtrNum'] ?? '';
            $this->billDescription = $billDetails['BillDesc'] ?? '';
            $this->billExpiryDate = $billDetails['BillExprDt'] ?? '';
            $this->paymentOption = $billDetails['PayOpt'] ?? '';
            $this->paymentPlan = $billDetails['PayPlan'] ?? '';
            $this->serviceProviderCode = $billDetails['SpCode'] ?? '';
            $this->serviceProviderName = $billDetails['SpName'] ?? '';
            $this->paymentReferenceId = $billDetails['PayRefId'] ?? '';
            $this->minimumPaymentAmount = $billDetails['MinPayAmt'] ?? 0;

            Log::channel('gepg')->info('GEPG Mapped Bill Data', [
                'component_id' => $componentId,
                'mapped_data' => [
                    'amount' => $this->amount,
                    'payer_name' => $this->payerName,
                    'payer_msisdn' => $this->payerMsisdn,
                    'payer_email' => $this->payerEmail,
                    'bill_number' => $this->billNumber,
                    'bill_description' => $this->billDescription,
                    'bill_expiry_date' => $this->billExpiryDate,
                    'payment_option' => $this->paymentOption,
                    'payment_plan' => $this->paymentPlan,
                    'service_provider_code' => $this->serviceProviderCode,
                    'service_provider_name' => $this->serviceProviderName,
                    'payment_reference_id' => $this->paymentReferenceId,
                    'minimum_payment_amount' => $this->minimumPaymentAmount,
                ],
                'timestamp' => now()->toIso8601String()
            ]);
            
            $this->dispatchBrowserEvent('bill-verified', [
                'message' => 'Bill verified successfully. You can now proceed with payment.'
            ]);

        } catch (\Exception $e) {
            Log::channel('gepg')->error('GEPG Verification Error', [
                'component_id' => $componentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toIso8601String()
            ]);
            $this->addError('controlNumber', 'Failed to verify control number: ' . $e->getMessage());
        }
    }

    public function initiatePayment()
    {
        $this->validate([
            'payerName' => 'required|string',
            'payerMsisdn' => 'required|string',
            'payerEmail' => 'required|email',
            'payerTin' => 'nullable|string',
            'payerNin' => 'nullable|string',
        ]);

        $componentId = uniqid('gepg_payment_');
        Log::channel('gepg')->info('GEPG Starting Payment Process', [
            'component_id' => $componentId,
            'bill_number' => $this->billNumber,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            $transactionRef = 'GEPG_' . time() . '_' . uniqid();
            $paymentData = [
                'channel_ref' => 'GEPG_' . time(),
                'cbp_gw_ref' => $this->billHeader['CbpGwRef'] ?? '',
                'result_url' => route('gepg.callback'),
                'channel_trx_id' => $transactionRef,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'payer_name' => $this->payerName,
                'payer_msisdn' => $this->payerMsisdn,
                'payer_email' => $this->payerEmail,
                'payer_tin' => $this->payerTin,
                'payer_nin' => $this->payerNin,
            ];

            Log::channel('gepg')->info('GEPG Payment Data Prepared', [
                'component_id' => $componentId,
                'payment_data' => $paymentData,
                'timestamp' => now()->toIso8601String()
            ]);

            $billType = $this->billHeader['PayTyp'] ?? 'POSTPAID';
            $response = null;

            if ($billType === 'PREPAID') {
                Log::channel('gepg')->info('GEPG Processing Prepaid Quote', [
                    'component_id' => $componentId,
                    'timestamp' => now()->toIso8601String()
                ]);

                $quoteResponse = $this->gepgGateway->processQuote($paymentData);
                
                Log::channel('gepg')->info('GEPG Quote Response', [
                    'component_id' => $componentId,
                    'quote_response' => $quoteResponse,
                    'timestamp' => now()->toIso8601String()
                ]);

                if (!isset($quoteResponse['QuoteResp'])) {
                    throw new \Exception('Invalid quote response from GEPG gateway');
                }

                $paymentData['quote_id'] = $quoteResponse['QuoteResp']['QuoteId'] ?? '';
            }

            Log::channel('gepg')->info('GEPG Processing Payment', [
                'component_id' => $componentId,
                'bill_type' => $billType,
                'timestamp' => now()->toIso8601String()
            ]);

            $response = $this->gepgGateway->processPayment($paymentData);

            Log::channel('gepg')->info('GEPG Payment Response', [
                'component_id' => $componentId,
                'payment_response' => $response,
                'timestamp' => now()->toIso8601String()
            ]);

            if (!isset($response['PayResp'])) {
                throw new \Exception('Invalid payment response from GEPG gateway');
            }

            $paymentResponse = $response['PayResp'];
            $statusCode = $paymentResponse['StsCode'] ?? '';
            $statusDescription = $paymentResponse['StsDesc'] ?? '';

            if ($statusCode !== '7100') {
                Log::channel('gepg')->error('GEPG Payment Failed', [
                    'component_id' => $componentId,
                    'status_code' => $statusCode,
                    'status_description' => $statusDescription,
                    'timestamp' => now()->toIso8601String()
                ]);
                throw new \Exception("Payment failed: $statusDescription");
            }

            // Store payment record
            $payment = Payment::create([
                'transaction_reference' => $transactionRef,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'status' => 'pending',
                'payment_method' => 'gepg',
                'payment_details' => [
                    'bill_number' => $this->billNumber,
                    'control_number' => $this->controlNumber,
                    'payer_name' => $this->payerName,
                    'payer_msisdn' => $this->payerMsisdn,
                    'payer_email' => $this->payerEmail,
                    'payer_tin' => $this->payerTin,
                    'payer_nin' => $this->payerNin,
                    'gepg_response' => $response
                ]
            ]);

            Log::channel('gepg')->info('GEPG Payment Record Created', [
                'component_id' => $componentId,
                'payment_id' => $payment->id,
                'transaction_reference' => $transactionRef,
                'timestamp' => now()->toIso8601String()
            ]);

            $this->dispatchBrowserEvent('payment-initiated', [
                'message' => 'Payment initiated successfully. Please wait for confirmation.'
            ]);

        } catch (\Exception $e) {
            Log::channel('gepg')->error('GEPG Payment Error', [
                'component_id' => $componentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toIso8601String()
            ]);
            $this->addError('payment', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.payments.gepg-payment');
    }
} 