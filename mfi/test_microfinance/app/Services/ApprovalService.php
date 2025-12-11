<?php

namespace App\Services;

use App\Models\ApprovalMatrixConfig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApprovalService
{
    public function getNextApprovers($processType, $amount = null, $currentLevel = 0)
    {
        $configs = ApprovalMatrixConfig::where('process_type', $processType)
            ->where('level', '>', $currentLevel)
            ->where('is_active', true)
            ->orderBy('level')
            ->get();

        if ($amount !== null) {
            $configs = $configs->filter(function ($config) use ($amount) {
                return ($config->min_amount === null || $config->min_amount <= $amount) &&
                       ($config->max_amount === null || $config->max_amount >= $amount);
            });
        }

        return $configs;
    }

    public function canApprove($processType, $amount = null, $user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }

        $config = ApprovalMatrixConfig::where('process_type', $processType)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('approver_role', $user->role)
                    ->orWhere('approver_sub_role', $user->sub_role);
            });

        if ($amount !== null) {
            $config = $config->where(function ($query) use ($amount) {
                $query->whereNull('min_amount')
                    ->orWhere('min_amount', '<=', $amount);
            })
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            });
        }

        return $config->exists();
    }

    public function getApprovalLevel($processType, $amount = null, $user = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }

        $config = ApprovalMatrixConfig::where('process_type', $processType)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->where('approver_role', $user->role)
                    ->orWhere('approver_sub_role', $user->sub_role);
            });

        if ($amount !== null) {
            $config = $config->where(function ($query) use ($amount) {
                $query->whereNull('min_amount')
                    ->orWhere('min_amount', '<=', $amount);
            })
            ->where(function ($query) use ($amount) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $amount);
            });
        }

        return $config->value('level');
    }

    public function getApprovalChain($processType, $amount = null)
    {
        return ApprovalMatrixConfig::where('process_type', $processType)
            ->where('is_active', true)
            ->when($amount !== null, function ($query) use ($amount) {
                $query->where(function ($q) use ($amount) {
                    $q->whereNull('min_amount')
                        ->orWhere('min_amount', '<=', $amount);
                })
                ->where(function ($q) use ($amount) {
                    $q->whereNull('max_amount')
                        ->orWhere('max_amount', '>=', $amount);
                });
            })
            ->orderBy('level')
            ->get();
    }

    public function validateApprovalChain($processType, $amount = null)
    {
        $chain = $this->getApprovalChain($processType, $amount);
        
        // Check if there are any gaps in the approval levels
        $levels = $chain->pluck('level')->sort()->values();
        $expectedLevels = range(1, $levels->max());
        
        return $levels->diff($expectedLevels)->isEmpty();
    }
} 