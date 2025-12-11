{{-- Approval Progress Bar --}}
@php
    // Get approval stages for this loan
    $approvalStages = [];
    $currentStage = $loan->approval_stage ?? 'Inputter';
    
    // Get loan process configuration
    $loanProcessConfig = DB::table('process_code_configs')
        ->where('process_code', 'LOAN_APP')
        ->where('is_active', true)
        ->first();
    
    if ($loanProcessConfig) {
        // Get role names
        $roleNames = [];
        $allRoleIds = [];
        
        // Collect all role IDs
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
        
        // Build stages array
        $stages = [];
        
        // Check if loan has exceptions - if so, add Exception stage
        $loanStatus = $loan->status ?? '';
        if ($loanStatus === 'PENDING-EXCEPTIONS' || $loanStatus === 'PENDING-WITH-EXCEPTIONS') {
            // Add Stage 0 - Exception
            $stages['Exception'] = 'Loan Officer';
            
            // If current stage is still 'Inputter' but loan has exceptions, set to Exception
            if ($currentStage === 'Inputter') {
                $currentStage = 'Exception';
            }
        }
        
        // Inputter Stage (Stage 1 for normal loans, Stage 2 for exception loans)
        $stages['Inputter'] = 'Loan Officer';
        
        // First Checker Stage
        if ($loanProcessConfig->requires_first_checker) {
            $firstCheckerRoles = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
            if (!empty($firstCheckerRoles)) {
                $firstCheckerNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $firstCheckerRoles);
                $stages['First Checker'] = implode(', ', $firstCheckerNames);
            } else {
                $stages['First Checker'] = 'Not Assigned';
            }
        }
        
        // Second Checker Stage
        if ($loanProcessConfig->requires_second_checker) {
            $secondCheckerRoles = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
            if (!empty($secondCheckerRoles)) {
                $secondCheckerNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $secondCheckerRoles);
                $stages['Second Checker'] = implode(', ', $secondCheckerNames);
            } else {
                $stages['Second Checker'] = 'Not Assigned';
            }
        }
        
        // Approver Stage
        if ($loanProcessConfig->requires_approver) {
            $approverRoles = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
            if (!empty($approverRoles)) {
                $approverNames = array_map(function($roleId) use ($roleNames) {
                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                }, $approverRoles);
                $stages['Approver'] = implode(', ', $approverNames);
            } else {
                $stages['Approver'] = 'Not Assigned';
            }
        }
        
        $approvalStages = $stages;
    } else {
        // Fallback stages if no config found
        $approvalStages = [];
        
        // Check if loan has exceptions
        $loanStatus = $loan->status ?? '';
        if ($loanStatus === 'PENDING-EXCEPTIONS' || $loanStatus === 'PENDING-WITH-EXCEPTIONS') {
            $approvalStages['Exception'] = 'Loan Officer';
            if ($currentStage === 'Inputter') {
                $currentStage = 'Exception';
            }
        }
        
        $approvalStages['Inputter'] = 'Loan Officer';
        $approvalStages['First Checker'] = 'Review';
        $approvalStages['Second Checker'] = 'Secondary Review';
        $approvalStages['Approver'] = 'Final Approval';
    }
    
    // Determine current stage index
    $stageKeys = array_keys($approvalStages);
    $currentStageIndex = array_search($currentStage, $stageKeys);
    if ($currentStageIndex === false) {
        $currentStageIndex = 0; // Default to first stage
    }
@endphp

