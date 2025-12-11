<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your material request has been submitted successfully.</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg wire:click="$set('showSuccessMessage', false)" class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </span>
    </div>
    @endif

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Materials & Equipment Request</h2>
            <p class="text-gray-600 mt-1">Request work materials, view request history, and check inventory</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    New Request
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Request History
                </button>
                <button wire:click="$set('selectedTab', 'inventory')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'inventory' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Available Materials
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Material Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Materials</h3>
                        <form wire:submit.prevent="submitMaterialRequest" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Material Category -->
                                <div>
                                    <label for="materialCategory" class="block text-sm font-medium text-gray-700 mb-1">
                                        Material Category <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="materialCategory" id="materialCategory" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                        <option value="">Select category</option>
                                        <option value="office_supplies">Office Supplies</option>
                                        <option value="it_equipment">IT Equipment</option>
                                        <option value="cleaning_supplies">Cleaning Supplies</option>
                                        <option value="safety_equipment">Safety Equipment</option>
                                    </select>
                                    @error('materialCategory') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Material Item -->
                                <div>
                                    <label for="materialItem" class="block text-sm font-medium text-gray-700 mb-1">
                                        Material Item <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="materialItem" id="materialItem" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                            {{ !$materialCategory ? 'disabled' : '' }}>
                                        <option value="">Select item</option>
                                        @foreach($filteredMaterials as $material)
                                            <option value="{{ $material['id'] }}">
                                                {{ $material['name'] }} ({{ $material['stock'] }} {{ $material['unit'] }} available)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('materialItem') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="quantity" id="quantity" min="1"
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                           placeholder="Enter quantity">
                                    @error('quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Urgency -->
                                <div>
                                    <label for="urgency" class="block text-sm font-medium text-gray-700 mb-1">
                                        Urgency Level <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="urgency" id="urgency" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('urgency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Justification -->
                            <div>
                                <label for="justification" class="block text-sm font-medium text-gray-700 mb-1">
                                    Justification <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="justification" id="justification" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                          placeholder="Please provide a detailed justification for this request..."></textarea>
                                @error('justification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                    @break

                @case('history')
                    <!-- Request History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Urgency
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Collection Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($requestHistory as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($request['requested_date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $request['category'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $request['item'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $request['quantity'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($request['urgency'] == 'urgent')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Urgent
                                                </span>
                                            @elseif($request['urgency'] == 'high')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    High
                                                </span>
                                            @elseif($request['urgency'] == 'normal')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Normal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Low
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($request['status'] == 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @elseif($request['status'] == 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            @elseif($request['status'] == 'collected')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Collected
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $request['collection_date'] ? \Carbon\Carbon::parse($request['collection_date'])->format('M d, Y') : '-' }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No request history found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('inventory')
                    <!-- Available Materials -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Materials</h3>
                        
                        <!-- Category Filter -->
                        <div class="mb-6">
                            <label for="selectedCategory" class="block text-sm font-medium text-gray-700 mb-2">
                                Filter by Category
                            </label>
                            <select wire:model="selectedCategory" id="selectedCategory" 
                                    class="w-full md:w-1/3 rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                <option value="">All Categories</option>
                                <option value="office_supplies">Office Supplies</option>
                                <option value="it_equipment">IT Equipment</option>
                                <option value="cleaning_supplies">Cleaning Supplies</option>
                                <option value="safety_equipment">Safety Equipment</option>
                            </select>
                        </div>

                        <!-- Materials Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($availableMaterials as $category => $materials)
                                @if(!$selectedCategory || $selectedCategory == $category)
                                    @foreach($materials as $material)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $material['name'] }}</h4>
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Category:</span>
                                                <span class="font-medium">{{ ucwords(str_replace('_', ' ', $category)) }}</span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600">Available:</span>
                                                <span class="font-medium {{ $material['stock'] <= 10 ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $material['stock'] }} {{ $material['unit'] }}
                                                </span>
                                            </div>
                                            @if($material['stock'] <= 10)
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    Low Stock
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>