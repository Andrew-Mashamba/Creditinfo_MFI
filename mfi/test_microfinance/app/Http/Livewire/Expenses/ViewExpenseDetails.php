<?php

namespace App\Http\Livewire\Expenses;

use Livewire\Component;
use App\Models\Expense;

class ViewExpenseDetails extends Component
{
    public $showModal = false;
    public $expense = null;
    
    protected $listeners = ['viewExpenseDetails' => 'loadExpense'];
    
    public function loadExpense($id)
    {
        $this->expense = Expense::with([
            'account',
            'user',
            'paidByUser',
            'paymentTransaction',
            'budgetAllocation',
            'approval'
        ])->find($id);
        
        if ($this->expense) {
            $this->showModal = true;
        }
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->expense = null;
    }
    
    public function render()
    {
        return view('livewire.expenses.view-expense-details');
    }
}