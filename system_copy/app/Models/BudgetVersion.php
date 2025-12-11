<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetVersion extends Model
{
    use HasFactory;
    
    protected $table = 'budget_versions';
    
    protected $fillable = [
        'budget_id',
        'version_number',
        'version_name',
        'version_type',
        'allocated_amount',
        'budget_data',
        'revision_reason',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];
    
    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'budget_data' => 'array',
        'approved_at' => 'datetime',
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
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Activate this version
     */
    public function activate()
    {
        // Deactivate other versions for this budget
        self::where('budget_id', $this->budget_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
        
        $this->is_active = true;
        $this->save();
        
        // Update the main budget with this version's values
        $budget = $this->budget;
        $budget->allocated_amount = $this->allocated_amount;
        $budget->revenue = $this->revenue;
        $budget->capital_expenditure = $this->capital_expenditure;
        $budget->save();
        
        return $this;
    }
    
    /**
     * Create a new version from the current budget state
     */
    public static function createFromBudget(BudgetManagement $budget, $data)
    {
        $latestVersion = self::where('budget_id', $budget->id)
            ->orderBy('version_number', 'desc')
            ->first();
        
        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;
        
        return self::create([
            'budget_id' => $budget->id,
            'version_number' => $newVersionNumber,
            'version_name' => $data['version_name'] ?? "Version {$newVersionNumber}",
            'version_type' => $data['version_type'] ?? 'REVISED',
            'allocated_amount' => $data['allocated_amount'] ?? $budget->allocated_amount,
            'revenue' => $data['revenue'] ?? $budget->revenue,
            'capital_expenditure' => $data['capital_expenditure'] ?? $budget->capital_expenditure,
            'spent_amount' => $budget->spent_amount,
            'committed_amount' => $budget->committed_amount,
            'available_amount' => $budget->available_amount,
            'revision_reason' => $data['revision_reason'] ?? null,
            'changes_summary' => $data['changes_summary'] ?? [],
            'is_active' => $data['make_active'] ?? false,
            'created_by' => auth()->id() ?? 1
        ]);
    }
}