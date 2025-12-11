<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetReport extends Model
{
    use HasFactory;
    
    protected $table = 'budget_reports';
    
    protected $fillable = [
        'report_name',
        'report_type',
        'parameters',
        'date_from',
        'date_to',
        'budget_id',
        'department_id',
        'data',
        'file_path',
        'format',
        'status',
        'generated_by',
        'generation_time',
        'accessed_count'
    ];
    
    protected $casts = [
        'parameters' => 'array',
        'data' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
        'generation_time' => 'integer',
        'accessed_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the budget
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }
    
    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(BudgetDepartment::class, 'department_id');
    }
    
    /**
     * Get the generator
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}