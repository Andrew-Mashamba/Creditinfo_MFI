<?php

namespace App\Traits;

use App\Models\Approval;
use App\Models\ProcessCodeConfig;
use Illuminate\Support\Facades\Auth;

trait HasApprovals
{
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'process_id');
    }

    public function initiateApproval($processName, $processDescription, $processCode, $amount = null, $editPackage = null)
    {
        // Get process code configuration
        $config = ProcessCodeConfig::where('process_code', $processCode)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new \Exception('Invalid process code or process is not active');
        }

        // Create the approval record
        $approval = Approval::create([
            'institution' => $this->institution_id,
            'process_name' => $processName,
            'process_description' => $processDescription,
            'approval_process_description' => 'Approval required for ' . $processName,
            'process_code' => $processCode,
            'process_id' => $this->id,
            'process_status' => 'PENDING',
            'user_id' => Auth::id(), // Maker
            'team_id' => Auth::user()->current_team_id,
            'approver_id' => null,
            'approval_status' => 'PENDING',
            'edit_package' => $editPackage,
            'checker_level' => $config->requires_first_checker ? 1 : ($config->requires_second_checker ? 2 : 3),
            'first_checker_id' => null,
            'second_checker_id' => null,
            'first_checker_status' => null,
            'second_checker_status' => null,
            'amount' => $amount
        ]);

        return $approval;
    }

    public function approve($comment = null)
    {
        $approval = $this->getCurrentApproval();
        if (!$approval) {
            throw new \Exception('No pending approval found');
        }

        $user = auth()->user();
        $config = ProcessCodeConfig::where('process_code', $approval->process_code)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new \Exception('No active configuration found for this process');
        }

        if (!$config->canUserApprove($user, $approval->checker_level)) {
            throw new \Exception('You are not authorized to approve this request');
        }

        // Check if user has admin role
        $userRoles = $user->roles()->pluck('roles.id')->toArray();
        $isAdmin = in_array(1, $userRoles); // Role ID 1 is Systems Administrator

        // If user is admin, approve all levels at once
        if ($isAdmin) {
            $approval->update([
                'first_checker_id' => $user->id,
                'first_checker_status' => 'APPROVED',
                'first_checker_comments' => $comment,
                'second_checker_id' => $user->id,
                'second_checker_status' => 'APPROVED',
                'second_checker_comments' => $comment,
                'approver_id' => $user->id,
                'approval_status' => 'APPROVED',
                'comments' => $comment,
                'checker_level' => 3,
                'process_status' => 'COMPLETED'
            ]);
            return;
        }

        // Normal approval flow
        switch ($approval->checker_level) {
            case 1:
                $approval->update([
                    'first_checker_id' => $user->id,
                    'first_checker_status' => 'APPROVED',
                    'first_checker_comments' => $comment,
                    'checker_level' => $config->requires_second_checker ? 2 : ($config->requires_approver ? 3 : null)
                ]);
                break;
            case 2:
                $approval->update([
                    'second_checker_id' => $user->id,
                    'second_checker_status' => 'APPROVED',
                    'second_checker_comments' => $comment,
                    'checker_level' => $config->requires_approver ? 3 : null
                ]);
                break;
            case 3:
                $approval->update([
                    'approver_id' => $user->id,
                    'approval_status' => 'APPROVED',
                    'comments' => $comment,
                    'checker_level' => null,
                    'process_status' => 'COMPLETED'
                ]);
                break;
        }

        if ($approval->checker_level === null) {
            $this->update(['status' => 'APPROVED']);
        }
    }

    public function reject($comment)
    {
        $approval = $this->getCurrentApproval();
        if (!$approval) {
            throw new \Exception('No pending approval found');
        }

        $user = auth()->user();
        $config = ProcessCodeConfig::where('process_code', $approval->process_code)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new \Exception('No active configuration found for this process');
        }

        if (!$config->canUserApprove($user, $approval->checker_level)) {
            throw new \Exception('You are not authorized to reject this request');
        }

        // Check if user has admin role
        $userRoles = $user->roles()->pluck('roles.id')->toArray();
        $isAdmin = in_array(1, $userRoles); // Role ID 1 is Systems Administrator

        // If user is admin, reject at all levels
        if ($isAdmin) {
            $approval->update([
                'first_checker_id' => $user->id,
                'first_checker_status' => 'REJECTED',
                'first_checker_comments' => $comment,
                'second_checker_id' => $user->id,
                'second_checker_status' => 'REJECTED',
                'second_checker_comments' => $comment,
                'approver_id' => $user->id,
                'approval_status' => 'REJECTED',
                'comments' => $comment,
                'process_status' => 'REJECTED'
            ]);
            return;
        }

        // Normal rejection flow
        switch ($approval->checker_level) {
            case 1:
                $approval->update([
                    'first_checker_id' => $user->id,
                    'first_checker_status' => 'REJECTED',
                    'first_checker_comments' => $comment,
                    'approval_status' => 'REJECTED',
                    'process_status' => 'REJECTED'
                ]);
                break;
            case 2:
                $approval->update([
                    'second_checker_id' => $user->id,
                    'second_checker_status' => 'REJECTED',
                    'second_checker_comments' => $comment,
                    'approval_status' => 'REJECTED',
                    'process_status' => 'REJECTED'
                ]);
                break;
            case 3:
                $approval->update([
                    'approver_id' => $user->id,
                    'approval_status' => 'REJECTED',
                    'comments' => $comment,
                    'process_status' => 'REJECTED'
                ]);
                break;
        }

        $this->onRejected();
    }

    public function getCurrentApproval()
    {
        return $this->approvals()->where('approval_status', 'PENDING')->latest()->first();
    }

    public function getApprovalStatus()
    {
        $approval = $this->getCurrentApproval();
        return $approval ? $approval->approval_status : null;
    }

    public function canApprove()
    {
        $approval = $this->getCurrentApproval();
        if (!$approval) return false;

        $user = Auth::user();
        $currentLevel = $approval->checker_level;

        // Get process code configuration
        $config = ProcessCodeConfig::where('process_code', $approval->process_code)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return false;
        }

        return $config->canUserApprove($user, $currentLevel);
    }

    // These methods can be overridden in the model to add custom behavior
    protected function onApproved()
    {
        // Override this method in your model to add custom behavior when approved
    }

    protected function onRejected()
    {
        // Override this method in your model to add custom behavior when rejected
    }
} 