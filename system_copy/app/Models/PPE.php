<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PPE extends Model
{
    use HasFactory;
    
    protected $table = 'ppes';
    
    protected $fillable = [
        'name', 'category', 'purchase_price', 'purchase_date', 'salvage_value',
        'useful_life', 'quantity', 'initial_value', 'depreciation_rate',
        'accumulated_depreciation', 'depreciation_for_year', 'depreciation_for_month',
        'closing_value', 'status', 'location', 'notes', 'account_number', 'depreciation_account_number',
        // Additional costs for proper capitalization
        'legal_fees', 'registration_fees', 'renovation_costs', 'transportation_costs',
        'installation_costs', 'other_costs',
        // Payment method and related accounts
        'payment_method', 'payment_account_number', 'payable_account_number',
        // Accounting transaction tracking
        'accounting_transaction_id', 'accounting_entry_created',
        // Additional useful fields
        'supplier_name', 'invoice_number', 'invoice_date', 'additional_notes',
        // Disposal tracking fields
        'disposal_date', 'disposal_method', 'disposal_proceeds', 'disposal_notes',
        // Approval workflow fields
        'disposal_approval_status', 'disposal_approved_by', 'disposal_approved_at', 'disposal_rejection_reason',
        // New enhanced fields
        'asset_code', 'barcode', 'serial_number', 'manufacturer', 'model',
        'depreciation_method', 'units_produced', 'total_units_expected',
        'condition', 'market_value', 'replacement_cost', 'last_valuation_date', 'valuation_by',
        'warranty_start_date', 'warranty_end_date', 'warranty_provider', 'warranty_terms',
        'department_id', 'custodian_id', 'assigned_to',
        'last_maintenance_date', 'next_maintenance_date', 'last_inspection_date', 'next_inspection_date',
        'usage_hours', 'mileage', 'usage_cycles',
        'parent_asset_id', 'is_component',
        'maintenance_cost_to_date', 'expected_annual_maintenance'
    ];
    
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'initial_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'depreciation_for_year' => 'decimal:2',
        'depreciation_for_month' => 'decimal:2',
        'closing_value' => 'decimal:2',
        // Additional costs
        'legal_fees' => 'decimal:2',
        'registration_fees' => 'decimal:2',
        'renovation_costs' => 'decimal:2',
        'transportation_costs' => 'decimal:2',
        'installation_costs' => 'decimal:2',
        'other_costs' => 'decimal:2',
        // Disposal fields
        'disposal_proceeds' => 'decimal:2',
        // Dates
        'purchase_date' => 'date',
        'invoice_date' => 'date',
        'disposal_date' => 'date',
        'disposal_approved_at' => 'datetime',
        // Integers
        'useful_life' => 'integer',
        'quantity' => 'integer',
        'disposal_approved_by' => 'integer',
        // Booleans
        'accounting_entry_created' => 'boolean'
    ];

    /**
     * Calculate the total capitalized cost including all additional costs
     */
    public function getTotalCapitalizedCostAttribute()
    {
        return $this->purchase_price + 
               $this->legal_fees + 
               $this->registration_fees + 
               $this->renovation_costs + 
               $this->transportation_costs + 
               $this->installation_costs + 
               $this->other_costs;
    }

    /**
     * Get the accounting entry description based on payment method
     */
    public function getAccountingEntryDescriptionAttribute()
    {
        $description = "PPE Purchase: {$this->name}";
        
        if ($this->payment_method === 'cash') {
            return $description . " (Cash Payment)";
        } elseif ($this->payment_method === 'credit') {
            return $description . " (Credit Purchase)";
        } elseif ($this->payment_method === 'loan') {
            return $description . " (Loan Financed)";
        } elseif ($this->payment_method === 'lease') {
            return $description . " (Lease Agreement)";
        }
        
        return $description;
    }

    /**
     * Check if accounting entry needs to be created
     */
    public function needsAccountingEntry()
    {
        return !$this->accounting_entry_created && $this->total_capitalized_cost > 0;
    }

    /**
     * Get the disposal approval record
     */
    public function disposalApproval()
    {
        return $this->hasOne(\App\Models\approvals::class, 'process_id', 'id')
                    ->where('process_code', 'ASSET_DISP');
    }

    /**
     * Get the user who approved the disposal
     */
    public function disposalApprovedBy()
    {
        return $this->belongsTo(User::class, 'disposal_approved_by');
    }

    /**
     * Check if disposal is pending approval
     */
    public function isDisposalPendingApproval()
    {
        return $this->status === 'pending_disposal' && $this->disposal_approval_status === 'pending';
    }

    /**
     * Check if disposal is approved for disposal
     */
    public function isApprovedForDisposal()
    {
        return $this->status === 'approved_for_disposal';
    }

    /**
     * Check if disposal is approved
     */
    public function isDisposalApproved()
    {
        return $this->disposal_approval_status === 'approved';
    }

    /**
     * Check if disposal is rejected
     */
    public function isDisposalRejected()
    {
        return $this->disposal_approval_status === 'rejected';
    }

    /**
     * Calculate gain or loss on disposal
     */
    public function getDisposalGainLossAttribute()
    {
        if (!$this->disposal_proceeds || !$this->closing_value) {
            return 0;
        }
        
        return $this->disposal_proceeds - $this->closing_value;
    }

    /**
     * Check if disposal resulted in a gain
     */
    public function hasDisposalGain()
    {
        return $this->disposal_gain_loss > 0;
    }

    /**
     * Check if disposal resulted in a loss
     */
    public function hasDisposalLoss()
    {
        return $this->disposal_gain_loss < 0;
    }

    /**
     * Get disposal method display name
     */
    public function getDisposalMethodDisplayAttribute()
    {
        $methods = [
            'sold' => 'Sold',
            'scrapped' => 'Scrapped',
            'donated' => 'Donated',
            'lost' => 'Lost',
            'stolen' => 'Stolen',
            'other' => 'Other'
        ];
        
        return $methods[$this->disposal_method] ?? $this->disposal_method;
    }

    // New Relationships
    
    /**
     * Get maintenance records for this asset
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(PpeMaintenanceRecord::class, 'ppe_id');
    }
    
    /**
     * Get transfer history for this asset
     */
    public function transfers()
    {
        return $this->hasMany(PpeTransfer::class, 'ppe_id');
    }
    
    /**
     * Get revaluation history for this asset
     */
    public function revaluations()
    {
        return $this->hasMany(PpeRevaluation::class, 'ppe_id');
    }
    
    /**
     * Get insurance policies for this asset
     */
    public function insurancePolicies()
    {
        return $this->hasMany(PpeInsurance::class, 'ppe_id');
    }
    
    /**
     * Get audit trail for this asset
     */
    public function auditTrails()
    {
        return $this->hasMany(PpeAuditTrail::class, 'ppe_id');
    }
    
    /**
     * Get documents for this asset
     */
    public function documents()
    {
        return $this->hasMany(PpeDocument::class, 'ppe_id');
    }
    
    /**
     * Get depreciation schedule for this asset
     */
    public function depreciationSchedule()
    {
        return $this->hasMany(PpeDepreciationSchedule::class, 'ppe_id');
    }
    
    /**
     * Get parent asset if this is a component
     */
    public function parentAsset()
    {
        return $this->belongsTo(PPE::class, 'parent_asset_id');
    }
    
    /**
     * Get component assets
     */
    public function components()
    {
        return $this->hasMany(PPE::class, 'parent_asset_id');
    }
    
    /**
     * Get department
     */
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }
    
    /**
     * Get custodian user
     */
    public function custodian()
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }
    
    /**
     * Get assigned user
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    // New Methods
    
    /**
     * Check if asset is under warranty
     */
    public function isUnderWarranty()
    {
        return $this->warranty_end_date && $this->warranty_end_date >= now();
    }
    
    /**
     * Get warranty status
     */
    public function getWarrantyStatusAttribute()
    {
        if (!$this->warranty_end_date) {
            return 'No Warranty';
        }
        
        if ($this->warranty_end_date >= now()) {
            $daysRemaining = now()->diffInDays($this->warranty_end_date);
            return "Active ({$daysRemaining} days remaining)";
        }
        
        return 'Expired';
    }
    
    /**
     * Check if maintenance is due
     */
    public function isMaintenanceDue()
    {
        return $this->next_maintenance_date && $this->next_maintenance_date <= now();
    }
    
    /**
     * Check if inspection is due
     */
    public function isInspectionDue()
    {
        return $this->next_inspection_date && $this->next_inspection_date <= now();
    }
    
    /**
     * Get active insurance policy
     */
    public function getActiveInsuranceAttribute()
    {
        return $this->insurancePolicies()->active()->first();
    }
    
    /**
     * Check if asset is insured
     */
    public function isInsured()
    {
        return $this->insurancePolicies()->active()->exists();
    }
    
    /**
     * Calculate depreciation based on method
     */
    public function calculateDepreciation($period = 'monthly')
    {
        $depreciableAmount = $this->total_capitalized_cost - $this->salvage_value;
        
        switch ($this->depreciation_method) {
            case 'straight_line':
                $yearlyDepreciation = $depreciableAmount / $this->useful_life;
                return $period === 'monthly' ? $yearlyDepreciation / 12 : $yearlyDepreciation;
                
            case 'declining_balance':
                $rate = (2 / $this->useful_life) * 100;
                $currentValue = $this->closing_value ?? $this->initial_value;
                $yearlyDepreciation = $currentValue * ($rate / 100);
                return $period === 'monthly' ? $yearlyDepreciation / 12 : $yearlyDepreciation;
                
            case 'units_of_production':
                if ($this->total_units_expected > 0) {
                    $depreciationPerUnit = $depreciableAmount / $this->total_units_expected;
                    return $depreciationPerUnit * ($this->units_produced ?? 0);
                }
                return 0;
                
            case 'sum_of_years':
                $yearsSum = ($this->useful_life * ($this->useful_life + 1)) / 2;
                $currentYear = now()->year - \Carbon\Carbon::parse($this->purchase_date)->year + 1;
                $remainingLife = max($this->useful_life - $currentYear + 1, 0);
                $yearlyDepreciation = ($remainingLife / $yearsSum) * $depreciableAmount;
                return $period === 'monthly' ? $yearlyDepreciation / 12 : $yearlyDepreciation;
                
            default:
                return $this->depreciation_for_month ?? 0;
        }
    }
    
    /**
     * Get asset age in years
     */
    public function getAgeInYearsAttribute()
    {
        if (!$this->purchase_date) {
            return 0;
        }
        
        return \Carbon\Carbon::parse($this->purchase_date)->diffInYears(now());
    }
    
    /**
     * Get remaining useful life in years
     */
    public function getRemainingUsefulLifeAttribute()
    {
        return max($this->useful_life - $this->age_in_years, 0);
    }
    
    /**
     * Get asset utilization percentage
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->total_units_expected > 0) {
            return round(($this->units_produced / $this->total_units_expected) * 100, 2);
        }
        
        if ($this->useful_life > 0) {
            return round(($this->age_in_years / $this->useful_life) * 100, 2);
        }
        
        return 0;
    }
    
    /**
     * Generate unique asset code
     */
    public static function generateAssetCode($category = 'PPE')
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $year = date('Y');
        $lastAsset = self::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->first();
        
        $sequence = ($lastAsset && $lastAsset->asset_code) ? (intval(substr($lastAsset->asset_code, -4)) + 1) : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $year, $sequence);
    }
    
    /**
     * Get condition badge color
     */
    public function getConditionColorAttribute()
    {
        $colors = [
            'excellent' => 'green',
            'good' => 'blue',
            'fair' => 'yellow',
            'poor' => 'orange',
            'needs_repair' => 'red'
        ];
        
        return $colors[$this->condition] ?? 'gray';
    }
    
    /**
     * Scope for assets needing maintenance
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now())
                    ->orWhere('condition', 'needs_repair');
    }
    
    /**
     * Scope for assets needing inspection
     */
    public function scopeNeedsInspection($query)
    {
        return $query->where('next_inspection_date', '<=', now());
    }
    
    /**
     * Scope for assets with expiring warranty
     */
    public function scopeExpiringWarranty($query, $days = 30)
    {
        return $query->whereNotNull('warranty_end_date')
                    ->whereBetween('warranty_end_date', [now(), now()->addDays($days)]);
    }
}
