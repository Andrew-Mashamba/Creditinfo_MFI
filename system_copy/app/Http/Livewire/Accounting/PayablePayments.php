<?php

namespace App\Http\Livewire\Accounting;

use App\Services\BalanceSheetItemIntegrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class PayablePayments extends Component
{
    use WithPagination;

    public $showPaymentModal = false;
    public $payableId;
    public $vendorName;
    public $billNumber;
    public $totalAmount;
    public $paidAmount;
    public $balance;
    public $paymentAmount;
    public $paymentMethod = 'bank_transfer';
    public $referenceNumber;
    public $paymentDate;
    public $bankAccountId;
    public $notes;

    protected $rules = [
        'paymentAmount' => 'required|numeric|min:0.01',
        'paymentMethod' => 'required',
        'paymentDate' => 'required|date',
    ];

    public function mount()
    {
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function openPaymentModal($payableId)
    {
        $payable = DB::table('trade_payables')->find($payableId);
        
        if ($payable) {
            $this->payableId = $payableId;
            $this->vendorName = $payable->vendor_name;
            $this->billNumber = $payable->bill_number;
            $this->totalAmount = $payable->amount;
            $this->paidAmount = $payable->paid_amount;
            $this->balance = $payable->balance;
            $this->paymentAmount = $payable->balance; // Default to full balance
            $this->showPaymentModal = true;
        }
    }

    public function processPayment()
    {
        $this->validate();

        // Validate payment amount doesn't exceed balance
        if ($this->paymentAmount > $this->balance) {
            $this->addError('paymentAmount', 'Payment amount cannot exceed the outstanding balance.');
            return;
        }

        DB::beginTransaction();
        try {
            // Get the payable
            $payable = DB::table('trade_payables')->find($this->payableId);
            
            if (!$payable) {
                throw new \Exception('Payable not found');
            }

            // Use Balance Sheet Integration Service
            $integrationService = new BalanceSheetItemIntegrationService();
            
            // Process the payment through integration service
            $integrationService->processPayablePayment($payable, $this->paymentAmount);

            // Generate payment number
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . str_pad($this->payableId, 4, '0', STR_PAD_LEFT);

            // Record in payable_payments table
            DB::table('payable_payments')->insert([
                'payable_id' => $this->payableId,
                'payment_number' => $paymentNumber,
                'payment_date' => $this->paymentDate,
                'amount_paid' => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'reference_number' => $this->referenceNumber,
                'bank_account_id' => $this->bankAccountId,
                'notes' => $this->notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            session()->flash('message', 'Payment processed successfully. Amount: ' . number_format($this->paymentAmount, 2));
            
            $this->showPaymentModal = false;
            $this->reset(['payableId', 'vendorName', 'billNumber', 'totalAmount', 
                        'paidAmount', 'balance', 'paymentAmount', 'referenceNumber', 
                        'bankAccountId', 'notes']);
            $this->paymentMethod = 'bank_transfer';
            $this->paymentDate = now()->format('Y-m-d');

            $this->emit('paymentProcessed');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process payment: ' . $e->getMessage());
            session()->flash('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $payables = DB::table('trade_payables')
            ->where('balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        $payments = DB::table('payable_payments as pp')
            ->join('trade_payables as tp', 'pp.payable_id', '=', 'tp.id')
            ->select('pp.*', 'tp.vendor_name', 'tp.bill_number')
            ->orderBy('pp.payment_date', 'desc')
            ->paginate(10);

        $bankAccounts = DB::table('accounts')
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%BANK%')
                      ->orWhere('account_name', 'LIKE', '%CASH%')
                      ->orWhere('major_category_code', '1000');
            })
            ->where('status', 'ACTIVE')
            ->get();

        return view('livewire.accounting.payable-payments', [
            'payables' => $payables,
            'payments' => $payments,
            'bankAccounts' => $bankAccounts
        ]);
    }
}