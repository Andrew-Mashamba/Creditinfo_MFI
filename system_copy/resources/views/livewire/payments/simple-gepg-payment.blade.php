<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">Simple GEPG Payment</h2>
    
    {{-- Success Message --}}
    @if($successMessage)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ $successMessage }}
        </div>
    @endif
    
    {{-- Error Message --}}
    @if($errorMessage)
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $errorMessage }}
        </div>
    @endif
    
    {{-- Verification Form --}}
    @if(!$showPaymentForm)
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Step 1: Verify Control Number</h3>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Control Number
                </label>
                <input type="text" 
                       wire:model.defer="controlNumber" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter control number">
                @error('controlNumber')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <button wire:click="verifyControlNumber" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400">
                <span wire:loading.remove wire:target="verifyControlNumber">Verify Control Number</span>
                <span wire:loading wire:target="verifyControlNumber">Verifying...</span>
            </button>
        </div>
    @endif
    
    {{-- Payment Form --}}
    @if($showPaymentForm && $verificationResult)
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Step 2: Confirm Payment</h3>
            
            {{-- Status Badge --}}
            <div class="flex items-center space-x-2">
                @if($canPay)
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        Status: {{ $billStatus }}
                    </span>
                @else
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        Status: {{ $billStatus }}
                    </span>
                @endif
            </div>
            
            {{-- Bill Details --}}
            <div class="p-4 bg-gray-50 rounded-md">
                <h4 class="font-medium mb-3">Bill Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Control Number:</span>
                        <p class="text-gray-900">{{ $controlNumber }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Service Provider:</span>
                        <p class="text-gray-900">{{ $serviceProvider }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Description:</span>
                        <p class="text-gray-900">{{ $billDescription }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Original Amount:</span>
                        <p class="text-gray-900">TZS {{ number_format($billAmount, 2) }}</p>
                    </div>
                    @if($paidAmount > 0)
                        <div>
                            <span class="font-medium text-gray-600">Already Paid:</span>
                            <p class="text-gray-900">TZS {{ number_format($paidAmount, 2) }}</p>
                        </div>
                    @endif
                    <div>
                        <span class="font-medium text-gray-600">Expiry Date:</span>
                        <p class="text-gray-900">{{ $expiryDate }}</p>
                    </div>
                </div>
            </div>
            
            {{-- Payment Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Payment Amount (TZS)
                </label>
                <input type="number" 
                       wire:model.defer="amount" 
                       min="1" 
                       step="0.01"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('amount')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Account Number --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Debit Account Number
                </label>
                <input type="text" 
                       wire:model.defer="accountNumber" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Enter account number">
                @error('accountNumber')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex space-x-3">
                <button wire:click="processPayment" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400">
                    <span wire:loading.remove wire:target="processPayment">Process Payment</span>
                    <span wire:loading wire:target="processPayment">Processing...</span>
                </button>
                
                <button wire:click="resetForm" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Cancel
                </button>
            </div>
        </div>
    @endif
    
    {{-- Payment Result --}}
    @if($paymentResult)
        <div class="mt-4 p-4 bg-blue-50 rounded-md">
            <h4 class="font-medium mb-2">Payment Result</h4>
            <pre class="text-sm">{{ json_encode($paymentResult, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
    
</div>