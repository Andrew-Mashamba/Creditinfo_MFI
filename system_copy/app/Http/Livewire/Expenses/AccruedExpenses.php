<?php

namespace App\Http\Livewire\Expenses;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Expense;
use App\Models\Account;
use Carbon\Carbon;

class AccruedExpenses extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters
    public $dateFrom;
    public $dateTo;
    public $statusFilter = '';
    public $search = '';
    public $perPage = 15;

    // Create accrual modal
    public $showCreateModal = false;
    public $accrualDescription = '';
    public $accrualAmount = '';
    public $accrualAccountId = '';
    public $accrualExpectedPaymentDate = '';
    public $accrualNotes = '';

    // Realize accrual modal
    public $showRealizeModal = false;
    public $selectedAccrualId = null;
    public $realizationDate;
    public $realizationNotes = '';

    // View details modal
    public $showDetailModal = false;
    public $selectedAccrual = null;

    protected $rules = [
        'accrualDescription' => 'required|string|max:500',
        'accrualAmount' => 'required|numeric|min:0.01',
        'accrualAccountId' => 'required|exists:accounts,id',
        'accrualExpectedPaymentDate' => 'required|date|after_or_equal:today',
    ];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->realizationDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function getAccruedExpensesProperty()
    {
        $query = DB::table('expenses as e')
            ->leftJoin('accounts as a', 'e.account_id', '=', 'a.id')
            ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
            ->where('e.is_accrued', true)
            ->select([
                'e.*',
                'a.account_name',
                'a.account_number',
                'u.name as created_by_name'
            ]);

        // Apply date filters
        if ($this->dateFrom) {
            $query->whereDate('e.accrual_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('e.accrual_date', '<=', $this->dateTo);
        }

        // Apply status filter
        if ($this->statusFilter === 'pending') {
            $query->whereNull('e.realization_date');
        } elseif ($this->statusFilter === 'realized') {
            $query->whereNotNull('e.realization_date');
        } elseif ($this->statusFilter === 'overdue') {
            $query->whereNull('e.realization_date')
                  ->where('e.expected_payment_date', '<', now()->format('Y-m-d'));
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('e.description', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('a.account_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('a.account_number', 'LIKE', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('e.accrual_date', 'desc')->paginate($this->perPage);
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = DB::table('expenses')->where('is_accrued', true);

        if ($this->dateFrom) {
            $baseQuery->whereDate('accrual_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $baseQuery->whereDate('accrual_date', '<=', $this->dateTo);
        }

        return [
            'total_accrued' => (clone $baseQuery)->sum('amount') ?? 0,
            'pending_count' => (clone $baseQuery)->whereNull('realization_date')->count(),
            'pending_amount' => (clone $baseQuery)->whereNull('realization_date')->sum('amount') ?? 0,
            'realized_count' => (clone $baseQuery)->whereNotNull('realization_date')->count(),
            'realized_amount' => (clone $baseQuery)->whereNotNull('realization_date')->sum('amount') ?? 0,
            'overdue_count' => (clone $baseQuery)->whereNull('realization_date')
                ->where('expected_payment_date', '<', now()->format('Y-m-d'))->count(),
            'overdue_amount' => (clone $baseQuery)->whereNull('realization_date')
                ->where('expected_payment_date', '<', now()->format('Y-m-d'))->sum('amount') ?? 0,
        ];
    }

    public function getExpenseAccountsProperty()
    {
        return Account::where('major_category_code', '5000')
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
    }

    public function openCreateModal()
    {
        $this->reset(['accrualDescription', 'accrualAmount', 'accrualAccountId', 'accrualExpectedPaymentDate', 'accrualNotes']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createAccrual()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Get the accrued expenses liability account
            $accruedExpenseAccount = Account::where('account_number', '0101200029002920')->first();
            if (!$accruedExpenseAccount) {
                $accruedExpenseAccount = Account::where('account_name', 'LIKE', '%ACCRUED EXPENSES%')
                    ->where('major_category_code', '2000')
                    ->first();
            }

            // Create the accrued expense record
            $expenseId = DB::table('expenses')->insertGetId([
                'account_id' => $this->accrualAccountId,
                'amount' => $this->accrualAmount,
                'description' => $this->accrualDescription,
                'payment_type' => 'accrual',
                'user_id' => auth()->id(),
                'status' => 'ACCRUED',
                'is_accrued' => true,
                'accrual_date' => now(),
                'expected_payment_date' => $this->accrualExpectedPaymentDate,
                'budget_notes' => $this->accrualNotes,
                'expense_month' => now()->startOfMonth(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create GL entries for accrual
            // DR: Expense Account (5000)
            // CR: Accrued Expenses Liability (2000)
            $referenceNumber = 'ACC-' . date('Ymd') . '-' . str_pad($expenseId, 6, '0', STR_PAD_LEFT);

            $expenseAccount = Account::find($this->accrualAccountId);

            // Debit expense account
            DB::table('general_ledger')->insert([
                'record_on_account_number' => $expenseAccount->account_number,
                'debit' => $this->accrualAmount,
                'credit' => 0,
                'narration' => 'Accrued expense: ' . $this->accrualDescription,
                'reference_number' => $referenceNumber,
                'trans_status' => 'POSTED',
                'sender_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Credit accrued expenses liability
            if ($accruedExpenseAccount) {
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $accruedExpenseAccount->account_number,
                    'debit' => 0,
                    'credit' => $this->accrualAmount,
                    'narration' => 'Accrued expense: ' . $this->accrualDescription,
                    'reference_number' => $referenceNumber,
                    'trans_status' => 'POSTED',
                    'sender_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->closeCreateModal();
            session()->flash('success', 'Accrued expense created successfully.');

            Log::channel('budget_management')->info('Accrued expense created', [
                'expense_id' => $expenseId,
                'amount' => $this->accrualAmount,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('budget_management')->error('Failed to create accrued expense', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to create accrued expense: ' . $e->getMessage());
        }
    }

    public function openRealizeModal($expenseId)
    {
        $this->selectedAccrualId = $expenseId;
        $this->realizationDate = now()->format('Y-m-d');
        $this->realizationNotes = '';
        $this->showRealizeModal = true;
    }

    public function closeRealizeModal()
    {
        $this->showRealizeModal = false;
        $this->selectedAccrualId = null;
    }

    public function realizeAccrual()
    {
        $this->validate([
            'realizationDate' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $expense = DB::table('expenses')->where('id', $this->selectedAccrualId)->first();

            if (!$expense) {
                throw new \Exception('Expense not found');
            }

            // Update the expense record
            DB::table('expenses')
                ->where('id', $this->selectedAccrualId)
                ->update([
                    'realization_date' => $this->realizationDate,
                    'status' => 'PENDING_APPROVAL',
                    'budget_notes' => $expense->budget_notes . "\nRealized: " . $this->realizationNotes,
                    'updated_at' => now(),
                ]);

            // Reverse the accrual GL entries
            $accruedExpenseAccount = Account::where('account_number', '0101200029002920')->first();
            if (!$accruedExpenseAccount) {
                $accruedExpenseAccount = Account::where('account_name', 'LIKE', '%ACCRUED EXPENSES%')
                    ->where('major_category_code', '2000')
                    ->first();
            }

            $referenceNumber = 'REV-ACC-' . date('Ymd') . '-' . str_pad($expense->id, 6, '0', STR_PAD_LEFT);

            // DR: Accrued Expenses Liability (reverse the credit)
            if ($accruedExpenseAccount) {
                DB::table('general_ledger')->insert([
                    'record_on_account_number' => $accruedExpenseAccount->account_number,
                    'debit' => $expense->amount,
                    'credit' => 0,
                    'narration' => 'Reversal of accrual - expense realized: ' . $expense->description,
                    'reference_number' => $referenceNumber,
                    'trans_status' => 'POSTED',
                    'sender_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->closeRealizeModal();
            session()->flash('success', 'Accrued expense realized successfully. It is now pending approval for payment.');

            Log::channel('budget_management')->info('Accrued expense realized', [
                'expense_id' => $this->selectedAccrualId,
                'amount' => $expense->amount,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('budget_management')->error('Failed to realize accrued expense', [
                'error' => $e->getMessage(),
                'expense_id' => $this->selectedAccrualId,
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to realize expense: ' . $e->getMessage());
        }
    }

    public function viewDetails($expenseId)
    {
        $this->selectedAccrual = DB::table('expenses as e')
            ->leftJoin('accounts as a', 'e.account_id', '=', 'a.id')
            ->leftJoin('users as u', 'e.user_id', '=', 'u.id')
            ->leftJoin('users as pu', 'e.paid_by_user_id', '=', 'pu.id')
            ->where('e.id', $expenseId)
            ->select([
                'e.*',
                'a.account_name',
                'a.account_number',
                'u.name as created_by_name',
                'pu.name as paid_by_name'
            ])
            ->first();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedAccrual = null;
    }

    public function render()
    {
        return view('livewire.expenses.accrued-expenses', [
            'accruedExpenses' => $this->accruedExpenses,
            'summaryStats' => $this->summaryStats,
            'expenseAccounts' => $this->expenseAccounts,
        ]);
    }
}
