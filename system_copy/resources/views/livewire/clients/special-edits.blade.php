<div class="p-2 bg-white rounded-lg shadow-md">
    <!-- Header -->
    <div class="mb-6">        
        <h1 class="text-lg text-gray-600">Edit client member phone number, account number, and email with approval workflow</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Client Search Section -->
    <div class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Search for Client</h2>
            
            <div class="flex gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        Search by Member Number, Name, Phone, or Email
                    </label>
                    <input 
                        type="text" 
                        id="search"
                        wire:model.debounce.300ms="searchTerm"
                        placeholder="Enter member number, name, phone, or email..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
            </div>

            <!-- Search Results -->
            @if(count($clients) > 0)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Search Results:</h3>
                    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                        @foreach($clients as $client)
                            <div class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                 wire:click="selectClient({{ $client->id }})">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Member #: {{ $client->client_number }} | 
                                            Phone: {{ $client->phone_number ?? 'N/A' }} | 
                                            Email: {{ $client->email ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($searchTerm && count($clients) === 0)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p class="text-yellow-800">No clients found matching your search criteria.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Form Section -->
    @if($showEditForm && $selectedClient)
        <div class="mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Client Details</h2>
                
                <!-- Selected Client Info -->
                <div class="mb-4 p-3 bg-white border border-blue-200 rounded-md">
                    <h3 class="font-medium text-gray-900 mb-2">Selected Client:</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Name:</span> 
                            {{ $selectedClient->first_name }} {{ $selectedClient->middle_name }} {{ $selectedClient->last_name }}
                        </div>
                        <div>
                            <span class="font-medium">Member Number:</span> 
                            {{ $selectedClient->client_number }}
                        </div>
                        <div>
                            <span class="font-medium">Current Phone:</span> 
                            {{ $selectedClient->phone_number ?? 'N/A' }}
                        </div>
                        <div>
                            <span class="font-medium">Current Email:</span> 
                            {{ $selectedClient->email ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form wire:submit.prevent="prepareEditPackage">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Phone Number -->
                        <div>
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="phone_number"
                                wire:model="phone_number"
                                placeholder="e.g., 0712345678"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number') border-red-500 @enderror"
                            >
                            @error('phone_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Account Number -->
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Number <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="account_number"
                                wire:model="account_number"
                                placeholder="Enter account number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('account_number') border-red-500 @enderror"
                            >
                            @error('account_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email"
                                wire:model="email"
                                placeholder="Enter email address"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex gap-3">
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2"
                        >
                            Prepare Approval Request
                        </button>
                        <button 
                            type="button"
                            wire:click="cancelEdit"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Changes</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Please review the changes before generating the approval package:
                        </p>
                        
                        <!-- Changes Summary -->
                        <div class="bg-gray-50 p-3 rounded-md text-left mb-4">
                            @foreach($editPackage as $field => $values)
                                @if($values['old'] !== $values['new'])
                                    <div class="mb-2">
                                        <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                        <div class="text-sm">
                                            <span class="text-red-600 line-through">{{ $values['old'] ?? 'N/A' }}</span>
                                            <span class="mx-2">→</span>
                                            <span class="text-green-600 font-medium">{{ $values['new'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        
                        <p class="text-xs text-gray-500">
                            This will create an approval request that needs to be reviewed before changes are applied.
                        </p>
                    </div>
                    <div class="mt-6 flex gap-3 justify-center">
                        <button 
                            wire:click="generateApprovalPackage"
                            class="px-4 py-2 bg-blue-900 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Generate Approval Request
                        </button>
                        <button 
                            wire:click="cancelEdit"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Instructions -->
    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-yellow-800 mb-2">Instructions</h3>
        <ul class="text-sm text-yellow-700 space-y-1">
            <li>• Search for a client using their member number, name, phone, or email</li>
            <li>• Select the client from the search results</li>
            <li>• Edit the phone number, account number, and/or email as needed</li>
            <li>• Review the changes in the confirmation dialog</li>
            <li>• Generate an approval request that will be sent for review</li>
            <li>• Changes will only be applied after approval by authorized personnel</li>
        </ul>
    </div>
</div>
