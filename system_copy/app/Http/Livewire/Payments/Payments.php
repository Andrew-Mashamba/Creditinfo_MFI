<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;
use App\Services\NbcBillsPaymentService;
use Illuminate\Support\Facades\Log;
use App\Traits\Livewire\WithModulePermissions;

use App\Services\NbcPayments\NbcLookupService;

use App\Services\NbcPayments\NbcPaymentService;
use App\Services\NbcPayments\FspDetailsService;
use App\Services\NbcPayments\LukuService;
use App\Services\NbcPayments\PaymentProcessorService;
use App\Models\AccountsModel;

class Payments extends Component
{
    use WithModulePermissions;

    public $billers = [];
    public $billersGrouped = [];
    public $selectedCategory = null;
    public $selectedSpCode = null;
    public $selectedBiller = null;
    public $billRef = '';
    public $billDetails = null;
    public $amount = '';
    public $paymentResponse = null;
    public $paymentStatus = null;
    public $transactions = [];
    public $paymentMode = null;
    public $inquiryRawResponse = null;
    public $gatewayRef = null;
    public $channelRef = null;
    
    // Payment form fields
    public $payerName = '';
    public $payerPhone = '';
    public $payerEmail = '';
    public $narration = '';
    
    public $inquiryResult;


    public string $lookupType = 'bank-to-bank';
    public array $form = [
        'accountNumber' => '',
        'bankCode' => '',
        'phoneNumber' => '',
        'walletProvider' => '',
        'merchantId' => '',
        'debitAccount' => '',
        'amount' => '',
        'debitAccountCategory' => 'PERSON'
    ];
    public $response = null;
    public $errorMessage = null;




    // Form fields
    public $transferType = 'bank'; // 'bank' or 'wallet'
    public $beneficiaryAccount;
    public $bankCode;
    public $walletProvider;
    public $phoneNumber;
    public $debitAccount;

    public $remarks;

    // State variables
    public $isProcessing = false;
    public $successMessage = '';

    public $engineRef = '';
    public $beneficiaryName = '';
    public $currentPhase = 'form'; // 'form', 'verify', 'complete'
    public $lookupData = [];
    public $meterNumber;
    public $accountNumber;
    //public $amount;
    public $lookupResult;
    public $paymentResult;

    public $step = 1; // 1: Verification, 2: Payment, 3: Confirmation
    public $controlNumber;
    public $accountNo;
    public $currency = 'TZS';
    public $accounts = [];
    public $verificationResult;
    public $paymentData = [];
    public $paymentType = 'postpaid'; // or 'prepaid'
    //public $isProcessing = false;
    public $transactionResult;
    public $error;

    // Sidebar navigation support
    public $selectedMenuItem = 1; // Default to Dashboard
    public $selectedPaymentType = 'money_transfer'; // Default to Money Transfer
    public $search = '';

    public $availableBanks = [
        'NMIBTZTZ' => 'NMB Bank',
        'CORUTZTZ' => 'CRDB Bank',
        'DTKETZTZ' => 'DTB Bank'
    ];
    public $availableWallets = [
        'VMCASHIN' => 'M-Pesa',
        'AMCASHIN' => 'Airtel Money',
        'APCASHIN' => 'Azam Pesa'
    ];

    protected $rules = [
        'transferType' => 'required|in:bank,wallet',
        'beneficiaryAccount' => 'required_if:transferType,bank|string|nullable',
        'bankCode' => 'required_if:transferType,bank|string|nullable',
        'walletProvider' => 'required_if:transferType,wallet|string|nullable',
        'phoneNumber' => 'required_if:transferType,wallet|string|nullable',
        'debitAccount' => 'required|string',
        'amount' => 'required|numeric|min:1000',
        'remarks' => 'required|string|max:50'
    ];

    protected $messages = [
        'amount.min' => 'Minimum transfer amount is 1000 TZS.',
        'beneficiaryAccount.required_if' => 'Account number is required for bank transfers.',
        'phoneNumber.required_if' => 'Phone number is required for wallet transfers.',
    ];



        public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        
        $this->fetchFsp();
        $this->fetchBillers();
        //$this->accounts = AccountsModel::where('user_id', auth()->id())->get();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'payments';
    }

    public function fetchFsp()
    {
        $fspService = new FspDetailsService();


        Log::info('Initializing FSP Details fetch process...');

        Log::debug('FspDetailsService instance resolved via service container.');

        $result = $fspService->fetchFspDetails();

        if (isset($result['error'])) {
            Log::error('Failed to retrieve FSP details.', [
                'message' => $result['message'] ?? 'Unknown error',
                'details' => $result['details'] ?? [],
            ]);
            // Handle error accordingly
        } else {
            Log::info('FSP details retrieved successfully.', [
                'fsp_count' => count($result),
                'fsp_sample' => array_slice($result, 0, 2), // preview first 2 items
            ]);
            // Process FSP list
        }


    }

    public function fetchBillers()
    {
        try {
            $service = new NbcBillsPaymentService();
            $result = $service->getBillers();
            
            $this->billers = $result['flat'] ?? [];
            $this->billersGrouped = $result['grouped'] ?? [];
            
            Log::info('Billers fetched successfully', [
                'total' => count($this->billers),
                'categories' => array_keys($this->billersGrouped)
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to fetch billers', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Unable to load service providers. Please try again.';
        }
    }

    public function selectBiller($spCode)
    {
        $this->selectedSpCode = $spCode;
        $this->selectedBiller = collect($this->billers)->firstWhere('spCode', $spCode);
        $this->billDetails = null;
        $this->paymentResponse = null;
        $this->paymentStatus = null;
        $this->billRef = '';
        $this->amount = '';
        $this->resetValidation();
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function inquireBill()
    {
        $this->validate([
            'billRef' => 'required|string|min:1'
        ]);
        
        $this->errorMessage = null;
        $this->successMessage = null;
        
        try {
            $service = new NbcBillsPaymentService();
            $payload = [
                'spCode' => $this->selectedSpCode,
                'billRef' => $this->billRef,
                'userId' => auth()->id() ?? 'USER001',
                'branchCode' => '015',
                'extraFields' => $this->getExtraFieldsForInquiry(),
            ];

            $result = $service->inquireDetailedBill($payload);
            
            if ($result['success']) {
                $this->billDetails = $result['data'];
                $this->inquiryRawResponse = $result['rawResponse'];
                $this->inquiryResult = $this->billDetails;
                
                // Extract payment mode from bill details
                $this->paymentMode = $this->billDetails['paymentMode'] ?? 'exact';
                
                // Pre-fill amount if exact payment mode
                if ($this->paymentMode === 'exact') {
                    $this->amount = $this->billDetails['balance'] ?? $this->billDetails['totalAmount'] ?? '';
                }
                
                // Pre-fill payer details if available
                if (auth()->check()) {
                    $user = auth()->user();
                    $this->payerName = $user->name ?? '';
                    $this->payerPhone = $user->phone ?? '';
                    $this->payerEmail = $user->email ?? '';
                }
                
                $this->successMessage = 'Bill details retrieved successfully';
            } else {
                $this->errorMessage = $result['message'] ?? 'Unable to retrieve bill details';
            }
        } catch (\Throwable $e) {
            Log::error('Bill inquiry failed', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Failed to inquire bill. Please try again.';
        }
    }

    public function makePayment()
    {
        // Validate payment amount based on payment mode
        $this->validatePaymentAmount();
        
        $this->errorMessage = null;
        $this->successMessage = null;
        
        try {
            $service = new NbcBillsPaymentService();
            $this->channelRef = 'PAY' . now()->timestamp;

            // Get user account details
            $userAccount = $this->getUserAccount();
            
            $payload = [
                'spCode' => $this->billDetails['spCode'] ?? $this->selectedSpCode,
                'billRef' => $this->billRef,
                'amount' => $this->amount,
                'callbackUrl' => route('nbc.payment.callback', ['ref' => $this->channelRef]),
                'userId' => auth()->id() ?? 'USER001',
                'branchCode' => '015',
                'channelRef' => $this->channelRef,
                'creditAccount' => $this->billDetails['creditAccount'] ?? '',
                'creditCurrency' => $this->billDetails['creditCurrency'] ?? 'TZS',
                'debitAccount' => $userAccount['account_number'] ?? '28012040011',
                'debitCurrency' => 'TZS',
                'payerName' => $this->payerName,
                'payerPhone' => $this->payerPhone,
                'payerEmail' => $this->payerEmail,
                'narration' => $this->narration ?: 'Bill Payment',
                'paymentType' => 'ACCOUNT',
                'channelCode' => 'APP',
                'extraFields' => $this->getExtraFieldsForPayment(),
                'inquiryRawResponse' => $this->inquiryRawResponse,
                'billDetails' => $this->billDetails
            ];

            $this->paymentResponse = $service->processPaymentAsync($payload);
            
            if ($this->paymentResponse['status'] === 'processing') {
                $this->gatewayRef = $this->paymentResponse['gatewayRef'];
                $this->successMessage = 'Payment initiated successfully. Processing...';
                
                // Store transaction for tracking
                $this->transactions[] = [
                    'spCode' => $this->selectedSpCode,
                    'billRef' => $this->billRef,
                    'amount' => $this->amount,
                    'channelRef' => $this->channelRef,
                    'gatewayRef' => $this->gatewayRef,
                    'status' => 'processing',
                    'timestamp' => now()->toDateTimeString()
                ];
                
                // Check status after 3 seconds
                $this->dispatchBrowserEvent('check-payment-status', [
                    'channelRef' => $this->channelRef,
                    'delay' => 3000
                ]);
            } else {
                $this->errorMessage = $this->paymentResponse['message'] ?? 'Payment processing failed';
            }
        } catch (\Throwable $e) {
            Log::error('Payment failed', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Payment processing failed. Please try again.';
        }
    }

    public function checkPaymentStatus($channelRef = null)
    {
        try {
            $service = new NbcBillsPaymentService();
            $result = $service->checkPaymentStatus([
                'spCode' => $this->selectedSpCode,
                'billRef' => $this->billRef,
                'channelRef' => $channelRef ?? $this->channelRef,
            ]);
            
            if ($result['status'] === 'success') {
                $this->paymentStatus = $result['data'];
                
                // Update transaction status
                $transactionIndex = collect($this->transactions)->search(function ($item) use ($channelRef) {
                    return $item['channelRef'] === ($channelRef ?? $this->channelRef);
                });
                
                if ($transactionIndex !== false) {
                    $this->transactions[$transactionIndex]['status'] = 
                        $this->paymentStatus['paymentDetails']['accountingStatus'] ?? 'pending';
                }
                
                if (isset($this->paymentStatus['paymentDetails']['accountingStatus']) && 
                    $this->paymentStatus['paymentDetails']['accountingStatus'] === 'success') {
                    $this->successMessage = 'Payment completed successfully!';
                    $this->paymentResponse['status'] = 'completed';
                } else {
                    // Continue checking if still processing
                    $this->dispatchBrowserEvent('check-payment-status', [
                        'channelRef' => $channelRef ?? $this->channelRef,
                        'delay' => 5000
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Status check failed', ['error' => $e->getMessage()]);
        }
    }
    
    protected function validatePaymentAmount()
    {
        $rules = ['amount' => 'required|numeric|min:100'];
        
        // Apply validation based on payment mode
        switch ($this->paymentMode) {
            case 'exact':
                $expectedAmount = $this->billDetails['balance'] ?? $this->billDetails['totalAmount'] ?? 0;
                $rules['amount'] .= '|in:' . $expectedAmount;
                break;
            case 'full':
                $minAmount = $this->billDetails['balance'] ?? $this->billDetails['totalAmount'] ?? 0;
                $rules['amount'] .= '|min:' . $minAmount;
                break;
            case 'partial':
            case 'limited':
                // Allow any amount for partial payments
                break;
        }
        
        $this->validate($rules);
    }
    
    protected function getExtraFieldsForInquiry()
    {
        // Add extra fields based on biller type
        $extraFields = new \stdClass();
        
        // Check if special biller (NIDC, Yanga, DSE)
        if ($this->selectedBiller) {
            $category = $this->selectedBiller['category'] ?? '';
            
            // Add specific inquiry types based on category
            // This can be expanded based on the NBC documentation
        }
        
        return $extraFields;
    }
    
    protected function getExtraFieldsForPayment()
    {
        $extraFields = new \stdClass();
        
        // Add extra fields from bill details if present
        if (isset($this->billDetails['extraFields'])) {
            $extraFields = $this->billDetails['extraFields'];
        }
        
        return $extraFields;
    }
    
    protected function getUserAccount()
    {
        // Get user's default account or first available account
        if (auth()->check()) {
            $account = AccountsModel::where('user_id', auth()->id())
                ->where('status', 'active')
                ->first();
                
            if ($account) {
                return $account->toArray();
            }
        }
        
        // Return default account if no user account found
        return [
            'account_number' => '28012040011',
            'account_name' => 'Default Account'
        ];
    }
    
    public function resetBillPayment()
    {
        $this->reset([
            'selectedSpCode',
            'selectedBiller',
            'billRef',
            'billDetails',
            'amount',
            'paymentResponse',
            'paymentStatus',
            'paymentMode',
            'inquiryRawResponse',
            'gatewayRef',
            'channelRef',
            'payerName',
            'payerPhone',
            'payerEmail',
            'narration',
            'errorMessage',
            'successMessage'
        ]);
    }




    ////////////////////////////////////TIPS////////////////////////////////////////////////////////


    public function updatedLookupType()
    {
        $this->reset(['response', 'errorMessage']);
    }

    public function performLookup()
    {
        $this->validate([
            'form.debitAccount' => 'required',
            'form.amount' => 'required|numeric',
            'form.debitAccountCategory' => 'required|in:PERSON,BUSINESS',
        ]);

        $lookupService = new NbcLookupService();
        $this->reset(['response', 'errorMessage']);

        try {
            switch ($this->lookupType) {
                case 'bank-to-bank':
                    $this->validate([
                        'form.accountNumber' => 'required',
                        'form.bankCode' => 'required',
                    ]);
                    $this->response = $lookupService->bankToBankLookup(
                        $this->form['accountNumber'],
                        $this->form['bankCode'],
                        $this->form['debitAccount'],
                        $this->form['amount'],
                        $this->form['debitAccountCategory']
                    );
                    break;

                case 'bank-to-wallet':
                    $this->validate([
                        'form.phoneNumber' => 'required',
                        'form.walletProvider' => 'required',
                    ]);
                    $this->response = $lookupService->bankToWalletLookup(
                        $this->form['phoneNumber'],
                        $this->form['walletProvider'],
                        $this->form['debitAccount'],
                        $this->form['amount'],
                        $this->form['debitAccountCategory']
                    );
                    break;

                case 'merchant-payment':
                    $this->validate([
                        'form.merchantId' => 'required',
                        'form.bankCode' => 'required',
                    ]);
                    $this->response = $lookupService->merchantPaymentLookup(
                        $this->form['merchantId'],
                        $this->form['bankCode'],
                        $this->form['debitAccount'],
                        $this->form['amount'],
                        $this->form['debitAccountCategory']
                    );
                    break;
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }


////////////////////////////////////////////////////////////////////////BANK TO BANK - BANK TO WALLET////////////////////////////////////////////////


public function verifyBeneficiary(NbcLookupService $lookupService)
{
    if (!$this->authorize('verify', 'You do not have permission to verify beneficiaries')) {
        return;
    }
    $this->validate();
    $this->resetMessages();
    $this->isProcessing = true;

    try {


        if ($this->transferType === 'bank') {
            $lookupResponse = $lookupService->bankToBankLookup(
                $this->beneficiaryAccount,
                $this->bankCode,
                $this->debitAccount,
                $this->amount
            );
        } else {

             // Ensure we have a valid phone number
        if ($this->phoneNumber === null) {
            $this->errorMessage = 'Phone number is required for verification. Please update your profile.';
            $this->isProcessing = false;
            return;
        }

            $lookupResponse = $lookupService->bankToWalletLookup(
                $this->phoneNumber,
                $this->walletProvider,
                $this->debitAccount,
                $this->amount
            );
        }

        if (!$lookupResponse['success']) {
            $this->errorMessage = 'Verification failed: ' . $lookupResponse['message'];
            $this->isProcessing = false;
            return;
        }

        $this->lookupData = $lookupResponse['data'];
        $this->beneficiaryName = $this->lookupData['body']['fullName'] ?? '';
        $this->currentPhase = 'verify';

    } catch (\Exception $e) {
        $this->errorMessage = 'Verification error: ' . $e->getMessage();
        logger()->error('Beneficiary verification error: ' . $e->getMessage());
    }

    $this->isProcessing = false;
}

public function confirmTransfer(NbcPaymentService $paymentService)
{
    if (!$this->authorize('transfer', 'You do not have permission to process transfers')) {
        return;
    }
    $this->resetMessages();
    $this->isProcessing = true;

    try {
        if ($this->transferType === 'bank') {
            $paymentResponse = $paymentService->processBankToBankTransfer(
                $this->lookupData,
                $this->debitAccount,
                $this->amount,
                auth()->user()->phone ?? '255715000000', // Default phone number if null
                auth()->id(),
                $this->remarks
            );
        } else {
            $paymentResponse = $paymentService->processBankToWalletTransfer(
                $this->lookupData,
                $this->debitAccount,
                $this->amount,
                $this->phoneNumber,
                auth()->id(),
                $this->remarks
            );
        }

        if ($paymentResponse['success']) {
            $this->successMessage = 'Transfer initiated successfully!';
            $this->engineRef = $paymentResponse['engineRef'];
            $this->currentPhase = 'complete';
        } else {
            // Get both message and remarks
            $message = $paymentResponse['message'] ?? 'Transfer failed';
            $remarks = '';

            if (isset($paymentResponse['data']) && is_array($paymentResponse['data'])) {
                if (isset($paymentResponse['data']['remarks'])) {
                    $remarks = $paymentResponse['data']['remarks'];
                }
            }

            // Combine message and remarks if both exist
            $this->errorMessage = $remarks ? "{$remarks} - {$message}" : $message;
            $this->currentPhase = 'form'; // Return to form
        }

    } catch (\Exception $e) {
        $this->errorMessage = 'Transfer error: ' . $e->getMessage();
        logger()->error('Money transfer error: ' . $e->getMessage());
        $this->currentPhase = 'form';
    }

    $this->isProcessing = false;
}

public function startNewTransfer()
{
    $this->resetExcept(['availableBanks', 'availableWallets']);
    $this->currentPhase = 'form';
}



protected function resetMessages()
{
    $this->reset(['successMessage', 'errorMessage', 'engineRef']);
}




//////////////////////////////////////////LUKU////////////////////////////////////////////////////////



public function lookup()
{
    try {
        Log::info('=== LUKU LOOKUP STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'meter_number' => $this->meterNumber,
            'account_number' => $this->accountNumber,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip' => request()->ip()
        ]);
        
        $this->validateOnly('meterNumber');
        Log::info('LUKU validation passed', ['meter_number' => $this->meterNumber]);
        
        $service = new LukuService();
        Log::info('LukuService instantiated');
        
        $this->lookupResult = $service->lookup($this->meterNumber, $this->accountNumber);
        
        Log::info('LUKU lookup result received', [
            'has_result' => !empty($this->lookupResult),
            'has_error' => isset($this->lookupResult['error']),
            'result_keys' => $this->lookupResult ? array_keys($this->lookupResult) : [],
            'timestamp' => now()->toDateTimeString()
        ]);
        
        if (isset($this->lookupResult['error'])) {
            $this->errorMessage = $this->lookupResult['error'];
            Log::error('LUKU lookup failed', [
                'error' => $this->lookupResult['error'],
                'meter_number' => $this->meterNumber
            ]);
        } else {
            $this->successMessage = 'Meter details retrieved successfully';
            Log::info('✓ LUKU lookup successful', [
                'meter_number' => $this->meterNumber,
                'result' => $this->lookupResult
            ]);
        }
    } catch (\Exception $e) {
        Log::error('✗ LUKU lookup exception', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'meter_number' => $this->meterNumber,
            'timestamp' => now()->toDateTimeString()
        ]);
        $this->errorMessage = 'Failed to lookup meter: ' . $e->getMessage();
        $this->lookupResult = null;
    }
}

public function pay()
{
    if (!$this->authorize('create', 'You do not have permission to process payments')) {
        return;
    }
    try {
        Log::info('=== LUKU PAYMENT STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'meter_number' => $this->meterNumber,
            'account_number' => $this->accountNumber,
            'amount' => $this->amount,
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ]);
        
        // LUKU-specific validation instead of generic $this->validate()
        $this->validate([
            'meterNumber' => 'required|string',
            'accountNumber' => 'required|string',
            'amount' => 'required|numeric|min:1000'
        ], [
            'meterNumber.required' => 'Meter number is required for LUKU payment.',
            'accountNumber.required' => 'Account number is required for LUKU payment.',
            'amount.required' => 'Amount is required.',
            'amount.min' => 'Minimum LUKU purchase amount is 1000 TZS.'
        ]);
        Log::info('LUKU payment validation passed');
        
        $service = new LukuService();
        
        $paymentData = [
            'channel_ref' => 'CHNL' . now()->timestamp,
            'cbp_gw_ref' => 'CBPGW' . now()->timestamp,
            'result_url' => url()->route('luku.callback'),
            'transaction_id' => 'TRX' . rand(10000, 99999),
            'meter_number' => $this->meterNumber,
            'account_number' => $this->accountNumber,
            'amount' => $this->amount,
        ];
        
        Log::info('Calling LukuService->pay()', ['payment_data' => $paymentData]);
        
        $this->paymentResult = $service->pay($paymentData);
        
        Log::info('LUKU payment result received', [
            'has_result' => !empty($this->paymentResult),
            'has_error' => isset($this->paymentResult['error']),
            'result_keys' => $this->paymentResult ? array_keys($this->paymentResult) : [],
            'timestamp' => now()->toDateTimeString()
        ]);
        
        if (isset($this->paymentResult['error'])) {
            $this->errorMessage = $this->paymentResult['error'];
            Log::error('✗ LUKU payment failed', [
                'error' => $this->paymentResult['error'],
                'payment_data' => $paymentData
            ]);
        } else {
            $this->successMessage = 'Payment processed successfully';
            Log::info('✓ LUKU payment successful', [
                'payment_result' => $this->paymentResult,
                'transaction_id' => $paymentData['transaction_id']
            ]);
        }
    } catch (\Exception $e) {
        Log::error('✗✗ LUKU payment exception', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'payment_data' => $paymentData ?? null,
            'timestamp' => now()->toDateTimeString()
        ]);
        $this->errorMessage = 'Payment failed: ' . $e->getMessage();
        $this->paymentResult = null;
    }
}


/////////////////////////////////////////////////////////GEPG////////////////////////////////////////////////////////






    public function verifyBill()
    {
        if (!$this->authorize('verify', 'You do not have permission to verify bills')) {
            return;
        }
        $this->validate([
            'controlNumber' => 'required|string|min:5',
            //'accountNo' => 'required|string',
            'currency' => 'required|string|size:3',
        ]);

        $this->isProcessing = true;
        $this->error = null;
        $this->accountNo = "28012040011";

        try {
            $processor = app(PaymentProcessorService::class);
            $result = $processor->verifyBill($this->controlNumber, $this->accountNo, $this->currency);

            if ($result['status'] === 'success') {
                $this->verificationResult = $result['response'];
                $this->preparePaymentData($result);
                $this->step = 2;
            } else {
                $this->error = $result['message'] ?? 'Verification failed';
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred during verification: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    protected function preparePaymentData(array $verificationResult)
    {
        $billDetails = $verificationResult['response']['BillDtls']['BillDtl'] ?? [];

        if (!is_array($billDetails) || empty($billDetails)) {
            $this->error = 'No bill details found';
            return;
        }

        // For simplicity, we'll process the first bill item
        $firstBill = is_array($billDetails[0]) ? $billDetails[0] : $billDetails;

        $this->paymentData = [
            'channel_ref' => uniqid('pay_'),
            'cbp_gw_ref' => $verificationResult['response']['BillHdr']['CbpGwRef'] ?? '',
            'control_number' => $this->controlNumber,
            'pay_type' => $verificationResult['response']['BillHdr']['PayType'] ?? '1',
            'status_code' => $verificationResult['response']['BillHdr']['BillStsCode'] ?? '7101',
            'debit_account_no' => $this->accountNo,
            'currency' => $this->currency,
            'total_amount' => $firstBill['BillAmt'] ?? 0,
            'items' => [
                [
                    'ChannelTrxId' => uniqid('trx_'),
                    'SpCode' => $firstBill['SpCode'] ?? '',
                    'PayRefId' => $firstBill['PayRefId'] ?? '',
                    'BillCtrNum' => $firstBill['BillCtrNum'] ?? $this->controlNumber,
                    'PaidAmt' => $firstBill['BillAmt'] ?? 0,
                    'TrxDtTm' => now()->format('Y-m-d\TH:i:s'),
                    'PayOpt' => $firstBill['PayOpt'] ?? '1',
                    'PayPlan' => $firstBill['PayPlan'] ?? '1',
                    'BillAmt' => $firstBill['BillAmt'] ?? 0,
                    'MinPayAmt' => $firstBill['MinPayAmt'] ?? 0,
                    'Ccy' => $firstBill['Ccy'] ?? $this->currency,
                    'PyrCellNum' => auth()->user()->phone ?? '',
                    'PyrName' => auth()->user()->name ?? '',
                    'PyrEmail' => auth()->user()->email ?? '',
                ]
            ]
        ];
    }

    public function processPayment()
    {
        if (!$this->authorize('create', 'You do not have permission to process payments')) {
            return;
        }
        $this->isProcessing = true;
        $this->error = null;

        try {
            $processor = app(PaymentProcessorService::class);

            if ($this->paymentType === 'prepaid') {
                $result = $processor->processPrepaidPayment($this->paymentData);
            } else {
                $result = $processor->processPostpaidPayment($this->paymentData);
            }

            if ($result['status'] === 'success') {
                $this->transactionResult = $result;
                $this->step = 3;
            } else {
                $this->error = $result['message'] ?? 'Payment processing failed';
            }
        } catch (\Exception $e) {
            $this->error = 'An error occurred during payment processing: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
    }

    public function render()
    {
        return view('livewire.payments.payments', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }
}
