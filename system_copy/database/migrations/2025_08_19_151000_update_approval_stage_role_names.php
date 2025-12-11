<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get loan process configuration
        $loanProcessConfig = DB::table('process_code_configs')
            ->where('process_code', 'LOAN_APP')
            ->where('is_active', true)
            ->first();
            
        // Get role names
        $roleNames = [];
        if ($loanProcessConfig) {
            $allRoleIds = [];
            $firstCheckerRoles = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
            $secondCheckerRoles = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
            $approverRoles = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
            
            $allRoleIds = array_merge($allRoleIds, $firstCheckerRoles, $secondCheckerRoles, $approverRoles);
            $allRoleIds = array_unique(array_filter($allRoleIds));
            
            if (!empty($allRoleIds)) {
                $roleNames = DB::table('roles')
                    ->whereIn('id', $allRoleIds)
                    ->pluck('name', 'id')
                    ->toArray();
            }
            
            // Update loans based on their approval stage
            // Inputter and Exception stages
            DB::table('loans')
                ->whereIn('approval_stage', ['Inputter', 'Exception'])
                ->update(['approval_stage_role_name' => 'Loan Officer']);
                
            // First Checker stage
            if (!empty($firstCheckerRoles)) {
                $firstCheckerNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $firstCheckerRoles);
                $roleName = count($firstCheckerNames) > 1 ? 'Loan Committee' : implode(', ', $firstCheckerNames);
                
                DB::table('loans')
                    ->where('approval_stage', 'First Checker')
                    ->update(['approval_stage_role_name' => $roleName]);
            }
            
            // Second Checker stage
            if (!empty($secondCheckerRoles)) {
                $secondCheckerNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $secondCheckerRoles);
                $roleName = count($secondCheckerNames) > 1 ? 'Loan Committee' : implode(', ', $secondCheckerNames);
                
                DB::table('loans')
                    ->where('approval_stage', 'Second Checker')
                    ->update(['approval_stage_role_name' => $roleName]);
            }
            
            // Approver stage
            if (!empty($approverRoles)) {
                $approverNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $approverRoles);
                $roleName = count($approverNames) > 1 ? 'Loan Committee' : implode(', ', $approverNames);
                
                DB::table('loans')
                    ->where('approval_stage', 'Approver')
                    ->update(['approval_stage_role_name' => $roleName]);
            }
        } else {
            // Fallback if no config found
            DB::table('loans')
                ->whereIn('approval_stage', ['Inputter', 'Exception'])
                ->update(['approval_stage_role_name' => 'Loan Officer']);
                
            DB::table('loans')
                ->where('approval_stage', 'First Checker')
                ->update(['approval_stage_role_name' => 'Review Officer']);
                
            DB::table('loans')
                ->where('approval_stage', 'Second Checker')
                ->update(['approval_stage_role_name' => 'Senior Review Officer']);
                
            DB::table('loans')
                ->where('approval_stage', 'Approver')
                ->update(['approval_stage_role_name' => 'Final Approver']);
        }
        
        // FINANCE stage
        DB::table('loans')
            ->where('approval_stage', 'FINANCE')
            ->update(['approval_stage_role_name' => 'Finance']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't really reverse this data update
        // But we could set all back to NULL if needed
        // DB::table('loans')->update(['approval_stage_role_name' => null]);
    }
};