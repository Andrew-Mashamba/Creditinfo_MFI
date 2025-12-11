<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMatrixConfig extends Model
{
    protected $fillable = [
        'process_type',
        'process_name',
        'process_code',
        'level',
        'approver_role',
        'approver_sub_role',
        'min_amount',
        'max_amount',
        'is_active',
        'additional_conditions'
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'additional_conditions' => 'array'
    ];

    public function getApproversForProcess($processType, $amount = null)
    {
        $query = $this->where('process_type', $processType)
                     ->where('is_active', true)
                     ->orderBy('level');

        if ($amount !== null) {
            $query->where(function($q) use ($amount) {
                $q->whereNull('min_amount')
                  ->orWhere('min_amount', '<=', $amount);
            })
            ->where(function($q) use ($amount) {
                $q->whereNull('max_amount')
                  ->orWhere('max_amount', '>=', $amount);
            });
        }

        return $query->get();
    }
} 