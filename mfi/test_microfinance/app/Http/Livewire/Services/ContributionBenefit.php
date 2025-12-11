<?php

namespace App\Http\Livewire\Services;

use App\Models\PayRolls;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class ContributionBenefit extends LivewireDatatable
{
    use WithPagination;
    public $search = ''; // For search functionality
    public $sortField = 'payroll_id'; // Default sorting field
    public $sortDirection = 'asc';


 public function builder()
    {

        return PayRolls::query()
           // ->with('employee') // Eager load the employee relationship if you have it
            ->when($this->search, function($query) {
                $query->where('employee_id', 'like', '%'.$this->search.'%')
                    ->orWhere('pay_period_start', 'like', '%'.$this->search.'%')
                    ->orWhere('pay_period_end', 'like', '%'.$this->search.'%')
                    ->orWhere('gross_salary', 'like', '%'.$this->search.'%')
                    ->orWhere('hours_worked', 'like', '%'.$this->search.'%')
                    ->orWhere('overtime_hours', 'like', '%'.$this->search.'%')
                    ->orWhere('tax_deductions', 'like', '%'.$this->search.'%')
                    ->orWhere('social_security', 'like', '%'.$this->search.'%')
                    ->orWhere('medicare', 'like', '%'.$this->search.'%')
                    ->orWhere('retirement_contributions', 'like', '%'.$this->search.'%')
                    ->orWhere('health_insurance', 'like', '%'.$this->search.'%')
                    ->orWhere('other_deductions', 'like', '%'.$this->search.'%')
                    ->orWhere('total_deductions', 'like', '%'.$this->search.'%')
                    ->orWhere('net_salary', 'like', '%'.$this->search.'%')
                    ->orWhere('payment_method', 'like', '%'.$this->search.'%')
                    ->orWhere('payment_date', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection);
            // ->paginate(10); // Adjust the number of items per page as needed
    }

    public function columns() {
        return [
            Column::name('payroll_id')
                ->label('Payroll ID')
                ->sortable()
                ->searchable(),

            Column::name('employee_id')
                ->label('Employee ID')
                ->sortable()
                ->searchable(),

            Column::name('pay_period_start')
                ->label('Pay Period Start')
                ->sortable()
                ->searchable(),

            Column::name('pay_period_end')
                ->label('Pay Period End')
                ->sortable()
                ->searchable(),

            Column::name('gross_salary')
                ->label('Gross Salary')
                ->sortable()
                ->searchable(),

            Column::name('hours_worked')
                ->label('Hours Worked')
                ->sortable()
                ->searchable(),

            Column::name('overtime_hours')
                ->label('Overtime Hours')
                ->sortable()
                ->searchable(),

            Column::name('tax_deductions')
                ->label('Tax Deductions')
                ->sortable()
                ->searchable(),

            Column::name('social_security')
                ->label('Social Security')
                ->sortable()
                ->searchable(),

            Column::name('medicare')
                ->label('Medicare')
                ->sortable()
                ->searchable(),

            Column::name('retirement_contributions')
                ->label('Retirement Contributions')
                ->sortable()
                ->searchable(),

            Column::name('health_insurance')
                ->label('Health Insurance')
                ->sortable()
                ->searchable(),

            Column::name('other_deductions')
                ->label('Other Deductions')
                ->sortable()
                ->searchable(),

            Column::name('total_deductions')
                ->label('Total Deductions')
                ->sortable()
                ->searchable(),

            Column::name('net_salary')
                ->label('Net Salary')
                ->sortable()
                ->searchable(),

            Column::name('payment_method')
                ->label('Payment Method')
                ->sortable()
                ->searchable(),

            Column::name('payment_date')
                ->label('Payment Date')
                ->sortable()
                ->searchable(),
        ];
    }
}
