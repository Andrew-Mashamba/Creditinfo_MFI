<div class="min-h-screen bg-gray-50 p-6">
    <!-- Success Message -->
    @if($showSuccessMessage)
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">Your request has been submitted successfully. You'll receive updates via email.</span>
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
            <h2 class="text-2xl font-bold text-gray-900">General Requests</h2>
            <p class="text-gray-600 mt-1">Submit general requests, track status, and find answers to common questions</p>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="$set('selectedTab', 'request')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'request' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    New Request
                </button>
                <button wire:click="$set('selectedTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    My Requests
                </button>
                <button wire:click="$set('selectedTab', 'faqs')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $selectedTab == 'faqs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    FAQs
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            @switch($selectedTab)
                @case('request')
                    <!-- New Request Form -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submit New Request</h3>
                        
                        <!-- Quick Tips -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                <strong>Tip:</strong> Check the FAQs tab first - your question might already be answered there!
                            </p>
                        </div>

                        <form wire:submit.prevent="submitRequest" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Request Type -->
                                <div>
                                    <label for="requestType" class="block text-sm font-medium text-gray-700 mb-1">
                                        Request Type <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="requestType" id="requestType" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select type</option>
                                        <option value="hr_query">HR Query</option>
                                        <option value="it_support">IT Support</option>
                                        <option value="facilities">Facilities</option>
                                        <option value="finance">Finance</option>
                                        <option value="admin">Administration</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('requestType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                        Priority <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="priority" id="priority" 
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model="subject" id="subject" 
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Brief description of your request">
                                @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Detailed Description <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="description" id="description" rows="5" 
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Provide detailed information about your request..."></textarea>
                                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Attachments -->
                            <div>
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">
                                    Attachments (Optional)
                                </label>
                                <input type="file" wire:model="attachments" id="attachments" 
                                       multiple
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">You can attach multiple files. Max 5MB per file.</p>
                                @error('attachments.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                    <!-- Request History -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Requests</h3>
                        
                        <!-- Status Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-yellow-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-yellow-900">1</p>
                                <p class="text-sm text-yellow-700">Pending</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-blue-900">1</p>
                                <p class="text-sm text-blue-700">In Progress</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-green-900">2</p>
                                <p class="text-sm text-green-700">Resolved</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-2xl font-bold text-gray-900">4</p>
                                <p class="text-sm text-gray-700">Total</p>
                            </div>
                        </div>

                        <!-- Requests Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request ID
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subject
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Submitted
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Priority
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Assigned To
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($requestHistory as $request)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $request['id'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $request['type'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">
                                            {{ $request['subject'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($request['submitted_date'])->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($request['priority'] == 'urgent')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Urgent
                                                </span>
                                            @elseif($request['priority'] == 'high')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    High
                                                </span>
                                            @elseif($request['priority'] == 'normal')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Normal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Low
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($request['status'] == 'resolved')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Resolved
                                                </span>
                                            @elseif($request['status'] == 'in_progress')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    In Progress
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $request['assigned_to'] }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            No requests found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @break

                @case('faqs')
                    <!-- FAQs -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Frequently Asked Questions</h3>
                        
                        <!-- Search FAQs -->
                        <div class="mb-6">
                            <div class="relative">
                                <input type="text" wire:model.debounce.300ms="searchFaq" 
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 pl-10"
                                       placeholder="Search FAQs...">
                                <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- FAQ Categories -->
                        <div class="space-y-6">
                            @forelse($filteredFaqs as $category => $questions)
                            <div>
                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                    @if($category == 'HR')
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    @elseif($category == 'IT')
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    @elseif($category == 'Finance')
                                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    @endif
                                    {{ $category }}
                                </h4>
                                <div class="space-y-3">
                                    @foreach($questions as $faq)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h5 class="font-medium text-gray-900 mb-2">{{ $faq['question'] }}</h5>
                                        <p class="text-sm text-gray-600">{{ $faq['answer'] }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No FAQs found matching your search.</p>
                            </div>
                            @endforelse
                        </div>

                        <!-- Still Need Help -->
                        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                            <h4 class="text-lg font-semibold text-blue-900 mb-2">Still have questions?</h4>
                            <p class="text-sm text-blue-700 mb-4">If you couldn't find the answer you're looking for, submit a new request.</p>
                            <button wire:click="$set('selectedTab', 'request')" 
                                    class="bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition duration-150 ease-in-out">
                                Submit New Request
                            </button>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
    </div>
</div>