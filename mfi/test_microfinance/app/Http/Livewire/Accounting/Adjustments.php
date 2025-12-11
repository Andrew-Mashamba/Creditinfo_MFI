<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Adjustments extends Component
{
    use WithPagination;

    // Form properties
    public $adjustmentType = 'accrued_revenue';
    public $adjustmentPeriod = 'monthly';
    public $debitAccount;
    public $creditAccount;
    public $amount;
    public $description;
    public $reference;
    public $adjustmentDate;
    public $reason;

    // UI properties
    public $showAdjustmentForm = false;
    public $searchTerm = '';
    public $filterType = 'all';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Adjustment types based on accounting principles
    public $adjustmentTypes = [
        'accrued_revenue' => 'Accrued Revenues',
        'accrued_expense' => 'Accrued Expenses', 
        'prepaid_expense' => 'Prepaid Expenses',
        'unearned_revenue' => 'Unearned Revenues',
        'depreciation' => 'Depreciation',
        'inventory_adjustment' => 'Inventory Adjustments',
        'bad_debt_provision' => 'Bad Debts Provision',
        'reclassification' => 'Reclassification',
        'correction' => 'Error Correction',
    ];

    // Adjustment periods
    public $adjustmentPeriods = [
        'monthly' => 'Month-End',
        'quarterly' => 'Quarter-End', 
        'annually' => 'Year-End',
        'other' => 'Other',
    ];

    // Validation rules
    protected $rules = [
        'adjustmentType' => 'required|string|in:accrued_revenue,accrued_expense,prepaid_expense,unearned_revenue,depreciation,inventory_adjustment,bad_debt_provision,reclassification,correction',
        'adjustmentPeriod' => 'required|string|in:monthly,quarterly,annually,other',
        'debitAccount' => 'required|exists:accounts,id',
        'creditAccount' => 'required|exists:accounts,id|different:debitAccount',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'required|string|min:3|max:255',
        'reference' => 'nullable|string|max:100',
        'adjustmentDate' => 'required|date|before_or_equal:today',
        'reason' => 'required|string|min:20|max:1000',
    ];

    protected $messages = [
        'adjustmentType.required' => 'Please select an adjustment type.',
        'adjustmentType.in' => 'Please select a valid adjustment type.',
        'adjustmentPeriod.required' => 'Please select the adjustment period.',
        'debitAccount.required' => 'Please select the debit account.',
        'creditAccount.required' => 'Please select the credit account.',
        'creditAccount.different' => 'Credit account must be different from debit account.',
        'amount.required' => 'Please enter the adjustment amount.',
        'amount.min' => 'Amount must be greater than zero.',
        'description.required' => 'Please provide an adjustment description.',
        'adjustmentDate.required' => 'Please select the adjustment date.',
        'adjustmentDate.before_or_equal' => 'Adjustment date cannot be in the future.',
        'reason.required' => 'Please provide a detailed reason for this adjustment.',
        'reason.min' => 'Reason must be at least 20 characters long to ensure proper documentation.',
    ];

    public function mount()
    {
        $this->adjustmentDate = now()->format('Y-m-d');
    }

    public function showForm()
    {
        $this->showAdjustmentForm = true;
        $this->resetValidation();
    }

    public function hideForm()
    {
        $this->showAdjustmentForm = false;
        $this->reset(['adjustmentType', 'adjustmentPeriod', 'debitAccount', 'creditAccount', 'amount', 'description', 'reference', 'reason']);
        $this->resetValidation();
    }

    public function submitAdjustment()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Create adjustment record (you'll need to create this table/model)
            // This is a placeholder for the actual adjustment logic
            
            DB::commit();
            
            session()->flash('success', 'Adjustment entry created successfully.');
            $this->hideForm();
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    public function getAdjustmentExamples()
    {
        return [
            'accrued_revenue' => [
                'description' => 'Interest earned but not yet received',
                'debit' => 'Interest Receivable',
                'credit' => 'Interest Revenue'
            ],
            'accrued_expense' => [
                'description' => 'Salaries payable, electricity bill due',
                'debit' => 'Salaries Expense',
                'credit' => 'Salaries Payable'
            ],
            'prepaid_expense' => [
                'description' => 'Rent paid for 12 months; adjust for 1 month used',
                'debit' => 'Rent Expense',
                'credit' => 'Prepaid Rent'
            ],
            'unearned_revenue' => [
                'description' => 'Advance payment for services now delivered',
                'debit' => 'Unearned Revenue',
                'credit' => 'Service Revenue'
            ],
            'depreciation' => [
                'description' => 'Monthly depreciation of office equipment',
                'debit' => 'Depreciation Expense',
                'credit' => 'Accumulated Depreciation'
            ],
            'inventory_adjustment' => [
                'description' => 'End-of-year stocktaking adjustment',
                'debit' => 'Cost of Goods Sold',
                'credit' => 'Inventory'
            ],
            'bad_debt_provision' => [
                'description' => 'Creating allowance for doubtful debts',
                'debit' => 'Bad Debt Expense',
                'credit' => 'Allowance for Doubtful Accounts'
            ]
        ];
    }

    public function getAdjustmentExample($type)
    {
        $examples = $this->getAdjustmentExamples();
        return $examples[$type] ?? null;
    }

    public function updatedAdjustmentType($value)
    {
        // Auto-fill description based on adjustment type
        $example = $this->getAdjustmentExample($value);
        if ($example && empty($this->description)) {
            $this->description = $example['description'];
        }
    }

    public function render()
    {
        $accounts = AccountsModel::where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();

        // Placeholder for adjustment history - you'll implement based on your adjustment model
        $adjustments = collect([]);

        // Calculate statistics
        $todayAdjustments = 0; // Implement based on your adjustment model
        $totalAdjustments = 0;
        $pendingApprovals = 0;
        $totalAmount = 0;

        return view('livewire.accounting.adjustments', [
            'accounts' => $accounts,
            'adjustments' => $adjustments,
            'adjustmentTypes' => $this->adjustmentTypes,
            'adjustmentPeriods' => $this->adjustmentPeriods,
            'todayAdjustments' => $todayAdjustments,
            'totalAdjustments' => $totalAdjustments,
            'pendingApprovals' => $pendingApprovals,
            'totalAmount' => $totalAmount,
        ]);
    }
}
