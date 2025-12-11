<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountsModel;
use App\Services\JournalEntryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JournalEntry extends Component
{
    use WithPagination;

    // Form properties
    public $transactionDate;
    public $referenceNo;
    public $description;
    public $journalLines = [];

    // UI properties
    public $showEntryForm = false;
    public $showDetailsModal = false;
    public $showPostConfirm = false;
    public $showReverseConfirm = false;
    public $selectedJournalEntryId = null;
    public $selectedJournalEntryLines = [];
    public $reversalReason = '';
    public $searchTerm = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Account search properties
    public $accountSearchTerms = [];

    // Validation rules
    protected $rules = [
        'transactionDate' => 'required|date|before_or_equal:today',
        'referenceNo' => 'required|string|max:50|unique:journal_entries,reference_no',
        'description' => 'required|string|min:3|max:500',
        'journalLines.*.account_number' => 'required|exists:accounts,account_number',
        'journalLines.*.debit' => 'nullable|numeric|min:0',
        'journalLines.*.credit' => 'nullable|numeric|min:0',
        'journalLines.*.line_description' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'transactionDate.required' => 'Please select the journal entry date.',
        'transactionDate.before_or_equal' => 'Entry date cannot be in the future.',
        'referenceNo.required' => 'Please provide a reference number.',
        'referenceNo.unique' => 'This reference number already exists.',
        'description.required' => 'Please provide a description for this journal entry.',
        'journalLines.*.account_number.required' => 'Please select an account.',
        'journalLines.*.account_number.exists' => 'Selected account does not exist.',
    ];

    public function mount()
    {
        $this->transactionDate = now()->format('Y-m-d');
        $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = now()->format('Y-m-d');
        $this->generateReferenceNo();
        $this->addLine();
        $this->addLine();
    }

    public function generateReferenceNo()
    {
        $this->referenceNo = 'JE-' . now()->format('YmdHis');
    }

    public function showForm()
    {
        $this->showEntryForm = true;
        $this->resetValidation();
    }

    public function hideForm()
    {
        $this->showEntryForm = false;
        $this->reset(['description', 'journalLines']);
        $this->transactionDate = now()->format('Y-m-d');
        $this->generateReferenceNo();
        $this->addLine();
        $this->addLine();
        $this->resetValidation();
    }

    public function addLine()
    {
        $this->journalLines[] = [
            'account_number' => '',
            'debit' => '',
            'credit' => '',
            'line_description' => ''
        ];
    }

    public function removeLine($index)
    {
        if (count($this->journalLines) > 2) {
            unset($this->journalLines[$index]);
            $this->journalLines = array_values($this->journalLines);
        }
    }

    public function getTotals()
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->journalLines as $line) {
            $totalDebit += floatval($line['debit'] ?? 0);
            $totalCredit += floatval($line['credit'] ?? 0);
        }

        return [
            'debit' => $totalDebit,
            'credit' => $totalCredit,
            'difference' => $totalDebit - $totalCredit,
            'balanced' => abs($totalDebit - $totalCredit) < 0.01
        ];
    }

    public function submitJournalEntry()
    {
        // Custom validation for balanced entry
        $totals = $this->getTotals();
        if (!$totals['balanced']) {
            session()->flash('error', 'Journal entry must be balanced. Debit and Credit totals must be equal.');
            return;
        }

        // Validate that each line has either debit or credit (but not both)
        foreach ($this->journalLines as $index => $line) {
            $hasDebit = !empty($line['debit']) && $line['debit'] > 0;
            $hasCredit = !empty($line['credit']) && $line['credit'] > 0;

            if (!$hasDebit && !$hasCredit) {
                session()->flash('error', 'Each line must have either a debit or credit amount.');
                return;
            }

            if ($hasDebit && $hasCredit) {
                session()->flash('error', 'Each line cannot have both debit and credit amounts.');
                return;
            }
        }

        $this->validate();

        try {
            // Prepare data for service
            $data = [
                'transaction_date' => $this->transactionDate,
                'reference_no' => $this->referenceNo,
                'description' => $this->description,
                'is_reversal' => false,
                'lines' => []
            ];

            // Prepare lines
            foreach ($this->journalLines as $line) {
                if (!empty($line['account_number'])) {
                    $data['lines'][] = [
                        'account_code' => $line['account_number'], // Service expects 'account_code' key but it's the account_number value
                        'debit' => floatval($line['debit'] ?? 0),
                        'credit' => floatval($line['credit'] ?? 0),
                        'description' => $line['line_description'] ?? ''
                    ];
                }
            }

            // Use service to create journal entry
            $journalEntryService = new JournalEntryService();
            $result = $journalEntryService->createJournalEntry($data);

            session()->flash('success', 'Journal entry created successfully with reference: ' . $this->referenceNo);
            $this->hideForm();

        } catch (\Exception $e) {
            Log::error('Journal entry creation failed: ' . $e->getMessage());
            session()->flash('error', 'Journal entry creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show details modal for a journal entry
     */
    public function viewDetails($journalEntryId)
    {
        $entry = DB::table('journal_entries')->where('id', $journalEntryId)->first();

        if ($entry) {
            $this->selectedJournalEntryId = $journalEntryId;
            $this->selectedJournalEntryLines = DB::table('journal_entry_lines')
                ->where('journal_entry_id', $journalEntryId)
                ->get()
                ->toArray();

            $this->showDetailsModal = true;
        }
    }

    /**
     * Close details modal
     */
    public function closeDetails()
    {
        $this->showDetailsModal = false;
        $this->selectedJournalEntryId = null;
        $this->selectedJournalEntryLines = [];
    }

    /**
     * Show post confirmation dialog
     */
    public function confirmPost($journalEntryId)
    {
        $this->selectedJournalEntryId = $journalEntryId;
        $this->showPostConfirm = true;
    }

    /**
     * Show reverse confirmation dialog
     */
    public function confirmReverse($journalEntryId)
    {
        $this->selectedJournalEntryId = $journalEntryId;
        $this->reversalReason = '';
        $this->showReverseConfirm = true;
    }

    /**
     * Cancel post/reverse confirmation
     */
    public function cancelAction()
    {
        $this->showPostConfirm = false;
        $this->showReverseConfirm = false;
        $this->selectedJournalEntryId = null;
        $this->reversalReason = '';
    }

    /**
     * Post a journal entry to the general ledger
     */
    public function postJournalEntry()
    {
        try {
            if (!$this->selectedJournalEntryId) {
                session()->flash('error', 'No journal entry selected.');
                return;
            }

            $journalEntryService = new JournalEntryService();
            $result = $journalEntryService->postJournalEntry($this->selectedJournalEntryId);

            session()->flash('success', $result['message']);
            $this->cancelAction();

        } catch (\Exception $e) {
            Log::error('Journal entry posting failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to post journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Reverse a posted journal entry
     */
    public function reverseJournalEntry()
    {
        try {
            if (!$this->selectedJournalEntryId) {
                session()->flash('error', 'No journal entry selected.');
                return;
            }

            if (empty($this->reversalReason)) {
                session()->flash('error', 'Please provide a reason for reversal.');
                return;
            }

            $journalEntryService = new JournalEntryService();
            $result = $journalEntryService->reverseJournalEntry(
                $this->selectedJournalEntryId,
                $this->reversalReason
            );

            session()->flash('success', $result['message'] . ' Reversal reference: ' . $result['reversal_reference_no']);
            $this->cancelAction();

        } catch (\Exception $e) {
            Log::error('Journal entry reversal failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to reverse journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Get filtered accounts for a specific line
     * Searches by: account name, account number, client_number, type, product_number, category codes, notes
     * Returns accounts from all levels (1, 2, 3, 4) as long as they don't have child accounts
     * Case-insensitive search across multiple fields
     */
    public function getFilteredAccounts($lineIndex)
    {
        $searchTerm = $this->accountSearchTerms[$lineIndex] ?? '';

        // Get all parent account numbers (accounts that have children)
        $parentAccountNumbers = DB::table('accounts')
            ->whereNotNull('parent_account_number')
            ->where('parent_account_number', '!=', '')
            ->distinct()
            ->pluck('parent_account_number')
            ->toArray();

        $query = AccountsModel::where('status', 'ACTIVE')
            ->whereNotIn('account_number', $parentAccountNumbers); // Exclude accounts that have children

        if (!empty($searchTerm)) {
            // PostgreSQL ILIKE for case-insensitive search across multiple fields
            $query->where(function($q) use ($searchTerm) {
                $q->where('account_name', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('account_number', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('client_number', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('account_type', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('type', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('product_number', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('sub_product_number', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('major_category_code', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('category_code', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('sub_category_code', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('notes', 'ILIKE', '%' . $searchTerm . '%');
            });
        }

        return $query->orderBy('account_name')->limit(100)->get();
    }

    /**
     * Update search term and trigger search
     */
    public function updatedAccountSearchTerms($value, $key)
    {
        // This will automatically re-render and show filtered results
    }

    /**
     * Select account from dropdown
     */
    public function selectAccount($lineIndex, $accountNumber)
    {
        $this->journalLines[$lineIndex]['account_number'] = $accountNumber;
        $this->accountSearchTerms[$lineIndex] = '';
    }

    public function render()
    {
        // Get all parent account numbers (accounts that have children)
        $parentAccountNumbers = DB::table('accounts')
            ->whereNotNull('parent_account_number')
            ->where('parent_account_number', '!=', '')
            ->distinct()
            ->pluck('parent_account_number')
            ->toArray();

        // Get all active accounts from all levels (1, 2, 3, 4) that don't have children
        $accounts = AccountsModel::where('status', 'ACTIVE')
            ->whereNotIn('account_number', $parentAccountNumbers) // Exclude accounts that have children
            ->orderBy('account_name')
            ->get();

        // Get journal entries with pagination and user information
        $journalEntries = DB::table('journal_entries')
            ->leftJoin('users as creator', 'journal_entries.created_by', '=', 'creator.id')
            ->leftJoin('users as poster', 'journal_entries.posted_by', '=', 'poster.id')
            ->select(
                'journal_entries.*',
                'creator.name as created_by_name',
                'poster.name as posted_by_name'
            )
            ->whereBetween('journal_entries.transaction_date', [$this->filterDateFrom, $this->filterDateTo])
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('journal_entries.reference_no', 'LIKE', '%' . $this->searchTerm . '%')
                      ->orWhere('journal_entries.description', 'LIKE', '%' . $this->searchTerm . '%');
                });
            })
            ->orderBy('journal_entries.transaction_date', 'desc')
            ->orderBy('journal_entries.created_at', 'desc')
            ->paginate(15);

        // Calculate statistics
        $todayEntries = DB::table('journal_entries')
            ->whereDate('transaction_date', now())
            ->count();

        $totalEntries = DB::table('journal_entries')->count();

        $thisMonthEntries = DB::table('journal_entries')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->count();

        $totals = $this->getTotals();

        // Get selected journal entry if ID is set
        $selectedJournalEntry = null;
        if ($this->selectedJournalEntryId) {
            $selectedJournalEntry = DB::table('journal_entries')
                ->where('id', $this->selectedJournalEntryId)
                ->first();
        }

        return view('livewire.accounting.journal-entry', [
            'accounts' => $accounts,
            'journalEntries' => $journalEntries,
            'todayEntries' => $todayEntries,
            'totalEntries' => $totalEntries,
            'thisMonthEntries' => $thisMonthEntries,
            'totals' => $totals,
            'selectedJournalEntry' => $selectedJournalEntry,
        ]);
    }
}
