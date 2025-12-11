<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'severity_level',
        'affected_resource',
        'resource_id',
        'status',
        'risk_level'
    ];

    protected $casts = [
        'details' => 'array',
        'severity_level' => 'integer',
        'risk_level' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeHighSeverity($query)
    {
        return $query->where('severity_level', '>=', 8);
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', '>=', 8);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByResource($query, $resource, $resourceId = null)
    {
        $query = $query->where('affected_resource', $resource);
        
        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }
        
        return $query;
    }

    public function scopeByTimeRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isHighSeverity()
    {
        return $this->severity_level >= 8;
    }

    public function isHighRisk()
    {
        return $this->risk_level >= 8;
    }

    public function requiresImmediateAttention()
    {
        return $this->isHighSeverity() && $this->isHighRisk();
    }
} 