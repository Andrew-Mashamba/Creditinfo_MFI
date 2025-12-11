<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class NbcPayment extends Component
{
    public $memberNumber;
    public $clientNumber;
    public $client;
    public $pendingBills;
    
    // Form data
    public $selectedBills = [];
    public $customAmounts = [];
    public $phoneNumber;
    public $mnoProvider = '';
    public $isProcessing = false;
    
    // UI state
    public $showSuccessModal = false;
    public $showErrorModal = false;
    public $errorMessage = '';
    public $transactionReference = '';
    public $totalAmount = 0;
    public $processedAmount = 0;
    
    // Quick services
    public $savingsAmount = '';
    public $depositAmount = '';
    public $loanAmount = '';

    protected $rules = [
        'selectedBills' => 'required|array|min:1',
        'selectedBills.*' => 'integer',
        'phoneNumber' => 'required|string|regex:/^0[0-9]{9,10}$/',
        'mnoProvider' => 'required|in:MPESA,AIRTEL,TIGOPESA,HALOPESA',
        'customAmounts.*' => 'numeric|min:1000',
        'savingsAmount' => 'numeric|min:1000',
        'depositAmount' => 'numeric|min:1000',
        'loanAmount' => 'numeric|min:1000',
    ];

    protected $messages = [
        'selectedBills.required' => 'Please select at least one bill to pay.',
        'selectedBills.min' => 'Please select at least one bill to pay.',
        'phoneNumber.required' => 'Please enter your phone number.',
        'phoneNumber.regex' => 'Please enter a valid phone number (e.g., 0755123456).',
        'mnoProvider.required' => 'Please select your mobile money provider.',
        'mnoProvider.in' => 'Please select a valid mobile money provider.',
        'customAmounts.*.min' => 'Custom amount must be at least TZS 1,000.',
        'savingsAmount.min' => 'Savings amount must be at least TZS 1,000.',
        'depositAmount.min' => 'Deposit amount must be at least TZS 1,000.',
        'loanAmount.min' => 'Loan amount must be at least TZS 1,000.',
    ];

    public function mount($memberNumber, $clientNumber)
    {
        $this->memberNumber = $memberNumber;
        $this->clientNumber = $clientNumber;
        
        // Load client data
        $this->loadClientData();
        
        // Load pending bills
        $this->loadPendingBills();
        
        // Set default phone number
        $this->phoneNumber = $this->client->phone_number ?? $this->client->mobile_phone_number ?? '';
        
        // Initialize custom amounts array
        $this->customAmounts = [];
    }

    public function loadClientData()
    {
        $this->client = Member::where('client_number', $this->clientNumber)
            ->orWhere('member_number', $this->clientNumber)
            ->first();

        if (!$this->client) {
            throw new Exception('Client not found');
        }
    }

    public function loadPendingBills()
    {
        $this->pendingBills = Bill::where('client_number', $this->clientNumber)
            ->where('status', 'PENDING')
            ->with('service')
            ->get();
    }

    public function updatedSelectedBills()
    {
        $this->calculateTotalAmount();
    }

    public function updatedCustomAmounts()
    {
        $this->calculateTotalAmount();
    }

    public function calculateTotalAmount()
    {
        $this->totalAmount = 0;

        // Calculate from selected bills
        foreach ($this->selectedBills as $billId) {
            $bill = $this->pendingBills->find($billId);
            if ($bill) {
                $customAmount = $this->customAmounts[$billId] ?? null;
                if ($customAmount && $customAmount > 0) {
                    // Check if this bill allows custom amounts
                    $serviceName = strtolower($bill->service->name ?? '');
                    $allowsCustomAmount = in_array($serviceName, [
                        'savings deposit', 
                        'member deposits', 
                        'fixed deposit', 
                        'loan payment', 
                        'loan repayment'
                    ]);
                    
                    if ($allowsCustomAmount) {
                        $this->totalAmount += $customAmount;
                    } else {
                        $this->totalAmount += $bill->amount_due;
                    }
                } else {
                    $this->totalAmount += $bill->amount_due;
                }
            }
        }

        // Add quick service amounts
        if ($this->savingsAmount && $this->savingsAmount >= 1000) {
            $this->totalAmount += $this->savingsAmount;
        }
        if ($this->depositAmount && $this->depositAmount >= 1000) {
            $this->totalAmount += $this->depositAmount;
        }
        if ($this->loanAmount && $this->loanAmount >= 1000) {
            $this->totalAmount += $this->loanAmount;
        }

        // Debug logging
        Log::info('Total amount calculated', [
            'selected_bills' => $this->selectedBills,
            'custom_amounts' => $this->customAmounts,
            'savings_amount' => $this->savingsAmount,
            'deposit_amount' => $this->depositAmount,
            'loan_amount' => $this->loanAmount,
            'total_amount' => $this->totalAmount
        ]);
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedBills) === $this->pendingBills->count()) {
            $this->selectedBills = [];
        } else {
            $this->selectedBills = $this->pendingBills->pluck('id')->toArray();
        }
        $this->calculateTotalAmount();
    }

    public function getBillAmount($billId, $defaultAmount)
    {
        $customAmount = $this->customAmounts[$billId] ?? null;
        if ($customAmount && $customAmount > 0) {
            return number_format($customAmount, 0);
        }
        return number_format($defaultAmount, 0);
    }

    public function isCustomAmountAllowed($serviceName)
    {
        $serviceName = strtolower($serviceName);
        return in_array($serviceName, [
            'savings deposit', 
            'member deposits', 
            'fixed deposit', 
            'loan payment', 
            'loan repayment'
        ]);
    }

    public function addQuickService($type)
    {
        $amount = 0;
        $serviceName = '';
        
        switch($type) {
            case 'savings':
                $amount = $this->savingsAmount;
                $serviceName = 'Savings Deposit';
                break;
            case 'deposit':
                $amount = $this->depositAmount;
                $serviceName = 'Fixed Deposit';
                break;
            case 'loan':
                $amount = $this->loanAmount;
                $serviceName = 'Loan Payment';
                break;
        }

        if ($amount < 1000) {
            $this->showError('Minimum amount is TZS 1,000');
            return;
        }

        // Clear the input
        switch($type) {
            case 'savings':
                $this->savingsAmount = '';
                break;
            case 'deposit':
                $this->depositAmount = '';
                break;
            case 'loan':
                $this->loanAmount = '';
                break;
        }

        $this->calculateTotalAmount();
        session()->flash('message', $serviceName . ' of TZS ' . number_format($amount, 0) . ' added to payment');
    }

    public function processPayment()
    {
        $this->validate();

        if (empty($this->selectedBills) && $this->totalAmount === 0) {
            $this->showError('Please select bills or add custom services to pay.');
            return;
        }

        $this->isProcessing = true;

        try {
            $requestId = 'payment_' . time() . '_' . \Illuminate\Support\Str::random(8);
            
            Log::info('NBC payment processing request received', [
                'request_id' => $requestId,
                'member_number' => $this->memberNumber,
                'client_number' => $this->clientNumber,
                'selected_bills' => $this->selectedBills,
                'custom_amounts' => $this->customAmounts,
                'total_amount' => $this->totalAmount
            ]);

            // Get bills
            $bills = collect();
            if (!empty($this->selectedBills)) {
                $bills = Bill::whereIn('id', $this->selectedBills)
                    ->where('status', 'PENDING')
                    ->with(['service', 'member'])
                    ->get();
            }

            // Simulate MNO push notification
            $paymentResult = $this->simulateMnoPayment($requestId);

            if ($paymentResult['success']) {
                // Store the amount before clearing the form
                $this->processedAmount = $this->totalAmount;
                
                // Process bill payments
                foreach ($bills as $bill) {
                    $customAmount = $this->customAmounts[$bill->id] ?? null;
                    $amountToPay = $bill->amount_due;
                    
                    if ($customAmount && $customAmount > 0 && $this->isCustomAmountAllowed($bill->service->name ?? '')) {
                        $amountToPay = $customAmount;
                    }
                    
                    $bill->update([
                        'status' => 'PAID',
                        'amount_paid' => $amountToPay,
                        'updated_at' => now()
                    ]);

                    // Create payment record
                    Payment::create([
                        'bill_id' => $bill->id,
                        'payment_ref' => 'PAY_' . time() . '_' . $bill->id,
                        'transaction_reference' => $paymentResult['transaction_reference'],
                        'control_number' => $bill->control_number,
                        'amount' => $amountToPay,
                        'currency' => 'TZS',
                        'payment_channel' => $this->mnoProvider,
                        'payer_name' => $this->client->full_name ?? 'Unknown',
                        'payer_msisdn' => $this->phoneNumber,
                        'paid_at' => now(),
                        'received_at' => now(),
                        'status' => 'Confirmed',
                        'raw_payload' => json_encode($this->selectedBills),
                        'response_data' => json_encode($paymentResult)
                    ]);
                }

                // Clear form
                $this->selectedBills = [];
                $this->customAmounts = [];
                $this->savingsAmount = '';
                $this->depositAmount = '';
                $this->loanAmount = '';
                $this->calculateTotalAmount();

                // Reload bills
                $this->loadPendingBills();

                $this->transactionReference = $paymentResult['transaction_reference'];
                $this->showSuccessModal = true;

                Log::info('Payment processed successfully', [
                    'request_id' => $requestId,
                    'bills_count' => $bills->count(),
                    'total_amount' => $this->totalAmount,
                    'transaction_reference' => $paymentResult['transaction_reference']
                ]);

            } else {
                $this->showError('Payment failed: ' . $paymentResult['message']);
            }

        } catch (Exception $e) {
            Log::error('Payment processing failed', [
                'member_number' => $this->memberNumber,
                'client_number' => $this->clientNumber,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->showError('An error occurred while processing your payment. Please try again.');
        } finally {
            $this->isProcessing = false;
        }
    }

    private function simulateMnoPayment($requestId)
    {
        Log::info('Simulating MNO push notification', [
            'request_id' => $requestId,
            'phone_number' => $this->phoneNumber,
            'mno_provider' => $this->mnoProvider,
            'amount' => $this->totalAmount
        ]);

        // Simulate processing delay
        sleep(2);

        // Simulate success (90% success rate for demo)
        $isSuccess = rand(1, 100) <= 90;

        if ($isSuccess) {
            $transactionReference = 'TXN_' . time() . '_' . strtoupper(substr(md5(rand()), 0, 8));
            
            Log::info('MNO payment simulation successful', [
                'request_id' => $requestId,
                'transaction_reference' => $transactionReference
            ]);

            return [
                'success' => true,
                'transaction_reference' => $transactionReference,
                'message' => 'Payment authorized successfully',
                'provider_response' => [
                    'status' => 'SUCCESS',
                    'message' => 'Transaction completed successfully',
                    'reference' => $transactionReference,
                    'timestamp' => now()->toISOString()
                ]
            ];
        } else {
            Log::info('MNO payment simulation failed', [
                'request_id' => $requestId
            ]);

            return [
                'success' => false,
                'message' => 'Payment was declined by mobile money provider',
                'provider_response' => [
                    'status' => 'FAILED',
                    'message' => 'Insufficient funds or transaction declined',
                    'timestamp' => now()->toISOString()
                ]
            ];
        }
    }

    public function showError($message)
    {
        $this->errorMessage = $message;
        $this->showErrorModal = true;
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->processedAmount = 0;
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
    }

    public function render()
    {
        return view('livewire.nbc-payment');
    }
}
