{{-- Create Share Account Modal --}}
@if($showCreateShareAccount)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div class="absolute right-0 top-0 pr-4 pt-4">
                <button type="button" wire:click="closeModal" class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Create New Share Account
                    </h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="member" class="block text-sm font-medium text-gray-700">Member Number</label>
                            <div class="mt-1">
                                <input type="text" 
                                    wire:model.defer="member" 
                                    wire:keyup="validateMemberNumber"
                                    id="member" 
                                    maxlength="5"
                                    placeholder="Enter 5-digit member number"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            </div>
                            @error('member') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($memberDetails)
                            <div class="mt-4 bg-gray-50 p-4 rounded-md">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Member Information</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Name:</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $memberDetails->first_name }} {{ $memberDetails->middle_name }} {{ $memberDetails->last_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Member Number:</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $memberDetails->client_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Phone:</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $memberDetails->phone_number ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Email:</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $memberDetails->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label for="product" class="block text-sm font-medium text-gray-700">Share Product</label>
                            <div class="mt-1">
                                <select wire:model="product" id="product" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select Product</option>
                                    @foreach($availableProducts as $product)
                                        <option value="{{ $product->product_account }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('product') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="addSharesAccount" class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    Create Account
                </button>
                <button type="button" wire:click="closeModal" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif 