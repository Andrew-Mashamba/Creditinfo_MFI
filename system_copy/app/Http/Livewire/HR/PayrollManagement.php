<?php

namespace App\Http\Livewire\HR;

use App\Models\Employee;
use App\Models\PayRolls as PayRoll;
use App\Services\PayrollBudgetCheckingService;
use App\Services\PayrollApprovalService;
use App\Services\PayrollPaymentService;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class PayrollManagement extends Component
{
    use WithPagination;

    public $month;
    public $year;
    public $showPayslipModal = false;
    public $selectedEmployee = null;
    public $search = '';
    public $budgetCheckResult = null;
    public $approvalStatus = null;
    public $showBudgetModal = false;
    public $rejectionReason = '';
    public $allPayrollsPaid = false;

    protected $budgetCheckingService;
    protected $approvalService;
    protected $paymentService;

    public function boot()
    {
        $this->budgetCheckingService = new PayrollBudgetCheckingService();
        $this->approvalService = new PayrollApprovalService();
        $this->paymentService = new PayrollPaymentService();
    }

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
        $this->loadBudgetCheck();
        $this->loadApprovalStatus();
        $this->checkPayrollPaymentStatus();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['month', 'year'])) {
            $this->loadBudgetCheck();
            $this->loadApprovalStatus();
            $this->checkPayrollPaymentStatus();
        }
    }

    private function loadBudgetCheck()
    {
        $this->budgetCheckResult = $this->budgetCheckingService->checkPayrollBudget($this->month, $this->year);
    }

    private function loadApprovalStatus()
    {
        $this->approvalStatus = $this->approvalService->getPayrollApprovalStatus($this->month, $this->year);
    }

    private function checkPayrollPaymentStatus()
    {
        // Check if there are any payrolls for this month/year
        $totalPayrolls = PayRoll::where('month', $this->month)
            ->where('year', $this->year)
            ->count();

        if ($totalPayrolls === 0) {
            $this->allPayrollsPaid = false;
            return;
        }

        // Check if all payrolls are paid
        $paidPayrolls = PayRoll::where('month', $this->month)
            ->where('year', $this->year)
            ->where('status', 'paid')
            ->count();

        $this->allPayrollsPaid = ($paidPayrolls === $totalPayrolls && $totalPayrolls > 0);
    }

    public function generatePayroll()
    {
        $employees = Employee::where('employee_status', 'active')->get();
        $generated = 0;

        foreach ($employees as $employee) {
            // Check if payroll already exists for this month
            $exists = PayRoll::where('employee_id', $employee->id)
                ->where('month', $this->month)
                ->where('year', $this->year)
                ->exists();

            if (!$exists) {
                $this->createPayrollEntry($employee);
                $generated++;
            }
        }

        // Reset payment status flag since new payrolls are generated
        $this->checkPayrollPaymentStatus();

        session()->flash('success', "Payroll generated for {$generated} employees!");
    }

    private function createPayrollEntry($employee)
    {
        $basicSalary = $employee->basic_salary ?? 0;
        
        // Calculate deductions
        $paye = $this->calculatePAYE($basicSalary);
        $nssf = $basicSalary * 0.10; // 10% NSSF
        $nhif = $this->calculateNHIF($basicSalary);
        
        // Calculate allowances (can be customized)
        $houseAllowance = $basicSalary * 0.15;
        $transportAllowance = $basicSalary * 0.10;
        
        $grossSalary = $basicSalary + $houseAllowance + $transportAllowance;
        
        // Calculate pay period dates
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        PayRoll::create([
            'employee_id' => $employee->id,
            'month' => $this->month,
            'year' => $this->year,
            'pay_period_start' => $startDate,
            'pay_period_end' => $endDate,
            'basic_salary' => $basicSalary,
            'house_allowance' => $houseAllowance,
            'transport_allowance' => $transportAllowance,
            'gross_salary' => $grossSalary,
            'paye' => $paye,
            'nssf' => $nssf,
            'nhif' => $nhif,
            'tax_deductions' => $paye, // Map PAYE to tax_deductions
            'social_security' => $nssf, // Map NSSF to social_security
            'health_insurance' => $nhif, // Map NHIF to health_insurance
            // total_deductions and net_salary are generated columns - don't insert
            'status' => 'pending',
            'payment_date' => Carbon::create($this->year, $this->month, 25)
        ]);
    }

    private function calculatePAYE($salary)
    {
        // Simplified PAYE calculation (customize based on your tax laws)
        if ($salary <= 270000) {
            return 0;
        } elseif ($salary <= 520000) {
            return ($salary - 270000) * 0.08;
        } elseif ($salary <= 760000) {
            return 20000 + (($salary - 520000) * 0.20);
        } elseif ($salary <= 1000000) {
            return 68000 + (($salary - 760000) * 0.25);
        } else {
            return 128000 + (($salary - 1000000) * 0.30);
        }
    }

    private function calculateNHIF($salary)
    {
        // Simplified NHIF calculation
        if ($salary <= 5999) return 150;
        if ($salary <= 7999) return 300;
        if ($salary <= 11999) return 400;
        if ($salary <= 14999) return 500;
        if ($salary <= 19999) return 600;
        if ($salary <= 24999) return 750;
        if ($salary <= 29999) return 850;
        if ($salary <= 34999) return 900;
        if ($salary <= 39999) return 950;
        if ($salary <= 44999) return 1000;
        if ($salary <= 49999) return 1100;
        if ($salary <= 59999) return 1200;
        if ($salary <= 69999) return 1300;
        if ($salary <= 79999) return 1400;
        if ($salary <= 89999) return 1500;
        if ($salary <= 99999) return 1600;
        return 1700;
    }

    /**
     * Submit payroll for approval (entire month)
     */
    public function submitForApproval()
    {
        $result = $this->approvalService->submitPayrollForApproval($this->month, $this->year);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->loadApprovalStatus();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Approve payroll (Finance/Management)
     */
    public function approvePayrollBatch()
    {
        $processId = $this->month . '-' . $this->year;
        $result = $this->approvalService->approvePayroll($processId);

        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->loadApprovalStatus();
            $this->loadBudgetCheck();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Reject payroll
     */
    public function rejectPayrollBatch()
    {
        if (empty($this->rejectionReason)) {
            session()->flash('error', 'Please provide a rejection reason');
            return;
        }

        $processId = $this->month . '-' . $this->year;
        $result = $this->approvalService->rejectPayroll($processId, $this->rejectionReason);

        if ($result['success']) {
            session()->flash('success', 'Payroll rejected');
            $this->rejectionReason = '';
            $this->loadApprovalStatus();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Process payments for entire month
     */
    public function processMonthlyPayments()
    {
        $result = $this->paymentService->processMonthlyPayroll($this->month, $this->year);

        if ($result['success']) {
            session()->flash('success', $result['message'] . ' Total: TZS ' . number_format($result['results']['total_amount'], 2));
            // Update payment status flag after successful payment
            $this->checkPayrollPaymentStatus();
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * Process single payroll payment
     */
    public function processPayment($id)
    {
        $result = $this->paymentService->processPayrollPayment($id);

        if ($result['success']) {
            session()->flash('success', 'Payment processed successfully! Ref: ' . $result['reference_number']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    /**
     * View budget details
     */
    public function viewBudgetDetails()
    {
        $this->showBudgetModal = true;
    }

    public function viewPayslip($id)
    {
        $this->selectedEmployee = PayRoll::with('employee')->find($id);
        $this->showPayslipModal = true;
    }

    public function render()
    {
        $payrolls = PayRoll::with('employee')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($this->search, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate(10);
            
        return view('livewire.h-r.payroll-management', [
            'payrolls' => $payrolls
        ]);
    }
}