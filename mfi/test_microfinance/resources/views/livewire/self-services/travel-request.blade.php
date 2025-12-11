<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your travel request has been submitted successfully. You will be notified once it's approved.</span>
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
            <h2 class="text-2xl font-bold text-gray-900">Travel Request</h2>
            <p class="text-gray-600 mt-1">Submit travel requests, view history, and check travel policies</p>
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
                    Travel History
                </button>
                <button wire:click="$set('selectedTab', 'policy')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'policy' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Travel Policy
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Travel Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit Travel Request</h3>
                        <form wire:submit.prevent="submitTravelRequest" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Travel Type -->
                                <div>
                                    <label for="travelType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Travel Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="travelType" id="travelType" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                        <option value="">Select travel type</option>
                                        <option value="business_meeting">Business Meeting</option>
                                        <option value="training">Training/Workshop</option>
                                        <option value="conference">Conference/Seminar</option>
                                        <option value="client_visit">Client Visit</option>
                                        <option value="site_inspection">Site Inspection</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('travelType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Destination -->
                                <div>
                                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">
                                        Destination <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="destination" id="destination" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                           placeholder="City, Country">
                                    @error('destination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Departure Date -->
                                <div>
                                    <label for="departureDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Departure Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="departureDate" id="departureDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                    @error('departureDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Return Date -->
                                <div>
                                    <label for="returnDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Return Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="returnDate" id="returnDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                    @error('returnDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Transport Mode -->
                                <div>
                                    <label for="transportMode" class="block text-sm font-medium text-gray-700 mb-1">
                                        Mode of Transport <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="transportMode" id="transportMode" 
                                            class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                        <option value="">Select transport mode</option>
                                        <option value="flight">Flight</option>
                                        <option value="company_vehicle">Company Vehicle</option>
                                        <option value="personal_vehicle">Personal Vehicle</option>
                                        <option value="bus">Bus</option>
                                        <option value="train">Train</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('transportMode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Estimated Budget -->
                                <div>
                                    <label for="estimatedBudget" class="block text-sm font-medium text-gray-700 mb-1">
                                        Estimated Budget (TSH) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="estimatedBudget" id="estimatedBudget" 
                                           class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                           placeholder="0">
                                    @error('estimatedBudget') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Additional Options -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Accommodation Required -->
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="accommodationRequired" id="accommodationRequired" 
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <label for="accommodationRequired" class="ml-2 block text-sm text-gray-900">
                                        Accommodation Required
                                    </label>
                                </div>

                                <!-- Advance Required -->
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="advanceRequired" id="advanceRequired" 
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <label for="advanceRequired" class="ml-2 block text-sm text-gray-900">
                                        Travel Advance Required
                                    </label>
                                </div>
                            </div>

                            <!-- Advance Amount (conditional) -->
                            @if($advanceRequired)
                            <div class="max-w-md">
                                <label for="advanceAmount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Advance Amount (TSH) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" wire:model="advanceAmount" id="advanceAmount" 
                                       class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                       placeholder="0">
                                @error('advanceAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            <!-- Purpose -->
                            <div>
                                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">
                                    Purpose of Travel <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="purpose" id="purpose" rows="3" 
                                          class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                          placeholder="Provide a clear purpose for this travel..."></textarea>
                                @error('purpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Detailed Itinerary -->
                            <div>
                                <label for="detailedItinerary" class="block text-sm font-medium text-gray-700 mb-1">
                                    Detailed Itinerary <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="detailedItinerary" id="detailedItinerary" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                          placeholder="Day 1: Departure from... Meeting with...
Day 2: Site visit at..."></textarea>
                                @error('detailedItinerary') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Supporting Documents -->
                            <div>
                                <label for="supportingDocuments" class="block text-sm font-medium text-gray-700 mb-1">
                                    Supporting Documents (Optional)
                                </label>
                                <input type="file" wire:model="supportingDocuments" id="supportingDocuments" 
                                       multiple
                                       class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                       accept=".pdf,.doc,.docx,.jpg,.png">
                                <p class="text-xs text-gray-500 mt-1">Attach invitation letters, agenda, etc. (PDF, Word, or images)</p>
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
                    <!-- Travel History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Travel History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Destination
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Travel Dates
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Expense
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Purpose
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($travelHistory as $travel)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $travel['destination'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $travel['type'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($travel['departure'])->format('M d') }} - 
                                            {{ \Carbon\Carbon::parse($travel['return'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($travel['status'] == 'completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif($travel['status'] == 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Approved
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            TSH {{ number_format($travel['total_expense']) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                                            {{ $travel['purpose'] }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            No travel history found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('policy')
                    <!-- Travel Policy -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Travel Policy & Guidelines</h3>
                        
                        <!-- Policy Guidelines -->
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">General Guidelines</h4>
                            <div class="space-y-3">
                                @foreach($travelPolicies as $key => $policy)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h5 class="font-medium text-gray-900 mb-1 capitalize">{{ str_replace('_', ' ', $key) }}</h5>
                                    <p class="text-sm text-gray-600">{{ $policy }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Per Diem Rates -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Per Diem Rates</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Location
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Accommodation
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Meals
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Incidentals
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total Daily
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($perDiemRates as $rate)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $rate['location'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                TSH {{ number_format($rate['accommodation']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                TSH {{ number_format($rate['meals']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                TSH {{ number_format($rate['incidentals']) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                TSH {{ number_format($rate['accommodation'] + $rate['meals'] + $rate['incidentals']) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Important Note -->
                        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Important</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>All travel expenses must be supported by original receipts. Failure to provide receipts may result in non-reimbursement of expenses.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>