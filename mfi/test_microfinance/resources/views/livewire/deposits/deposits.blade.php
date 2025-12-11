<div class="container-fluid">
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Loading Overlay -->
        @if($isLoading)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                <p class="mt-2 text-sm text-gray-600">Loading...</p>
            </div>
        </div>
        @endif

        <!-- Error Messages -->
        @if($errorMessage)
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ $errorMessage }}</p>
        </div>
        @endif

        <!-- Success Messages -->
        @if($successMessage)
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p class="font-bold">Success</p>
            <p>{{ $successMessage }}</p>
        </div>
        @endif

        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Deposits Management</h1>
                <p class="text-gray-600 mt-1">Track and manage all deposits accounts</p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                @if($permissions['canCreate'] ?? false)
                <button 
                    wire:click="showIssueNewDepositsModal(1)" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span wire:loading.remove wire:target="showIssueNewDepositsModal">New Account</span>
                    <span wire:loading wire:target="showIssueNewDepositsModal">Processing...</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Deposits -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Deposits</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">TZS {{ number_format($totalDeposits, 2) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <span>Total deposits balance across all accounts</span>
                </div>
            </div>

            <!-- Active Accounts -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Accounts</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($activeAccounts) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <span class="flex items-center text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-1">Active deposits accounts</span>
                    </span>
                </div>
            </div>

            <!-- Inactive Accounts -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Inactive Accounts</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($inactiveAccounts) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-amber-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <span>Accounts requiring attention</span>
                </div>
            </div>

            <!-- Deposits Products -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Deposits Products</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalProducts) }}</p>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-gray-500">
                    <span>Available deposits products</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row gap-6 w-full">
            <!-- Sidebar Navigation -->
            <div class="w-full max-w-xs shrink-0">
                <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <nav class="space-y-2">
                        @if($permissions['canCreate'] ?? false)
                        <button 
                            wire:click="showCreateNewDepositsAccount"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-indigo-50 hover:text-red-900 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span wire:loading.remove wire:target="showCreateNewDepositsAccount">New Account</span>
                            <span wire:loading wire:target="showCreateNewDepositsAccount">Processing...</span>
                        </button>
                        @endif

                        @if($permissions['canDeposit'] ?? false)
                        <button 
                            wire:click="showReceiveDepositsModal"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-green-50 hover:text-green-700 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            </svg>
                            <span wire:loading.remove wire:target="showReceiveDepositsModal">Receive Deposits</span>
                            <span wire:loading wire:target="showReceiveDepositsModal">Processing...</span>
                        </button>
                        @endif

                        @if($permissions['canWithdraw'] ?? false)
                        <button 
                            wire:click="showWithdrawDepositsModal"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-red-50 hover:text-red-700 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            </svg>
                            <span wire:loading.remove wire:target="showWithdrawDepositsModal">Withdraw Deposits</span>
                            <span wire:loading wire:target="showWithdrawDepositsModal">Processing...</span>
                        </button>
                        @endif

                        @if($permissions['canView'] ?? false)
                        <button 
                            wire:click="showDepositsFullReportPage"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-indigo-50 hover:text-red-900 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span wire:loading.remove wire:target="showDepositsFullReportPage">Deposits Full Report</span>
                            <span wire:loading wire:target="showDepositsFullReportPage">Processing...</span>
                        </button>
                        @endif

                        @if($permissions['canExport'] ?? false)
                        <button 
                            wire:click="showDepositsBulkUploadPage" 
                            wire:loading.attr="disabled"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-indigo-50 hover:text-red-900 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span wire:loading.remove wire:target="showDepositsBulkUploadPage">Deposits Bulk Upload</span>
                            <span wire:loading wire:target="showDepositsBulkUploadPage">Processing...</span>
                        </button>
                        @endif

                        <button
                            wire:click="showIssueNewDepositsModal(4)"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-indigo-50 hover:text-red-900 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span wire:loading.remove wire:target="showIssueNewDepositsModal">Monthly Report</span>
                            <span wire:loading wire:target="showIssueNewDepositsModal">Processing...</span>
                        </button>

                        <button
                            wire:click="showIssueNewDepositsModal(13)"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span wire:loading.remove wire:target="showIssueNewDepositsModal(13)">Interest on Deposits</span>
                            <span wire:loading wire:target="showIssueNewDepositsModal(13)">Processing...</span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
              
                <!-- Content Area -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    @if ($selected==1 || $selected==2 || $selected==10)
                        <livewire:deposits.deposits-overview/>
                    @elseif($selected==4)
                        <livewire:deposits.monthly-report />
                    @elseif($selected==11)
                        <livewire:deposits.deposits-full-report />
                    @elseif($selected==12)
                        <livewire:deposits.deposits-bulk-upload />
                    @elseif($selected==13)
                        <livewire:deposits.interest-on-deposits />
                    @elseif($selected==3)
                        <div class="p-6 text-center text-gray-500">
                            Awaiting sample report
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @if($showCreateNewDepositsAccount)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create New Deposits Account
                            </h3>
                            <div class="mt-2">

                                <form wire:submit.prevent="createDepositsAccount">
                                    <!-- Client Number with Real-time Validation -->
                                    <div class="mb-4">
                                        <label for="clientNumber" class="block text-sm font-medium text-gray-700">Client Number</label>
                                        <input
                                            type="text"
                                            wire:model.defer="clientNumber"
                                            wire:keyup.debounce.750ms="validateDepositsMemberNumber"
                                            id="clientNumber"
                                            maxlength="5"
                                            placeholder="Enter 5-digit client number"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                            wire:loading.attr="disabled"
                                        >
                                        @error('clientNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Member Information Display --}}
                                    @if($depositsMemberDetails)
                                        <div class="mt-4 mb-4 bg-gray-50 p-4 rounded-md">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">Member Information</h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <p class="text-sm text-gray-600">Name:</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $depositsMemberDetails['first_name'] }} {{ $depositsMemberDetails['last_name'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Client Number:</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $depositsMemberDetails['client_number'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Phone:</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $depositsMemberDetails['phone_number'] ?? 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Email:</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $depositsMemberDetails['email'] ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Product Selection -->
                                    <div class="mb-4">
                                        <label for="productId" class="block text-sm font-medium text-gray-700">Product</label>
                                        <select
                                            wire:model.defer="productId"
                                            id="productId"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            wire:loading.attr="disabled"
                                        >
                                            <option value="">Select a product</option>
                                            @foreach($availableProducts as $product)
                                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('productId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Account Number, Name, and Balance removed - auto-generated by system --}}

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        type="submit"
                        wire:click="createDepositsAccount"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="createDepositsAccount">Create Account</span>
                        <span wire:loading wire:target="createDepositsAccount">Processing...</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('showCreateNewDepositsAccount', false)"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Receive Deposits Modal --}}
    @if($showReceiveDepositsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="submitReceiveDeposits">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Receive Deposits</h3>
                        </div>

                        {{-- Membership Number Verification --}}
                        <div class="mb-4">
                            <label for="membershipNumber" class="block text-sm font-medium text-gray-700">Membership Number</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" wire:model.defer="membershipNumber" id="membershipNumber" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Enter membership number">
                                <button type="button" wire:click="verifyMembership" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Verify
                                </button>
                            </div>
                            @error('membershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($verifiedMember)
                            {{-- Member Details --}}
                            <div class="mb-4 p-4 bg-gray-50 rounded-md">
                                <h4 class="text-sm font-medium text-gray-900">Member Details</h4>
                                <p class="mt-1 text-sm text-gray-600">{{ $verifiedMember['name'] }}</p>
                                <p class="mt-1 text-sm text-gray-600">Membership Type: {{ $verifiedMember['membership_type'] }}</p>
                                <p class="mt-1 text-sm text-gray-600">Client Number: {{ $verifiedMember['client_number'] }}</p>
                            </div>

                            {{-- Account Selection --}}
                            <div class="mb-4">
                                <label for="selectedAccount" class="block text-sm font-medium text-gray-700">Select Account</label>
                                <select wire:model.defer="selectedAccount" id="selectedAccount" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select an account</option>
                                    @foreach($memberAccounts as $account)
                                        <option value="{{ $account->account_number  }}">{{ $account->account_name }} ({{ $account->account_number }})</option>
                                    @endforeach
                                </select>
                                @error('selectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Amount --}}
                            <div class="mb-4">
                                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                <div class="mt-1">
                                    <input type="number" step="0.01" wire:model.defer="amount" id="amount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                                </div>
                                @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Payment Method --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <div class="mt-2 space-y-4 sm:space-y-0 sm:space-x-4">
                                    <div class="flex items-center">
                                        <input name="paymentMethod" type="radio" wire:model="paymentMethod" value="cash" id="cash" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="cash" class="ml-3 block text-sm font-medium text-gray-700">Cash</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input name="paymentMethod" type="radio" wire:model="paymentMethod" value="bank" id="bank" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="bank" class="ml-3 block text-sm font-medium text-gray-700">Bank Deposit</label>
                                    </div>
                                </div>
                                @error('paymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($paymentMethod === 'bank')
                                {{-- Bank Selection --}}
                                <div class="mb-4">
                                    <label for="selectedBank" class="block text-sm font-medium text-gray-700">Select Bank</label>
                                    <select wire:model.defer="selectedBank" id="selectedBank" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Select a bank</option>
                                        @foreach($bankAccounts as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedBank') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                @if($selectedBankDetails)
                                    {{-- Bank Account Details --}}
                                    <div class="mb-4 p-4 bg-gray-50 rounded-md">
                                        <h4 class="text-sm font-medium text-gray-900">Bank Account Details</h4>
                                        <p class="mt-1 text-sm text-gray-600">Bank: {{ $selectedBankDetails->bank_name }}</p>
                                        <p class="mt-1 text-sm text-gray-600">Account Name: {{ $selectedBankDetails->account_name }}</p>
                                        <p class="mt-1 text-sm text-gray-600">Account Number: {{ $selectedBankDetails->account_number }}</p>
                                        @if($selectedBankDetails->branch_name)
                                            <p class="mt-1 text-sm text-gray-600">Branch: {{ $selectedBankDetails->branch_name }}</p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Reference Number --}}
                                <div class="mb-4">
                                    <label for="referenceNumber" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="referenceNumber" id="referenceNumber" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter reference number">
                                    </div>
                                    @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                {{-- Deposit Date and Time --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="depositDate" class="block text-sm font-medium text-gray-700">Deposit Date</label>
                                        <div class="mt-1">
                                            <input type="date" wire:model.defer="depositDate" id="depositDate" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('depositDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="depositTime" class="block text-sm font-medium text-gray-700">Deposit Time</label>
                                        <div class="mt-1">
                                            <input type="time" wire:model.defer="depositTime" id="depositTime" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('depositTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Depositor Name --}}
                            <div class="mb-4">
                                <label for="depositorName" class="block text-sm font-medium text-gray-700">Name of Depositor</label>
                                <div class="mt-1">
                                    <input type="text" wire:model.defer="depositorName" id="depositorName" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter depositor name">
                                </div>
                                @error('depositorName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Narration --}}
                            <div class="mb-4">
                                <label for="narration" class="block text-sm font-medium text-gray-700">Narration</label>
                                <div class="mt-1">
                                    <textarea wire:model.defer="narration" id="narration" rows="3" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter transaction narration"></textarea>
                                </div>
                                @error('narration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Submit
                        </button>
                        <button type="button" wire:click="$set('showReceiveDepositsModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Withdraw Deposits Modal --}}
    @if($showWithdrawDepositsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="submitWithdrawDeposits">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Withdraw Deposits</h3>
                        </div>

                        {{-- Membership Number Verification --}}
                        <div class="mb-4">
                            <label for="withdrawMembershipNumber" class="block text-sm font-medium text-gray-700">Membership Number</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" wire:model.defer="withdrawMembershipNumber" id="withdrawMembershipNumber" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Enter membership number">
                                <button type="button" wire:click="verifyWithdrawMembership" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Verify
                                </button>
                            </div>
                            @error('withdrawMembershipNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        @if($withdrawVerifiedMember)
                            {{-- Member Details --}}
                            <div class="mb-4 p-4 bg-gray-50 rounded-md">
                                <h4 class="text-sm font-medium text-gray-900">Member Details</h4>
                                <p class="mt-1 text-sm text-gray-600">{{ $withdrawVerifiedMember['name'] }}</p>
                                <p class="mt-1 text-sm text-gray-600">Membership Type: {{ $withdrawVerifiedMember['membership_type'] }}</p>
                                <p class="mt-1 text-sm text-gray-600">Client Number: {{ $withdrawVerifiedMember['client_number'] }}</p>
                            </div>

                            {{-- Account Selection --}}
                            <div class="mb-4">
                                <label for="withdrawSelectedAccount" class="block text-sm font-medium text-gray-700">Select Account</label>
                                <select wire:model.defer="withdrawSelectedAccount" id="withdrawSelectedAccount" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select an account</option>
                                    @foreach($withdrawMemberAccounts as $account)
                                        <option value="{{ $account->account_number  }}">{{ $account->account_name }} ({{ $account->account_number }}) - Balance: {{ number_format($account->balance, 2) }}</option>
                                    @endforeach
                                </select>
                                @error('withdrawSelectedAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($withdrawSelectedAccount)
                                {{-- Account Balance Display --}}
                                <div class="mb-4 p-4 bg-red-50 rounded-md">
                                    <h4 class="text-sm font-medium text-red-900">Account Balance</h4>
                                    <p class="mt-1 text-lg font-semibold text-red-700">
                                        {{ number_format($withdrawSelectedAccountBalance, 2) }}
                                    </p>
                                </div>
                            @endif

                            {{-- Amount --}}
                            <div class="mb-4">
                                <label for="withdrawAmount" class="block text-sm font-medium text-gray-700">Withdrawal Amount</label>
                                <div class="mt-1">
                                    <input type="number" step="0.01" wire:model.defer="withdrawAmount" id="withdrawAmount" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                                </div>
                                @error('withdrawAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- Payment Method --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Withdrawal Method</label>
                                <div class="mt-2 space-y-3">
                                    <div class="flex items-center">
                                        <input name="withdrawPaymentMethod" type="radio" wire:model="withdrawPaymentMethod" value="cash" id="withdrawCash" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                                        <label for="withdrawCash" class="ml-3 block text-sm font-medium text-gray-700">Cash (Cash in Safe)</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input name="withdrawPaymentMethod" type="radio" wire:model="withdrawPaymentMethod" value="internal_transfer" id="withdrawInternal" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                                        <label for="withdrawInternal" class="ml-3 block text-sm font-medium text-gray-700">Internal Funds Transfer (NBC to NBC)</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input name="withdrawPaymentMethod" type="radio" wire:model="withdrawPaymentMethod" value="tips_mno" id="withdrawTipsMno" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                                        <label for="withdrawTipsMno" class="ml-3 block text-sm font-medium text-gray-700">TIPS Transfer to MNO Wallet</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input name="withdrawPaymentMethod" type="radio" wire:model="withdrawPaymentMethod" value="tips_bank" id="withdrawTipsBank" class="focus:ring-red-500 h-4 w-4 text-red-600 border-gray-300">
                                        <label for="withdrawTipsBank" class="ml-3 block text-sm font-medium text-gray-700">TIPS Transfer to Other Bank</label>
                                    </div>
                                </div>
                                @error('withdrawPaymentMethod') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($withdrawPaymentMethod === 'internal_transfer')
                                {{-- Internal Transfer Fields --}}
                                <div class="mb-4">
                                    <label for="withdrawNbcAccount" class="block text-sm font-medium text-gray-700">NBC Account Number</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawNbcAccount" id="withdrawNbcAccount" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter NBC account number">
                                    </div>
                                    @error('withdrawNbcAccount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="withdrawAccountHolderName" class="block text-sm font-medium text-gray-700">Account Holder Name</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawAccountHolderName" id="withdrawAccountHolderName" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter account holder name">
                                    </div>
                                    @error('withdrawAccountHolderName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            @if($withdrawPaymentMethod === 'tips_mno')
                                {{-- TIPS MNO Fields --}}
                                <div class="mb-4">
                                    <label for="withdrawMnoProvider" class="block text-sm font-medium text-gray-700">Mobile Money Provider</label>
                                    <select wire:model.defer="withdrawMnoProvider" id="withdrawMnoProvider" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md">
                                        <option value="">Select provider</option>
                                        <option value="VMCASHIN">M-PESA</option>
                                        <option value="AMCASHIN">AIRTEL-MONEY</option>
                                        <option value="TPCASHIN">TIGO-PESA</option>
                                        <option value="HPCASHIN">HALLOTEL</option>
                                        <option value="APCASHIN">AZAMPESA</option>
                                        <option value="ZPCASHIN">EZYPESA</option>
                                    </select>
                                    @error('withdrawMnoProvider') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="withdrawPhoneNumber" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawPhoneNumber" id="withdrawPhoneNumber" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter phone number (e.g., 0786123456)">
                                    </div>
                                    @error('withdrawPhoneNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="withdrawWalletHolderName" class="block text-sm font-medium text-gray-700">Wallet Holder Name</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawWalletHolderName" id="withdrawWalletHolderName" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter wallet holder name">
                                    </div>
                                    @error('withdrawWalletHolderName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            @if($withdrawPaymentMethod === 'tips_bank')
                                {{-- TIPS Bank Fields --}}
                                <div class="mb-4">
                                    <label for="withdrawBankCode" class="block text-sm font-medium text-gray-700">Bank Code</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawBankCode" id="withdrawBankCode" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter bank code (e.g., CORUTZTZ)">
                                    </div>
                                    @error('withdrawBankCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="withdrawBankAccountNumber" class="block text-sm font-medium text-gray-700">Bank Account Number</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawBankAccountNumber" id="withdrawBankAccountNumber" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter bank account number">
                                    </div>
                                    @error('withdrawBankAccountNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="withdrawBankAccountHolderName" class="block text-sm font-medium text-gray-700">Bank Account Holder Name</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawBankAccountHolderName" id="withdrawBankAccountHolderName" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter bank account holder name">
                                    </div>
                                    @error('withdrawBankAccountHolderName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            @if(in_array($withdrawPaymentMethod, ['internal_transfer', 'tips_mno', 'tips_bank']))
                                {{-- Reference Number --}}
                                <div class="mb-4">
                                    <label for="withdrawReferenceNumber" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <div class="mt-1">
                                        <input type="text" wire:model.defer="withdrawReferenceNumber" id="withdrawReferenceNumber" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter reference number">
                                    </div>
                                    @error('withdrawReferenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                {{-- Withdrawal Date and Time --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="withdrawDate" class="block text-sm font-medium text-gray-700">Withdrawal Date</label>
                                        <div class="mt-1">
                                            <input type="date" wire:model.defer="withdrawDate" id="withdrawDate" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('withdrawDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="withdrawTime" class="block text-sm font-medium text-gray-700">Withdrawal Time</label>
                                        <div class="mt-1">
                                            <input type="time" wire:model.defer="withdrawTime" id="withdrawTime" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('withdrawTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif

                            {{-- Narration --}}
                            <div class="mb-4">
                                <label for="withdrawNarration" class="block text-sm font-medium text-gray-700">Narration</label>
                                <div class="mt-1">
                                    <textarea wire:model.defer="withdrawNarration" id="withdrawNarration" rows="3" class="focus:ring-red-500 focus:border-red-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Enter transaction narration"></textarea>
                                </div>
                                @error('withdrawNarration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="submitWithdrawDeposits">Process Withdrawal</span>
                            <span wire:loading wire:target="submitWithdrawDeposits">Processing...</span>
                        </button>
                        <button type="button" wire:click="$set('showWithdrawDepositsModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Receipt Modal --}}
    @if($showReceiptModal && $receiptData)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Transaction Receipt</h3>
                        <p class="mt-1 text-sm text-gray-500">Your deposit has been processed successfully</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold">Receipt No:</span>
                                <p class="text-gray-600">{{ $receiptData['receipt_number'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Date:</span>
                                <p class="text-gray-600">{{ $receiptData['transaction_date'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Member:</span>
                                <p class="text-gray-600">{{ $receiptData['member_name'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Account:</span>
                                <p class="text-gray-600">{{ $receiptData['account_number'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Amount:</span>
                                <p class="text-green-600 font-bold">{{ $receiptData['currency'] }} {{ $receiptData['amount'] }}</p>
                            </div>
                            <div>
                                <span class="font-semibold">Payment Method:</span>
                                <p class="text-gray-600">{{ $receiptData['payment_method'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-md">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Your receipt has been generated. You can print it or save it for your records.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="printReceipt"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-print mr-2"></i>
                        Print Receipt
                    </button>
                    <button type="button" 
                            wire:click="closeReceiptModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script nonce="{{ csp_nonce() }}">
// Receipt printing functionality
window.addEventListener('printReceipt', event => {
    const receiptData = event.detail.receiptData;
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    
    // Generate the receipt HTML
    const receiptHTML = `
        <!DOCTYPE html>
        <html>
        <head>
    <meta charset="UTF-8">
            <title>Deposits Receipt</title>
            <style nonce="{{ csp_nonce() }}">
                @media print {
                    body { margin: 0; }
                    .no-print { display: none !important; }
                    .receipt { box-shadow: none; border: 1px solid #000; }
                }
                
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    line-height: 1.2;
                    color: #000;
                    margin: 0;
                    padding: 10px;
                    background-color: #f5f5f5;
                }
                
                .receipt {
                    max-width: 300px;
                    margin: 0 auto;
                    background: white;
                    padding: 15px;
                    border-radius: 5px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .header {
                    text-align: center;
                    border-bottom: 2px solid #000;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                }
                
                .header h1 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: bold;
                    text-transform: uppercase;
                }
                
                .header p {
                    margin: 5px 0;
                    font-size: 10px;
                }
                
                .receipt-title {
                    text-align: center;
                    font-size: 14px;
                    font-weight: bold;
                    margin: 10px 0;
                    text-transform: uppercase;
                }
                
                .receipt-number {
                    text-align: center;
                    font-size: 12px;
                    margin-bottom: 15px;
                }
                
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                    border-bottom: 1px dotted #ccc;
                    padding-bottom: 3px;
                }
                
                .info-label {
                    font-weight: bold;
                    min-width: 80px;
                }
                
                .info-value {
                    text-align: right;
                    flex: 1;
                }
                
                .amount-section {
                    border-top: 2px solid #000;
                    border-bottom: 2px solid #000;
                    padding: 10px 0;
                    margin: 15px 0;
                }
                
                .amount-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                }
                
                .amount-label {
                    font-weight: bold;
                    font-size: 14px;
                }
                
                .amount-value {
                    font-weight: bold;
                    font-size: 14px;
                    text-align: right;
                }
                
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 1px solid #000;
                    font-size: 10px;
                }
                
                .signature-line {
                    border-top: 1px solid #000;
                    margin-top: 30px;
                    padding-top: 5px;
                    text-align: center;
                    font-size: 10px;
                }
                
                .print-button {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                }
                
                .print-button:hover {
                    background: #0056b3;
                }
                
                .close-button {
                    position: fixed;
                    top: 20px;
                    left: 20px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                }
                
                .close-button:hover {
                    background: #545b62;
                }
                
                .barcode {
                    text-align: center;
                    margin: 10px 0;
                    font-family: monospace;
                    font-size: 20px;
                }
            </style>
        </head>
        <body>
            <button class="print-button no-print" onclick="window.print()">
                Print Receipt
            </button>
            
            <button class="close-button no-print" onclick="window.close()">
                Close
            </button>
            
            <div class="receipt">
                <div class="header">
                    <h1>MFI CORE SYSTEM</h1>
                    <p>Deposits Receipt</p>
                    <p>${receiptData.branch || 'Main Branch'}</p>
                </div>
                
                <div class="receipt-title">DEPOSITS RECEIPT</div>
                
                <div class="receipt-number">
                    Receipt No: ${receiptData.receipt_number}
                </div>
                
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">${receiptData.transaction_date}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Member:</span>
                    <span class="info-value">${receiptData.member_name}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Member No:</span>
                    <span class="info-value">${receiptData.member_number}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Account:</span>
                    <span class="info-value">${receiptData.account_number}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Account Name:</span>
                    <span class="info-value">${receiptData.account_name}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Depositor:</span>
                    <span class="info-value">${receiptData.depositor_name}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">${receiptData.payment_method}</span>
                </div>
                
                ${receiptData.payment_method === 'Bank' ? `
                <div class="info-row">
                    <span class="info-label">Bank:</span>
                    <span class="info-value">${receiptData.bank_name}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Reference:</span>
                    <span class="info-value">${receiptData.reference_number}</span>
                </div>
                ` : ''}
                
                <div class="info-row">
                    <span class="info-label">Narration:</span>
                    <span class="info-value">${receiptData.narration}</span>
                </div>
                
                <div class="amount-section">
                    <div class="amount-row">
                        <span class="amount-label">AMOUNT DEPOSITED:</span>
                        <span class="amount-value">${receiptData.currency} ${receiptData.amount}</span>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Balance After:</span>
                    <span class="info-value">${receiptData.currency} ${receiptData.balance_after}</span>
                </div>
                
                <div class="barcode">
                    *${receiptData.receipt_number}*
                </div>
                
                <div class="signature-line">
                    Processed by: ${receiptData.processed_by}
                </div>
                
                <div class="footer">
                    <p>Thank you for your deposit!</p>
                    <p>This is a computer generated receipt</p>
                    <p>For queries, contact your branch office</p>
                    <p>Generated on: ${receiptData.transaction_date}</p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(receiptHTML);
    printWindow.document.close();
    
    // Auto-print when the window loads
    printWindow.onload = function() {
        printWindow.print();
    };
});
</script>


