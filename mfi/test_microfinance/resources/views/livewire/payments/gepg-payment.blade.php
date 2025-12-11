<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Include Enhanced Error Handler Component -->
        @include('livewire.payments.error-handler')

        <!-- Control Number Verification Form -->
        @if (!$showPaymentForm)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Verify Control Number</h3>
                <form wire:submit.prevent="verifyControlNumber">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="controlNumber" class="block text-sm font-medium text-gray-700">Control Number</label>
                            <input type="text" wire:model.defer="controlNumber" id="controlNumber" 
                                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                   placeholder="Enter control number">
                            @error('controlNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="verifyControlNumber">Verify</span>
                                <span wire:loading wire:target="verifyControlNumber">Verifying...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Verified Bill Details -->
      
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Verified Bill Details</h3>
                
                <!-- Bill Header Information -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Bill Header</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Control Number</p>
                                <p class="text-sm font-medium">{{ $controlNumber }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Payment Type</p>
                                <p class="text-sm font-medium">{{ $billHeader['PayType'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Entry Count</p>
                                <p class="text-sm font-medium">{{ $billHeader['EntryCnt'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status Code</p>
                                <p class="text-sm font-medium">{{ $billHeader['BillStsCode'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status Description</p>
                                <p class="text-sm font-medium">{{ $billHeader['BillStsDesc'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Channel Reference</p>
                                <p class="text-sm font-medium">{{ $billHeader['ChannelRef'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">CBP Gateway Reference</p>
                                <p class="text-sm font-medium">{{ $billHeader['CbpGwRef'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Details Information -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Bill Information</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Service Provider Code</p>
                                <p class="text-sm font-medium">{{ $serviceProviderCode }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Service Provider Name</p>
                                <p class="text-sm font-medium">{{ $serviceProviderName }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Payment Reference ID</p>
                                <p class="text-sm font-medium">{{ $paymentReferenceId }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Bill Number</p>
                                <p class="text-sm font-medium">{{ $billNumber }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Payment Option</p>
                                <p class="text-sm font-medium">{{ $paymentOption }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Bill Amount</p>
                                <p class="text-sm font-medium">{{ number_format($amount, 2) }} {{ $currency }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Minimum Payment Amount</p>
                                <p class="text-sm font-medium">{{ number_format($minimumPaymentAmount, 2) }} {{ $currency }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Currency</p>
                                <p class="text-sm font-medium">{{ $currency }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Expiry Date</p>
                                <p class="text-sm font-medium">{{ $billExpiryDate ? \Carbon\Carbon::parse($billExpiryDate)->format('d M Y H:i:s') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Bill Description</p>
                                <p class="text-sm font-medium">{{ $billDescription }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Payment Plan</p>
                                <p class="text-sm font-medium">{{ $paymentPlan }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Customer Information</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Customer Name</p>
                                <p class="text-sm font-medium">{{ $payerName }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Phone Number</p>
                                <p class="text-sm font-medium">{{ $payerMsisdn }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="text-sm font-medium">{{ $payerEmail ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="mb-6">
                    <h4 class="text-md font-medium text-gray-700 mb-3">Bank Details</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Bank Type</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankType'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Forex</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['Forex'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Bank Name</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankDetails']['BankTrfDetails']['CreditBankName'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Account Number</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankDetails']['BankTrfDetails']['CreditBankAccountNo'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Account Currency</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankDetails']['BankTrfDetails']['CreditAccountCurrency'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Account Name</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankDetails']['BankTrfDetails']['CreditAccountName'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">BIC Code</p>
                                <p class="text-sm font-medium">{{ $billDetails[0]['GepgGatewayTxn']['BankDetails']['BankTrfDetails']['CreditBankBenBic'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            wire:click="$set('showPaymentForm', false)"
                            class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Back
                    </button>
                    @if(($billHeader['BillStsCode'] ?? '') === '7101')
                        <button type="button" 
                                wire:click="initiatePayment"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="initiatePayment">Pay Now</span>
                            <span wire:loading wire:target="initiatePayment">Processing...</span>
                        </button>
                    @endif
                </div>
            </div>
    

        <!-- Payment Processing Modal -->
        @if($processing)
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 max-w-md w-full">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Processing Payment</h3>
                        <p class="mt-2 text-sm text-gray-500">Please wait while we process your payment...</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div> 