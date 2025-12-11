<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'client_name',
        'description',
        'is_active',
        'rate_limit',
        'allowed_ips',
        'permissions',
        'expires_at',
        'last_used_at',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_ips' => 'array',
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'key', // Never expose the actual key in responses
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($apiKey) {
            if (!$apiKey->key) {
                $apiKey->key = self::generateKey();
            }
        });
    }

    /**
     * Generate a secure API key
     */
    public static function generateKey()
    {
        return 'sk_' . Str::random(32);
    }

    /**
     * Scope for active keys
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired keys
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check if key is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if key has specific permission
     */
    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions) || 
               in_array('*', $this->permissions); // Wildcard permission
    }

    /**
     * Check if IP is allowed for this key
     */
    public function isIpAllowed($ip)
    {
        if (!$this->allowed_ips || empty($this->allowed_ips)) {
            return true; // No IP restrictions
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Get masked key for display
     */
    public function getMaskedKeyAttribute()
    {
        if (!$this->key) {
            return null;
        }

        return substr($this->key, 0, 8) . '...' . substr($this->key, -4);
    }

    /**
     * Relationship with user who created the key
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 