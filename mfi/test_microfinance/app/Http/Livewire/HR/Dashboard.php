<?php

namespace App\Http\Livewire\HR;

use App\Models\Employee;
use App\Models\Department;
use App\Models\PayRolls as PayRoll;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use App\Traits\Livewire\WithModulePermissions;

class Dashboard extends Component
{
    use WithModulePermissions;
    public $menuNumber = 0; // 0=dashboard, 1=employees, 2=payroll, 3=leave, 4=attendance, 5=requests
    public $totalEmployees;
    public $totalDepartments;
    public $activeEmployees;
    public $inactiveEmployees;
    public $suspendedEmployees;
    public $pendingPayroll;
    public $departmentStats;
    public $monthlyPayrollTotal;
    public $recentHires;
    public $employmentTypeStats;
    public $genderStats;
    public $averageSalary;
    public $totalSalaryExpense;
    public $recentExits;
    public $topDepartments;

    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
        $this->loadDashboardData();
    }

    public function setMenuNumber($number)
    {
        // Check permissions based on the menu being accessed
        $permissionMap = [
            0 => 'canView',         // Dashboard Overview
            1 => 'canEmployees',    // Employee Management
            2 => 'canPayroll',      // Payroll Management
            3 => 'canLeave',        // Leave Management
            4 => 'canAttendance',   // Attendance Tracking
            5 => 'canRequests'      // Request Management
        ];
        
        $requiredPermission = $permissionMap[$number] ?? 'canView';
        
        if (!($this->permissions[$requiredPermission] ?? false)) {
            session()->flash('error', 'You do not have permission to access this HR section');
            return;
        }
        
        $this->menuNumber = $number;
    }

    protected function loadDashboardData()
    {
        // Employee counts by status
        $this->totalEmployees = Employee::count();
        $this->activeEmployees = Employee::where('employee_status', 'ACTIVE')->count();
        $this->inactiveEmployees = Employee::where('employee_status', 'INACTIVE')->count();
        $this->suspendedEmployees = Employee::where('employee_status', 'SUSPENDED')->count();
        $this->totalDepartments = Department::count();

        // Get current month payroll stats
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $this->pendingPayroll = PayRoll::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->where('status', 'pending')
            ->count();

        $this->monthlyPayrollTotal = PayRoll::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_salary');

        // Recent hires (last 30 days)
        $this->recentHires = Employee::where('hire_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('hire_date', 'desc')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'job_title', 'hire_date', 'department_id'])
            ->map(function($employee) {
                return [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'job_title' => $employee->job_title,
                    'hire_date' => $employee->hire_date,
                    'department' => $employee->department->department_name ?? 'N/A'
                ];
            })->toArray();

        // Recent exits (last 60 days) - check notes for exit details
        $this->recentExits = Employee::where('employee_status', 'INACTIVE')
            ->where('updated_at', '>=', Carbon::now()->subDays(60))
            ->whereNotNull('notes')
            ->where('notes', 'like', '%EXIT DETAILS%')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'job_title', 'updated_at', 'notes'])
            ->map(function($employee) {
                // Extract exit date from notes
                preg_match('/Exit Date: (\d{4}-\d{2}-\d{2})/', $employee->notes, $matches);
                $exitDate = $matches[1] ?? $employee->updated_at->format('Y-m-d');

                // Extract exit type
                preg_match('/Exit Type: ([^\n]+)/', $employee->notes, $typeMatches);
                $exitType = $typeMatches[1] ?? 'Unknown';

                return [
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'job_title' => $employee->job_title,
                    'exit_date' => $exitDate,
                    'exit_type' => $exitType
                ];
            })->toArray();

        // Employment type statistics
        $employmentTypes = Employee::select('employment_type', DB::raw('count(*) as count'))
            ->groupBy('employment_type')
            ->get();

        $this->employmentTypeStats = $employmentTypes->map(function($type) {
            return [
                'type' => ucwords(str_replace('-', ' ', $type->employment_type ?? 'Unknown')),
                'count' => $type->count,
                'percentage' => $this->totalEmployees > 0 ? round(($type->count / $this->totalEmployees) * 100, 1) : 0
            ];
        })->toArray();

        // Gender statistics
        $genderDistribution = Employee::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get();

        $this->genderStats = $genderDistribution->map(function($gender) {
            return [
                'gender' => ucfirst($gender->gender ?? 'Not Specified'),
                'count' => $gender->count,
                'percentage' => $this->totalEmployees > 0 ? round(($gender->count / $this->totalEmployees) * 100, 1) : 0
            ];
        })->toArray();

        // Salary statistics
        $salaryStats = Employee::where('employee_status', 'ACTIVE')
            ->selectRaw('AVG(basic_salary) as avg_salary, SUM(basic_salary) as total_salary')
            ->first();

        $this->averageSalary = $salaryStats->avg_salary ?? 0;
        $this->totalSalaryExpense = $salaryStats->total_salary ?? 0;

        // Top 5 departments by employee count
        $topDepts = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.department_name', DB::raw('count(employees.id) as employee_count'))
            ->whereNotNull('departments.id')
            ->groupBy('departments.id', 'departments.department_name')
            ->orderBy('employee_count', 'desc')
            ->limit(5)
            ->get();

        $maxCount = $topDepts->max('employee_count') ?? 1;

        $this->topDepartments = $topDepts->map(function ($dept) use ($maxCount) {
            return [
                'name' => $dept->department_name ?? 'Unassigned',
                'count' => $dept->employee_count,
                'percentage' => $maxCount > 0 ? round(($dept->employee_count / $maxCount) * 100, 1) : 0
            ];
        })->toArray();

        // Get all department statistics
        $departmentStats = DB::table('employees')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.department_name', DB::raw('count(employees.id) as employee_count'))
            ->whereNotNull('departments.id')
            ->groupBy('departments.id', 'departments.department_name')
            ->get();

        $this->departmentStats = $departmentStats->map(function ($dept) {
            return [
                'name' => $dept->department_name ?? 'Unassigned',
                'count' => $dept->employee_count
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.h-r.dashboard', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }

    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    protected function getModuleName(): string
    {
        return 'hr';
    }
} 