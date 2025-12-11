<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ExpensesModel;
use App\Models\grants;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table='employees';

    public function benefits()
    {
        return $this->hasMany(ExpensesModel::class);
    }

    public function absences()
    {
        return $this->hasMany(ExpensesModel::class);
    }

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'employeeId', 'id');
    }

    /**
     * Get the roles that belong to the employee through the user.
     */
    public function roles()
    {
        return $this->user->roles();
    }
    
    /**
     * Get the department that the employee belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
    /**
     * Get the attendance records for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'employee_id');
    }
    
    /**
     * Get the payroll records for the employee.
     */
    public function payrolls()
    {
        return $this->hasMany(PayRolls::class, 'employee_id');
    }
    
    /**
     * Get the branch that the employee belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
    /**
     * Get the loans supervised by this employee.
     */
    public function loans()
    {
        return $this->hasMany(LoansModel::class, 'supervisor_id', 'id');
    }

    /**
     * Get the reporting manager for the employee.
     */
    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }
}
