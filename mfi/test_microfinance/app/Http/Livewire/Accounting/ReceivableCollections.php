<?php

namespace App\Http\Livewire\Accounting;

use App\Models\Receivable;
use App\Services\BalanceSheetItemIntegrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ReceivableCollections extends Component
{
    use WithPagination;

    public $showCollectionModal = false;
    public $receivableId;
    public $customerName;
    public $invoiceNumber;
    public $totalAmount;
    public $paidAmount;
    public $balance;
    public $collectionAmount;
    public $paymentMethod = 'cash';
    public $referenceNumber;
    public $collectionDate;
    public $notes;

    protected $rules = [
        'collectionAmount' => 'required|numeric|min:0.01',
        'paymentMethod' => 'required',
        'collectionDate' => 'required|date',
    ];

    public function mount()
    {
        $this->collectionDate = now()->format('Y-m-d');
    }

    public function openCollectionModal($receivableId)
    {
        $receivable = DB::table('trade_receivables')->find($receivableId);
        
        if ($receivable) {
            $this->receivableId = $receivableId;
            $this->customerName = $receivable->customer_name;
            $this->invoiceNumber = $receivable->invoice_number;
            $this->totalAmount = $receivable->amount;
            $this->paidAmount = $receivable->paid_amount;
            $this->balance = $receivable->balance;
            $this->collectionAmount = $receivable->balance; // Default to full balance
            $this->showCollectionModal = true;
        }
    }

    public function processCollection()
    {
        $this->validate();

        // Validate collection amount doesn't exceed balance
        if ($this->collectionAmount > $this->balance) {
            $this->addError('collectionAmount', 'Collection amount cannot exceed the outstanding balance.');
            return;
        }

        DB::beginTransaction();
        try {
            // Get the receivable
            $receivable = DB::table('trade_receivables')->find($this->receivableId);
            
            if (!$receivable) {
                throw new \Exception('Receivable not found');
            }

            // Use Balance Sheet Integration Service
            $integrationService = new BalanceSheetItemIntegrationService();
            
            // Process the collection through integration service
            $integrationService->processReceivableCollection($receivable, $this->collectionAmount);

            // Generate collection number
            $collectionNumber = 'COL-' . date('Ymd') . '-' . str_pad($this->receivableId, 4, '0', STR_PAD_LEFT);

            // Record in receivable_collections table
            DB::table('receivable_collections')->insert([
                'receivable_id' => $this->receivableId,
                'collection_number' => $collectionNumber,
                'collection_date' => $this->collectionDate,
                'amount_collected' => $this->collectionAmount,
                'payment_method' => $this->paymentMethod,
                'reference_number' => $this->referenceNumber,
                'notes' => $this->notes,
                'collected_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            session()->flash('message', 'Collection processed successfully. Amount: ' . number_format($this->collectionAmount, 2));
            
            $this->showCollectionModal = false;
            $this->reset(['receivableId', 'customerName', 'invoiceNumber', 'totalAmount', 
                        'paidAmount', 'balance', 'collectionAmount', 'referenceNumber', 'notes']);
            $this->paymentMethod = 'cash';
            $this->collectionDate = now()->format('Y-m-d');

            $this->emit('collectionProcessed');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process collection: ' . $e->getMessage());
            session()->flash('error', 'Failed to process collection: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $receivables = DB::table('trade_receivables')
            ->where('balance', '>', 0)
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        $collections = DB::table('receivable_collections as rc')
            ->join('trade_receivables as tr', 'rc.receivable_id', '=', 'tr.id')
            ->select('rc.*', 'tr.customer_name', 'tr.invoice_number')
            ->orderBy('rc.collection_date', 'desc')
            ->paginate(10);

        return view('livewire.accounting.receivable-collections', [
            'receivables' => $receivables,
            'collections' => $collections
        ]);
    }
}