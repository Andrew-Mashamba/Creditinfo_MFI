{{-- Assessment Completion Banner --}}
@php
$tabStateService = app(\App\Services\LoanTabStateService::class);
$loanID = session('currentloanID');
$isAssessmentCompleted = $tabStateService->isTabCompleted($loanID, 'assessment');
@endphp



<div class="mt-2"></div>

{{-- LOANS TO BE SETTLED SECTION --}}
<p for="stability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400 mt-4">LOANS TO BE SETTLED</p>
<div id="stability" class="w-full bg-gray-50 rounded rounded-lg shadow-sm p-1 mb-4">

    @if($settlementData && is_array($settlementData) && isset($settlementData['total_amount']))
    <div class="w-full bg-white rounded rounded-lg shadow-sm p-2">

        <!-- Settlement Summary Dashboard -->
        @php 
        $summary = [
            'status' => ($settlementData['has_settlements'] ?? false) ? 'Active' : 'No Settlements',
            'settlement_count' => $settlementData['count'] ?? 0,
            'total_amount' => $settlementData['total_amount'] ?? 0,
            'average_amount' => ($settlementData['count'] ?? 0) > 0 ? ($settlementData['total_amount'] ?? 0) / ($settlementData['count'] ?? 1) : 0,
            'last_updated' => ($settlementData['count'] ?? 0) > 0 ? now() : null
        ];
        @endphp
        
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-blue-900">Settlement Summary</h3>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                        @if($summary['status'] === 'Active') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $summary['status'] }}
                    </span>
                    <button wire:click="$set('showSettlementForm', true)"
                        class="px-3 py-1 text-xs font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-900 focus:ring-2 focus:ring-blue-500 transition-colors">
                        + Add Settlement
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="bg-white p-3 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-blue-900">{{ $summary['settlement_count'] }}</div>
                    <div class="text-xs text-blue-600">Total Settlements</div>
                </div>
                <div class="bg-white p-3 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-green-900">{{ number_format($summary['total_amount'], 2) }}</div>
                    <div class="text-xs text-green-600">Total Amount (TZS)</div>
                </div>
                <div class="bg-white p-3 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-purple-900">{{ number_format($summary['average_amount'], 2) }}</div>
                    <div class="text-xs text-purple-600">Average Amount (TZS)</div>
                </div>
                <div class="bg-white p-3 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-orange-900">{{ isset($summary['last_updated']) && $summary['last_updated'] ? \Carbon\Carbon::parse($summary['last_updated'])->format('M d') : 'N/A' }}</div>
                    <div class="text-xs text-orange-600">Last Updated</div>
                </div>
            </div>

            <!-- Impact on Disbursement -->
            @if($summary['total_amount'] > 0)
            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h6 class="text-sm font-semibold text-yellow-900">Impact on Disbursement</h6>
                        <p class="text-xs text-yellow-700">This amount will be deducted from the loan disbursement</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-yellow-900">-{{ number_format($summary['total_amount'], 2) }} TZS</div>
                        <div class="text-xs text-yellow-600">Deduction</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Error Message -->
        @if($errorMessage)
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm text-red-800">{{ $errorMessage }}</span>
            </div>
        </div>
        @endif

        <!-- Add/Edit Settlement Form -->
        @if($showSettlementForm)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">
                    {{ $editingSettlementId ? 'Edit Settlement' : 'Add New Settlement' }}
                </h4>
                <button wire:click="cancelSettlementForm"
                    class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Institution Name <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="settlementForm.institution" type="text"
                        class="w-full p-3 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="e.g., Commercial Bank, Microfinance Institution" />
                    @error('settlementForm.institution')
                    <span class="text-xs text-red-600 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Account Number <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="settlementForm.account" type="text"
                        class="w-full p-3 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter account number" />
                    @error('settlementForm.account')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Settlement Amount (TZS) *</label>
                    <input wire:model="settlementForm.amount" type="number" step="0.01" min="0"
                        class="w-full p-3 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00" />
                    @error('settlementForm.amount')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <button wire:click="cancelSettlementForm"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                @if($editingSettlementId)
                <button wire:click="updateSettlement"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-900 focus:ring-2 focus:ring-blue-500">
                    Update Settlement
                </button>
                @else
                <button wire:click="addSettlement"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500">
                    Add Settlement
                </button>
                @endif
            </div>
        </div>
        @endif

        <!-- Existing Settlements -->
        @if(isset($settlementData['settlements']) && is_array($settlementData['settlements']) && count($settlementData['settlements']) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-900">Existing Settlements</h4>

            @foreach($settlementData['settlements'] as $settlement)
            <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">{{ $settlement['id'] }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div>
                                        <h5 class="font-medium text-gray-900">{{ $settlement['institution'] }}</h5>
                                        <p class="text-sm text-gray-500">Account: {{ $settlement['account'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">{{ number_format($settlement['amount'], 2) }} TZS</div>
                                        <div class="text-xs text-gray-500">
                                            Updated: {{ \Carbon\Carbon::parse($settlement['updated_at'])->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-3">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox"
                            wire:change="toggleAmount({{ $settlement['amount'] ?? 0 }}, {{ $settlement['id'] ?? 0 }})"
                            @if(isset($selectedContracts) && is_array($selectedContracts) && in_array($settlement['id'] ?? 0, $selectedContracts)) checked @endif
                            value="{{ $settlement['id'] ?? '' }}"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <label class="text-sm text-gray-700">Include in settlement</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="editSettlement({{ $settlement['id'] }})"
                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                        <button wire:click="deleteSettlement({{ $settlement['id'] }})"
                            onclick="return confirm('Are you sure you want to delete this settlement?')"
                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Settlement Actions -->
        @if(isset($selectedContracts) && is_array($selectedContracts) && count($selectedContracts) > 0)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Settlement Actions</h4>
            <div class="flex space-x-3">
                <button wire:click="addSettlement" 
                    class="px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Settlement
                </button>
                
                <button wire:click="clearSettlementSelection" 
                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Selection
                </button>
            </div>
        </div>
        @endif
        @else
        <!-- No Settlements -->
        <div class="text-center py-8">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="text-gray-500 text-sm">No settlements found</div>
            <div class="text-gray-400 text-xs mt-2">Click "Add Settlement" to create your first settlement</div>
            <button wire:click="$set('showSettlementForm', true)" 
                class="mt-4 px-4 py-2 bg-blue-900 text-white text-sm font-medium rounded-lg hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add First Settlement
            </button>
        </div>
        @endif

        <!-- Settlement Summary -->
        @if($summary['total_amount'] > 0)
        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
            <h4 class="text-sm font-semibold text-green-900 mb-2">Total Settlement Summary</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-green-700">Total Amount:</span>
                    <span class="font-semibold text-green-900">{{ number_format($summary['total_amount'], 2) }} TZS</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-green-700">Settlements:</span>
                    <span class="font-semibold text-green-900">{{ $summary['settlement_count'] }}</span>
                </div>
            </div>
        </div>
        @endif

    </div>
    @else
    <div class="text-center py-8">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="text-gray-500 text-sm">No settlement data available</div>
        <div class="text-gray-400 text-xs mt-2">Settlement information will appear here when available</div>
    </div>
    @endif

</div> 