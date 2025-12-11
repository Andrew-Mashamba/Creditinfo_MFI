<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpeMaintenanceRecord extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ppe_id', 'maintenance_type', 'maintenance_date', 'performed_by',
        'vendor_name', 'description', 'parts_replaced', 'cost', 'downtime_hours',
        'next_maintenance_date', 'status', 'notes', 'work_order_number', 'invoice_number'
    ];
    
    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'downtime_hours' => 'decimal:2'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function getMaintenanceTypeDisplayAttribute()
    {
        $types = [
            'preventive' => 'Preventive Maintenance',
            'corrective' => 'Corrective Maintenance',
            'emergency' => 'Emergency Repair',
            'inspection' => 'Inspection',
            'calibration' => 'Calibration'
        ];
        
        return $types[$this->maintenance_type] ?? $this->maintenance_type;
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('maintenance_date', '>=', now())
                    ->orderBy('maintenance_date');
    }
    
    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('maintenance_date', '<', now());
    }
    
    public function isOverdue()
    {
        return $this->status === 'scheduled' && $this->maintenance_date < now();
    }
}