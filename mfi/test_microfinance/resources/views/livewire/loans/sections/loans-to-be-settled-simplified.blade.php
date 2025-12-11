{{-- SIMPLIFIED LOANS TO BE SETTLED SECTION --}}
<div class="mt-4">
    <p class="text-sm font-medium text-gray-700 mb-2">LOANS TO BE SETTLED</p>
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        
        @if($settlementData && is_array($settlementData))
            {{-- Summary Bar --}}
            @if(($settlementData['count'] ?? 0) > 0)
                <div class="flex justify-between items-center p-3 bg-red-50 rounded mb-4">
                    <div>
                        <span class="text-sm text-gray-600">Total Settlement Amount:</span>
                        <span class="ml-2 text-lg font-bold text-red-900">{{ number_format($settlementData['total_amount'] ?? 0, 2) }} TZS</span>
                    </div>
                    <button wire:click="$set('showSettlementForm', true)"
                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                        + Add
                    </button>
                </div>
            @endif

            {{-- Simple Add Form --}}
            @if($showSettlementForm)
                <div class="border border-gray-200 rounded p-3 mb-4 bg-gray-50">
                    <div class="grid grid-cols-3 gap-3">
                        <input wire:model="settlementForm.institution" type="text"
                            class="px-3 py-2 border border-gray-300 rounded text-sm"
                            placeholder="Institution Name">
                        <input wire:model="settlementForm.account" type="text"
                            class="px-3 py-2 border border-gray-300 rounded text-sm"
                            placeholder="Account Number">
                        <input wire:model="settlementForm.amount" type="number" step="0.01"
                            class="px-3 py-2 border border-gray-300 rounded text-sm"
                            placeholder="Amount">
                    </div>
                    <div class="flex justify-end gap-2 mt-3">
                        <button wire:click="cancelSettlementForm"
                            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800">
                            Cancel
                        </button>
                        <button wire:click="{{ $editingSettlementId ? 'updateSettlement' : 'addSettlement' }}"
                            class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                            {{ $editingSettlementId ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- Settlement List --}}
            @if(isset($settlementData['settlements']) && count($settlementData['settlements']) > 0)
                <div class="space-y-2">
                    @foreach($settlementData['settlements'] as $settlement)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <input type="checkbox"
                                    wire:change="toggleAmount({{ $settlement['amount'] ?? 0 }}, {{ $settlement['id'] ?? 0 }})"
                                    @if(in_array($settlement['id'] ?? 0, $selectedContracts ?? [])) checked @endif
                                    class="w-4 h-4 text-red-600 rounded">
                                <div>
                                    <div class="text-sm font-medium">{{ $settlement['institution'] }}</div>
                                    <div class="text-xs text-gray-500">Acc: {{ $settlement['account'] }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-medium">{{ number_format($settlement['amount'], 2) }} TZS</span>
                                <div class="flex gap-1">
                                    <button wire:click="editSettlement({{ $settlement['id'] }})"
                                        class="p-1 text-red-600 hover:bg-red-50 rounded">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteSettlement({{ $settlement['id'] }})"
                                        onclick="return confirm('Delete this settlement?')"
                                        class="p-1 text-red-600 hover:bg-red-50 rounded">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-6 text-gray-500">
                    <div class="text-sm mb-2">No settlements added</div>
                    <button wire:click="$set('showSettlementForm', true)"
                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                        + Add Settlement
                    </button>
                </div>
            @endif
        @else
            <div class="text-center py-4 text-gray-500 text-sm">
                No settlement data available
            </div>
        @endif
    </div>
</div>