<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-red-100 rounded-xl">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Communications Management</h2>
                    <p class="text-gray-600">Manage incoming and outgoing official communications, letters, and documents</p>
                </div>
            </div>
            <button wire:click="openUploadModal" 
                    class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Upload Communication</span>
            </button>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="mb-8 bg-gray-50 rounded-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="searchTerm" 
                           type="text" 
                           placeholder="Search communications..."
                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select wire:model.live="selectedType" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    <option value="">All Types</option>
                    @foreach($communicationTypes as $key => $type)
                        <option value="{{ $key }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select wire:model.live="selectedDepartment" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    <option value="">All Departments</option>
                    @foreach($departments as $key => $dept)
                        <option value="{{ $key }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="selectedStatus" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    <option value="">All Status</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Results Count -->
            <div class="flex items-end">
                <div class="text-sm text-gray-600">
                    {{ count($communications) }} of {{ $totalCommunications }} communications
                </div>
            </div>
        </div>
    </div>

    <!-- Communications Grid -->
    @if(count($communications) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($communications as $comm)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                    <!-- Communication Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                        {{ $comm['type'] === 'incoming' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $communicationTypes[$comm['type']] }}
                                    </span>
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                        {{ $comm['priority'] === 'urgent' ? 'bg-red-100 text-red-800' : 
                                           ($comm['priority'] === 'high' ? 'bg-orange-100 text-orange-800' : 
                                           ($comm['priority'] === 'normal' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($comm['priority']) }}
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $comm['subject'] }}</h3>
                                <p class="text-sm text-gray-600 mb-2">Ref: {{ $comm['reference_number'] }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if(str_contains($comm['file_type'], 'pdf'))
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @elseif(str_contains($comm['file_type'], 'word'))
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Department and Status -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">{{ $departments[$comm['department']] ?? $comm['department'] }}</span>
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full
                                {{ $comm['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($comm['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($comm['status'] === 'archived' ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800')) }}">
                                {{ $statuses[$comm['status']] }}
                            </span>
                        </div>

                        <!-- From/To Information -->
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-600">From: <span class="font-medium text-gray-900">{{ $comm['from_whom'] }}</span></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-gray-600">To: <span class="font-medium text-gray-900">{{ $comm['to_whom'] }}</span></span>
                            </div>
                        </div>

                        <!-- Date Information -->
                        <div class="flex items-center justify-between text-xs text-gray-500 mt-3">
                            <span>
                                @if($comm['type'] === 'incoming' && $comm['date_received'])
                                    Received: {{ \Carbon\Carbon::parse($comm['date_received'])->format('M d, Y') }}
                                @elseif($comm['type'] === 'outgoing' && $comm['date_sent'])
                                    Sent: {{ \Carbon\Carbon::parse($comm['date_sent'])->format('M d, Y') }}
                                @endif
                            </span>
                            <span>{{ number_format($comm['file_size'] / 1024, 1) }} KB</span>
                        </div>
                    </div>

                    <!-- Communication Actions -->
                    <div class="p-6">
                        <div class="flex items-center space-x-3">
                            <button wire:click="openViewModal('{{ $comm['id'] }}')" 
                                    class="flex-1 px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View</span>
                            </button>
                            
                            <button wire:click="downloadCommunication('{{ $comm['id'] }}')" 
                                    class="px-4 py-2 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                            
                            <button wire:click="openDeleteModal('{{ $comm['id'] }}')" 
                                    class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($totalPages > 1)
            <div class="mt-8 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Page {{ $page }} of {{ $totalPages }}
                </div>
                <div class="flex items-center space-x-2">
                    @if($page > 1)
                        <button wire:click="previousPage" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Previous
                        </button>
                    @endif
                    
                    @if($page < $totalPages)
                        <button wire:click="nextPage" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Next
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No communications found</h3>
            <p class="text-gray-600 mb-4">
                @if($searchTerm || $selectedType || $selectedDepartment || $selectedStatus)
                    Try adjusting your search criteria or upload a new communication
                @else
                    Get started by uploading your first communication
                @endif
            </p>
            @if(!$searchTerm && !$selectedType && !$selectedDepartment && !$selectedStatus)
                <button wire:click="openUploadModal" 
                        class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200">
                    Upload First Communication
                </button>
            @endif
        </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Upload New Communication</h2>
                    <button wire:click="closeUploadModal" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6">
                    <form wire:submit.prevent="uploadCommunication">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Communication Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Communication Type *</label>
                                    <select wire:model="communicationType" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                        <option value="">Select type</option>
                                        @foreach($communicationTypes as $key => $type)
                                            <option value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('communicationType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Subject -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                    <input wire:model="subject" 
                                           type="text" 
                                           placeholder="Enter communication subject..."
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                    @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Reference Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number *</label>
                                    <input wire:model="referenceNumber" 
                                           type="text" 
                                           placeholder="Enter reference number..."
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                    @error('referenceNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Department -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                                    <select wire:model="department" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                        <option value="">Select department</option>
                                        @foreach($departments as $key => $dept)
                                            <option value="{{ $key }}">{{ $dept }}</option>
                                        @endforeach
                                    </select>
                                    @error('department') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                                    <select wire:model="priority" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                        @foreach($priorities as $key => $priority)
                                            <option value="{{ $key }}">{{ $priority }}</option>
                                        @endforeach
                                    </select>
                                    @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- From Whom -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">From Whom *</label>
                                    <input wire:model="fromWhom" 
                                           type="text" 
                                           placeholder="Enter sender name/organization..."
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                    @error('fromWhom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- To Whom -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">To Whom *</label>
                                    <input wire:model="toWhom" 
                                           type="text" 
                                           placeholder="Enter recipient name/organization..."
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                    @error('toWhom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Date Received/Sent -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        @if($communicationType === 'incoming')
                                            Date Received *
                                        @elseif($communicationType === 'outgoing')
                                            Date Sent *
                                        @else
                                            Date *
                                        @endif
                                    </label>
                                    <input wire:model="{{ $communicationType === 'incoming' ? 'dateReceived' : 'dateSent' }}" 
                                           type="date" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                    @error('dateReceived') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    @error('dateSent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                    <select wire:model="status" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                                        @foreach($statuses as $key => $status)
                                            <option value="{{ $key }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                    <textarea wire:model="description" 
                                              rows="3" 
                                              placeholder="Enter communication description..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"></textarea>
                                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Communication File *</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-red-400 transition-colors">
                                <input wire:model="uploadedFile" 
                                       type="file" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="hidden" 
                                       id="comm-file-upload">
                                <label for="comm-file-upload" class="cursor-pointer">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-gray-600 mb-2">
                                        <span class="font-medium text-red-600 hover:text-red-500">Click to upload</span>
                                        or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)</p>
                                </label>
                            </div>
                            @if($uploadedFile)
                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-sm text-green-700">{{ $uploadedFile->getClientOriginalName() }}</span>
                                    </div>
                                </div>
                            @endif
                            @error('uploadedFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <button type="button" 
                                    wire:click="closeUploadModal"
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200">
                                Upload Communication
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Communication Modal -->
    @if($showViewModal && $selectedCommunication)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $selectedCommunication['subject'] }}</h2>
                            <p class="text-sm text-gray-600">Ref: {{ $selectedCommunication['reference_number'] }}</p>
                        </div>
                    </div>
                    <button wire:click="closeViewModal" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Type:</span>
                                        <span class="font-medium text-gray-900">{{ $communicationTypes[$selectedCommunication['type']] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Department:</span>
                                        <span class="font-medium text-gray-900">{{ $departments[$selectedCommunication['department']] ?? $selectedCommunication['department'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Priority:</span>
                                        <span class="font-medium text-gray-900">{{ ucfirst($selectedCommunication['priority']) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-medium text-gray-900">{{ $statuses[$selectedCommunication['status']] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-gray-600 block mb-1">From:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedCommunication['from_whom'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 block mb-1">To:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedCommunication['to_whom'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Date Information</h3>
                                <div class="space-y-4">
                                    @if($selectedCommunication['type'] === 'incoming' && $selectedCommunication['date_received'])
                                        <div>
                                            <span class="text-gray-600 block mb-1">Date Received:</span>
                                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedCommunication['date_received'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    @if($selectedCommunication['type'] === 'outgoing' && $selectedCommunication['date_sent'])
                                        <div>
                                            <span class="text-gray-600 block mb-1">Date Sent:</span>
                                            <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedCommunication['date_sent'])->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="text-gray-600 block mb-1">Uploaded:</span>
                                        <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedCommunication['uploaded_at'])->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Description -->
                            @if($selectedCommunication['description'])
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                                    <p class="text-gray-700">{{ $selectedCommunication['description'] }}</p>
                                </div>
                            @endif

                            <!-- File Information -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">File Information</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">File Name:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedCommunication['original_name'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">File Size:</span>
                                        <span class="font-medium text-gray-900">{{ number_format($selectedCommunication['file_size'] / 1024, 1) }} KB</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">File Type:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedCommunication['file_type'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Management -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
                                <div class="space-y-3">
                                    @foreach($statuses as $key => $status)
                                        <button wire:click="updateStatus('{{ $selectedCommunication['id'] }}', '{{ $key }}')" 
                                                class="w-full px-4 py-2 text-left text-sm font-medium rounded-lg transition-colors
                                                    {{ $selectedCommunication['status'] === $key ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                                            {{ $status }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                                <div class="space-y-3">
                                    <button wire:click="downloadCommunication('{{ $selectedCommunication['id'] }}')" 
                                            class="w-full px-4 py-2 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>Download File</span>
                                    </button>
                                    
                                    <button wire:click="openDeleteModal('{{ $selectedCommunication['id'] }}')" 
                                            class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Delete Communication</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $selectedCommunication)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Delete Communication</h3>
                            <p class="text-sm text-gray-600">This action cannot be undone</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-6">
                        Are you sure you want to delete "<strong>{{ $selectedCommunication['subject'] }}</strong>"? 
                        This will permanently remove the communication file and all associated data.
                    </p>

                    <div class="flex items-center justify-end space-x-3">
                        <button wire:click="closeDeleteModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteCommunication" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200">
                            Delete Communication
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Download Script -->
<script nonce="{{ csp_nonce() }}">
document.addEventListener('livewire:load', function () {
    Livewire.on('download-file', data => {
        const link = document.createElement('a');
        link.href = data.url;
        link.download = data.filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>
