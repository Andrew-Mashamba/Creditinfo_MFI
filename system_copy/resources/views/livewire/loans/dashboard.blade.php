<style nonce="{{ csp_nonce() }}">
    .striped-table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }
    .striped-table tbody tr:nth-child(even) {
        background-color: #e5e7eb;
    }
    .striped-table tbody tr:hover {
        background-color: #f9fafb;
    }
</style>

<div class="space-y-8">

    <!-- Loan Product Overview Cards -->
    <div class="mb-8 bg-gray-50 p-6 rounded-lg">
      
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @php
                $loansByProduct = App\Models\LoansModel::select('loan_sub_product', 'principle')
                ->whereNotNull('loan_sub_product')
                ->get()
                ->groupBy('loan_sub_product');
            @endphp

            @foreach ($loansByProduct as $productId => $loans)
                @php
                    // Get product name from loan_sub_products table
                    $productName = DB::table('loan_sub_products')
                        ->where('sub_product_id', $productId)
                        ->value('sub_product_name') ?? $productId;
                    
                    // Generate consistent colors based on product ID
                    $colorIndex = crc32($productId) % 4;
                    $productColors = [
                        ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'text-blue-600'],
                        ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800', 'icon' => 'text-green-600'],
                        ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'icon' => 'text-purple-600'],
                        ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-800', 'icon' => 'text-orange-600'],
                    ];
                    $colors = $productColors[$colorIndex];
                @endphp

                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center w-full">

                           

                            <div class="w-full text-center p-2 mx-auto">
                                <span class="text-5xl font-semibold text-gray-900">{{ $loans->count() }}</span>
                            </div>

                                
                            </div>
                           
                        </div>

                        <div class="flex items-center justify-between bg-gray-100 p-2">
                        <p class="text-lg font-bold text-gray-500 mx-auto text-center">{{ $productName }}</p>
                            
                        </div>

                        

                        



                        <!-- Loan Approval Stages Table -->
                        <div class="pt-4 border-t border-gray-100">
                            
                            @php
                                // Get approval stages for this product using role-based stages
                                $approvalStages = collect();
                                
                                // Get all loan-related process configs
                                $loanProcessConfigs = DB::table('process_code_configs')
                                    ->whereIn('process_code', ['LOAN_APP', 'LOAN_DISB', 'LOAN_REST', 'LOAN_WOFF', 'LOAN_EXTEND', 'LOAN_RESCHEDULE'])
                                    ->where('is_active', true)
                                    ->get();
                                
                                if ($loanProcessConfigs->count() > 0) {
                                    // Get all unique role IDs from all loan processes
                                    $allRoleIds = [];
                                    foreach ($loanProcessConfigs as $config) {
                                        $firstCheckerRoles = json_decode($config->first_checker_roles ?? '[]', true);
                                        $secondCheckerRoles = json_decode($config->second_checker_roles ?? '[]', true);
                                        $approverRoles = json_decode($config->approver_roles ?? '[]', true);
                                        
                                        $allRoleIds = array_merge($allRoleIds, $firstCheckerRoles, $secondCheckerRoles, $approverRoles);
                                    }
                                    
                                    // Remove duplicates and get role names
                                    $allRoleIds = array_unique(array_filter($allRoleIds));
                                    $roleNames = [];
                                    if (!empty($allRoleIds)) {
                                        $roleNames = DB::table('roles')
                                            ->whereIn('id', $allRoleIds)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }
                                    
                                    // Build approval stages based on the most common loan process (LOAN_APP)
                                    $loanAppConfig = $loanProcessConfigs->where('process_code', 'LOAN_APP')->first();
                                    if (!$loanAppConfig) {
                                        $loanAppConfig = $loanProcessConfigs->first(); // Fallback to any loan process
                                    }
                                    
                                    if ($loanAppConfig) {
                                        $stages = [];
                                        
                                        // Exception Stage - Always first stage (stage 0)
                                        $stages['Exception'] = 'Loan Officer';
                                        
                                        // Inputter Stage
                                        $stages['Inputter'] = 'Loan Officer';
                                        
                                        // First Checker Stage
                                        if ($loanAppConfig->requires_first_checker) {
                                            $firstCheckerRoles = json_decode($loanAppConfig->first_checker_roles ?? '[]', true);
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
                                        if ($loanAppConfig->requires_second_checker) {
                                            $secondCheckerRoles = json_decode($loanAppConfig->second_checker_roles ?? '[]', true);
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
                                        if ($loanAppConfig->requires_approver) {
                                            $approverRoles = json_decode($loanAppConfig->approver_roles ?? '[]', true);
                                            if (!empty($approverRoles)) {
                                                $approverNames = array_map(function($roleId) use ($roleNames) {
                                                    return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
                                                }, $approverRoles);
                                                $stages['Approver'] = implode(', ', $approverNames);
                                            } else {
                                                $stages['Approver'] = 'Not Assigned';
                                            }
                                        }
                                        
                                        // Add special stages
                                        $stages['FINANCE'] = 'Finance';
                                        
                                        // Only show Auto-Approved for loan product id = 1
                                        if ($productId == 1) {
                                            $stages['Auto-Approved'] = 'System';
                                        }
                                        
                                        // Convert to collection format and add actual counts
                                        foreach ($stages as $stageName => $roleNames) {
                                            // In this context, $productId is already the loan_sub_product value (like "ONJA001")
                                            // So we can use it directly without looking up sub_product_id
                                            
                                            // Get actual count for this stage using approval_stage field and the productId directly
                                            // Map stage names to actual database values
                                            // Use the exact stage names as stored in the database
                                            $dbStageName = $stageName; // Default to original name (already correct)
                                            // Note: The database now stores stages with proper casing:
                                            // 'Inputter', 'First Checker', 'Second Checker', 'Approver', 'FINANCE'
                                            // So we don't need to map them anymore
                                            
                                            $stageCount = DB::table('loans')
                                                ->where('loan_sub_product', $productId)
                                                ->where('approval_stage', $dbStageName)
                                                ->count();
                                            
                                            // Only add stages that have loans (count > 0)
                                            if ($stageCount > 0) {
                                                $approvalStages->push((object)[
                                                    'stage_name' => $stageName,
                                                    'role_names' => $roleNames,
                                                    'count' => $stageCount
                                                ]);
                                            }
                                        }
                                    }
                                }
                                
                                // If no process config found or no stages built, fall back to loan approval stages
                                if ($approvalStages->count() == 0) {
                                    $approvalStages = DB::table('loans')
                                        ->where('loan_sub_product', $productId)
                                        ->whereNotNull('approval_stage')
                                        ->select('approval_stage as stage_name', DB::raw('count(*) as count'))
                                        ->groupBy('approval_stage')
                                        ->orderBy('approval_stage')
                                        ->get();
                                    
                                    // If still no data, fall back to status
                                    if ($approvalStages->count() == 0) {
                                        $approvalStages = DB::table('loans')
                                            ->where('loan_sub_product', $productId)
                                            ->select('status as stage_name', DB::raw('count(*) as count'))
                                            ->groupBy('status')
                                            ->orderBy('status')
                                            ->get();
                                    }
                                }
                            @endphp
                            
                            @if($approvalStages->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-xs striped-table">
                                      
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                               
                                                <th class="text-left py-1 px-2 font-medium text-gray-600">Stages</th>
                                                <th class="text-right py-1 px-2 font-medium text-gray-600">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($approvalStages as $stage)
                                                @php
                                                    $stageColors = [
                                                        'First Checker' => 'text-blue-600 bg-blue-50',
                                                        'Second Checker' => 'text-purple-600 bg-purple-50',
                                                        'Approver' => 'text-green-600 bg-green-50',
                                                    ];
                                                    $stageColor = $stageColors[$stage->stage_name] ?? 'text-gray-600 bg-gray-50';
                                                @endphp
                                                <tr>
                                                  
                                                    <td class="text-left py-1 px-2 text-xs text-gray-600 font-bold">
                                                        @php
                                                            $roleNamesArray = explode(', ', $stage->role_names ?? '');
                                                            $roleCount = count(array_filter($roleNamesArray));
                                                            // Debug: Let's see what we're working with
                                                            // echo "<!-- Debug: role_names='{$stage->role_names}', roleCount={$roleCount} -->";
                                                        @endphp
                                                        @if($stage->stage_name === 'Exception')
                                                            Exceptions
                                                        @elseif($roleCount > 1)
                                                            <span class="cursor-help" title="{{ $stage->role_names ?? 'N/A' }}">
                                                                Loan Committee
                                                            </span>
                                                        @else
                                                            {{ $stage->role_names ?? 'N/A' }}
                                                        @endif
                                                    </td>

                                                    <td class="text-right py-1 px-2 text-xs text-gray-600 font-bold">
                                                        {{ $stage->count }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-2">
                                    <p class="text-xs text-gray-500">No approval data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Restructure Loans Card --}}
            @php
                $restructureLoans = App\Models\LoansModel::whereIn('loan_type_2', ['Restructure', 'Restructuring'])
                    ->whereNull('loan_type_3')
                    ->get();
                
                $restructureCount = $restructureLoans->count();
                $restructureAmount = $restructureLoans->sum('principle');
            @endphp

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center w-full">
                            <div class="w-full text-center p-2 mx-auto">
                                <span class="text-5xl font-semibold text-gray-900">{{ $restructureCount }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-gray-100 p-2">
                        <p class="text-lg font-bold text-gray-500 mx-auto text-center">LOAN RESTRUCTURING</p>
                    </div>

                    {{-- Restructure Loans Stages Table --}}
                    <div class="pt-4 border-t border-gray-100">
                        @php
                            // Get restructure loans by approval stage
                            // Define display stages with their role names (matching the proper flow)
                            $displayStages = [
                                'Exception' => 'Exceptions',
                                'Inputter' => 'Loan Officer',
                                'First Checker' => 'Loan Committee',
                                'Second Checker' => 'System Administrator',
                                'Approver' => 'System Administrator',
                                'FINANCE' => 'Finance'
                            ];
                            
                            $restructureApprovalStages = collect();
                            
                            foreach ($displayStages as $stageKey => $displayName) {
                                $stageCount = App\Models\LoansModel::whereIn('loan_type_2', ['Restructure', 'Restructuring'])
                                    ->whereNull('loan_type_3')
                                    ->where('approval_stage', $stageKey)
                                    ->count();
                                
                                // Only add stages that have loans (count > 0)
                                if ($stageCount > 0) {
                                    $restructureApprovalStages->push((object)[
                                        'stage_name' => $stageKey,
                                        'display_name' => $displayName,
                                        'count' => $stageCount
                                    ]);
                                }
                            }
                        @endphp
                        
                        @if($restructureApprovalStages->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs striped-table">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-1 px-2 font-medium text-gray-600">Stages</th>
                                            <th class="text-right py-1 px-2 font-medium text-gray-600">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($restructureApprovalStages as $stage)
                                            <tr>
                                                <td class="text-left py-1 px-2 text-xs text-gray-600 font-bold">
                                                    {{ $stage->display_name }}
                                                </td>
                                                <td class="text-right py-1 px-2 text-xs text-gray-600 font-bold">
                                                    {{ $stage->count }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-2">
                                <p class="text-xs text-gray-500">No approval data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
