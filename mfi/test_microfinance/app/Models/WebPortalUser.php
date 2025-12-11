<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class WebPortalUser extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'web_portal_users';

    protected $fillable = [
        'client_id',
        'client_number',
        'username',
        'email',
        'phone',
        'password_hash',
        'email_verified_at',
        'is_active',
        'is_locked',
        'locked_at',
        'locked_reason',
        'failed_login_attempts',
        'last_failed_attempt',
        'last_login_at',
        'last_login_ip',
        'last_user_agent',
        'total_logins',
        'password_reset_token',
        'password_reset_expires_at',
        'password_changed_at',
        'force_password_change',
        'current_session_id',
        'session_expires_at',
        'active_sessions',
        'permissions',
        'preferences',
        'preferred_language',
        'timezone',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_notifications',
        'sms_notifications',
        'login_notifications',
        'transaction_notifications',
        'portal_registered_at',
        'registered_by',
        'last_activity_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
        'password_reset_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'last_failed_attempt' => 'datetime',
        'last_login_at' => 'datetime',
        'password_reset_expires_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_change' => 'boolean',
        'session_expires_at' => 'datetime',
        'active_sessions' => 'array',
        'permissions' => 'array',
        'preferences' => 'array',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'two_factor_confirmed_at' => 'datetime',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'login_notifications' => 'boolean',
        'transaction_notifications' => 'boolean',
        'portal_registered_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the password for authentication
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Set the password attribute
     */
    public function setPasswordHashAttribute($value)
    {
        $this->attributes['password_hash'] = Hash::make($value);
        $this->attributes['password_changed_at'] = now();
    }

    /**
     * Relationships
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeCanLogin($query)
    {
        return $query->active()->notLocked();
    }

    public function scopeRecentlyActive($query, $minutes = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Authentication Methods
     */
    public function canLogin()
    {
        return $this->is_active && !$this->is_locked;
    }

    public function isAccountLocked()
    {
        return $this->is_locked || ($this->failed_login_attempts >= 5);
    }

    public function lockAccount($reason = 'Too many failed login attempts')
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_reason' => $reason,
        ]);
    }

    public function unlockAccount()
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_reason' => null,
            'failed_login_attempts' => 0,
            'last_failed_attempt' => null,
        ]);
    }

    public function recordFailedLogin()
    {
        $this->increment('failed_login_attempts');
        $this->update([
            'last_failed_attempt' => now(),
        ]);

        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount();
        }
    }

    public function recordSuccessfulLogin($ip = null, $userAgent = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?: request()->ip(),
            'last_user_agent' => $userAgent ?: request()->userAgent(),
            'last_activity_at' => now(),
            'failed_login_attempts' => 0,
            'last_failed_attempt' => null,
        ]);

        $this->increment('total_logins');
    }

    public function updateActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Session Management
     */
    public function startSession($sessionId)
    {
        $activeSessions = $this->active_sessions ?: [];
        
        // Add new session
        $activeSessions[] = [
            'id' => $sessionId,
            'started_at' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now()->toISOString(),
        ];

        // Keep only last 5 sessions
        $activeSessions = array_slice($activeSessions, -5);

        $this->update([
            'current_session_id' => $sessionId,
            'session_expires_at' => now()->addHours(24),
            'active_sessions' => $activeSessions,
        ]);
    }

    public function endSession($sessionId = null)
    {
        $sessionId = $sessionId ?: $this->current_session_id;
        
        $activeSessions = $this->active_sessions ?: [];
        $activeSessions = array_filter($activeSessions, function($session) use ($sessionId) {
            return $session['id'] !== $sessionId;
        });

        $this->update([
            'current_session_id' => null,
            'session_expires_at' => null,
            'active_sessions' => array_values($activeSessions),
        ]);
    }

    public function endAllSessions()
    {
        $this->update([
            'current_session_id' => null,
            'session_expires_at' => null,
            'active_sessions' => [],
        ]);
    }

    /**
     * Password Management
     */
    public function needsPasswordChange()
    {
        return $this->force_password_change || 
               ($this->password_changed_at && $this->password_changed_at->diffInDays() > 90);
    }

    public function generatePasswordResetToken()
    {
        $token = \Illuminate\Support\Str::random(60);
        
        $this->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    public function clearPasswordResetToken()
    {
        $this->update([
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);
    }

    public function isValidPasswordResetToken($token)
    {
        return $this->password_reset_token === $token && 
               $this->password_reset_expires_at && 
               $this->password_reset_expires_at->isFuture();
    }

    /**
     * Permissions
     */
    public function hasPermission($permission)
    {
        $permissions = $this->permissions ?: [];
        return in_array($permission, $permissions) || 
               (isset($permissions[$permission]) && $permissions[$permission] === true);
    }

    public function grantPermission($permission)
    {
        $permissions = $this->permissions ?: [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    public function revokePermission($permission)
    {
        $permissions = $this->permissions ?: [];
        $permissions = array_diff($permissions, [$permission]);
        $this->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Preferences
     */
    public function getPreference($key, $default = null)
    {
        $preferences = $this->preferences ?: [];
        return $preferences[$key] ?? $default;
    }

    public function setPreference($key, $value)
    {
        $preferences = $this->preferences ?: [];
        $preferences[$key] = $value;
        $this->update(['preferences' => $preferences]);
    }

    /**
     * Utility Methods
     */
    public function getFullNameAttribute()
    {
        return $this->client ? $this->client->getFullNameAttribute() : 'Unknown';
    }

    public function getMemberNumberAttribute()
    {
        return $this->client_number;
    }

    public function isOnline()
    {
        return $this->last_activity_at && $this->last_activity_at->diffInMinutes() <= 5;
    }

    public function getLastSeenAttribute()
    {
        if (!$this->last_activity_at) {
            return 'Never';
        }

        if ($this->isOnline()) {
            return 'Online';
        }

        return $this->last_activity_at->diffForHumans();
    }

    /**
     * Route notifications for the notifiable.
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    public function routeNotificationForSms($notification)
    {
        return $this->phone;
    }

    /**
     * Two-Factor Authentication
     */
    public function enableTwoFactor($secret, $recoveryCodes)
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function disableTwoFactor()
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    public function replaceRecoveryCode($code)
    {
        $codes = $this->two_factor_recovery_codes ?: [];
        $index = array_search($code, $codes);
        
        if ($index !== false) {
            unset($codes[$index]);
            $this->update(['two_factor_recovery_codes' => array_values($codes)]);
            return true;
        }
        
        return false;
    }
}
