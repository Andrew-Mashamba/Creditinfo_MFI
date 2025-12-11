<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayRolls extends Model
{
    use HasFactory;
    protected $table = 'pay_rolls';
    protected $guarded = [];
    
    protected $casts = [
        'payment_date' => 'datetime',
        'basic_salary' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
