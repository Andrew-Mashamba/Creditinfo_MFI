<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Account;
use App\Models\InternalTransfer;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InternalTransfers extends Component
{
    use WithFileUploads, WithPagination;

    // Form properties
    public $transferDate;
    public $transferType = 'asset_to_asset';
    public $fromAccountId;
    public $toAccountId;
    public $amount;
    public $narration;
    public $supportingDocument;
    public $status = 'posted';

    // UI state
    public $showTransferForm = false;
    public $editingTransferId = null;

    // Filters
    public $searchTerm = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Transfer types with their account type constraints
    public $transferTypes = [
        'asset_to_asset' => 'Asset to Asset',
        'asset_to_liability' => 'Asset to Liability',
        'liability_to_liability' => 'Liability to Liability',
        'liability_to_asset' => 'Liability to Asset',
        'equity_to_equity' => 'Equity to Equity',
        'liability_to_equity' => 'Liability to Equity',
        'equity_to_liability' => 'Equity to Liability',
        'asset_to_equity' => 'Asset to Equity',
        'equity_to_asset' => 'Equity to Asset',
    ];

    protected $rules = [
        'transferDate' => 'required|date|before_or_equal:today',
        'transferType' => 'required|string|in:asset_to_asset,asset_to_liability,liability_to_liability,liability_to_asset,equity_to_equity,liability_to_equity,equity_to_liability,asset_to_equity,equity_to_asset',
        'fromAccountId' => 'required|exists:accounts,id',
        'toAccountId' => 'required|exists:accounts,id|different:fromAccountId',
        'amount' => 'required|numeric|min:0.01',
        'narration' => 'nullable|string|max:1000',
        'supportingDocument' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        'status' => 'required|in:draft,posted',
    ];

    protected $messages = [
        'transferDate.required' => 'Transfer date is required.',
        'transferDate.before_or_equal' => 'Transfer date cannot be in the future.',
        'transferType.required' => 'Please select a transfer type.',
        'fromAccountId.required' => 'Please select the source account.',
        'toAccountId.required' => 'Please select the destination account.',
        'toAccountId.different' => 'Destination account must be different from source account.',
        'amount.required' => 'Please enter the transfer amount.',
        'amount.min' => 'Amount must be greater than zero.',
        'supportingDocument.mimes' => 'Document must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        'supportingDocument.max' => 'Document size must not exceed 5MB.',
    ];

    public function mount()
    {
        $this->transferDate = now()->format('Y-m-d');
    }

    public function getAccountTypesForTransfer($transferType)
    {
        $mapping = [
            'asset_to_asset' => ['from' => 'asset', 'to' => 'asset'],
            'asset_to_liability' => ['from' => 'asset', 'to' => 'liability'],
            'liability_to_liability' => ['from' => 'liability', 'to' => 'liability'],
            'liability_to_asset' => ['from' => 'liability', 'to' => 'asset'],
            'equity_to_equity' => ['from' => 'equity', 'to' => 'equity'],
            'liability_to_equity' => ['from' => 'liability', 'to' => 'equity'],
            'equity_to_liability' => ['from' => 'equity', 'to' => 'liability'],
            'asset_to_equity' => ['from' => 'asset', 'to' => 'equity'],
            'equity_to_asset' => ['from' => 'equity', 'to' => 'asset'],
        ];

        return $mapping[$transferType] ?? ['from' => null, 'to' => null];
    }

    public function getFromAccountsProperty()
    {
        $accountTypes = $this->getAccountTypesForTransfer($this->transferType);
        if (!$accountTypes['from']) return collect();

        return Account::where('account_type', $accountTypes['from'])
            ->where('status', 'active')
            ->orderBy('account_name')
            ->get();
    }

    public function getToAccountsProperty()
    {
        $accountTypes = $this->getAccountTypesForTransfer($this->transferType);
        if (!$accountTypes['to']) return collect();

        return Account::where('account_type', $accountTypes['to'])
            ->where('status', 'active')
            ->where('id', '!=', $this->fromAccountId)
            ->orderBy('account_name')
            ->get();
    }

    public function updatedTransferType()
    {
        // Reset account selections when transfer type changes
        $this->fromAccountId = null;
        $this->toAccountId = null;
    }

    public function updatedFromAccountId()
    {
        // Reset to account if it's the same as from account
        if ($this->toAccountId == $this->fromAccountId) {
            $this->toAccountId = null;
        }
    }

    public function showForm()
    {
        $this->showTransferForm = true;
        $this->editingTransferId = null;
        $this->resetForm();
    }

    public function hideForm()
    {
        $this->showTransferForm = false;
        $this->editingTransferId = null;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->transferDate = now()->format('Y-m-d');
        $this->transferType = 'asset_to_asset';
        $this->fromAccountId = null;
        $this->toAccountId = null;
        $this->amount = null;
        $this->narration = '';
        $this->supportingDocument = null;
        $this->status = 'posted';
    }

    public function editTransfer($transferId)
    {
        $transfer = InternalTransfer::findOrFail($transferId);
        
        $this->editingTransferId = $transferId;
        $this->transferDate = $transfer->transfer_date->format('Y-m-d');
        $this->transferType = $transfer->transfer_type;
        $this->fromAccountId = $transfer->from_account_id;
        $this->toAccountId = $transfer->to_account_id;
        $this->amount = $transfer->amount;
        $this->narration = $transfer->narration;
        $this->status = $transfer->status;
        
        $this->showTransferForm = true;
    }

    public function validateTransfer()
    {
        $this->validate();

        // Additional business rules validation
        $fromAccount = Account::find($this->fromAccountId);
        $toAccount = Account::find($this->toAccountId);

        if (!$fromAccount || !$toAccount) {
            $this->addError('general', 'Selected accounts not found.');
            return false;
        }

        if (!$fromAccount->is_active || !$toAccount->is_active) {
            $this->addError('general', 'Both accounts must be active.');
            return false;
        }

        // Validate account types match transfer type
        $accountTypes = $this->getAccountTypesForTransfer($this->transferType);
        if ($fromAccount->account_type !== $accountTypes['from']) {
            $this->addError('fromAccountId', 'Source account type does not match selected transfer type.');
            return false;
        }

        if ($toAccount->account_type !== $accountTypes['to']) {
            $this->addError('toAccountId', 'Destination account type does not match selected transfer type.');
            return false;
        }

        return true;
    }

    public function submitTransfer()
    {
        if (!$this->validateTransfer()) {
            return;
        }

        DB::beginTransaction();
        
        try {
            $attachmentPath = null;
            if ($this->supportingDocument) {
                $attachmentPath = $this->supportingDocument->store('internal-transfers', 'public');
            }

            if ($this->editingTransferId) {
                // Update existing transfer
                $transfer = InternalTransfer::findOrFail($this->editingTransferId);
                $transfer->update([
                    'transfer_date' => $this->transferDate,
                    'transfer_type' => $this->transferType,
                    'from_account_id' => $this->fromAccountId,
                    'to_account_id' => $this->toAccountId,
                    'amount' => $this->amount,
                    'narration' => $this->narration,
                    'attachment_path' => $attachmentPath ?: $transfer->attachment_path,
                    'status' => $this->status,
                ]);
            } else {
                // Create new transfer
                $transfer = InternalTransfer::create([
                    'transfer_date' => $this->transferDate,
                    'transfer_type' => $this->transferType,
                    'from_account_id' => $this->fromAccountId,
                    'to_account_id' => $this->toAccountId,
                    'amount' => $this->amount,
                    'narration' => $this->narration,
                    'attachment_path' => $attachmentPath,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                ]);
            }

            // Create journal entries if status is posted
            if ($this->status === 'posted') {
                $this->createJournalEntries($transfer);
            }

            DB::commit();

            $this->hideForm();
            session()->flash('success', $this->editingTransferId ? 'Transfer updated successfully!' : 'Transfer created successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->addError('general', 'An error occurred while processing the transfer: ' . $e->getMessage());
        }
    }

    private function createJournalEntries($transfer)
    {
        $fromAccount = Account::find($transfer->from_account_id);
        $toAccount = Account::find($transfer->to_account_id);

        // Determine debit and credit based on account types
        $debitAccountId = $transfer->to_account_id;
        $creditAccountId = $transfer->from_account_id;

        // For asset decreases and liability/equity increases, reverse the entries
        if (in_array($fromAccount->account_type, ['asset']) && in_array($toAccount->account_type, ['liability', 'equity'])) {
            $debitAccountId = $transfer->to_account_id;
            $creditAccountId = $transfer->from_account_id;
        }

        $reference = 'IT' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT);

        // Create journal entries
        JournalEntry::create([
            'transaction_date' => $transfer->transfer_date,
            'account_id' => $debitAccountId,
            'reference' => $reference,
            'description' => $transfer->narration ?: 'Internal Transfer',
            'debit_amount' => $transfer->amount,
            'credit_amount' => 0,
            'created_by' => $transfer->created_by,
        ]);

        JournalEntry::create([
            'transaction_date' => $transfer->transfer_date,
            'account_id' => $creditAccountId,
            'reference' => $reference,
            'description' => $transfer->narration ?: 'Internal Transfer',
            'debit_amount' => 0,
            'credit_amount' => $transfer->amount,
            'created_by' => $transfer->created_by,
        ]);
    }

    public function deleteTransfer($transferId)
    {
        try {
            $transfer = InternalTransfer::findOrFail($transferId);
            
            // Delete associated file if exists
            if ($transfer->attachment_path) {
                Storage::disk('public')->delete($transfer->attachment_path);
            }

            $transfer->delete();
            session()->flash('success', 'Transfer deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting transfer: ' . $e->getMessage());
        }
    }

    public function downloadAttachment($transferId)
    {
        $transfer = InternalTransfer::findOrFail($transferId);
        if ($transfer->attachment_path && Storage::disk('public')->exists($transfer->attachment_path)) {
            return Storage::disk('public')->download($transfer->attachment_path);
        }
        
        session()->flash('error', 'File not found.');
    }

    public function render()
    {
        // Get transfers with filters
        $transfers = InternalTransfer::with(['fromAccount', 'toAccount', 'creator'])
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('narration', 'like', '%' . $this->searchTerm . '%')
                      ->orWhereHas('fromAccount', function($subQ) {
                          $subQ->where('account_name', 'like', '%' . $this->searchTerm . '%');
                      })
                      ->orWhereHas('toAccount', function($subQ) {
                          $subQ->where('account_name', 'like', '%' . $this->searchTerm . '%');
                      });
                });
            })
            ->when($this->filterStatus, function($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterDateFrom, function($query) {
                $query->where('transfer_date', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function($query) {
                $query->where('transfer_date', '<=', $this->filterDateTo);
            })
            ->orderBy('transfer_date', 'desc')
            ->paginate(10);

        // Statistics
        $totalTransfers = InternalTransfer::count();
        $totalAmount = InternalTransfer::where('status', 'posted')->sum('amount');
        $todayTransfers = InternalTransfer::whereDate('transfer_date', today())->count();
        $draftTransfers = InternalTransfer::where('status', 'draft')->count();

        return view('livewire.accounting.internal-transfers', [
            'transfers' => $transfers,
            'transferTypes' => $this->transferTypes,
            'fromAccounts' => $this->fromAccounts,
            'toAccounts' => $this->toAccounts,
            'totalTransfers' => $totalTransfers,
            'totalAmount' => $totalAmount,
            'todayTransfers' => $todayTransfers,
            'draftTransfers' => $draftTransfers,
        ]);
    }
}
