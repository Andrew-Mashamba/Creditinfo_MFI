<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your resignation request has been submitted successfully. HR will review and process your request.</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg wire:click="$set('showSuccessMessage', false)" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Resignation Request</h2>
            <p class="text-gray-600 mt-1">Submit resignation, view status, and access exit guidelines</p>
        </div>

        <!-- Current Status Alert -->
        @if($currentStatus && $currentStatus['status'] !== 'draft')
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">
                        Status: 
                        @if($currentStatus['status'] === 'submitted')
                            <span class="font-bold">Submitted - Under Review</span>
                        @elseif($currentStatus['status'] === 'under_review')
                            <span class="font-bold">Under Review by Management</span>
                        @elseif($currentStatus['status'] === 'approved')
                            <span class="font-bold text-green-700">Approved</span>
                        @elseif($currentStatus['status'] === 'withdrawn')
                            <span class="font-bold text-gray-700">Withdrawn</span>
                        @endif
                    </p>
                    @if($currentStatus['submitted_date'])
                        <p class="text-sm text-blue-700 mt-1">Submitted on: {{ \Carbon\Carbon::parse($currentStatus['submitted_date'])->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Resignation Form
                </button>
                <button wire:click="$set('selectedTab', 'status')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'status' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Exit Status
                </button>
                <button wire:click="$set('selectedTab', 'guidelines')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'guidelines' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Guidelines
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Resignation Form -->
                    <div class="p-6">
                        @if($currentStatus['status'] === 'draft')
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit Resignation Request</h3>
                        <form wire:submit.prevent="submitResignation" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Resignation Date -->
                                <div>
                                    <label for="resignationDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Resignation Submission Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="resignationDate" id="resignationDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('resignationDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Last Working Date -->
                                <div>
                                    <label for="lastWorkingDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Proposed Last Working Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="lastWorkingDate" id="lastWorkingDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('lastWorkingDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Minimum 30 days notice period required</p>
                                </div>

                                <!-- Reason Category -->
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                                        Primary Reason <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="reason" id="reason" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select reason</option>
                                        <option value="career_growth">Career Growth</option>
                                        <option value="higher_education">Higher Education</option>
                                        <option value="personal_reasons">Personal Reasons</option>
                                        <option value="relocation">Relocation</option>
                                        <option value="health_reasons">Health Reasons</option>
                                        <option value="better_opportunity">Better Opportunity</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Upload Resignation Letter -->
                                <div>
                                    <label for="resignationLetter" class="block text-sm font-medium text-gray-700 mb-1">
                                        Upload Resignation Letter <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" wire:model="resignationLetter" id="resignationLetter" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           accept=".pdf,.doc,.docx">
                                    @error('resignationLetter') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">PDF or Word document (max 2MB)</p>
                                </div>
                            </div>

                            <!-- Detailed Reason -->
                            <div>
                                <label for="detailedReason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Detailed Reason for Resignation <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="detailedReason" id="detailedReason" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Please provide a detailed explanation for your resignation..."></textarea>
                                @error('detailedReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Handover Notes -->
                            <div>
                                <label for="handoverNotes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Handover Plan <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="handoverNotes" id="handoverNotes" rows="3" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Brief description of how you plan to hand over your responsibilities..."></textarea>
                                @error('handoverNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                                    Submit Resignation
                                </button>
                            </div>
                        </form>
                        @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Resignation Already Submitted</h3>
                            <p class="mt-1 text-sm text-gray-500">Your resignation is currently {{ $currentStatus['status'] }}.</p>
                            @if($currentStatus['status'] === 'submitted' || $currentStatus['status'] === 'under_review')
                            <div class="mt-6">
                                <button wire:click="withdrawResignation" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm">
                                    Withdraw Resignation
                                </button>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @break

                @case('status')
                    <!-- Exit Status & Checklist -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exit Process Checklist</h3>
                        
                        <div class="mb-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Notice Period:</span>
                                        <span class="font-medium ml-2">{{ $currentStatus['notice_period'] }} days</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Clearance Status:</span>
                                        <span class="font-medium ml-2 capitalize">{{ $currentStatus['clearance_status'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($exitChecklist as $index => $item)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($item['status'])
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['item'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $item['department'] }}</p>
                                    </div>
                                </div>
                                <div>
                                    @if($item['status'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @break

                @case('guidelines')
                    <!-- Exit Guidelines -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exit Process Guidelines</h3>
                        
                        <div class="space-y-4">
                            @foreach($guidelines as $key => $guideline)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2 capitalize">{{ str_replace('_', ' ', $key) }}</h4>
                                <p class="text-sm text-gray-600">{{ $guideline }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Important Note</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Failure to serve the complete notice period may result in deduction from final settlement as per company policy.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>

    <!-- Withdrawal Confirmation Modal -->
    @if($withdrawRequest)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Withdraw Resignation
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to withdraw your resignation? This action will cancel your resignation request.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="confirmWithdraw" type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Withdraw
                    </button>
                    <button wire:click="$set('withdrawRequest', false)" type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>