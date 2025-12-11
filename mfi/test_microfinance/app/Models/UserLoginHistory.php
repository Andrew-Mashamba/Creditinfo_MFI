<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginHistory extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'session_id',
        'location',
        'device_type',
        'browser',
        'os',
        'is_successful',
        'failure_reason'
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_successful' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->logout_at === null;
    }

    public function getSessionDuration()
    {
        if (!$this->logout_at) {
            return now()->diffInSeconds($this->login_at);
        }

        return $this->logout_at->diffInSeconds($this->login_at);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('logout_at');
    }

    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }
} 