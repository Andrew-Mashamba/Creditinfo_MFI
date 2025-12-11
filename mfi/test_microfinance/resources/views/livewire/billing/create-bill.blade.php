<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-4">Create New Bill</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="createBill">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Client Selection -->
            <div class="mb-4">
                <label for="client_number" class="block text-sm font-medium text-gray-700">Client</label>
                <div x-data="{ open: false, search: '', selected: null }" 
                     x-init="
                        $watch('search', value => {
                            if (value.length > 0) {
                                open = true;
                            }
                        });
                        $watch('selected', value => {
                            if (value) {
                                $wire.set('client_number', value);
                                open = false;
                            }
                        });
                     "
                     class="relative">
                    <div class="relative">
                        <input type="text" 
                               x-model="search"
                               placeholder="Search client by name or client number..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white text-gray-900"
                               @click="open = true">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="open" 
                         @click.away="open = false"
                         class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                        <template x-for="client in $wire.clients.filter(c => 
                            c.full_name.toLowerCase().includes(search.toLowerCase()) ||
                            c.client_number.toLowerCase().includes(search.toLowerCase()) ||
                            c.mobile_phone_number.toLowerCase().includes(search.toLowerCase())
                        )" :key="client.id">
                            <div @click="selected = client.client_number; search = client.full_name"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50">
                                <div class="flex items-center">
                                    <span class="ml-3 block truncate text-gray-900" x-text="client.full_name"></span>
                                </div>
                                <div class="text-xs text-gray-500 ml-3">
                                    <span x-text="'Client #: ' + client.client_number"></span>
                                    <span class="ml-2" x-text="'Phone: ' + client.mobile_phone_number"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="$wire.clients.filter(c => 
                            c.full_name.toLowerCase().includes(search.toLowerCase()) ||
                            c.client_number.toLowerCase().includes(search.toLowerCase()) ||
                            c.mobile_phone_number.toLowerCase().includes(search.toLowerCase())
                        ).length === 0" 
                             class="cursor-default select-none relative py-2 pl-3 pr-9 text-gray-500">
                            No clients found
                        </div>
                    </div>
                </div>
                @error('client_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Service Selection -->
            <div class="mb-4">
                <label for="service_id" class="block text-sm font-medium text-gray-700">Service</label>
                <select id="service_id" wire:model="service_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
                @error('service_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Amount -->
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">TZS</span>
                    </div>
                    <input type="number" wire:model="amount" id="amount" step="0.01"
                           class="pl-12 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Due Date -->
            <div class="mb-4">
                <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                <input type="date" wire:model="due_date" id="due_date"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Is Recurring -->
            <div class="mb-4">
                <label for="is_recurring" class="block text-sm font-medium text-gray-700">Bill Type</label>
                <select id="is_recurring" wire:model="is_recurring"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="1">Recurring</option>
                    <option value="2">One-time</option>
                </select>
                @error('is_recurring') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Payment Mode -->
            <div class="mb-4">
                <label for="payment_mode" class="block text-sm font-medium text-gray-700">Payment Mode</label>
                <select id="payment_mode" wire:model="payment_mode"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="1">Cash</option>
                    <option value="2">Bank Transfer</option>
                    <option value="3">Mobile Money</option>
                    <option value="4">Check</option>
                    <option value="5">Other</option>
                </select>
                @error('payment_mode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Is Mandatory -->
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model="is_mandatory" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-600">This bill is mandatory</span>
            </label>
            @error('is_mandatory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span wire:loading.remove wire:target="createBill">Create Bill</span>
                <span wire:loading wire:target="createBill">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating...
                </span>
            </button>
        </div>
    </form>
</div> 