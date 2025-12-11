<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityTransportLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_reference',
        'transfer_type',
        'amount',
        'currency',
        'source_vault_id',
        'destination_vault_id',
        'bank_account_id',
        'pickup_location',
        'delivery_location',
        'transport_company_name',
        'transport_company_license',
        'transport_company_contact',
        'insurance_policy_number',
        'insurance_coverage_amount',
        'vehicle_registration',
        'vehicle_type',
        'vehicle_gps_tracker',
        'security_personnel',
        'team_leader_name',
        'team_leader_badge',
        'team_leader_contact',
        'cash_bag_seal_number',
        'container_seal_number',
        'verification_codes',
        'scheduled_pickup_time',
        'actual_pickup_time',
        'scheduled_delivery_time',
        'actual_delivery_time',
        'planned_route',
        'actual_route',
        'status',
        'status_notes',
        'pickup_verified_by',
        'delivery_verified_by',
        'pickup_verification_time',
        'delivery_verification_time',
        'pickup_notes',
        'delivery_notes',
        'has_incident',
        'incident_description',
        'incident_reported_at',
        'incident_report_number',
        'initiated_by',
        'authorized_by',
        'additional_metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'insurance_coverage_amount' => 'decimal:2',
        'security_personnel' => 'array',
        'verification_codes' => 'array',
        'additional_metadata' => 'array',
        'scheduled_pickup_time' => 'datetime',
        'actual_pickup_time' => 'datetime',
        'scheduled_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'pickup_verification_time' => 'datetime',
        'delivery_verification_time' => 'datetime',
        'incident_reported_at' => 'datetime',
        'has_incident' => 'boolean',
    ];

    // Relationships
    public function sourceVault()
    {
        return $this->belongsTo(\App\Models\Vault::class, 'source_vault_id');
    }

    public function destinationVault()
    {
        return $this->belongsTo(\App\Models\Vault::class, 'destination_vault_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class, 'bank_account_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'initiated_by');
    }

    public function authorizedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'authorized_by');
    }

    // Scopes
    public function scopeInTransit($query)
    {
        return $query->where('status', 'IN_TRANSIT');
    }

    public function scopeWithIncidents($query)
    {
        return $query->where('has_incident', true);
    }

    public function scopeByTransferType($query, $type)
    {
        return $query->where('transfer_type', $type);
    }

    // Helper Methods
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'SCHEDULED' => 'blue',
            'IN_TRANSIT' => 'yellow',
            'DELIVERED' => 'green',
            'CANCELLED' => 'gray',
            'DELAYED' => 'orange',
            'INCIDENT' => 'red',
            default => 'gray'
        };
    }

    public function isOverdue()
    {
        if ($this->status === 'DELIVERED') {
            return false;
        }
        
        return now()->gt($this->scheduled_delivery_time);
    }

    public function getTransportDuration()
    {
        if ($this->actual_pickup_time && $this->actual_delivery_time) {
            return $this->actual_pickup_time->diffInMinutes($this->actual_delivery_time);
        }
        
        return null;
    }
}
