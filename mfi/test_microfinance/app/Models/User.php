<?php

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getOtpTimeAttribute($value): ?\Carbon\Carbon
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public static function isEmailAvailable($email): bool
    {
        return static::where('email', $email)->count() == 0;
    }

    public function registerUser($email,$department_code,$name,$employeeId,$password){
        User::create([
            'email'=>$email,
            'password'=>Hash::make($password),   // password is 1234567890
            'department_code'=>$department_code,
            'created_at'=>Carbon::now(),
             'updated_at'=>Carbon::now(),
            'name'=>$name,
            'employeeId'=>$employeeId,
            'branch'=>auth()->user()->branch,
        ]);
        // send mail
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code')
            ->withDefault([
                'department_name' => 'No Department',
                'department_code' => null
            ]);
    }

    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_memberships', 'user_id', 'committee_id')
            ->withPivot('role', 'is_primary_approver', 'approval_order')
            ->withTimestamps();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'branch_code');
    }

    // Removed institution relationship as institution_id is no longer used

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }
    
    public function subRoles()
    {
        return $this->belongsToMany(SubRole::class, 'user_sub_roles', 'user_id', 'sub_role_id')
            ->withTimestamps();
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
            ->withTimestamps();
    }
    
    public function getAllPermissions()
    {
        // Get direct user permissions
        $directPermissions = $this->permissions;
        
        // Get permissions from roles
        $rolePermissions = collect();
        foreach ($this->roles as $role) {
            $rolePermissions = $rolePermissions->merge($role->permissions);
        }
        
        // Get permissions from sub-roles
        $subRolePermissions = collect();
        foreach ($this->subRoles as $subRole) {
            $subRolePermissions = $subRolePermissions->merge($subRole->permissions);
        }
        
        // Merge all permissions and get unique
        return $directPermissions
            ->merge($rolePermissions)
            ->merge($subRolePermissions)
            ->unique('id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employeeId', 'id');
    }

    public function hasRole($roleId)
    {
        return $this->roles()->where('roles.id', $roleId)->exists();
    }

    public function isAdmin()
    {
        return $this->hasRole(1); // Role ID 1 is Systems Administrator
    }

    /**
     * Get the user's primary role ID
     * This accessor allows $user->role_id to work
     */
    public function getRoleIdAttribute()
    {
        $role = $this->roles()->first();
        return $role ? $role->id : null;
    }

    public function securityProfile()
    {
        return $this->hasOne(UserSecurityProfile::class);
    }

    public function loginHistory()
    {
        return $this->hasMany(UserLoginHistory::class);
    }

    public function securityAuditLogs()
    {
        return $this->hasMany(SecurityAuditLog::class);
    }

    public function canAccess($ip = null)
    {
        if (!$this->securityProfile) {
            return true;
        }

        if ($this->securityProfile->isAccountLocked()) {
            return false;
        }

        if ($this->securityProfile->isPasswordExpired()) {
            return false;
        }

        if (!$this->securityProfile->isWithinAccessHours()) {
            return false;
        }

        if ($ip && !$this->securityProfile->isIpAllowed($ip)) {
            return false;
        }

        return true;
    }

    public function recordLogin($ip, $userAgent)
    {
        $this->loginHistory()->create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_at' => now()
        ]);

        if ($this->securityProfile) {
            $this->securityProfile->resetFailedLogins();
        }
    }

    public function recordSecurityAudit($action, $details = null)
    {
        $this->securityAuditLogs()->create([
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    public function requiresPasswordChange()
    {
        return $this->securityProfile && $this->securityProfile->force_password_change;
    }

    public function getSecurityClearanceLevel()
    {
        return $this->securityProfile ? $this->securityProfile->security_clearance_level : 'standard';
    }

    public function isHighRiskUser()
    {
        return $this->securityProfile && 
               $this->securityProfile->security_clearance_level === 'high_risk';
    }

    public function getActiveSessions()
    {
        return $this->loginHistory()
            ->where('logout_at', null)
            ->where('login_at', '>', now()->subMinutes($this->securityProfile->session_timeout_minutes ?? 30))
            ->get();
    }

    public function forceLogoutAllSessions()
    {
        $this->loginHistory()
            ->where('logout_at', null)
            ->update(['logout_at' => now()]);
    }
}