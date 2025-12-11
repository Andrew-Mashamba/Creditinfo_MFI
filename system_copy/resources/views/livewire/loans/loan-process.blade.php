<div class="min-h-screen bg-gray-50">
    {{-- Error Alert --}}
    @if($errorMessage)
        <div class="fixed top-4 right-4 z-50 max-w-md">
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-lg" role="alert">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-red-800 text-sm font-medium">{{ $errorMessage }}</span>
                    <button wire:click="$set('errorMessage', '')" class="ml-auto text-red-400 hover:text-red-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Success Alert --}}
    @if($successMessage)
        <div class="fixed top-4 right-4 z-50 max-w-md">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-lg" role="alert">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-green-800 text-sm font-medium">{{ $successMessage }}</span>
                    <button wire:click="$set('successMessage', '')" class="ml-auto text-green-400 hover:text-green-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Container --}}
    <div class="p-4">
        {{-- Header Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    {{-- Client Info --}}
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-900 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">
                                @if($client)
                                    {{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                @else
                                    Loading Client...
                                @endif
                            </h1>
                            <p class="text-sm text-gray-500">
                                Client #{{ Session::get('currentloanClient') }}
                            </p>
                        </div>
                    </div>

                    {{-- Loan Status --}}
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-500">Loan Status</p>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                    @if($loanStatus === 'AWAITING_DISBURSEMENT') bg-green-100 text-green-800
                                    @elseif($loanStatus === 'ONPROGRESS') bg-yellow-100 text-yellow-800
                                    @elseif($loanStatus === 'REJECTED') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $loanStatus }}
                                </span>
                            </div>
                        </div>

                        {{-- Close Button --}}
                        <button wire:click="close" 
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            title="Close Loan Process">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

{{-- Progress Bar --}}
<div class="px-6 py-4">

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
        
        // Build stages array - Always show all stages
        $stages = [];
        
        // Check if loan initially had exceptions using the new tracking fields
        // has_exceptions flag should remain true even after clearing for historical tracking
        $hasInitialExceptions = $loan->has_exceptions || !empty($loan->exception_tracking_id);
        
        // Also check current status for immediate exceptions
        $loanStatus = $loan->status ?? '';
        $hasCurrentExceptions = $loanStatus === 'PENDING-EXCEPTIONS' || $loanStatus === 'PENDING-WITH-EXCEPTIONS';
        
        // Show Exception stage if loan had exceptions initially (for workflow visibility)
        // The has_exceptions flag is permanent for tracking purposes
        if ($hasInitialExceptions) {
            // Add Stage 0 - Exception
            $stages['Exception'] = 'Loan Officer';
            
            // If current stage is still 'Inputter' but loan has exceptions, set to Exception
            if ($currentStage === 'Inputter' && $hasCurrentExceptions) {
                $currentStage = 'Exception';
            }
        }
        
        // Inputter Stage (Stage 1 for normal loans, Stage 2 for exception loans)
        $stages['Inputter'] = 'Loan Officer';
        
        // First Checker Stage - Always show
        $firstCheckerRoles = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
        if (!empty($firstCheckerRoles)) {
            $firstCheckerNames = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $firstCheckerRoles);
            $stages['First Checker'] = implode(', ', $firstCheckerNames);
        } else {
            $stages['First Checker'] = 'Not Assigned';
        }
        
        // Second Checker Stage - Always show
        $secondCheckerRoles = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
        if (!empty($secondCheckerRoles)) {
            $secondCheckerNames = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $secondCheckerRoles);
            $stages['Second Checker'] = implode(', ', $secondCheckerNames);
        } else {
            $stages['Second Checker'] = 'Not Assigned';
        }
        
        // Approver Stage - Always show
        $approverRoles = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
        if (!empty($approverRoles)) {
            $approverNames = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $approverRoles);
            $stages['Approver'] = implode(', ', $approverNames);
        } else {
            $stages['Approver'] = 'Not Assigned';
        }
        
        $approvalStages = $stages;
    } else {
        // Fallback stages if no config found - Always show all stages
        $approvalStages = [];
        
        // Check if loan initially had exceptions using the new tracking fields
        // has_exceptions flag should remain true even after clearing for historical tracking
        $hasInitialExceptions = $loan->has_exceptions || !empty($loan->exception_tracking_id);
        
        // Also check current status for immediate exceptions
        $loanStatus = $loan->status ?? '';
        $hasCurrentExceptions = $loanStatus === 'PENDING-EXCEPTIONS' || $loanStatus === 'PENDING-WITH-EXCEPTIONS';
        
        // Show Exception stage if loan had exceptions initially (for workflow visibility)
        // The has_exceptions flag is permanent for tracking purposes
        if ($hasInitialExceptions) {
            $approvalStages['Exception'] = 'Loan Officer';
            if ($currentStage === 'Inputter' && $hasCurrentExceptions) {
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
    
    // Special handling for FINANCE stage - show Approver as completed
    if ($currentStage === 'FINANCE') {
        // When loan is at FINANCE stage, Approver should show as completed
        $currentStageIndex = count($stageKeys); // Set to beyond last stage
    } else {
        $currentStageIndex = array_search($currentStage, $stageKeys);
        if ($currentStageIndex === false) {
            $currentStageIndex = 0; // Default to first stage
        }
    }
@endphp

<ol class="flex justify-between items-center w-full relative z-10 px-4 mt-4">
    @foreach($approvalStages as $stageName => $roleNames)
        @php
            $stageIndex = array_search($stageName, array_keys($approvalStages));
            $isCompleted = $stageIndex < $currentStageIndex;
            $isCurrent = $stageIndex == $currentStageIndex;
            $isPending = $stageIndex > $currentStageIndex;

            // Check if this is the Exception stage
            $isExceptionStage = ($stageName === 'Exception');
            
            // Stage numbering
            $stageNumber = $isExceptionStage ? 0 : $stageIndex;
            if (!$isExceptionStage && isset($approvalStages['Exception'])) {
                $stageNumber = $stageIndex;
            }
            
            // Styling logic - Simplified and consistent
            if ($isCompleted) {
                // Completed stages: Green circle with white checkmark
                $circleContent = '<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                $circleBg = 'bg-green-600';
                $textColor = 'text-green-700 font-medium';
            } elseif ($isCurrent) {
                // Current stage: Blue circle with white number
                if ($isExceptionStage) {
                    $circleContent = '<svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    $circleBg = 'bg-blue-900';
                    $textColor = 'text-blue-700 font-bold';
                } else {
                    $circleContent = '<span class="text-white text-sm font-semibold">'.$stageNumber.'</span>';
                    $circleBg = 'bg-blue-900';
                    $textColor = 'text-blue-700 font-bold';
                }
            } else {
                // Pending stages: Gray circle with gray number
                if ($isExceptionStage) {
                    $circleContent = '<span class="text-gray-400 text-sm font-semibold">0</span>';
                    $circleBg = 'bg-white border-2 border-gray-300';
                    $textColor = 'text-gray-400';
                } else {
                    $circleContent = '<span class="text-gray-400 text-sm font-semibold">'.$stageNumber.'</span>';
                    $circleBg = 'bg-white border-2 border-gray-300';
                    $textColor = 'text-gray-400';
                }
            }

            $roleNamesArray = explode(', ', $roleNames);
            $roleCount = count(array_filter($roleNamesArray));
        @endphp

        <li class="flex-1 flex flex-col items-center relative z-10">
            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $circleBg }}">
                {!! $circleContent !!}
            </div>
            <span class="mt-2 text-sm font-medium {{ $textColor }}">
                @if($isExceptionStage)
                    <span class="cursor-help" title="Clear all exceptions before proceeding to approval workflow">
                        Exception
                    </span>
                @elseif($roleCount > 1)
                    <span class="cursor-help" title="{{ $roleNames }}">
                        Loan Committee
                    </span>
                @else
                    {{ $roleNames }}
                @endif
            </span>
        </li>
    @endforeach

    {{-- Line behind steps --}}
    <div class="absolute top-4 left-0 w-full h-0.5 bg-gray-200 z-0"></div>
</ol>

</div>



        </div>

        {{-- Tab Navigation --}}
        <div id="newTabs" class="bg-white rounded-t-2xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                    @foreach($tabs as $tabKey => $tabConfig)
                        @php $status = $this->getTabStatus($tabKey) @endphp
                        <li class="me-2" role="presentation">
                        <button wire:click="showTab('{{ $tabKey }}')"
    class="inline-block p-4 border-b-2 transition-all duration-200
        @if($status === 'active') text-blue-900 border-blue-900
        @else text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 @endif"
    role="tab" 
    aria-controls="{{ $tabKey }}-tab" 
    aria-selected="{{ $status === 'active' ? 'true' : 'false' }}">
    {{ $tabConfig['label'] }}
</button>

                        </li>
                    @endforeach
                    
                    {{-- Credit Score Tab --}}
                    <li class="me-2" role="presentation">
                        <button wire:click="showTab('creditScore')"
                            class="inline-block p-4 border-b-2 transition-all duration-200
                                @if($activeTab === 'creditScore') text-blue-900 border-blue-900
                                @else text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 @endif"
                            role="tab" 
                            aria-controls="creditScore-tab" 
                            aria-selected="{{ $activeTab === 'creditScore' ? 'true' : 'false' }}">
                            Credit Score
                        </button>
                    </li>
                    
                    {{-- Lending Framework Tab --}}
                    <li class="me-2" role="presentation">
                        <button wire:click="showTab('lendingFramework')"
                            class="inline-block p-4 border-b-2 transition-all duration-200
                                @if($activeTab === 'lendingFramework') text-blue-900 border-blue-900
                                @else text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 @endif"
                            role="tab" 
                            aria-controls="lendingFramework-tab" 
                            aria-selected="{{ $activeTab === 'lendingFramework' ? 'true' : 'false' }}">
                            Lending Framework
                        </button>
                    </li>
                    
                    {{-- Approvals Tab --}}
                    <li class="me-2" role="presentation">
                        <button wire:click="showTab('approvals')"
                            class="inline-block p-4 border-b-2 transition-all duration-200
                                @if($activeTab === 'approvals') text-blue-900 border-blue-900
                                @else text-gray-500 border-transparent hover:text-gray-600 hover:border-gray-300 @endif"
                            role="tab" 
                            aria-controls="approvals-tab" 
                            aria-selected="{{ $activeTab === 'approvals' ? 'true' : 'false' }}">
                            <div class="flex items-center space-x-2">
                                <span>Approvals</span>
                                
                            </div>
                        </button>
                    </li>
                </ul>
            </div>
       
            {{-- Loading State --}}
            @if($isLoading)
                <div class="flex items-center justify-center py-12">
                    <div class="flex items-center space-x-3">
                        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="text-gray-600 font-medium">Loading...</span>
                    </div>
                </div>
            @else
                {{-- Tab Content --}}
                <div class="p-6">
                    @if ($activeTab === 'client')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Client Information</h2>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">View and review member information</span>
                                </div>
                            </div>
                            <livewire:loans.client-info />
                        </div>
                    @endif

                    @if ($activeTab === 'guarantor')
                        <div class="space-y-4">
                            
                            <livewire:loans.guarantos />
                      
                        </div>
                    @endif

                    @if ($activeTab === 'addDocument')
                        <div class="space-y-4">
                           
                            <livewire:loans.add-document />
                        </div>
                    @endif

                    @if ($activeTab === 'assessment')
                        <div class="space-y-4">
                           
                            <livewire:loans.assessment />
                        </div>
                    @endif

                    @if ($activeTab === 'accounting')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Accounting Information</h2>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Review loan accounting details</span>
                                </div>
                            </div>
                            @livewire('loans.sections.accounting')
                        </div>
                    @endif

                    @if ($activeTab === 'creditScore')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Credit Score Analysis</h2>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">Automated credit assessment</span>
                                </div>
                            </div>
                            <!-- Personal Details -->
                    <div class="space-y-4">
                       
                       @livewire('loans.credit-score')
                       
                   </div>
                        </div>
                    @endif

                    @if ($activeTab === 'lendingFramework')
                        <div class="space-y-4">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-900">Lending Framework</h2>
                                
                            </div>
                            
                          
                            @livewire('loans.sections.lending-framework')
                            
                          
                        </div>
                    @endif
                    
                    @if ($activeTab === 'approvals')
                        <div class="space-y-4">

                     

                            <div class="flex items-center justify-between mb-6">

                            
                              
                                <div class="flex items-center space-x-2">
                                    @if($loan && $loan->status === 'APPROVED')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Approved
                                        </span>
                                    @elseif($loan && $loan->status === 'REJECTED')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                            Rejected
                                        </span>
                                    @else
                                      
                                    @endif
                                </div>
                            </div>
                            
                         
                            
                            {{-- Approval Actions Section --}}
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                @include('livewire.loans.partials.approval-actions')
                            </div>
                            
                    
                            {{-- Approval Confirmation Modal is now handled in approval-actions.blade.php --}}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
