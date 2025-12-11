<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeApproval extends Model
{
    protected $fillable = [
        'loan_id',
        'committee_id',
        'committee_member_id',
        'decision',
        'reason',
        'status',
    ];

    /**
     * Get the loan that this approval belongs to.
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoansModel::class, 'loan_id');
    }

    /**
     * Get the committee that this approval belongs to.
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the committee member who made this approval.
     */
    public function committeeMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'committee_member_id');
    }

    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include approved decisions.
     */
    public function scopeApproved($query)
    {
        return $query->where('decision', 'APPROVED');
    }

    /**
     * Scope a query to only include rejected decisions.
     */
    public function scopeRejected($query)
    {
        return $query->where('decision', 'REJECTED');
    }
} 