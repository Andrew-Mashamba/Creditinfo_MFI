<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;
use App\Services\LukuGatewayService;
use Illuminate\Support\Facades\Log;

class LukuPayment extends Component
{
    protected $lukuGatewayService;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->lukuGatewayService = app(LukuGatewayService::class);
    }

    public $meterNumber;
    public $debitAccountNo;
    public $amount;
    public $customerName;
    public $customerMsisdn;
    public $customerEmail;
    public $customerTin;
    public $customerNin;
    public $paymentChannel = 'USSD';
    public $thirdParty = 'SELCOM';

    public $meterInfo;
    public $showPaymentForm = false;
    public $processing = false;
    public $errorMessage;
    public $successMessage;

    // Add properties for lookup response
    public $lookupResponse = [
        'meter' => null,
        'owner' => null,
        'debts' => [],
        'reference' => null,
        'status' => null,
        'statusDescription' => null
    ];

    // Add properties to accept data from parent
    public $initialMeterNumber;
    public $initialDebitAccount;
    public $initialAmount;
    public $initialCustomerName;
    public $initialCustomerPhone;

    protected $rules = [
        'meterNumber' => 'required|string',
        'debitAccountNo' => 'required|string',
        'amount' => 'required|numeric|min:1000',
        'customerName' => 'required|string',
        'customerMsisdn' => 'required|string',
        'customerEmail' => 'nullable|email',
        'customerTin' => 'nullable|string',
        'customerNin' => 'nullable|string',
    ];

    public function mount($initialMeterNumber = null, $initialDebitAccount = null, 
                         $initialAmount = null, $initialCustomerName = null, 
                         $initialCustomerPhone = null)
    {
        // Test log message
        Log::channel('luku')->info('Luku Payment: Component mounted', [
            'timestamp' => now()->toDateTimeString(),
            'initialMeterNumber' => $initialMeterNumber,
            'initialDebitAccount' => $initialDebitAccount,
            'initialAmount' => $initialAmount,
            'initialCustomerName' => $initialCustomerName,
            'initialCustomerPhone' => $initialCustomerPhone
        ]);

        $this->initialMeterNumber = $initialMeterNumber;
        $this->initialDebitAccount = $initialDebitAccount;
        $this->initialAmount = $initialAmount;
        $this->initialCustomerName = $initialCustomerName;
        $this->initialCustomerPhone = $initialCustomerPhone;

        if ($initialMeterNumber) {
            Log::channel('luku')->info('Luku Payment: Pre-filling form with initial data', [
                'meterNumber' => $initialMeterNumber,
                'debitAccount' => $initialDebitAccount,
                'amount' => $initialAmount,
                'customerName' => $initialCustomerName,
                'customerPhone' => $initialCustomerPhone
            ]);
            
            $this->meterNumber = $initialMeterNumber;
            $this->debitAccountNo = $initialDebitAccount;
            $this->amount = $initialAmount;
            $this->customerName = $initialCustomerName;
            $this->customerMsisdn = $initialCustomerPhone;
            
            $this->lookupMeter();
        }

        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function lookupMeter()
    {
        try {
            Log::channel('luku')->info('Luku Payment: Starting meter lookup', [
                'meterNumber' => $this->meterNumber,
                'debitAccountNo' => $this->debitAccountNo,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->validate([
                'meterNumber' => 'required|string',
                'debitAccountNo' => 'required|string'
            ]);

            $this->lookupResponse = [
                'meter' => '',
                'owner' => '',
                'debts' => [],
                'reference' => '',
                'status' => '',
                'statusDescription' => ''
            ];

            $channelRef = 'LUKU_' . time();
            $response = $this->lukuGatewayService->meterLookup($this->meterNumber, $this->debitAccountNo, $channelRef);
            
            Log::channel('luku')->info('Luku Payment: Meter lookup response received', [
                'response' => $response,
                'timestamp' => now()->toDateTimeString()
            ]);

            $respInf = $response['sgGepgCustomerInfoRes']['RespInf'];
                
                $this->lookupResponse = [
                    'meter' => $respInf['Meter'] ?? '',
                    'owner' => $respInf['Owner'] ?? '',
                    'debts' => $respInf['ExpectedDeductions']['Debt'] ?? [],
                    'reference' => $response['sgGepgCustomerInfoRes']['RespHdr']['ChannelRef'] ?? '',
                    'status' => $response['sgGepgCustomerInfoRes']['RespHdr']['StsCode'] ?? '',
                    'statusDescription' => $response['sgGepgCustomerInfoRes']['RespHdr']['StsDesc'] ?? ''
                ];

                $this->showPaymentForm = true;
                $this->successMessage = 'Meter lookup successful';
                
                Log::channel('luku')->info('Luku Payment: Meter lookup successful', [
                    'meter' => $this->lookupResponse['meter'],
                    'owner' => $this->lookupResponse['owner'],
                    'timestamp' => now()->toDateTimeString()
                ]);

                
        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Payment: Meter lookup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->errorMessage = 'An error occurred while looking up the meter. Please try again.';
        }
    }

    public function processPayment()
    {
        Log::channel('luku')->info('Luku Payment: Starting payment processing', [
            'meterNumber' => $this->meterNumber,
            'debitAccountNo' => $this->debitAccountNo,
            'amount' => $this->amount,
            'customerName' => $this->customerName,
            'customerMsisdn' => $this->customerMsisdn,
            'timestamp' => now()->toDateTimeString()
        ]);

        $this->validate();

        try {
            $this->processing = true;
            $this->errorMessage = null;
            $this->successMessage = null;

            $paymentData = [
                'channel_ref' => 'LUKU_' . time(),
                'cbp_gw_ref' => $this->meterInfo['CbpGwRef'] ?? '',
                'result_url' => route('luku.callback'),
                'channel_trx_id' => 'TRX_' . time(),
                'meter_number' => $this->meterNumber,
                'debit_account_no' => $this->debitAccountNo,
                'amount' => $this->amount,
                'credit_account_no' => $this->debitAccountNo,
                'transaction_datetime' => now()->format('Y-m-d\TH:i:s'),
                'payment_channel' => $this->paymentChannel,
                'third_party' => $this->thirdParty,
                'customer_msisdn' => $this->customerMsisdn,
                'customer_name' => $this->customerName,
                'customer_tin' => $this->customerTin,
                'customer_nin' => $this->customerNin,
                'customer_email' => $this->customerEmail,
            ];

            Log::channel('luku')->info('Luku Payment: Preparing payment request', [
                'paymentData' => $paymentData,
                'timestamp' => now()->toDateTimeString()
            ]);

            $response = $this->lukuGatewayService->processPayment($paymentData);

            Log::channel('luku')->info('Luku Payment: Payment response received', [
                'response' => $response,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->successMessage = 'Payment request received and is being processed';
                
            Log::channel('luku')->info('Luku Payment: Payment request successful', [
                'successMessage' => $this->successMessage,
                'response' => $response,
                'timestamp' => now()->toDateTimeString()
            ]);

            $this->reset(['meterNumber', 'debitAccountNo', 'amount', 'customerName', 'customerMsisdn', 
                        'customerEmail', 'customerTin', 'customerNin', 'meterInfo', 'showPaymentForm']);

        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Payment: Payment processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            $this->errorMessage = 'Failed to process payment request';
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payments.luku-payment');
    }
}  