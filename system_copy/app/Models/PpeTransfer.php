<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpeTransfer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ppe_id', 'from_location', 'to_location', 'from_department_id', 'to_department_id',
        'from_custodian_id', 'to_custodian_id', 'transfer_date', 'reason', 'approved_by',
        'notes', 'transfer_document_number', 'status'
    ];
    
    protected $casts = [
        'transfer_date' => 'date',
        'from_department_id' => 'integer',
        'to_department_id' => 'integer',
        'from_custodian_id' => 'integer',
        'to_custodian_id' => 'integer'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function fromDepartment()
    {
        return $this->belongsTo(\App\Models\Department::class, 'from_department_id');
    }
    
    public function toDepartment()
    {
        return $this->belongsTo(\App\Models\Department::class, 'to_department_id');
    }
    
    public function fromCustodian()
    {
        return $this->belongsTo(User::class, 'from_custodian_id');
    }
    
    public function toCustodian()
    {
        return $this->belongsTo(User::class, 'to_custodian_id');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy
        ]);
        
        // Update the PPE record with new location and custodian
        $this->ppe->update([
            'location' => $this->to_location,
            'department_id' => $this->to_department_id,
            'custodian_id' => $this->to_custodian_id
        ]);
    }
}