@php
    use Carbon\Carbon;
@endphp

<div class="w-full mx-auto">
  

    
    <!-- Progress Bar -->
    <div class="m-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold
                            {{ $currentStep >= $i ? 'bg-blue-900' : 'bg-gray-300' }}
                            {{ $currentStep === $i ? 'ring-4 ring-blue-200' : '' }}
                            transition-all duration-300">
                            {{ $i }}
                        </div>
                        <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs font-medium whitespace-nowrap
                            {{ $currentStep >= $i ? 'text-blue-600' : 'text-gray-500' }}">
                            @if($i === 1) {{ $loanType }} Details
                            @elseif($i === 2) Guarantor And Pledged Collateral
                            @elseif($i === 3) Application Summary
                            @elseif($i === 4) Documents
                            @else Review & Submit
                            @endif
                        </span>
                    </div>
                    @if($i < $totalSteps)
                        <div class="flex-1 h-1 mx-3 rounded-full overflow-hidden bg-gray-200">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 transition-all duration-500"
                                 style="width: {{ $currentStep > $i ? '100%' : '0%' }}"></div>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>



    <!-- Form Content -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mt-12" id="loan-application-container">
        <!-- Global Error Messages -->
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-0">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800 font-medium">
                            Please correct the following errors to continue:
                        </p>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Debug Messages -->
        @if(session()->has('debug'))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-0">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800">
                            Debug: {{ session('debug') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Messages -->
        @if(session()->has('success') && $showSuccessMessage)
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-0">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button wire:click="hideSuccessMessage" type="button" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">
                                <span class="sr-only">Dismiss</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if(session()->has('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-0">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="submitApplication" novalidate>
            <!-- Form Content Container -->
            <div class="form-content-container">

            

            <!-- Step 1: Loan Details - Improved UI -->
            <div class="{{ $currentStep === 1 ? 'block' : 'hidden' }}">
                <div class="bg-gradient-to-r from-blue-900 to-indigo-800 p-4">
                    <h2 class="text-lg font-bold text-white">{{ $loanType }} Loan Application</h2>
                    <p class="text-gray-500 text-xs">Complete all required fields to proceed</p>
                </div>
                
                <div class="p-4 space-y-3">
                    <!-- Primary Section: Loan Type & Product - Grid of 3 -->
                    <div class="bg-white rounded-lg p-3">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Column 1: Loan Type -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Loan Type <span class="text-red-500">*</span>
                                </label>
                                <select wire:model="loanType"
                                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                        {{ (in_array($loanType, ['Top-up', 'Restructure', 'Restructuring']) && ($selectedLoanForTopUp || $selectedLoanForRestructure)) || ($loanType === 'New' && $isLoanTypeLocked) ? 'bg-gray-50' : '' }}
                                        {{ $errors->has('loanType') ? 'border-red-500' : '' }}"
                                        {{ (in_array($loanType, ['Top-up', 'Restructure', 'Restructuring']) && ($selectedLoanForTopUp || $selectedLoanForRestructure)) || ($loanType === 'New' && $isLoanTypeLocked) ? 'disabled' : '' }}>
                                    <option value="New">New Loan</option>
                                    <option value="Top-up">Top-up Loan</option>
                                    <option value="Restructure">Restructure Loan</option>
                                    <option value="Takeover">Takeover Loan</option>
                                </select>
                                @error('loanType') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Column 2: Selected Loan (conditional) -->
                            <div>
                                @if($loanType === 'Top-up')
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Select Loan to Top-up <span class="text-red-500">*</span>
                                    </label>
                                    @if(!$selectedLoanForTopUp)
                                        <select wire:model="selectedLoanForTopUp" 
                                                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select loan...</option>
                                            @foreach($this->existingLoansForSelection as $loan)
                                                @if($loan['owner'] === 'current_user')
                                                    <option value="{{ $loan['id'] }}">{{ $loan['display_text'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        @php
                                            $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $selectedLoanForTopUp);
                                        @endphp
                                        @if($selectedLoan)
                                            <div class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-900">{{ $selectedLoan['display_text'] }}</span>
                                                    <button type="button" wire:click="resetLoanSelection" 
                                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                        Change
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($loanType === 'Restructure' || $loanType === 'Restructuring')
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Selected Loan <span class="text-red-500">*</span>
                                    </label>
                                    @if(!$selectedLoanForRestructure)
                                        <select wire:model="selectedLoanForRestructure" 
                                                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select loan...</option>
                                            @foreach($this->existingLoansForSelection as $loan)
                                                @if($loan['owner'] === 'current_user')
                                                    <option value="{{ $loan['id'] }}">{{ $loan['display_text'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        @php
                                            $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $selectedLoanForRestructure);
                                        @endphp
                                        @if($selectedLoan)
                                            <div class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-900">{{ $selectedLoan['display_text'] }}</span>
                                                    <button type="button" wire:click="resetLoanSelection" 
                                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                        Change
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($loanType === 'Takeover')
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Select Loan to Takeover <span class="text-red-500">*</span>
                                    </label>
                                    @if(!$selectedLoanForTakeover)
                                        <select wire:model="selectedLoanForTakeover" 
                                                class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select loan...</option>
                                            @foreach($this->existingLoansForSelection as $loan)
                                                @if($loan['owner'] === 'other_member')
                                                    <option value="{{ $loan['id'] }}">{{ $loan['display_text'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        @php
                                            $selectedLoan = collect($this->existingLoansForSelection)->firstWhere('id', $selectedLoanForTakeover);
                                        @endphp
                                        @if($selectedLoan)
                                            <div class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-900">{{ $selectedLoan['display_text'] }}</span>
                                                    <button type="button" wire:click="resetLoanSelection" 
                                                            class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                        Change
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    <div></div>
                                @endif
                            </div>

                            <!-- Column 3: Loan Product -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Loan Product <span class="text-red-500">*</span>
                                </label>
                                @if($hasLoanProducts)
                                    <select wire:model="selectedProductId" 
                                            wire:key="product-select-{{ $selectedProductId }}"
                                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                            {{ (in_array($loanType, ['Top-up', 'Restructuring', 'Restructure'])) || ($loanType === 'New' && $isLoanTypeLocked) ? 'bg-gray-50' : '' }}
                                            {{ $errors->has('selectedProductId') ? 'border-red-500' : '' }}"
                                            {{ (in_array($loanType, ['Top-up', 'Restructuring', 'Restructure'])) || ($loanType === 'New' && $isLoanTypeLocked) ? 'disabled' : '' }}>
                                        <option value="">Select product...</option>
                                        @foreach($this->loanProducts as $product)
                                            @php
                                                $productId = is_array($product) ? $product['id'] : $product->id;
                                                $productName = is_array($product) ? $product['sub_product_name'] : $product->sub_product_name;
                                                $interestRate = is_array($product) ? ($product['interest_value'] ?? '') : ($product->interest_value ?? '');
                                            @endphp
                                            <option value="{{ $productId }}">
                                                {{ $productName }} @if($interestRate)({{ $interestRate }}%)@endif
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="bg-yellow-50 border border-yellow-200 rounded p-1">
                                        <p class="text-xs text-yellow-800">No products available</p>
                                    </div>
                                @endif
                                @error('selectedProductId') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Loan Details Section - Grid of 3 -->
                    <div class="bg-white rounded-lg p-3">
                     
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Loan Amount -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Amount (TZS) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-gray-500 text-sm">TZS</span>
                                    <input type="number" wire:model.lazy="loanAmount"
                                        class="w-full pl-10 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                        {{ $errors->has('loanAmount') ? 'border-red-500' : '' }}
                                        {{ (in_array($loanType, ['Restructure', 'Restructuring']) && $isRestructureLoanAmountDisabled) ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                        placeholder="0.00"
                                        {{ (in_array($loanType, ['Restructure', 'Restructuring']) && $isRestructureLoanAmountDisabled) ? 'readonly' : '' }}>
                                </div>
                                @error('loanAmount') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                                @if(!empty($warnings['loanAmount']))
                                    <div class="mt-0.5 text-xs text-orange-600">{{ $warnings['loanAmount'][0] ?? '' }}</div>
                                @endif
                            </div>

                            <!-- Tenure -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Tenure <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="repaymentPeriod" 
                                        class="w-full pr-14 pl-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                        {{ $errors->has('repaymentPeriod') ? 'border-red-500' : '' }}"
                                        placeholder="12"
                                        min="{{ $selectedProductMinTerm }}"
                                        max="{{ $selectedProductMaxTerm }}">
                                    <span class="absolute right-2 top-1.5 text-gray-500 text-sm">Months</span>
                                </div>
                                @error('repaymentPeriod') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                                @if(!empty($warnings['repaymentPeriod']))
                                    <div class="mt-0.5 text-xs text-orange-600">{{ $warnings['repaymentPeriod'][0] ?? '' }}</div>
                                @endif
                            </div>

                            <!-- Monthly Income -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Monthly Income <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-gray-500 text-sm">TZS</span>
                                    <input type="number" wire:model="salaryTakeHome" 
                                        class="w-full pl-10 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                        {{ $errors->has('salaryTakeHome') ? 'border-red-500' : '' }}"
                                        placeholder="0.00">
                                </div>
                                @error('salaryTakeHome') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                                @if(!empty($warnings['salaryTakeHome']))
                                    <div class="mt-0.5 text-xs text-orange-600">{{ $warnings['salaryTakeHome'][0] ?? '' }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Loan Purpose - Full Width -->
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Purpose of Loan <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="loanPurpose" rows="2"
                                    class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                    {{ $errors->has('loanPurpose') ? 'border-red-500' : '' }}"
                                    placeholder="Describe the purpose of this loan application..."></textarea>
                            @error('loanPurpose') 
                                <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                            @if(!empty($warnings['loanPurpose']))
                                <div class="mt-0.5 text-xs text-orange-600">{{ $warnings['loanPurpose'][0] ?? '' }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Early Settlement Penalty (if applicable) -->
                    @if($topUpAmount > 0)
                        @php
                            $penaltyInfo = $this->getEarlySettlementPenaltyInfo();
                        @endphp
                        @if($penaltyInfo && $penaltyInfo['applies'])
                            <div class="bg-red-50 border border-red-200 rounded-lg p-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <div class="text-xs font-medium text-red-800">Early Settlement Penalty</div>
                                            <div class="text-xs text-red-600">
                                                Loan is {{ $penaltyInfo['months_difference'] }} months old ({{ $penaltyInfo['penalty_percentage'] }}% penalty)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-bold text-red-900">
                                        TZS {{ number_format($penaltyInfo['penalty_amount'], 2) }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>





            <!-- Step 3: Application Summary (swapped) -->
            <div class="{{ $currentStep === 3 ? 'block' : 'hidden' }}" data-step="3">
                <div class="p-8 space-y-6">

                    <!-- Application Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">

                        <div class="bg-white shadow-md rounded-lg overflow-hidden w-full flex p-4">
                        <h3 class="font-semibold text-gray-900 text-sm">Loan Application Summary </h3>
                        </div>
                        <!-- Top Row: Left and Middle Panels -->
                        <div class="bg-white shadow-md rounded-lg overflow-hidden w-full flex">

                            <!-- Left Panel -->
                            <div class="w-1/3 bg-blue-100 text-black p-3 flexx flex-col items-center justify-between">
                                <div class="w-full text-left text-blue-900 font-bold text-sm">
                                    LN | <span class="text-black">{{ date('dmy') . str_pad($client_number, 8, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="text-center mt-8 items-center justify-between">
                                    <div class="bg-blue-900 text-white font-bold rounded px-3 py-1 inline-block mb-4">TZS</div>
                                    <div class="text-2xl font-bold mb-2">{{ number_format(floatval($loanAmount), 0) }}</div>
                                    <div class="text-sm mb-4">{{ $selectedProductName ?: 'Loan Product' }}</div>
                                    <div class="bg-blue-900 text-white font-bold rounded px-3 py-1 inline-block mb-4 w-1/2">
                                        @if($loanType === 'New') LOAN TYPE
                                        @elseif($loanType === 'Top-up') LOAN TYPE
                                        @elseif($loanType === 'Restructure') LOAN TYPE
                                        @elseif($loanType === 'Takeover') LOAN TYPE
                                        @else LOAN TYPE
                                        @endif
                                    </div>
                                    <div class="text-sm mt-1">
                                        @if($loanType === 'New') NEW
                                        @elseif($loanType === 'Top-up') TOP UP
                                        @elseif($loanType === 'Restructure') LOAN RESTRUCTURING
                                        @elseif($loanType === 'Takeover') TAKEOVER
                                        @else NEW
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Panel -->
                            <div class="w-2/3 bg-gray-100 p-3 grid grid-cols-3 gap-2 text-xs text-blue-900 font-semibold">
                                <div class="bg-white p-2 rounded shadow">Net Interest Rate<br><span class="font-bold text-sm">{{ number_format($selectedProductInterestRate/12, 2) }}%</span></div>
                                <div class="bg-white p-2 rounded shadow">Monthly Installment<br><span class="font-bold text-sm">TZS {{ number_format((float)($monthlyInstallment ?? 0), 2) }} </span></div>
                                @if(!empty($productCharges))
                                @foreach($productCharges as $ch)
                                <div class="bg-white p-2 rounded shadow">
                                    <div class="text-xs text-gray-600">{{ $ch['name'] }}</div>
                                    <div class="font-bold text-sm text-blue-900">
                                        TZS {{ number_format((float)$ch['computed_amount'], 0) }}
                                        @if(isset($ch['cap_applied']))

                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @endif

                                @if(!empty($productInsurance))
                                @foreach($productInsurance as $ins)
                                <div class="bg-white p-2 rounded shadow">
                                    <div class="text-xs text-gray-600">{{ $ins['name'] }}
                                        @if($ins['value_type'] === 'percentage' && isset($ins['tenure']))

                                        @endif
                                    </div>
                                    <div class="font-bold text-sm text-blue-900">
                                        TZS {{ number_format((float)$ins['computed_amount'], 0) }}
                                    </div>
                                </div>
                                @endforeach
                                @endif



                                <div class="bg-white p-2 rounded shadow">Total amount payable<br><span class="font-bold text-sm">TZS {{ number_format((float)($totalAmountPayable ?? 0), 2) }}</span></div>


                                <div class="bg-white p-2 rounded shadow">Tenure<br><span class="font-bold text-sm">{{ $repaymentPeriod }}</span></div>
                                <div class="bg-white p-2 rounded shadow">Total Saving <br><span class="font-bold text-sm">TZS {{ number_format((float)($totalSavings ?? 0), 0) }}</span></div>
                                <div class="bg-white p-2 rounded shadow">Eligible loan Amount<br><span class="font-bold text-sm">TZS {{ number_format((float)($eligibleLoanAmount ?? 0), 0) }}</span></div>
                                <div class="bg-white p-2 rounded shadow">Active Loans<br><span class="font-bold text-sm">{{ $activeLoansCount ?? 0 }}</span></div>
                                <div class="bg-white p-2 rounded shadow">
                                    <span class="text-xs text-gray-600">First Interest</span><br>
                                    <span class="font-bold text-sm">TZS {{ number_format((float)($firstInterest ?? 0), 2) }}</span>
                                </div>
                                @if($earlyTopUpPenalty > 0)
                                    <div class="bg-white p-2 rounded shadow">
                                        <span class="text-xs text-gray-600">Early TopUp Penalty (5%)</span><br>
                                        <span class="font-bold text-sm">TZS {{ number_format((float)($earlyTopUpPenalty ?? 0), 2) }}</span>
                                    </div>
                                @endif
                                @if(in_array($loanType, ['Restructure', 'Restructuring']) && $adjustmentValue > 0)
                                    <div class="bg-blue-50 p-2 rounded shadow border-2 border-blue-500">
                                        <span class="text-xs text-blue-600">Adjustment</span><br>
                                        <span class="font-bold text-sm text-blue-700">TZS {{ number_format((float)($adjustmentValue ?? 0), 2) }}</span>
                                    </div>
                                @endif
                                <div class="bg-green-50 p-2 rounded shadow border-2 border-green-500">
                                    <span class="text-xs text-gray-600">Take Home</span><br>
                                    <span class="font-bold text-sm text-green-700">TZS {{ number_format((float)($takeHome ?? 0), 0) }}</span>
                                </div>

                            </div>

                        </div>

                        <!-- Bottom Row: Breaches Summary Table -->
                        @php
                            $savingsBreachExists = $this->getSavingsMultiplierBreachData() && (!$this->selectedProduct || !$this->selectedProduct->ltv || $this->selectedProduct->ltv <= 0);
                            $ltvBreachExists = $this->totalPhysicalCollateralValue > 0 && $this->selectedProduct && $this->selectedProduct->ltv > 0 && $this->ltvData && $this->ltvData['is_exceeded'];
                            $mobBreachExists = $loanType === 'Top-up' && $selectedLoanForTopUp && $this->getMinMobRequirement() > 0 && $this->getMonthsOnBook() < $this->getMinMobRequirement();
                            $hasAnyBreach = !empty($this->step1PolicyViolations) || $savingsBreachExists || $ltvBreachExists || $mobBreachExists;
                        @endphp
                        @if($hasAnyBreach)
                        <div class="bg-white shadow-md rounded-lg overflow-hidden w-full">
                            <div class="bg-white border border-gray-200 rounded-lg">
                                <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
                                    <h5 class="font-semibold text-gray-900 text-sm">Summary of Breaches</h5>
                                </div>
                                <div class="p-4">
                                        <!-- Full Width Table -->
                                        <table class="w-full text-sm border-collapse border border-gray-300">
                                            <thead>
                                                <tr class="bg-red-600 text-white">
                                                    <th class="px-4 py-2 text-left font-medium border border-gray-300">Parameter</th>
                                                    <th class="px-4 py-2 text-left font-medium border border-gray-300">Approved Lending Limit</th>
                                                    <th class="px-4 py-2 text-left font-medium border border-gray-300">Actual Breaches</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white text-black">
                                                @foreach(($this->step1PolicyViolations ?? []) as $violation)
                                                    <tr class="border-b border-gray-300 bg-red-50">
                                                        <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $violation['title'] }}</td>
                                                        <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $violation['limit_value'] }}</td>
                                                        <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $violation['current_value'] ?? $violation['given'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach

                                                <!-- Additional breach checks that are not in step1PolicyViolations -->
                                                @if($this->selectedProduct && $loanAmount)

                                                    <!-- Savings Multiplier Breaches -->
                                                    @php
                                                        $savingsBreachData = $this->getSavingsMultiplierBreachData();
                                                    @endphp
                                                    @if($savingsBreachData && (!$this->selectedProduct || !$this->selectedProduct->ltv || $this->selectedProduct->ltv <= 0))
                                                        <tr class="border-b border-gray-300 bg-red-50">
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">
                                                                Total Loan Exceeds Total Savings or Collateral Value
                                                            </td>
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">TZS {{ number_format($savingsBreachData['limit'], 0) }}</td>
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">TZS {{ number_format($savingsBreachData['actual'], 0) }}</td>
                                                        </tr>
                                                    @endif

                                                    <!-- LTV -->
                                                    @if($this->totalPhysicalCollateralValue > 0 && $this->selectedProduct && $this->selectedProduct->ltv > 0 && $this->ltvData && $this->ltvData['is_exceeded'])
                                                        <tr class="border-b border-gray-300 bg-red-50">
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">
                                                                LTV
                                                            </td>
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">
                                                                {{ $this->ltvData['limit'] }}%
                                                            </td>
                                                            <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $this->ltvData['ratio'] }}%</td>
                                                        </tr>
                                                    @endif

                                                    <!-- Minimum Months on Book (MoB) -->
                                                    @if($loanType === 'Top-up' && $selectedLoanForTopUp)
                                                        @php
                                                            $monthsOnBook = $this->getMonthsOnBook();
                                                            $minMob = $this->getMinMobRequirement();
                                                        @endphp

                                                        @if($minMob > 0 && $monthsOnBook < $minMob)
                                                            <tr class="border-b border-gray-300 bg-red-50">
                                                                <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">Minimum Months on Book (MoB)</td>
                                                                <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $minMob }} Months</td>
                                                                <td class="px-4 py-2 font-medium text-red-800 border border-gray-300">{{ $monthsOnBook }} Months</td>
                                                            </tr>
                                                        @endif
                                                    @endif

                                                @endif


                                            </tbody>
                                        </table>

                                        <!-- Upload Section -->
                                        <div class="mt-3">
                                            @if(!$exceptionApprovalUploaded)
                                                <label for="exceptionLetterStep3" class="inline-flex items-center justify-center px-4 py-2 rounded text-sm font-medium bg-blue-900 text-white hover:bg-blue-800 cursor-pointer shadow">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0-9a4 4 0 100-8 4 4 0 000 8z" />
                                                    </svg>
                                                    Attach Approval Letter to Proceed
                                                </label>
                                                <input id="exceptionLetterStep3" type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" wire:model="exceptionApprovalFile" />
                                                @error('exceptionApprovalFile')
                                                    <div class="text-sm text-red-600 mt-2">{{ $message }}</div>
                                                @enderror
                                            @else
                                                <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded px-3 py-2">
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                    <span class="text-sm text-green-800">Approval letter uploaded</span>
                                                </div>
                                            @endif
                                        </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 2: Guarantor & Collateral (swapped) -->
            <div class="{{ $currentStep === 2 ? 'block' : 'hidden' }}" data-step="2">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                    <h2 class="text-2xl font-bold text-white">Guarantor & Collateral</h2>
                    <p class="text-indigo-100 mt-1">Select your guarantor type and collateral</p>
                </div>
                
                <div class="p-8 space-y-6">
                    <!-- Savings Policy Violations Section -->
                    @if(!empty($this->step3SavingsViolations))
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 space-y-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-semibold text-orange-900">Savings Policy Notice</h3>
                            </div>
                            <p class="text-orange-700 text-sm">
                                Your loan amount exceeds the standard savings multiplier. You may need to provide additional collateral to proceed.
                            </p>
                            
                            <div class="space-y-4">
                                @foreach($this->step3SavingsViolations as $violation)
                                    <div class="bg-white border border-orange-200 rounded-lg p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        High Priority
                                                    </span>
                                                </div>
                                                <h4 class="font-semibold text-gray-900 mb-1">{{ $violation['title'] }}</h4>
                                                <p class="text-gray-700 text-sm mb-2">{{ $violation['description'] }}</p>
                                                
                                                <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                                                    <div>
                                                        <span class="font-medium text-gray-600">Loan Amount:</span>
                                                        <span class="text-red-600 font-semibold">{{ $violation['current_value'] }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-gray-600">Savings Limit (3x):</span>
                                                        <span class="text-green-600 font-semibold">{{ $violation['limit_value'] }}</span>
                                                    </div>
                                                    @if(isset($violation['savings_shortfall']))
                                                        <div class="col-span-2">
                                                            <span class="font-medium text-gray-600">Additional Collateral Needed:</span>
                                                            <span class="text-orange-600 font-semibold">{{ $violation['savings_shortfall'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                                    <div class="flex items-start">
                                                        <svg class="w-4 h-4 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <div>
                                                            <span class="font-medium text-blue-900">Recommendation:</span>
                                                            <p class="text-blue-700 text-sm mt-1">{{ $violation['recommendation'] }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-medium text-blue-900">Next Steps</span>
                                </div>
                                <p class="text-blue-800 text-sm mt-1">
                                    Please provide sufficient collateral below to cover the loan amount. You can use savings accounts, shares, deposits, or physical collateral.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Existing Guarantor & Collateral Display for Top-up/Restructure -->
                    @if(in_array($loanType, ['Top-up', 'Restructuring']) || $loanType === 'Restructure')
                        @php
                            // Check if a loan is selected for top-up or restructuring
                            $loanSelected = ($loanType === 'Top-up' && $selectedLoanForTopUp) || 
                                          (in_array($loanType, ['Restructuring', 'Restructure']) && $selectedLoanForRestructure);
                        @endphp
                        
          
                    @endif
                            
                    <!-- Compact Guarantor & Collateral Section -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <!-- Section Header with Tabs -->
                        <div class="flex items-center justify-between mb-4 border-b pb-3">
                            <h3 class="text-lg font-semibold text-gray-900">Guarantor & Collateral Details</h3>
                            <div class="flex gap-2">
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Required</span>
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Step 2 of 5</span>
                            </div>
                        </div>
                        
                        <!-- Existing Collateral Information for Top-up/Restructure -->
                        @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']))
                            @php
                                // Check if a loan is selected for top-up or restructuring
                                $loanSelected = ($loanType === 'Top-up' && $selectedLoanForTopUp) || 
                                              (in_array($loanType, ['Restructuring', 'Restructure']) && $selectedLoanForRestructure);
                            @endphp
                            
                            <!-- Simplified Collateral Summary Table -->
                            @if($loanSelected)
                                @php
                                    // Calculate existing collateral
                                    $existingAccountCollateral = 0;
                                    $existingPhysicalCollateral = 0;
                                    if (!empty($existingCollateralData)) {
                                        foreach ($existingCollateralData as $collateral) {
                                            if (is_array($collateral)) {
                                                if (in_array($collateral['collateral_type'], ['savings', 'deposits', 'shares'])) {
                                                    $existingAccountCollateral += floatval($collateral['collateral_amount']);
                                                } else {
                                                    $existingPhysicalCollateral += floatval($collateral['collateral_amount']);
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Calculate additional collateral
                                    $additionalAccountCollateral = $collateralCommitted ? (float)($committedCollateralAmount ?? 0) : (float)($collateralAmount ?? 0);
                                    $additionalPhysicalCollateral = $collateralCommitted ? (float)($committedPhysicalCollateralValue ?? 0) : (float)($physicalCollateralValue ?: 0);
                                    
                                    // Calculate totals
                                    $totalExisting = $existingAccountCollateral + $existingPhysicalCollateral;
                                    $totalAdditional = $additionalAccountCollateral + $additionalPhysicalCollateral;
                                    $grandTotal = $totalExisting + $totalAdditional;
                                    
                                    // Calculate coverage
                                    $coverageRatio = $loanAmount > 0 ? ($grandTotal / $loanAmount) * 100 : 0;
                                    
                                    // Check if there's any collateral to show
                                    $hasCollateral = $totalExisting > 0 || $totalAdditional > 0 || count($existingGuarantorData) > 0;
                                @endphp
                                
                                @if($hasCollateral || $collateralCommitted)
                                <div class="mb-4 bg-white border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Collateral Summary</h4>
                                    
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="text-left py-2 font-medium text-gray-700">Type</th>
                                                <th class="text-right py-2 font-medium text-gray-700">Existing</th>
                                                <th class="text-right py-2 font-medium text-gray-700">Additional</th>
                                                <th class="text-right py-2 font-medium text-gray-700">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($existingAccountCollateral > 0 || $additionalAccountCollateral > 0)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Account Collateral</td>
                                                <td class="py-2 text-right">{{ number_format($existingAccountCollateral, 0) }}</td>
                                                <td class="py-2 text-right">{{ number_format($additionalAccountCollateral, 0) }}</td>
                                                <td class="py-2 text-right font-semibold">{{ number_format($existingAccountCollateral + $additionalAccountCollateral, 0) }}</td>
                                            </tr>
                                            @endif
                                            
                                            @if($existingPhysicalCollateral > 0 || $additionalPhysicalCollateral > 0)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-2 text-gray-600">Physical Collateral</td>
                                                <td class="py-2 text-right">{{ number_format($existingPhysicalCollateral, 0) }}</td>
                                                <td class="py-2 text-right">{{ number_format($additionalPhysicalCollateral, 0) }}</td>
                                                <td class="py-2 text-right font-semibold">{{ number_format($existingPhysicalCollateral + $additionalPhysicalCollateral, 0) }}</td>
                                            </tr>
                                            @endif
                                            
                                            @if(count($existingGuarantorData) > 0 || $guarantorType === 'third_party_guarantee')
                                         
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t-2 border-gray-300">
                                                <td class="py-2 font-bold text-gray-900">Total (TZS)</td>
                                                <td class="py-2 text-right font-bold">{{ number_format($totalExisting, 0) }}</td>
                                                <td class="py-2 text-right font-bold">{{ number_format($totalAdditional, 0) }}</td>
                                                <td class="py-2 text-right font-bold text-lg text-indigo-600">{{ number_format($grandTotal, 0) }}</td>
                                            </tr>
                                            @if($loanAmount > 0 && $grandTotal > 0)
                                          
                                            @endif
                                        </tfoot>
                                    </table>
                                </div>
                                @endif
                            @endif
                        @endif
                        
                        <!-- Removed redundant Current Loan Collateral section - now combined in the table above -->
                        @if(false)
                            <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center mb-3">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <h4 class="text-sm font-semibold text-green-900">Current Loan Collateral (Committed)</h4>
                                </div>
                                <p class="text-xs text-green-700 mb-3">
                                    The following collateral has been committed for this loan application, including both existing collateral from the original loan and additional collateral you've added.
                                </p>
                                
                                @php
                                    // Calculate current loan collateral (existing + additional)
                                    $currentAccountCollateral = 0;
                                    $currentPhysicalCollateral = 0;
                                    
                                    // Add existing collateral
                                    if (!empty($existingCollateralData)) {
                                        foreach ($existingCollateralData as $collateral) {
                                            if (is_array($collateral)) {
                                                if (in_array($collateral['collateral_type'], ['savings', 'deposits', 'shares'])) {
                                                    $currentAccountCollateral += floatval($collateral['collateral_amount']);
                                                } else {
                                                    $currentPhysicalCollateral += floatval($collateral['collateral_amount']);
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Add additional collateral (use committed values if collateral is committed)
                                    if ($collateralCommitted) {
                                        $currentAccountCollateral += (float)($committedCollateralAmount ?? 0);
                                        $currentPhysicalCollateral += (float)($committedPhysicalCollateralValue ?? 0);
                                    } else {
                                        $currentAccountCollateral += (float)($collateralAmount ?? 0);
                                        $currentPhysicalCollateral += (float)($physicalCollateralValue ?: 0);
                                    }
                                    
                                    $totalCurrentCollateral = $currentAccountCollateral + $currentPhysicalCollateral;
                                @endphp
                                
                                <!-- Current Collateral Summary -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                    @if(count($existingGuarantorData) > 0)
                                    <div class="bg-white rounded p-3 border border-green-100">
                                        <div class="text-xs text-green-600 font-medium">Total Guarantors</div>
                                        <div class="text-sm font-bold text-green-900">{{ count($existingGuarantorData) }}</div>
                                        <div class="text-xs text-green-600">Including existing</div>
                                    </div>
                                    @endif
                                    
                                    @if($currentAccountCollateral > 0)
                                    <div class="bg-white rounded p-3 border border-green-100">
                                        <div class="text-xs text-green-600 font-medium">Total Account Collateral</div>
                                        <div class="text-sm font-bold text-green-900">TZS {{ number_format($currentAccountCollateral, 0) }}</div>
                                        <div class="text-xs text-green-600">Existing + Additional</div>
                                    </div>
                                    @endif
                                    
                                    @if($currentPhysicalCollateral > 0)
                                    <div class="bg-white rounded p-3 border border-green-100">
                                        <div class="text-xs text-green-600 font-medium">Total Physical Collateral</div>
                                        <div class="text-sm font-bold text-green-900">TZS {{ number_format($currentPhysicalCollateral, 0) }}</div>
                                        <div class="text-xs text-green-600">Existing + Additional</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Collateral Breakdown -->
                                <div class="bg-white rounded p-3 border border-green-200 mb-3">
                                    <h5 class="text-xs font-semibold text-green-800 mb-2">Collateral Breakdown:</h5>
                                    <div class="space-y-2">
                                        @if(!empty($existingCollateralData))
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-green-700">Existing Collateral:</span>
                                            <span class="font-semibold text-green-900">
                                                @php
                                                    $existingTotal = 0;
                                                    foreach ($existingCollateralData as $collateral) {
                                                        if (is_array($collateral)) {
                                                            $existingTotal += floatval($collateral['collateral_amount']);
                                                        }
                                                    }
                                                @endphp
                                                TZS {{ number_format($existingTotal, 0) }}
                                            </span>
                                        </div>
                                        @endif
                                        
                                        @if($collateralCommitted && ((float)($committedCollateralAmount ?? 0) > 0 || (float)($committedPhysicalCollateralValue ?? 0) > 0))
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-green-700">Additional Collateral:</span>
                                            <span class="text-green-900 font-semibold">
                                                TZS {{ number_format((float)($committedCollateralAmount ?? 0) + (float)($committedPhysicalCollateralValue ?? 0), 0) }}
                                            </span>
                                        </div>
                                        @elseif(!$collateralCommitted && ((float)($collateralAmount ?? 0) > 0 || (float)($physicalCollateralValue ?: 0) > 0))
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-green-700">Additional Collateral:</span>
                                            <span class="text-green-900 font-semibold">
                                                TZS {{ number_format((float)($collateralAmount ?? 0) + (float)($physicalCollateralValue ?: 0), 0) }}
                                            </span>
                                        </div>
                                        @endif
                                        
                                        <div class="border-t border-green-200 pt-2">
                                            <div class="flex justify-between items-center text-sm font-bold">
                                                <span class="text-green-800">Total Current Collateral:</span>
                                                <span class="text-green-900">TZS {{ number_format($totalCurrentCollateral, 0) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Coverage Analysis -->
                                @if($loanAmount > 0 && $totalCurrentCollateral > 0)
                                @php
                                    $coverageRatio = ($totalCurrentCollateral / $loanAmount) * 100;
                                    $isGoodCoverage = $coverageRatio >= 150;
                                    $isAdequateCoverage = $coverageRatio >= 120;
                                @endphp
                                <div class="bg-{{ $isGoodCoverage ? 'green' : ($isAdequateCoverage ? 'yellow' : 'red') }}-50 rounded p-3 border border-{{ $isGoodCoverage ? 'green' : ($isAdequateCoverage ? 'yellow' : 'red') }}-200">
                                    <div class="flex justify-between items-center">
                                        <div class="text-xs text-{{ $isGoodCoverage ? 'green' : ($isAdequateCoverage ? 'yellow' : 'red') }}-700">
                                            <strong>Collateral Coverage:</strong> {{ number_format($coverageRatio, 1) }}% of loan amount
                                        </div>
                                        <div class="text-xs">
                                            @if($isGoodCoverage)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Excellent</span>
                                            @elseif($isAdequateCoverage)
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Adequate</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Insufficient</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Guarantor Type Selection - Inline Radio -->
                        <div class="mb-4 bg-gray-50 rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Guarantor Type *
                                @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && (!empty($existingGuarantorData) || !empty($existingCollateralData)))
                                    <span class="text-xs text-blue-600 ml-1">(Additional guarantor)</span>
                                @endif
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           wire:model="guarantorType" 
                                           value="self_guarantee" 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Self Guarantee</span>
                                </label>
                                
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                           wire:model="guarantorType" 
                                           value="third_party_guarantee" 
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Third Party Guarantee</span>
                                </label>
                            </div>
                            @error('guarantorType') 
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                            @if(!empty($warnings['guarantorType']))
                                <div class="mt-1 text-xs text-red-600">
                                    @foreach($warnings['guarantorType'] as $w)
                                        {{ $w }}
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Third Party Guarantor Details - Compact Grid -->
                        @if($guarantorType === 'third_party_guarantee')
                        <div class="mb-4 bg-blue-50 rounded-lg p-3 border border-blue-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Guarantor Member Number *</label>
                                    <input type="text" 
                                           wire:model="selectedGuarantorId" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Enter member number">
                                    @error('selectedGuarantorId') 
                                        <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Relationship *</label>
                                    <input type="text" 
                                           wire:model="guarantorRelationship" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="e.g., Spouse, Parent">
                                    @error('guarantorRelationship') 
                                        <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endif

                   
                        
                        <!-- Account Collateral - Compact Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Select Account *
                                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && (!empty($existingGuarantorData) || !empty($existingCollateralData)))
                                        <span class="text-xs text-blue-600 ml-1">(Additional)</span>
                                    @endif
                                </label>
                                <select wire:model="selectedAccountId" 
                                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Choose account...</option>
                                    @foreach($this->getAllMemberAccounts() as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->account_name }} -  TZS {{ number_format($account->available_balance ?? $account->balance ?? 0, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedAccountId') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Collateral Amount (TZS) *
                                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && (!empty($existingGuarantorData) || !empty($existingCollateralData)))
                                        <span class="text-xs text-blue-600 ml-1">(Additional)</span>
                                    @endif
                                </label>
                                <input type="number" 
                                       wire:model="collateralAmount" 
                                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Amount"
                                       step="0.01">
                                @error('collateralAmount') 
                                    <div class="mt-0.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Physical Collateral Toggle -->
                        <div class="mb-3">
                            <button type="button" 
                                    wire:click="$toggle('showPhysicalCollateral')" 
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <span class="font-medium text-gray-700">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Physical Collateral (Optional)
                                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && (!empty($existingGuarantorData) || !empty($existingCollateralData)))
                                        <span class="text-xs text-blue-600 ml-1">(Additional)</span>
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-gray-500 transition-transform {{ $showPhysicalCollateral ? 'rotate-180' : '' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($showPhysicalCollateral)
                        <div class="bg-amber-50 rounded-lg p-3 mb-3 border border-amber-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                    <input type="text" 
                                           wire:model="physicalCollateralDescription" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500"
                                           placeholder="e.g., Vehicle">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Value (TZS)</label>
                                    <input type="number" 
                                           wire:model="physicalCollateralValue" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Estimated value"
                                           step="0.01">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                                    <input type="text" 
                                           wire:model="physicalCollateralLocation" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Location">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Owner Name</label>
                                    <input type="text" 
                                           wire:model="physicalCollateralOwnerName" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Owner name">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Owner Contact</label>
                                    <input type="text" 
                                           wire:model="physicalCollateralOwnerContact" 
                                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500"
                                           placeholder="Contact">
                                </div>
                            </div>
                        </div>
                        @endif

                       

                        <!-- Commit Collateral Button - Compact -->
                        @if(!$collateralCommitted)
                        <div class="flex justify-end">
                            <button type="button" 
                                    wire:click="commitCollateral"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors font-medium inline-flex items-center">
                                <svg wire:loading.remove class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg wire:loading class="animate-spin w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove>Commit Collateral</span>
                                <span wire:loading>Committing...</span>
                            </button>
                        </div>
                        @else
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-800">Collateral has been committed successfully</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 4: Documents -->
            <div class="{{ $currentStep === 4 ? 'block' : 'hidden' }}">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4">
                    <h2 class="text-xl font-bold text-white">Documents</h2>
                    <p class="text-indigo-100 text-sm">Upload supporting documents</p>
                </div>
                
                <div class="p-4">
                    
                  
                    
                    <!-- Existing Documents from Original Loan -->
                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && !empty($existingDocumentsData))
                        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <h4 class="text-sm font-semibold text-blue-900">Existing Documents from Original Loan</h4>
                            </div>
                            <p class="text-xs text-blue-700 mb-3">
                                The following documents have been carried forward from the original loan. You can add additional documents below.
                            </p>
                            
                            <!-- Existing Documents Summary -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div class="bg-white rounded p-3 border border-blue-100">
                                    <div class="text-xs text-blue-600 font-medium">Total Documents</div>
                                    <div class="text-sm font-bold text-blue-900">{{ count($existingDocumentsData) }}</div>
                                    <div class="text-xs text-blue-600">From original loan</div>
                                </div>
                                
                                @php
                                    $existingGeneralDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'general'; }));
                                    $existingIdentityDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'identity'; }));
                                    $existingFinancialDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'financial'; }));
                                    $existingCollateralDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'collateral'; }));
                                @endphp
                                
                                @if($existingIdentityDocs > 0)
                                <div class="bg-white rounded p-3 border border-blue-100">
                                    <div class="text-xs text-blue-600 font-medium">Identity Documents</div>
                                    <div class="text-sm font-bold text-blue-900">{{ $existingIdentityDocs }}</div>
                                    <div class="text-xs text-blue-600">ID, Passport, etc.</div>
                                </div>
                                @endif
                                
                                @if($existingFinancialDocs > 0)
                                <div class="bg-white rounded p-3 border border-blue-100">
                                    <div class="text-xs text-blue-600 font-medium">Financial Documents</div>
                                    <div class="text-sm font-bold text-blue-900">{{ $existingFinancialDocs }}</div>
                                    <div class="text-xs text-blue-600">Bank statements, etc.</div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Existing Documents List -->
                            <div class="bg-white rounded p-3 border border-blue-200">
                                <h5 class="text-xs font-semibold text-blue-800 mb-2">Existing Documents:</h5>
                                <div class="space-y-2">
                                    @foreach($existingDocumentsData as $index => $doc)
                                    <div class="relative group bg-blue-50 border border-blue-100 rounded p-2 hover:shadow-sm transition-all duration-300">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-blue-700 text-xs">{{ $doc['filename'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-blue-600 font-medium text-xs">{{ ucfirst($doc['category']) }}</span>
                                                
                                                <!-- Actions -->
                                                <div class="flex gap-1 ml-2">
                                                    <!-- Download Button -->
                                                    <button type="button" 
                                                            wire:click="downloadExistingDocument({{ $index }})"
                                                            class="p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors"
                                                            title="Download">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Remove Button -->
                                                    <button type="button" 
                                                            wire:click="removeExistingDocument({{ $index }})"
                                                            class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                                            title="Remove from display">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Current Loan Documents (After Upload) -->
                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && !empty($existingDocumentsData))
                        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <h4 class="text-sm font-semibold text-green-900">Current Loan Documents (Available)</h4>
                            </div>
                            <p class="text-xs text-green-700 mb-3">
                                The following documents are available for this loan application. You can upload additional documents to supplement the existing ones.
                            </p>
                            
                            @php
                                $totalDocuments = count($existingDocumentsData) + count($committedDocumentsData);
                                $totalGeneralDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'general'; })) + 
                                                   count(array_filter($committedDocumentsData, function($doc) { return $doc['category'] === 'general'; }));
                                $totalIdentityDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'identity'; })) + 
                                                    count(array_filter($committedDocumentsData, function($doc) { return $doc['category'] === 'identity'; }));
                                $totalFinancialDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'financial'; })) + 
                                                      count(array_filter($committedDocumentsData, function($doc) { return $doc['category'] === 'financial'; }));
                                $totalCollateralDocs = count(array_filter($existingDocumentsData, function($doc) { return $doc['category'] === 'collateral'; })) + 
                                                       count(array_filter($committedDocumentsData, function($doc) { return $doc['category'] === 'collateral'; }));
                            @endphp
                            
                            <!-- Current Documents Summary -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div class="bg-white rounded p-3 border border-green-100">
                                    <div class="text-xs text-green-600 font-medium">Total Documents</div>
                                    <div class="text-sm font-bold text-green-900">{{ $totalDocuments }}</div>
                                    <div class="text-xs text-green-600">Existing + Additional</div>
                                </div>
                                
                                @if($totalIdentityDocs > 0)
                                <div class="bg-white rounded p-3 border border-green-100">
                                    <div class="text-xs text-green-600 font-medium">Identity Documents</div>
                                    <div class="text-sm font-bold text-green-900">{{ $totalIdentityDocs }}</div>
                                    <div class="text-xs text-green-600">Existing + Additional</div>
                                </div>
                                @endif
                                
                                @if($totalFinancialDocs > 0)
                                <div class="bg-white rounded p-3 border border-green-100">
                                    <div class="text-xs text-green-600 font-medium">Financial Documents</div>
                                    <div class="text-sm font-bold text-green-900">{{ $totalFinancialDocs }}</div>
                                    <div class="text-xs text-green-600">Existing + Additional</div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Documents Breakdown -->
                            <div class="bg-white rounded p-3 border border-green-200 mb-3">
                                <h5 class="text-xs font-semibold text-green-800 mb-2">Documents Breakdown:</h5>
                                <div class="space-y-2">
                                    @if(!empty($existingDocumentsData))
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-green-700">Existing Documents:</span>
                                        <span class="font-semibold text-green-900">{{ count($existingDocumentsData) }}</span>
                                    </div>
                                    @endif
                                    
                                    @if(!empty($committedDocumentsData))
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-green-700">Additional Documents:</span>
                                        <span class="text-green-900 font-semibold">{{ count($committedDocumentsData) }}</span>
                                    </div>
                                    @else
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-green-700">Additional Documents:</span>
                                        <span class="text-green-600 font-medium">0 (None uploaded yet)</span>
                                    </div>
                                    @endif
                                    
                                    <div class="border-t border-green-200 pt-2">
                                        <div class="flex justify-between items-center text-sm font-bold">
                                            <span class="text-green-800">Total Current Documents:</span>
                                            <span class="text-green-900">{{ $totalDocuments }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Current Documents List -->
                            <div class="bg-white rounded p-3 border border-green-200">
                                <h5 class="text-xs font-semibold text-green-800 mb-2">Current Documents List:</h5>
                                <div class="space-y-2">
                                    <!-- Existing Documents -->
                                    @if(!empty($existingDocumentsData))
                                        @foreach($existingDocumentsData as $index => $doc)
                                        <div class="relative group bg-green-50 border border-green-100 rounded p-2 hover:shadow-sm transition-all duration-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-green-700 text-xs">{{ $doc['filename'] }}</span>
                                                    <span class="ml-2 px-1 py-0.5 bg-blue-100 text-blue-600 rounded text-xs">Existing</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-green-600 font-medium text-xs">{{ ucfirst($doc['category']) }}</span>
                                                    
                                                    <!-- Actions -->
                                                    <div class="flex gap-1 ml-2">
                                                        <!-- Download Button -->
                                                        <button type="button" 
                                                                wire:click="downloadExistingDocument({{ $index }})"
                                                                class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors"
                                                                title="Download">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <!-- Remove Button -->
                                                        <button type="button" 
                                                                wire:click="removeExistingDocument({{ $index }})"
                                                                class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                                                title="Remove from display">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                    
                                    <!-- Additional Documents -->
                                    @if(!empty($committedDocumentsData))
                                        @foreach($committedDocumentsData as $index => $doc)
                                        <div class="relative group bg-green-50 border border-green-100 rounded p-2 hover:shadow-sm transition-all duration-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-3 h-3 text-green-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="text-green-700 text-xs">{{ $doc['filename'] }}</span>
                                                    <span class="ml-2 px-1 py-0.5 bg-green-100 text-green-600 rounded text-xs">Additional</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-green-600 font-medium text-xs">{{ ucfirst($doc['category']) }}</span>
                                                    
                                                    <!-- Actions -->
                                                    <div class="flex gap-1 ml-2">
                                                        <!-- Download Button -->
                                                        <button type="button" 
                                                                wire:click="downloadDocumentFile({{ $index }})"
                                                                class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors"
                                                                title="Download">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <!-- Remove Button -->
                                                        <button type="button" 
                                                                wire:click="removeDocument({{ $index }})"
                                                                class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                                                title="Remove">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                    
                                    @if(empty($existingDocumentsData) && empty($committedDocumentsData))
                                    <div class="text-center py-4 text-xs text-green-600">
                                        No documents available
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
       
                    <!-- Additional Documents Section -->
                    @if(in_array($loanType, ['Top-up', 'Restructuring', 'Restructure']) && $loanSelected && !empty($existingDocumentsData))
                        <div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center mb-2">
                                <svg class="w-4 h-4 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <h4 class="text-sm font-semibold text-gray-900">Additional Documents</h4>
                            </div>
                            <p class="text-xs text-gray-600 mb-3">
                                You can upload additional documents below to supplement the existing documents from your original loan.
                            </p>
                        </div>
                    @endif



                    <div class="bg-white border border-gray-200 rounded-lg p-3 mb-4 w-full flex bg-blackx gap-2">
                        <div class="flex items-center justify-between mb-3 w-1/2 gap-2">
                                    <!-- Compact Upload Area -->
                            <div class="mb-4 w-1/2">
                                <div wire:ignore
                                    class="relative border-2 border-dashed rounded-lg p-4 text-center transition-all duration-300 {{ $isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-gray-50 hover:bg-gray-100' }}"
                                    id="drop-zone">
                                    
                                    <!-- Upload Progress Overlay -->
                                    @if($isUploading)
                                        <div class="absolute inset-0 bg-white bg-opacity-90 rounded-lg flex items-center justify-center z-10">
                                        <div class="text-center">
                                            <div class="mb-2">
                                                <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                                    style="width: {{ $uploadProgress }}%"></div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-600">{{ $uploadProgress }}% uploaded</p>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Drop Zone Content -->
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    
                                    <p class="text-sm font-medium text-gray-900 mb-1">
                                        Drop files here or click to browse
                                    </p>
                                    <p class="text-xs text-gray-500 mb-2">
                                        PDF, DOC, DOCX, JPG, PNG up to 10MB
                                    </p>
                                    
                                    <input type="file" 
                                        wire:model="documentFile" 
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        class="hidden"
                                        id="fileInput">
                                    
                                    <label for="fileInput" 
                                        class="inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Select Files
                                    </label>
                                </div>
                                
                                @error('documentFile') 
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @if(!empty($warnings['documentFile']))
                                    <div class="mt-1 text-xs text-red-600">
                                        @foreach($warnings['documentFile'] as $w)
                                            <div>{{ $w }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Compact Upload Form -->
                            @if($documentFile)
                                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-4 w-1/2">
                                    <h3 class="font-medium text-gray-900 mb-3 text-sm">Complete Document Details</h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                                            <select wire:model="documentCategory" 
                                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="general">General</option>
                                                <option value="identity">Identity</option>
                                                <option value="financial">Financial</option>
                                                <option value="collateral">Collateral</option>
                                                <option value="other">Other</option>
                                            </select>
                                            @if(!empty($warnings['documentCategory']))
                                                <div class="mt-1 text-xs text-red-600">
                                                    @foreach($warnings['documentCategory'] as $w)
                                                        <div>{{ $w }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                            <input type="text" 
                                                wire:model="documentDescription"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="e.g., National ID Front">
                                            @error('documentDescription') 
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                            @if(!empty($warnings['documentDescription']))
                                                <div class="mt-1 text-xs text-red-600">
                                                    @foreach($warnings['documentDescription'] as $w)
                                                        <div>{{ $w }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 flex justify-end space-x-2">
                                        <button type="button" 
                                                wire:click="$set('documentFile', null)"
                                                class="px-3 py-1 border border-gray-300 text-gray-700 rounded text-xs hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button type="button" 
                                                wire:click="uploadDocument" 
                                                wire:loading.attr="disabled"
                                                class="px-3 py-1 bg-indigo-600 text-white rounded text-xs hover:bg-indigo-700 transition-all disabled:opacity-50">
                                            <span wire:loading.remove>Upload</span>
                                            <span wire:loading>Uploading...</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between mb-3 w-1/2 ">
                                    <!-- Uploaded Documents List -->
                            <div class="w-full">
                            
                                
                                @if($uploadedDocumentsCount > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($this->uploadedDocuments as $index => $doc)
                                            <div class="relative group bg-white border border-gray-200 rounded-lg p-2 hover:shadow-md transition-all duration-300">
                                                <!-- File Type Icon -->
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 mr-2">
                                                        @php
                                                            $extension = pathinfo($doc['filename'], PATHINFO_EXTENSION);
                                                            $isPdf = in_array(strtolower($extension), ['pdf']);
                                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                                                            $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                                                        @endphp
                                                        
                                                        @if($isPdf)
                                                            <div class="w-8 h-8 bg-red-100 rounded flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M10,19L8,14H10L11,17L12,14H14L12,19H10Z"></path>
                                                                </svg>
                                                            </div>
                                                        @elseif($isImage)
                                                            <div class="w-8 h-8 bg-green-100 rounded flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                </svg>
                                                            </div>
                                                        @elseif($isDoc)
                                                            <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M13,17V11H10V13H8V11A2,2 0 0,1 10,9H14A2,2 0 0,1 16,11V17A2,2 0 0,1 14,19H10A2,2 0 0,1 8,17V15H10V17H13Z"></path>
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- File Details -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-1">
                                                            <button type="button" 
                                                                    wire:click="downloadDocumentFile({{ $index }})"
                                                                    class="text-xs font-medium text-blue-600 hover:text-blue-800 truncate text-left">
                                                                {{ $doc['filename'] }}
                                                            </button>
                                                            @if(isset($doc['is_existing']) && $doc['is_existing'])
                                                                <span class="px-1 py-0.5 bg-orange-100 text-orange-600 rounded text-xs text-xs">
                                                                    Existing
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-xs text-gray-500 mt-0.5">
                                                            {{ $doc['description'] }}
                                                        </p>
                                                        <div class="flex items-center mt-1 text-xs text-gray-400">
                                                            <span class="px-1 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                                                                {{ ucfirst($doc['category']) }}
                                                            </span>
                                                            <span class="ml-1">{{ number_format((float)($doc['size'] / 1024), 1) }} KB</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Actions -->
                                                <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                                    <!-- Download Button -->
                                                    <button type="button" 
                                                            wire:click="downloadDocumentFile({{ $index }})"
                                                            class="p-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors"
                                                            title="Download">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Remove Button -->
                                                    <button type="button" 
                                                            wire:click="removeDocument({{ $index }})"
                                                            class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                                            title="Remove">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Summary -->
                                    <div class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                <p class="text-blue-800">
                                                    {{ $uploadedDocumentsCount }} document{{ $uploadedDocumentsCount > 1 ? 's' : '' }} uploaded
                                                </p>
                                            </div>
                                            @if($loanType === 'Restructure' && isset($uploadedDocuments[0]['is_existing']))
                                                <span class="text-xs text-orange-600 font-medium">
                                                    ({{ count(array_filter($uploadedDocuments, function($doc) { return isset($doc['is_existing']) && $doc['is_existing']; })) }} from existing loan)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-6 bg-gray-50 rounded-lg">
                                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">No documents uploaded</h4>
                                        <p class="text-xs text-gray-500">Upload at least one supporting document</p>
                                    </div>
                                @endif
                                
                                @error('uploadedDocuments') 
                                    <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs">
                                        <p class="text-red-600">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>


                        </div>
                                
                    </div>
                            
    
                 </div>
            </div>


            <!-- Step 5: Review & Submit - Compact -->
            <div class="{{ $currentStep === 5 ? 'block' : 'hidden' }}" data-step="5">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4">
                    <h2 class="text-xl font-bold text-white">Review & Submit</h2>
                    <p class="text-indigo-100 text-sm">Review your application details before submitting</p>
                </div>
                
                <div class="p-4 space-y-3">
                    {{-- Breaches summary moved to Step 3 --}}
                    <!-- Terms and Conditions - Compact -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">Loan Agreement & Terms</h4>
                            <div class="flex gap-2">
                                <button type="button" 
                                        onclick="window.print()"
                                        class="inline-flex items-center px-2 py-1 text-xs bg-blue-900 text-white rounded hover:bg-blue-700 transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print
                                </button>
                                <button type="button" 
                                        onclick="downloadAgreement()"
                                        class="inline-flex items-center px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download
                                </button>
                            </div>
                        </div>
                        
                        <!-- Agreement Content - Compact -->
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-3 max-h-64 overflow-y-auto bg-gray-50">
                                @include('components.loan-agreement-template')
                            </div>
                        </div>
                        
                        <!-- Accept Terms Checkbox -->
                        <div class="bg-blue-50 border border-blue-200 rounded p-3">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" 
                                       wire:model="acceptedTerms" 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5">
                                <span class="ml-2 text-sm text-gray-700">
                                    I have read and agree to the loan agreement terms and conditions. 
                                    I understand that submitting this application constitutes a legally binding agreement.
                                </span>
                            </label>
                            @error('acceptedTerms')
                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            
            </div> <!-- End Form Content Container -->

       
        </form>



    







 <!-- Navigation Buttons -->



    <div class="bg-gray-50 px-4 py-3 flex justify-between items-center border-t border-gray-200 sticky bottom-0 z-50 shadow-md" style="position: sticky; bottom: 0; z-index: 50;">
                <!-- Previous Button - Compact -->
                <button type="button" 
                        wire:click="previousStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="{{ $currentStep === 1 ? 'invisible' : '' }} inline-flex items-center px-3 py-1.5 text-sm text-gray-700 rounded hover:bg-gray-200 transition-colors font-medium border border-gray-300">
                    <svg wire:loading.remove class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <svg wire:loading class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Previous</span>
                    <span wire:loading>Loading...</span>
                </button>

                                <!-- Step Indicator -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Step</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $currentStep }}</span>
                        <span class="text-sm text-gray-500">of {{ $totalSteps }}</span>
                    </div>
                    
                  
                </div>
                
                <!-- Next/Submit Button -->
                @if($currentStep < $totalSteps)
                    <button type="button" 
                            wire:click="nextStep"
                            wire:loading.attr="disabled"
                            wire:target="nextStep"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-6 py-3 text-white rounded-lg transition-all font-medium shadow-md hover:bg-blue-700 {{ ($currentStep === 3 && ($this->hasWarnings && !$exceptionApprovalUploaded)) ? 'opacity-60 cursor-not-allowed pointer-events-none bg-gray-400 hover:bg-gray-400' : '' }}"
                            style="background-color: #1e3a8a;"
                            {{ ($currentStep === 3 && ($this->hasWarnings && !$exceptionApprovalUploaded)) ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="nextStep">Next </span>
                        <span wire:loading wire:target="nextStep">Processing...</span>
                        <svg wire:loading.remove wire:target="nextStep" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <svg wire:loading wire:target="nextStep" class="animate-spin w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                @else
                    <button type="button" 
                            wire:click="submitApplication"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all font-medium shadow-md">
                        <svg wire:loading.remove class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg wire:loading class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>Submit Application</span>
                        <span wire:loading>Submitting...</span>
                    </button>
                @endif
    </div>




      <!-- Client Number Modal -->
      @if($showClientNumberModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="clientNumberModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Enter Client Number</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Please enter your client number to continue with the loan application.
                    </p>
                    <div class="mb-4">
                        <input type="text" 
                               wire:model="inputClientNumber" 
                               wire:keydown.enter="submitClientNumber"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter your client number"
                               autofocus>
                        @if($clientNumberError)
                            <p class="text-red-500 text-sm mt-1">{{ $clientNumberError }}</p>
                        @endif
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button wire:click="submitClientNumber"
                            class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Verify Client Number
                    </button>
                    <button wire:click="closeClientNumberModal"
                            class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif



</div>










 

<!-- All JavaScript functionality -->
<script nonce="{{ csp_nonce() }}">
// Drag and Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            @this.setDragging(true);
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            @this.setDragging(false);
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            @this.setDragging(false);
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                @this.upload('documentFile', files[0], 
                    (uploadedFilename) => {
                        @this.setUploading(false);
                        @this.setUploadProgress(0);
                    }, 
                    (error) => {
                        @this.setUploading(false);
                        @this.setUploadProgress(0);
                    }, 
                    (event) => {
                        @this.setUploading(true);
                        @this.setUploadProgress(event.detail.progress);
                    }
                );
            }
        });
    }
});

// Monitor form structure changes
document.addEventListener('DOMContentLoaded', function() {
    checkFormStructure();
});

// Monitor Livewire updates
document.addEventListener('livewire:load', function() {
    Livewire.hook('message.processed', (message, component) => {
        // Small delay to ensure DOM is updated
        setTimeout(checkFormStructure, 100);
    });
});

function checkFormStructure() {
    const form = document.getElementById('loan-application-form');
    const navigationButtons = document.getElementById('navigation-buttons');
    const formContentContainer = document.querySelector('.form-content-container');
    
    if (form && navigationButtons) {
        // Check if navigation buttons are inside the form
        const isInsideForm = form.contains(navigationButtons);
        console.log('Form structure check:', {
            formExists: !!form,
            navigationExists: !!navigationButtons,
            contentContainerExists: !!formContentContainer,
            navigationInsideForm: isInsideForm
        });
        
        if (!isInsideForm) {
            console.error('❌ Navigation buttons are outside the form!');
            // Move navigation buttons back inside the form, after the content container
            if (formContentContainer) {
                formContentContainer.parentNode.insertBefore(navigationButtons, formContentContainer.nextSibling);
            } else {
                form.appendChild(navigationButtons);
            }
            console.log('✅ Navigation buttons moved back inside form');
        }
    }
}

// Agreement Functions
function downloadAgreement() {
    // For now, just print the agreement
    window.print();
    
    // TODO: Implement PDF generation functionality
    // This could be done by:
    // 1. Creating a proper route that generates PDF
    // 2. Using a client-side PDF library like jsPDF
    // 3. Sending data to backend for PDF generation
    console.log('Download functionality - printing agreement instead');
}

// Credit Score Gauge functionality
function initializeReviewCreditScoreGauge() {
    console.log('🔍 initializeReviewCreditScoreGauge() called');
    
    const canvas = document.getElementById('credit-score-gauge');
    const textElement = document.getElementById('credit-score-text');
    
    console.log('📊 Canvas element:', canvas);
    console.log('📝 Text element:', textElement);
    
    // If credit score UI is not present on this step, do nothing
    if (!canvas || !textElement) return;
    
    const creditScore = {{ $creditScoreValue }};
    console.log('🎯 Credit score value:', creditScore, 'Type:', typeof creditScore);
    
    const ctx = canvas.getContext('2d');
    console.log('🎨 Canvas context:', ctx);
    
    if (!ctx) {
        console.error('❌ Could not get 2D context from canvas!');
        return;
    }
    
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2 + 20;
    const radius = 60;
    
    console.log('📐 Canvas dimensions:', {
        width: canvas.width,
        height: canvas.height,
        centerX: centerX,
        centerY: centerY,
        radius: radius
    });
    
    // Clear canvas
    console.log('🧹 Clearing canvas...');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw background arc
    console.log('🎨 Drawing background arc...');
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, 2 * Math.PI);
    ctx.lineWidth = 15;
    ctx.strokeStyle = '#e5e7eb';
    ctx.stroke();
    
    // Calculate score position (0-1000 mapped to PI to 2*PI)
    const scoreAngle = Math.PI + (creditScore / 1000) * Math.PI;
    console.log('📐 Score angle calculation:', {
        creditScore: creditScore,
        normalizedScore: creditScore / 1000,
        scoreAngle: scoreAngle,
        scoreAngleDegrees: (scoreAngle * 180) / Math.PI
    });
    
    // Draw score arc
    console.log('🎨 Drawing score arc...');
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, Math.PI, scoreAngle);
    ctx.lineWidth = 15;
    
    // Color based on score
    let strokeColor;
    if (creditScore >= 700) {
        strokeColor = '#10b981'; // Green
        console.log('🟢 Using green color (score >= 700)');
    } else if (creditScore >= 500) {
        strokeColor = '#f59e0b'; // Yellow
        console.log('🟡 Using yellow color (score >= 500)');
    } else {
        strokeColor = '#ef4444'; // Red
        console.log('🔴 Using red color (score < 500)');
    }
    
    ctx.strokeStyle = strokeColor;
    ctx.stroke();
    
    // Draw score text
    console.log('📝 Drawing score text...');
    ctx.font = 'bold 36px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#1f2937';
    ctx.fillText(creditScore, centerX, centerY - 15);
    
    // Draw labels
    console.log('🏷️ Drawing labels...');
    ctx.font = '10px Arial';
    ctx.fillStyle = '#6b7280';
    ctx.fillText('0', centerX - radius - 8, centerY + 8);
    ctx.fillText('1000', centerX + radius + 8, centerY + 8);
    
    // Update text
    console.log('📝 Updating text element...');
    textElement.textContent = `Credit Score: ${creditScore}`;
    
    console.log('✅ Credit score gauge initialization completed successfully!');
}

// Initialize credit score gauge on page load
console.log('🚀 Setting up credit score gauge initialization...');
console.log('📄 Document ready state:', document.readyState);

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    console.log('⚡ Document already ready, initializing gauge in 100ms...');
    setTimeout(() => {
        console.log('⏰ Timeout triggered - calling initializeReviewCreditScoreGauge()');
        initializeReviewCreditScoreGauge();
    }, 100);
} else {
    console.log('⏳ Document not ready, adding load event listener...');
    window.addEventListener('load', () => {
        console.log('📄 Window load event fired - calling initializeReviewCreditScoreGauge()');
        initializeReviewCreditScoreGauge();
    });
}

// Initialize credit score gauge on Livewire updates
console.log('🔄 Adding Livewire event listeners...');

document.addEventListener('livewire:load', () => {
    console.log('🔄 Livewire load event fired - calling initializeReviewCreditScoreGauge()');
    initializeReviewCreditScoreGauge();
});

document.addEventListener('livewire:update', () => {
    console.log('🔄 Livewire update event fired - calling initializeReviewCreditScoreGauge()');
    initializeReviewCreditScoreGauge();
});

// Listen for step changes specifically
document.addEventListener('livewire:update', (event) => {
            // Check if we're on step 3 (Application Summary)
        const step3Element = document.querySelector('[data-step="3"]');
        if (step3Element && step3Element.style.display !== 'none') {
            console.log('🎯 Step 3 detected, re-initializing credit score gauge...');
        // Small delay to ensure DOM is updated
        setTimeout(() => {
            initializeReviewCreditScoreGauge();
        }, 200);
    }
});

        // Also listen for when the step 2 div becomes visible
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const step3Element = document.querySelector('[data-step="3"]');
                    if (step3Element && step3Element.classList.contains('block')) {
                        console.log('👁️ Step 3 became visible, re-initializing credit score gauge...');
                setTimeout(() => {
                    initializeReviewCreditScoreGauge();
                }, 300);
            }
        }
    });
});

// Start observing when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
            const step3Container = document.querySelector('[data-step="3"]');
        if (step3Container) {
            observer.observe(step3Container, { attributes: true });
    }
});

// Additional debugging: Check if elements exist on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOMContentLoaded event fired');
    console.log('🔍 Checking for credit score elements...');
    
    const canvas = document.getElementById('credit-score-gauge');
    const textElement = document.getElementById('credit-score-text');
    
    console.log('📊 Canvas found on DOMContentLoaded:', canvas);
    console.log('📝 Text element found on DOMContentLoaded:', textElement);
    
    if (canvas) {
        console.log('📐 Canvas dimensions on DOMContentLoaded:', {
            width: canvas.width,
            height: canvas.height,
            offsetWidth: canvas.offsetWidth,
            offsetHeight: canvas.offsetHeight
        });
    }
});
</script>








    <!-- OTP Verification Modal -->
    @if($showOtpVerification)
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <!-- Modal Content -->
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Verify Your Identity</h3>
                <button type="button" 
                        wire:click="closeOtpModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <!-- Flash Messages -->
                @if (session()->has('otp_success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg">
                        {{ session('otp_success') }}
                    </div>
                @endif
                
                @if (session()->has('otp_error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                        {{ session('otp_error') }}
                    </div>
                @endif
                
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        We've sent a verification code to your {{ $otpSentVia ?? 'phone/email' }}. 
                        Please enter the code below to complete your loan application.
                    </p>
                </div>

                <!-- OTP Input -->
                <div class="mb-6">
                    <label for="otp-input" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter Verification Code
                    </label>
                    <input type="text" 
                           id="otp-input"
                           maxlength="6" 
                           placeholder="000000"
                           class="w-full h-12 text-center text-lg font-semibold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           wire:model="otpCode"
                           wire:keydown.enter="verifyOtp"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    @error('otpCode') 
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Resend OTP -->
                <div class="mb-6 text-center">
                    <button type="button" 
                            wire:click="resendOtp"
                            class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                        Didn't receive the code? Resend
                    </button>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200">
                <button type="button" 
                        wire:click="closeOtpModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="button" 
                        wire:click="verifyOtp"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        onclick="console.log('Verify button clicked, OTP input value:', document.getElementById('otp-input').value)"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">

<div wire:loading.remove wire:target="verifyOtp">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
</div>
                    <div wire:loading wire:target="verifyOtp">
                    <svg  class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    </div>
                    <span wire:loading.remove>Verify & Submit</span>
                    <span wire:loading>Verifying...</span>
                </button>
            </div>
        </div>
    </div>
    @endif









</div>
