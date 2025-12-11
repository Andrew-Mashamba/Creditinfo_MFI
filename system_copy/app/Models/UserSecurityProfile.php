<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSecurityProfile extends Model
{
    protected $fillable = [
        'user_id',
        'last_password_change',
        'password_expiry_date',
        'failed_login_attempts',
        'last_failed_login',
        'account_locked_until',
        'two_factor_enabled',
        'two_factor_method', // email, sms, authenticator
        'ip_whitelist',
        'device_whitelist',
        'session_timeout_minutes',
        'force_password_change',
        'last_security_review',
        'security_clearance_level',
        'access_hours_start',
        'access_hours_end',
        'restricted_ips',
        'vpn_required'
    ];

    protected $casts = [
        'last_password_change' => 'datetime',
        'password_expiry_date' => 'datetime',
        'last_failed_login' => 'datetime',
        'account_locked_until' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'ip_whitelist' => 'array',
        'device_whitelist' => 'array',
        'force_password_change' => 'boolean',
        'last_security_review' => 'datetime',
        'restricted_ips' => 'array',
        'vpn_required' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAccountLocked()
    {
        return $this->account_locked_until && now()->lt($this->account_locked_until);
    }

    public function isPasswordExpired()
    {
        return $this->password_expiry_date && now()->gt($this->password_expiry_date);
    }

    public function isWithinAccessHours()
    {
        if (!$this->access_hours_start || !$this->access_hours_end) {
            return true;
        }

        $now = now();
        $start = $now->copy()->setTimeFromTimeString($this->access_hours_start);
        $end = $now->copy()->setTimeFromTimeString($this->access_hours_end);

        return $now->between($start, $end);
    }

    public function isIpAllowed($ip)
    {
        if (empty($this->ip_whitelist)) {
            return true;
        }

        return in_array($ip, $this->ip_whitelist);
    }

    public function recordFailedLogin()
    {
        $this->failed_login_attempts++;
        $this->last_failed_login = now();

        if ($this->failed_login_attempts >= config('auth.max_failed_attempts', 5)) {
            $this->account_locked_until = now()->addMinutes(config('auth.lockout_minutes', 30));
        }

        $this->save();
    }

    public function resetFailedLogins()
    {
        $this->failed_login_attempts = 0;
        $this->account_locked_until = null;
        $this->save();
    }
} 