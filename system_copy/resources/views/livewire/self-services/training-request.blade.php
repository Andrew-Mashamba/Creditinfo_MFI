<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your training request has been submitted successfully. HR will review and notify you.</span>
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
            <h2 class="text-2xl font-bold text-gray-900">Training & Development</h2>
            <p class="text-gray-600 mt-1">Request training, view history, and explore available courses</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Request Training
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    My Training History
                </button>
                <button wire:click="$set('selectedTab', 'catalog')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'catalog' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Training Catalog
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- Training Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit Training Request</h3>
                        <form wire:submit.prevent="submitTrainingRequest" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Training Type -->
                                <div>
                                    <label for="trainingType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Training Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="trainingType" id="trainingType" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select type</option>
                                        <option value="technical">Technical Skills</option>
                                        <option value="soft_skills">Soft Skills</option>
                                        <option value="management">Management/Leadership</option>
                                        <option value="certification">Professional Certification</option>
                                        <option value="conference">Conference/Seminar</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('trainingType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Training Title -->
                                <div>
                                    <label for="trainingTitle" class="block text-sm font-medium text-gray-700 mb-1">
                                        Training Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="trainingTitle" id="trainingTitle" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="e.g., Advanced Excel Training">
                                    @error('trainingTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Training Provider -->
                                <div>
                                    <label for="provider" class="block text-sm font-medium text-gray-700 mb-1">
                                        Training Provider <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="provider" id="provider" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Organization/Institution name">
                                    @error('provider') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Duration -->
                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">
                                        Duration <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="duration" id="duration" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="e.g., 3 days, 2 weeks">
                                    @error('duration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="startDate" id="startDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Minimum 14 days advance notice required</p>
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                        End Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="endDate" id="endDate" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Location -->
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                        Location <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="location" id="location" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Training venue/location">
                                    @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Delivery Mode -->
                                <div>
                                    <label for="deliveryMode" class="block text-sm font-medium text-gray-700 mb-1">
                                        Delivery Mode <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="deliveryMode" id="deliveryMode" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select mode</option>
                                        <option value="in_person">In-Person</option>
                                        <option value="online">Online</option>
                                        <option value="hybrid">Hybrid</option>
                                        <option value="self_paced">Self-Paced</option>
                                    </select>
                                    @error('deliveryMode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Cost -->
                                <div>
                                    <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">
                                        Training Cost (TSH) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="cost" id="cost" 
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="0">
                                    @error('cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Training Objectives -->
                            <div>
                                <label for="objectives" class="block text-sm font-medium text-gray-700 mb-1">
                                    Training Objectives <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="objectives" id="objectives" rows="3" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="What are the main objectives of this training?"></textarea>
                                @error('objectives') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Benefits to Role -->
                            <div>
                                <label for="benefits" class="block text-sm font-medium text-gray-700 mb-1">
                                    How will this training benefit your current role? <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="benefits" id="benefits" rows="3" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Explain how this training will improve your performance..."></textarea>
                                @error('benefits') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Business Justification -->
                            <div>
                                <label for="justification" class="block text-sm font-medium text-gray-700 mb-1">
                                    Business Justification <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="justification" id="justification" rows="4" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Provide a detailed business case for this training investment..."></textarea>
                                @error('justification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Training Brochure -->
                            <div>
                                <label for="trainingBrochure" class="block text-sm font-medium text-gray-700 mb-1">
                                    Training Brochure/Information (Optional)
                                </label>
                                <input type="file" wire:model="trainingBrochure" id="trainingBrochure" 
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                       accept=".pdf,.doc,.docx">
                                <p class="text-xs text-gray-500 mt-1">Upload training brochure or detailed information (PDF or Word)</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                    @break

                @case('history')
                    <!-- Training History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Training History</h3>
                        
                        <!-- Certifications Summary -->
                        <div class="mb-6 bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Active Certifications</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach($certifications as $cert)
                                <div class="bg-white rounded p-3 border border-blue-200">
                                    <p class="font-medium text-gray-900">{{ $cert['name'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $cert['issuer'] }} • Expires: {{ \Carbon\Carbon::parse($cert['expiry'])->format('M Y') }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Training History Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Training Title
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Provider
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dates
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cost
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Certificate
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rating
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($trainingHistory as $training)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $training['title'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $training['provider'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $training['dates'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            TSH {{ number_format($training['cost']) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($training['status'] == 'completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif($training['status'] == 'approved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Approved
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($training['certificate'])
                                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($training['rating'])
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $training['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No training history found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('catalog')
                    <!-- Training Catalog -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Training Programs</h3>
                        
                        <div class="space-y-6">
                            <!-- Technical Skills -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Technical Skills
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($trainingCatalog['technical'] as $course)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-blue-300 transition-colors">
                                        <h5 class="font-medium text-gray-900">{{ $course['title'] }}</h5>
                                        <div class="mt-2 flex items-center justify-between text-sm text-gray-600">
                                            <span>Duration: {{ $course['duration'] }}</span>
                                            <span>Level: {{ $course['level'] }}</span>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-blue-600">TSH {{ number_format($course['cost']) }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Soft Skills -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Soft Skills
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($trainingCatalog['soft_skills'] as $course)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-green-300 transition-colors">
                                        <h5 class="font-medium text-gray-900">{{ $course['title'] }}</h5>
                                        <div class="mt-2 flex items-center justify-between text-sm text-gray-600">
                                            <span>Duration: {{ $course['duration'] }}</span>
                                            <span>Level: {{ $course['level'] }}</span>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-green-600">TSH {{ number_format($course['cost']) }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Management -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Management & Leadership
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($trainingCatalog['management'] as $course)
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-purple-300 transition-colors">
                                        <h5 class="font-medium text-gray-900">{{ $course['title'] }}</h5>
                                        <div class="mt-2 flex items-center justify-between text-sm text-gray-600">
                                            <span>Duration: {{ $course['duration'] }}</span>
                                            <span>Level: {{ $course['level'] }}</span>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-purple-600">TSH {{ number_format($course['cost']) }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        These are pre-approved training programs. Custom training requests may require additional approval time.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>