<?php

namespace App\Http\Livewire\Expenses;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Expense;
use Illuminate\Support\Facades\Storage;

class ExpensesList extends Component
{
    use WithFileUploads;

    public $expenses;
    public $retirement_receipt;
    public $selected_expense_id;

    public function mount()
    {
        $this->loadExpenses();
    }

    public function loadExpenses()
    {
        $this->expenses = Expense::where('user_id', auth()->id())
            ->where('status', 'APPROVED')
            ->get();
    }

    public function openRetirementUpload($expenseId)
    {
        $this->selected_expense_id = $expenseId;
        $this->reset('retirement_receipt');
    }

    public function uploadRetirementReceipt()
    {
        $this->validate([
            'retirement_receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240' // 10MB max
        ]);

        $expense = Expense::findOrFail($this->selected_expense_id);

        // Generate a unique filename
        $filename = 'retirement_receipts/' . uniqid() . '.' . $this->retirement_receipt->getClientOriginalExtension();

        // Store the file
        $path = $this->retirement_receipt->storeAs('public', $filename);

        // Update the expense with the receipt path
        $expense->update([
            'retirement_receipt_path' => $filename,
            'status' => 'RETIRED'
        ]);

        // Reset form
        $this->reset(['selected_expense_id', 'retirement_receipt']);
        $this->loadExpenses();

        session()->flash('success', 'Retirement receipt uploaded successfully.');
    }

    public function render()
    {
        return view('livewire.expenses.expenses-list');
    }
} 