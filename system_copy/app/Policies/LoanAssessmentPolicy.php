<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LoansModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanAssessmentPolicy
{
    use HandlesAuthorization;

    public function assess(User $user, LoansModel $loan)
    {
        // Check if user has basic assessment permission
        if (!$user->can('assess_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        // Check loan amount limits based on user role
        return $this->hasRequiredRole($user, $loan->principle);
    }

    public function approve(User $user, LoansModel $loan)
    {
        // Check if user has approval permission
        if (!$user->can('approve_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        // Check approval limits based on user role
        return $this->hasApprovalAuthority($user, $loan->principle);
    }

    public function reject(User $user, LoansModel $loan)
    {
        // Check if user has rejection permission
        if (!$user->can('reject_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        return true;
    }

    public function disburse(User $user, LoansModel $loan)
    {
        // Check if user has disbursement permission
        if (!$user->can('disburse_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        // Check if loan is approved
        if ($loan->status !== 'APPROVED') {
            return false;
        }

        return true;
    }

    public function view(User $user, LoansModel $loan)
    {
        // Check if user has view permission
        if (!$user->can('view_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        return true;
    }

    public function update(User $user, LoansModel $loan)
    {
        // Check if user has update permission
        if (!$user->can('update_loans')) {
            return false;
        }

        // Check branch access
        if ($user->branch_id !== $loan->branch_id) {
            return false;
        }

        // Check if loan is in editable state
        if (!in_array($loan->status, ['PENDING', 'DRAFT'])) {
            return false;
        }

        return true;
    }

    protected function hasRequiredRole(User $user, $loanAmount)
    {
        $roleLimits = [
            'loan_officer' => 1000000,      // 1M
            'senior_officer' => 5000000,    // 5M
            'manager' => 10000000,          // 10M
            'director' => 50000000,         // 50M
            'ceo' => 100000000              // 100M
        ];

        $userRole = $user->roles->first()->name ?? 'loan_officer';
        $limit = $roleLimits[$userRole] ?? 1000000;

        return $loanAmount <= $limit;
    }

    protected function hasApprovalAuthority(User $user, $loanAmount)
    {
        $approvalLimits = [
            'loan_officer' => 500000,       // 500K
            'senior_officer' => 2000000,    // 2M
            'manager' => 5000000,           // 5M
            'director' => 20000000,         // 20M
            'ceo' => 50000000               // 50M
        ];

        $userRole = $user->roles->first()->name ?? 'loan_officer';
        $limit = $approvalLimits[$userRole] ?? 500000;

        return $loanAmount <= $limit;
    }
} 