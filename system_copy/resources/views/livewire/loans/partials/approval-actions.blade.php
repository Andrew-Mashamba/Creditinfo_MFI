{{-- Approval Actions --}}
@php
    $currentStage = $loan->approval_stage ?? 'Inputter';
    $loanStatus = $loan->status ?? '';
    $isExceptionStage = ($currentStage === 'Exception');
    $canTakeAction = true; // You may want to check user permissions here
    
    // Get loan process configuration to determine available stages and roles
    $loanProcessConfig = DB::table('process_code_configs')
        ->where('process_code', 'LOAN_APP')
        ->where('is_active', true)
        ->first();
    
    // Build dynamic stage progression based on configuration
    $stageSequence = [];
    $stageRoles = []; // Store roles for each stage
    
    // Get role names for display
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
    }
    
    // Check if loan should have Exception stage
    // Include loans that have exception tracking or are currently at Exception stage
    $hasExceptionStage = ($loanStatus === 'PENDING-EXCEPTIONS' || 
                         $loanStatus === 'PENDING-WITH-EXCEPTIONS' ||
                         $loan->has_exceptions || 
                         !empty($loan->exception_tracking_id) ||
                         $currentStage === 'Exception');
    if ($hasExceptionStage) {
        $stageSequence[] = 'Exception';
        $stageRoles['Exception'] = ['Loan Officer'];
    }
    
    // Always have Inputter stage
    $stageSequence[] = 'Inputter';
    $stageRoles['Inputter'] = ['Loan Officer'];
    
    // Add configured stages with their roles
    if ($loanProcessConfig) {
        if ($loanProcessConfig->requires_first_checker) {
            $stageSequence[] = 'First Checker';
            $firstCheckerRoleIds = json_decode($loanProcessConfig->first_checker_roles ?? '[]', true);
            $stageRoles['First Checker'] = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $firstCheckerRoleIds);
        }
        if ($loanProcessConfig->requires_second_checker) {
            $stageSequence[] = 'Second Checker';
            $secondCheckerRoleIds = json_decode($loanProcessConfig->second_checker_roles ?? '[]', true);
            $stageRoles['Second Checker'] = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $secondCheckerRoleIds);
        }
        if ($loanProcessConfig->requires_approver) {
            $stageSequence[] = 'Approver';
            $approverRoleIds = json_decode($loanProcessConfig->approver_roles ?? '[]', true);
            $stageRoles['Approver'] = array_map(function($roleId) use ($roleNames) {
                return $roleNames[$roleId] ?? 'Role ID: ' . $roleId;
            }, $approverRoleIds);
        }
    } else {
        // Fallback if no config
        $stageSequence[] = 'First Checker';
        $stageSequence[] = 'Second Checker';
        $stageSequence[] = 'Approver';
        $stageRoles['First Checker'] = ['Reviewer'];
        $stageRoles['Second Checker'] = ['Senior Reviewer'];
        $stageRoles['Approver'] = ['Final Approver'];
    }
    
    // Find current stage index
    $currentStageIndex = array_search($currentStage, $stageSequence);
    
    // Determine next and previous stages
    $nextStage = null;
    $prevStage = null;
    $nextStageRoles = [];
    
    if ($currentStageIndex !== false) {
        // Get next stage if not at the end
        if ($currentStageIndex < count($stageSequence) - 1) {
            $nextStage = $stageSequence[$currentStageIndex + 1];
            $nextStageRoles = $stageRoles[$nextStage] ?? [];
        }
        
        // Get previous stage if not at the beginning
        if ($currentStageIndex > 0) {
            $prevStage = $stageSequence[$currentStageIndex - 1];
        }
    }
    
    // Get current stage roles
    $currentStageRoles = $stageRoles[$currentStage] ?? [];
    $isLoanCommittee = count($currentStageRoles) > 1;
    
    // Special case: If loan was at Inputter but should have Exception
    if ($currentStage === 'Inputter' && $hasExceptionStage && !in_array('Exception', $stageSequence)) {
        // This loan needs exception clearing but is at Inputter
        $prevStage = 'Exception';
    }
    
    // Ensure all checker stages always have a previous stage
    if (in_array($currentStage, ['First Checker', 'Second Checker', 'Third Checker']) && !$prevStage) {
        // Find the appropriate previous stage
        if ($currentStage === 'First Checker') {
            $prevStage = 'Inputter';
        } elseif ($currentStage === 'Second Checker') {
            $prevStage = 'First Checker';
        } elseif ($currentStage === 'Third Checker') {
            $prevStage = 'Second Checker';
        }
    }
    
    // Debug information (remove in production)
    // Log::info('Approval actions stage info', [
    //     'current_stage' => $currentStage,
    //     'stage_sequence' => $stageSequence,
    //     'current_stage_index' => $currentStageIndex,
    //     'prev_stage' => $prevStage,
    //     'next_stage' => $nextStage,
    //     'has_exception_stage' => $hasExceptionStage
    // ]);
@endphp

<div class="space-y-4">
    {{-- Current Stage Information --}}


    @if($isExceptionStage)
        {{-- Exception Actions --}}
        <div class="ml-3 flex-1">
                 
                    {{-- Policy Adherence Checkbox --}}
                    <div class="mt-4 bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="policyAdherenceConfirmed" 
                                    id="policy-adherence-exception" 
                                    type="checkbox" 
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="policy-adherence-exception" class="font-medium text-gray-700">
                                    Governance Policy
                                </label>
                                <p class="text-gray-500">
                                    I attest that the applicant meets all related Governance requirements in line with the Product
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    <div class="mt-4 flex space-x-3">
                        <button wire:click="clearExceptions" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ (!$policyAdherenceConfirmed || !$approvalComment) ? 'disabled' : '' }}>
                           
                            Recommendation for approval
                        </button>
                        
                      
                    </div>

                    {{-- Add Comment Section --}}
                    <div class="mt-4 bg-white rounded-lg p-4 border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Recommendation for approval <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model.debounce.500ms="approvalComment"
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Add any comments for clearing exceptions..."></textarea>
                    </div>

                </div>
    @elseif($loan->status !== 'APPROVED' && $loan->status !== 'REJECTED')
        {{-- Normal Stage Actions (only show if not already approved/rejected) --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="space-y-4">
                {{-- Policy Adherence Checkbox --}}
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input wire:model="policyAdherenceConfirmed" 
                                id="policy-adherence-main" 
                                type="checkbox" 
                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="policy-adherence-main" class="font-medium text-gray-700">
                            Governance Policy
                            </label>
                            <p class="text-gray-500">
                            I attest that the applicant meets all related Governance requirements in line with the Product
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Committee Minutes Upload (if Loan Committee) --}}
                @if($isLoanCommittee)
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Loan Committee Minutes with Approvals <span class="text-red-500">*</span>
                                </label>
                               
                            </div>
                            
                            @if($committeeMinutesPath)
                                <div class="flex items-center justify-between bg-green-50 p-3 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm text-green-800">Committee minutes uploaded</span>
                                    </div>
                                    <button wire:click="removeCommitteeMinutes" 
                                        class="text-sm text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                </div>
                            @else
                                <div class="mt-1">
                                    <input wire:model="committeeMinutesFile" 
                                        type="file" 
                                        accept=".pdf,.doc,.docx"
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-blue-50 file:text-blue-700
                                            hover:file:bg-blue-100">
                                    @error('committeeMinutesFile')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between">
                    <div class="flex space-x-3">
                        @if($nextStage)
                            <button wire:click="validateAndMoveToStage('{{ $nextStage }}')" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ (!$policyAdherenceConfirmed || ($isLoanCommittee && !$committeeMinutesPath)) ? 'disabled' : '' }}>
                               
                                Recommendation for approval
                            </button>
                            
                            @if($prevStage)
                                <button wire:click="moveToStage('{{ $prevStage }}')" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                  
                                    Return to Sender
                                </button>
                            @endif
                            
                           
                            <button wire:click="declineLoan" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Decline
                            </button>
                        @elseif($currentStage === 'Approver')
                            {{-- Final Approval Actions with Disbursement --}}
                            @if($loan->status === 'APPROVED' && $loan->disbursement_status !== 'DISBURSED')
                                {{-- Disbursement Section for Approved Loans --}}
                                @php
                                    // Get bank accounts for disbursement
                                    $bankAccounts = DB::table('bank_accounts')
                                        ->where('status', 'ACTIVE')
                                        ->orderBy('bank_name', 'asc')
                                        ->get();
                                    
                                    // Calculate disbursement amount
                                    $netDisbursementAmount = $loan->approved_loan_value ?? ($loan->principle ?? 0);
                                    $totalCharges = $loan->total_charges ?? 0;
                                    $totalInsurance = $loan->total_insurance ?? 0;
                                    $firstInterestAmount = $loan->future_interest ?? 0;
                                    $topUpAmount = 0;
                                    
                                    if (in_array($loan->loan_type_2 ?? '', ['Top-up', 'TopUp', 'Top Up'])) {
                                        if (isset($loan->top_up_amount) && $loan->top_up_amount > 0) {
                                            $topUpAmount = abs($loan->top_up_amount);
                                        }
                                    }
                                    
                                    $totalDeductions = $totalCharges + $totalInsurance + $firstInterestAmount + $topUpAmount;
                                    $netDisbursementAmount = $netDisbursementAmount - $totalDeductions;
                                    
                                    $selectedAccount = null;
                                    if (isset($this->selectedBankAccount) && $this->selectedBankAccount) {
                                        $selectedAccount = $bankAccounts->firstWhere('internal_mirror_account_number', $this->selectedBankAccount);
                                    }
                                    
                                    $hasInsufficientFunds = $selectedAccount && $selectedAccount->current_balance < $netDisbursementAmount;
                                    $canDisburse = isset($this->selectedBankAccount) && $this->selectedBankAccount && !$hasInsufficientFunds;
                                @endphp
                                
                                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-3">Disbursement Information</h4>
                                    
                                    {{-- Disbursement Summary --}}
                                    <div class="grid grid-cols-2 gap-4 mb-3 text-sm">
                                        <div>
                                            <span class="text-blue-600">Approved Amount:</span>
                                            <span class="font-semibold text-blue-900 ml-2">{{ number_format($loan->approved_loan_value ?? 0, 2) }} TZS</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-600">Total Deductions:</span>
                                            <span class="font-semibold text-red-600 ml-2">-{{ number_format($totalDeductions, 2) }} TZS</span>
                                        </div>
                                        <div class="col-span-2 border-t border-blue-200 pt-2">
                                            <span class="text-blue-700 font-semibold">Net Disbursement:</span>
                                            <span class="font-bold text-green-600 text-lg ml-2">{{ number_format($netDisbursementAmount, 2) }} TZS</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Bank Account Selection --}}
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-blue-700 mb-1">Select Disbursement Account</label>
                                        <select wire:model="selectedBankAccount" 
                                                class="w-full px-3 py-2 border border-blue-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                            <option value="">Choose a bank account...</option>
                                            @foreach($bankAccounts as $account)
                                                <option value="{{ $account->internal_mirror_account_number }}">
                                                    {{ $account->bank_name }} - {{ $account->account_name }} : {{ number_format($account->current_balance, 2) }} TZS
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    {{-- Balance Check Alert --}}
                                    @if($selectedAccount)
                                        @if($hasInsufficientFunds)
                                            <div class="p-2 bg-red-100 border border-red-300 rounded text-sm">
                                                <div class="flex items-center text-red-800">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="font-semibold">Insufficient Funds - Shortfall: {{ number_format($netDisbursementAmount - $selectedAccount->current_balance, 2) }} TZS</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-2 bg-green-100 border border-green-300 rounded text-sm">
                                                <div class="flex items-center text-green-800">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="font-semibold">Sufficient Funds - Ready for Disbursement</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                
                                {{-- Disbursement Button --}}
                                <button wire:click="disburseLoan" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all
                                           {{ $canDisburse ? 'bg-blue-900 hover:bg-blue-800 focus:ring-blue-500' : 'bg-gray-400 cursor-not-allowed' }}"
                                    {{ !$canDisburse ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Disburse Loan
                                </button>
                            @else
                                {{-- Standard Approval Button --}}
                                <button wire:click="confirmApproval()" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ (!$policyAdherenceConfirmed || ($isLoanCommittee && !$committeeMinutesPath)) ? 'disabled' : '' }}>
                                   
                                    Approve
                                </button>
                            @endif
                            
                            @if($prevStage)
                                <button wire:click="moveToStage('{{ $prevStage }}')" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                 
                                    Return to Sender
                                </button>
                            @endif
                            
                            <button wire:click="rejectLoan" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                              
                                Reject
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Add Comment Section --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Recommendation for approval
                    </label>
                    <textarea wire:model.debounce.500ms="approvalComment"
                        rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Add any comments for this action...">{{ $approvalComment ?: 'Approved' }}</textarea>
                </div>

                {{-- Validation Messages --}}
                @if(!$policyAdherenceConfirmed || ($isLoanCommittee && !$committeeMinutesPath))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium">Required actions</p>
                                <ul class="mt-1 list-disc list-inside">
                                    @if(!$policyAdherenceConfirmed)
                                        <li>Confirm adherence to lending policies</li>
                                    @endif
                                    @if($isLoanCommittee && !$committeeMinutesPath)
                                        <li>Upload Loan Committee minutes</li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    {{-- Status Messages --}}
    @if($loan->status === 'APPROVED')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">This loan has been approved</p>
                </div>
            </div>
        </div>
    @elseif($loan->status === 'REJECTED')
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">This loan has been rejected</p>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Approval Confirmation Modal --}}
    @if($showApprovalModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div wire:click="closeApprovalModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                {{-- Center modal --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Confirm Loan Approval
                                </h3>
                                <div class="mt-4 space-y-4">
                                    {{-- Policy Adherence Checkbox (Modal) --}}
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input wire:model="policyAdherenceConfirmed" 
                                                id="policy-adherence-modal" 
                                                type="checkbox" 
                                                class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded"
                                                required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy-adherence-modal" class="font-medium text-gray-700">
                                                I confirm adherence to all lending policies
                                            </label>
                                            <p class="text-gray-500">By checking this box, you confirm that this loan approval complies with all institutional lending policies and procedures.</p>
                                        </div>
                                    </div>

                                    {{-- Check for Committee Minutes if Required --}}
                                    @if($isLoanCommittee && !$committeeMinutesPath)
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                            <p class="text-sm text-red-800">
                                                <span class="font-medium">Committee minutes required:</span> Please upload the Loan Committee minutes before approving.
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Approval Comment --}}
                                    <div>
                                        <label for="approval-comment" class="block text-sm font-medium text-gray-700">
                                            Approval Comment
                                        </label>
                                        <div class="mt-1">
                                            <textarea wire:model.debounce.500ms="approvalComment"
                                                id="approval-comment"
                                                rows="3"
                                                class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                placeholder="Enter approval comment...">Approved</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="confirmApproval" 
                            type="button" 
                            {{ (!$policyAdherenceConfirmed || ($isLoanCommittee && !$committeeMinutesPath)) ? 'disabled' : '' }}
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirm Approval
                        </button>
                        <button wire:click="closeApprovalModal" 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>