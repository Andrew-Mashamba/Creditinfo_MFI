<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
        'settings',
        'contact_email',
        'contact_phone',
        'address',
        'logo_url',
        'operations_account',
        'mandatory_shares_account',
        'mandatory_savings_account',
        'mandatory_deposits_account',
        'members_external_loans_crealance',
        'institution_id',
        'institution_name',
        'region',
        'wilaya',
        'phone_number',
        'email',
        'institution_status',
        'imgUrl',
        'admin_name',
        'available_shares',
        'registration_fees',
        'min_shares',
        'initial_shares',
        'temp_shares_holding_account',
        'value_per_share',
        'selected',
        'inactivity',
        'allocated_shares',
        'admin_email',
        'manager_email',
        'tin_number',
        'tcdc_form',
        'microfinance_license',
        'manager_name',
        'total_shares',
        'settings_status',
        'repayment_frequency',
        'startDate',
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',
        'notes',
        'onboarding_process',
        'petty_amount_limit'
    ];

    protected $casts = [
        'status' => 'boolean',
        'settings' => 'array',
        'selected' => 'boolean',
        'inactivity' => 'boolean',
        'settings_status' => 'boolean',
        'startDate' => 'date'
    ];

    /**
     * Get the departments for this institution.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the roles for this institution.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the committees for this institution.
     */
    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    /**
     * Get the users for this institution.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active institutions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the root departments for this institution.
     */
    public function rootDepartments()
    {
        return $this->departments()->whereNull('parent_department_id');
    }

    /**
     * Get all departments in a hierarchical structure.
     */
    public function getDepartmentHierarchy()
    {
        $departments = $this->departments()->orderBy('level')->get();
        $hierarchy = [];

        foreach ($departments as $department) {
            if (!$department->parent_department_id) {
                $hierarchy[] = $this->buildDepartmentTree($department, $departments);
            }
        }

        return $hierarchy;
    }

    /**
     * Build a department tree recursively.
     */
    protected function buildDepartmentTree($department, $allDepartments)
    {
        $tree = [
            'id' => $department->id,
            'name' => $department->department_name,
            'code' => $department->department_code,
            'children' => []
        ];

        foreach ($allDepartments as $child) {
            if ($child->parent_department_id === $department->id) {
                $tree['children'][] = $this->buildDepartmentTree($child, $allDepartments);
            }
        }

        return $tree;
    }
} 