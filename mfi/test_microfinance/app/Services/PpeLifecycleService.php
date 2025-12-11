<?php

namespace App\Services;

use App\Models\PPE;
use App\Models\PpeMaintenanceRecord;
use App\Models\PpeTransfer;
use App\Models\PpeRevaluation;
use App\Models\PpeInsurance;
use App\Models\PpeAuditTrail;
use App\Models\PpeDepreciationSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PpeLifecycleService
{
    /**
     * Create a new PPE asset with complete lifecycle setup
     */
    public function createAsset(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Generate unique asset code
            $data['asset_code'] = PPE::generateAssetCode($data['category'] ?? 'PPE');
            
            // Set default values for lifecycle fields
            $data['condition'] = $data['condition'] ?? 'excellent';
            $data['depreciation_method'] = $data['depreciation_method'] ?? 'straight_line';
            
            // Create the PPE asset
            $ppe = PPE::create($data);
            
            // Generate depreciation schedule
            $this->generateDepreciationSchedule($ppe);
            
            // Create initial audit trail entry
            $this->createAuditTrail($ppe, 'created', null, $data);
            
            // Schedule initial maintenance if applicable
            if (isset($data['maintenance_interval_months'])) {
                $this->scheduleNextMaintenance($ppe, $data['maintenance_interval_months']);
            }
            
            // Create insurance record if provided
            if (isset($data['insurance']) && is_array($data['insurance'])) {
                $this->createInsurancePolicy($ppe, $data['insurance']);
            }
            
            return $ppe;
        });
    }
    
    /**
     * Generate depreciation schedule for an asset
     */
    public function generateDepreciationSchedule(PPE $ppe)
    {
        $schedules = [];
        $currentValue = $ppe->initial_value;
        $accumulatedDepreciation = 0;
        
        for ($year = 1; $year <= $ppe->useful_life; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $depreciationAmount = $ppe->calculateDepreciation('monthly');
                $accumulatedDepreciation += $depreciationAmount;
                $closingValue = $currentValue - $depreciationAmount;
                
                // Don't depreciate below salvage value
                if ($closingValue < $ppe->salvage_value) {
                    $depreciationAmount = $currentValue - $ppe->salvage_value;
                    $closingValue = $ppe->salvage_value;
                }
                
                $schedules[] = [
                    'ppe_id' => $ppe->id,
                    'period_year' => Carbon::parse($ppe->purchase_date)->addYears($year - 1)->year,
                    'period_month' => $month,
                    'opening_value' => $currentValue,
                    'depreciation_amount' => $depreciationAmount,
                    'closing_value' => $closingValue,
                    'accumulated_depreciation' => $accumulatedDepreciation,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $currentValue = $closingValue;
                
                // Stop if we've reached salvage value
                if ($closingValue <= $ppe->salvage_value) {
                    break 2;
                }
            }
        }
        
        // Bulk insert depreciation schedules
        PpeDepreciationSchedule::insert($schedules);
        
        return $schedules;
    }
    
    /**
     * Schedule maintenance for an asset
     */
    public function scheduleMaintenance(PPE $ppe, array $data)
    {
        $maintenance = PpeMaintenanceRecord::create([
            'ppe_id' => $ppe->id,
            'maintenance_type' => $data['maintenance_type'] ?? 'preventive',
            'maintenance_date' => $data['maintenance_date'],
            'performed_by' => $data['performed_by'] ?? 'TBD',
            'description' => $data['description'],
            'status' => 'scheduled',
            'next_maintenance_date' => $data['next_maintenance_date'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
        
        // Update PPE next maintenance date
        $ppe->update([
            'next_maintenance_date' => $data['maintenance_date']
        ]);
        
        $this->createAuditTrail($ppe, 'maintenance_scheduled', null, $data);
        
        return $maintenance;
    }
    
    /**
     * Complete a maintenance record
     */
    public function completeMaintenance(PpeMaintenanceRecord $maintenance, array $data)
    {
        $maintenance->update([
            'status' => 'completed',
            'vendor_name' => $data['vendor_name'] ?? null,
            'parts_replaced' => $data['parts_replaced'] ?? null,
            'cost' => $data['cost'] ?? 0,
            'downtime_hours' => $data['downtime_hours'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'work_order_number' => $data['work_order_number'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null
        ]);
        
        // Update PPE maintenance cost and dates
        $ppe = $maintenance->ppe;
        $ppe->update([
            'maintenance_cost_to_date' => $ppe->maintenance_cost_to_date + ($data['cost'] ?? 0),
            'last_maintenance_date' => $maintenance->maintenance_date,
            'next_maintenance_date' => $maintenance->next_maintenance_date
        ]);
        
        // Update condition if provided
        if (isset($data['new_condition'])) {
            $ppe->update(['condition' => $data['new_condition']]);
        }
        
        $this->createAuditTrail($ppe, 'maintenance_completed', 
            ['maintenance_id' => $maintenance->id], $data);
        
        return $maintenance;
    }
    
    /**
     * Transfer asset to new location/custodian
     */
    public function transferAsset(PPE $ppe, array $data)
    {
        $transfer = PpeTransfer::create([
            'ppe_id' => $ppe->id,
            'from_location' => $ppe->location,
            'to_location' => $data['to_location'],
            'from_department_id' => $ppe->department_id,
            'to_department_id' => $data['to_department_id'] ?? null,
            'from_custodian_id' => $ppe->custodian_id,
            'to_custodian_id' => $data['to_custodian_id'] ?? null,
            'transfer_date' => $data['transfer_date'] ?? now(),
            'reason' => $data['reason'],
            'status' => $data['requires_approval'] ?? false ? 'pending' : 'completed',
            'notes' => $data['notes'] ?? null,
            'transfer_document_number' => $data['document_number'] ?? null
        ]);
        
        // If auto-approved or doesn't require approval, update asset immediately
        if ($transfer->status === 'completed') {
            $this->completeTransfer($transfer);
        }
        
        $this->createAuditTrail($ppe, 'transfer_initiated', 
            ['from_location' => $ppe->location], 
            ['to_location' => $data['to_location']]);
        
        return $transfer;
    }
    
    /**
     * Complete an asset transfer
     */
    public function completeTransfer(PpeTransfer $transfer)
    {
        $transfer->update(['status' => 'completed']);
        
        $transfer->ppe->update([
            'location' => $transfer->to_location,
            'department_id' => $transfer->to_department_id,
            'custodian_id' => $transfer->to_custodian_id
        ]);
        
        $this->createAuditTrail($transfer->ppe, 'transfer_completed', 
            ['transfer_id' => $transfer->id], 
            ['new_location' => $transfer->to_location]);
    }
    
    /**
     * Revalue an asset
     */
    public function revalueAsset(PPE $ppe, array $data)
    {
        $oldValue = $ppe->closing_value;
        $newValue = $data['new_value'];
        $revaluationAmount = $newValue - $oldValue;
        
        $revaluation = PpeRevaluation::create([
            'ppe_id' => $ppe->id,
            'revaluation_date' => $data['revaluation_date'] ?? now(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'revaluation_amount' => $revaluationAmount,
            'revaluation_type' => $revaluationAmount > 0 ? 'appreciation' : 'impairment',
            'performed_by' => $data['performed_by'],
            'reason' => $data['reason'],
            'supporting_documents' => $data['supporting_documents'] ?? null,
            'valuation_method' => $data['valuation_method'] ?? null,
            'status' => $data['requires_approval'] ?? true ? 'pending' : 'approved'
        ]);
        
        // If auto-approved, post immediately
        if ($revaluation->status === 'approved') {
            $this->postRevaluation($revaluation);
        }
        
        $this->createAuditTrail($ppe, 'revaluation_initiated', 
            ['old_value' => $oldValue], 
            ['new_value' => $newValue]);
        
        return $revaluation;
    }
    
    /**
     * Post revaluation to general ledger
     */
    public function postRevaluation(PpeRevaluation $revaluation)
    {
        if ($revaluation->status !== 'approved') {
            throw new \Exception('Revaluation must be approved before posting');
        }
        
        $revaluation->postToGeneralLedger();
        
        $this->createAuditTrail($revaluation->ppe, 'revaluation_posted', 
            ['revaluation_id' => $revaluation->id], 
            ['journal_reference' => $revaluation->journal_entry_reference]);
    }
    
    /**
     * Create or renew insurance policy
     */
    public function createInsurancePolicy(PPE $ppe, array $data)
    {
        // Check if there's an existing active policy
        $existingPolicy = $ppe->insurancePolicies()->active()->first();
        
        if ($existingPolicy && isset($data['is_renewal'])) {
            // Renew existing policy
            $existingPolicy->renew(
                $data['end_date'],
                $data['premium_amount'] ?? $existingPolicy->premium_amount
            );
            
            $this->createAuditTrail($ppe, 'insurance_renewed', 
                ['policy_number' => $existingPolicy->policy_number], 
                ['new_end_date' => $data['end_date']]);
            
            return $existingPolicy;
        }
        
        // Create new policy
        $insurance = PpeInsurance::create([
            'ppe_id' => $ppe->id,
            'policy_number' => $data['policy_number'],
            'insurance_company' => $data['insurance_company'],
            'coverage_type' => $data['coverage_type'] ?? 'comprehensive',
            'insured_value' => $data['insured_value'] ?? $ppe->closing_value,
            'premium_amount' => $data['premium_amount'],
            'start_date' => $data['start_date'] ?? now(),
            'end_date' => $data['end_date'],
            'deductible' => $data['deductible'] ?? 0,
            'coverage_details' => $data['coverage_details'] ?? null,
            'agent_name' => $data['agent_name'] ?? null,
            'agent_contact' => $data['agent_contact'] ?? null,
            'status' => 'active',
            'notes' => $data['notes'] ?? null
        ]);
        
        $this->createAuditTrail($ppe, 'insurance_created', null, 
            ['policy_number' => $insurance->policy_number]);
        
        return $insurance;
    }
    
    /**
     * Schedule next maintenance based on interval
     */
    private function scheduleNextMaintenance(PPE $ppe, int $intervalMonths)
    {
        $nextMaintenanceDate = Carbon::parse($ppe->purchase_date)->addMonths($intervalMonths);
        
        $ppe->update([
            'next_maintenance_date' => $nextMaintenanceDate,
            'expected_annual_maintenance' => ($ppe->purchase_price * 0.05) // 5% of purchase price as estimate
        ]);
        
        // Create scheduled maintenance record
        $this->scheduleMaintenance($ppe, [
            'maintenance_type' => 'preventive',
            'maintenance_date' => $nextMaintenanceDate,
            'description' => 'Scheduled preventive maintenance',
            'performed_by' => 'TBD'
        ]);
    }
    
    /**
     * Create audit trail entry
     */
    private function createAuditTrail(PPE $ppe, string $action, ?array $oldValues = null, ?array $newValues = null)
    {
        return DB::table('ppe_audit_trails')->insert([
            'ppe_id' => $ppe->id,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'user_id' => auth()->id() ?? 1,
            'user_name' => auth()->user()->name ?? 'System',
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Get asset health score (0-100)
     */
    public function calculateHealthScore(PPE $ppe)
    {
        $score = 100;
        
        // Condition factor (40%)
        $conditionScores = [
            'excellent' => 40,
            'good' => 32,
            'fair' => 24,
            'poor' => 16,
            'needs_repair' => 8
        ];
        $score = $conditionScores[$ppe->condition] ?? 20;
        
        // Age factor (20%)
        $agePercentage = ($ppe->age_in_years / $ppe->useful_life) * 100;
        if ($agePercentage < 25) {
            $score += 20;
        } elseif ($agePercentage < 50) {
            $score += 15;
        } elseif ($agePercentage < 75) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // Maintenance factor (20%)
        if (!$ppe->isMaintenanceDue()) {
            $score += 20;
        } elseif ($ppe->next_maintenance_date && $ppe->next_maintenance_date->diffInDays(now()) < 30) {
            $score += 10;
        }
        
        // Insurance factor (10%)
        if ($ppe->isInsured()) {
            $score += 10;
        }
        
        // Warranty factor (10%)
        if ($ppe->isUnderWarranty()) {
            $score += 10;
        }
        
        return min(100, $score);
    }
    
    /**
     * Get assets requiring attention
     */
    public function getAssetsRequiringAttention()
    {
        return PPE::where(function ($query) {
            $query->where('condition', 'needs_repair')
                  ->orWhere('condition', 'poor')
                  ->orWhere('next_maintenance_date', '<=', now())
                  ->orWhere('next_inspection_date', '<=', now())
                  ->orWhereHas('insurancePolicies', function ($q) {
                      $q->where('status', 'active')
                        ->where('end_date', '<=', now()->addDays(30));
                  });
        })->get();
    }
    
    /**
     * Generate comprehensive asset report
     */
    public function generateAssetReport(PPE $ppe)
    {
        return [
            'basic_info' => [
                'asset_code' => $ppe->asset_code,
                'name' => $ppe->name,
                'category' => $ppe->category,
                'status' => $ppe->status,
                'condition' => $ppe->condition,
                'location' => $ppe->location,
                'custodian' => $ppe->custodian ? $ppe->custodian->name : 'N/A',
                'department' => $ppe->department ? $ppe->department->name : 'N/A'
            ],
            'financial' => [
                'purchase_price' => $ppe->purchase_price,
                'current_value' => $ppe->closing_value,
                'accumulated_depreciation' => $ppe->accumulated_depreciation,
                'depreciation_method' => $ppe->depreciation_method,
                'remaining_useful_life' => $ppe->remaining_useful_life . ' years',
                'utilization_percentage' => $ppe->utilization_percentage . '%'
            ],
            'maintenance' => [
                'last_maintenance' => $ppe->last_maintenance_date,
                'next_maintenance' => $ppe->next_maintenance_date,
                'maintenance_cost_to_date' => $ppe->maintenance_cost_to_date,
                'is_maintenance_due' => $ppe->isMaintenanceDue()
            ],
            'warranty' => [
                'status' => $ppe->warranty_status,
                'end_date' => $ppe->warranty_end_date,
                'provider' => $ppe->warranty_provider
            ],
            'insurance' => [
                'is_insured' => $ppe->isInsured(),
                'active_policy' => $ppe->active_insurance ? [
                    'policy_number' => $ppe->active_insurance->policy_number,
                    'company' => $ppe->active_insurance->insurance_company,
                    'expiry' => $ppe->active_insurance->end_date
                ] : null
            ],
            'health_score' => $this->calculateHealthScore($ppe),
            'history' => [
                'transfers' => $ppe->transfers()->count(),
                'maintenance_records' => $ppe->maintenanceRecords()->count(),
                'revaluations' => $ppe->revaluations()->count()
            ]
        ];
    }
}