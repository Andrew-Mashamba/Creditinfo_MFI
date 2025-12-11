<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Committee extends Model
{
    use HasFactory;

    protected $table = "committees";
    
    protected $fillable = [
        'name',
        'description',
        'status',
        'department_id',
        'loan_category',
        'min_approvals_required',
        'approval_order',
        'institution_id',
        'type',
        'level',
        'parent_committee_id',
        'approval_sequence'
    ];

    protected $casts = [
        'min_approvals_required' => 'integer',
        'approval_order' => 'integer',
        'status' => 'boolean',
        'level' => 'integer',
        'approval_sequence' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($committee) {
            if ($committee->parent_committee_id) {
                $parent = Committee::find($committee->parent_committee_id);
                $committee->level = $parent->level + 1;
            } else {
                $committee->level = 1;
            }
        });

        static::updating(function ($committee) {
            if ($committee->isDirty('parent_committee_id')) {
                $parent = Committee::find($committee->parent_committee_id);
                $committee->level = $parent ? $parent->level + 1 : 1;
            }
        });
    }

    /**
     * Get the department this committee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the members of the committee.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['is_primary_approver', 'approval_order'])
            ->withTimestamps();
    }

    /**
     * Get the approvals associated with this committee.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(CommitteeApproval::class);
    }

    /**
     * Get the loan stages associated with this committee.
     */
    public function loanStages(): HasMany
    {
        return $this->hasMany(LoanStage::class);
    }

    /**
     * Get the institution this committee belongs to.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Check if a user is a member of this committee.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user is a primary approver in this committee.
     */
    public function isPrimaryApprover(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('is_primary_approver', true)
            ->exists();
    }

    /**
     * Get the number of approvals required for this committee.
     */
    public function getRequiredApprovalsCount(): int
    {
        return $this->min_approvals_required ?? $this->members()->count();
    }

    /**
     * Check if a loan has received enough approvals from this committee.
     */
    public function hasEnoughApprovals(int $loanId): bool
    {
        $requiredApprovals = $this->members()
            ->wherePivot('is_primary_approver', true)
            ->count();

        $currentApprovals = $this->approvals()
            ->where('loan_id', $loanId)
            ->where('status', 'approved')
            ->count();

        return $currentApprovals >= $requiredApprovals;
    }

    /**
     * Get the next approver in the sequence.
     */
    public function getNextApprover(int $loanId): ?User
    {
        $lastApproval = $this->approvals()
            ->where('loan_id', $loanId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastApproval) {
            return $this->members()
                ->wherePivot('approval_order', 1)
                ->first();
        }

        return $this->members()
            ->wherePivot('approval_order', $lastApproval->approval_order + 1)
            ->first();
    }

    /**
     * Scope a query to only include active committees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include committees for a specific loan category.
     */
    public function scopeForLoanCategory($query, string $category)
    {
        return $query->where('loan_category', $category);
    }

    /**
     * Scope a query to only include committees for a specific department.
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include committees for a specific institution.
     */
    public function scopeForInstitution($query, int $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * Scope a query to only include committees of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include committees at a specific level.
     */
    public function scopeAtLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get the parent committee.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'parent_committee_id');
    }

    /**
     * Get the child committees.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Committee::class, 'parent_committee_id');
    }

    /**
     * Get all ancestor committees.
     */
    public function ancestors()
    {
        return Committee::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get all descendant committees.
     */
    public function descendants()
    {
        return Committee::whereRaw("path LIKE ?", [$this->path . '%'])
            ->where('id', '!=', $this->id)
            ->orderBy('level');
    }

    /**
     * Get all members from this committee and its ancestors.
     */
    public function getAllMembers()
    {
        return User::whereIn('id', function($query) {
            $query->select('user_id')
                ->from('committee_memberships')
                ->whereIn('committee_id', function($subQuery) {
                    $subQuery->select('id')
                        ->from('committees')
                        ->whereRaw("path LIKE ?", [$this->path . '%']);
                });
        })->get();
    }

    /**
     * Get all primary approvers from this committee and its ancestors.
     */
    public function getAllPrimaryApprovers()
    {
        return User::whereIn('id', function($query) {
            $query->select('user_id')
                ->from('committee_memberships')
                ->whereIn('committee_id', function($subQuery) {
                    $subQuery->select('id')
                        ->from('committees')
                        ->whereRaw("path LIKE ?", [$this->path . '%']);
                })
                ->where('is_primary_approver', true);
        })->get();
    }

    /**
     * Check if this committee inherits from another committee.
     */
    public function inheritsFrom(Committee $committee): bool
    {
        return strpos($this->path, $committee->path) === 0;
    }

    /**
     * Get all committees that inherit from this committee.
     */
    public function getInheritingCommittees()
    {
        return Committee::whereRaw("path LIKE ?", [$this->path . '%'])
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get all committees that this committee inherits from.
     */
    public function getInheritedCommittees()
    {
        return Committee::whereRaw("? LIKE CONCAT(path, '%')", [$this->path])
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get the total number of required approvals across all committees in the hierarchy.
     */
    public function getTotalRequiredApprovals(): int
    {
        return $this->descendants()
            ->sum('min_approvals_required') + $this->min_approvals_required;
    }

    /**
     * Check if all required approvals have been received across the committee hierarchy.
     */
    public function hasAllRequiredApprovals(int $loanId): bool
    {
        $totalApprovals = $this->descendants()
            ->withCount(['approvals' => function($query) use ($loanId) {
                $query->where('loan_id', $loanId)
                    ->where('decision', 'APPROVED');
            }])
            ->get()
            ->sum('approvals_count');

        $currentApprovals = $this->approvals()
            ->where('loan_id', $loanId)
            ->where('decision', 'APPROVED')
            ->count();

        return ($totalApprovals + $currentApprovals) >= $this->getTotalRequiredApprovals();
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
