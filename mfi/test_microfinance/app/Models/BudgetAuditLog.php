<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetAuditLog extends Model
{
    use HasFactory;
    
    protected $table = 'budget_audit_logs';
    
    protected $fillable = [
        'budget_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
        'notes'
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
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
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Log a budget action
     */
    public static function logAction($budgetId, $action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null, $notes = null)
    {
        return self::create([
            'budget_id' => $budgetId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $notes
        ]);
    }
}