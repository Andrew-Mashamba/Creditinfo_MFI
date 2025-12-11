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
                <div class="p-3 bg-purple-100 rounded-xl">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">SACCO Forms Management</h2>
                    <p class="text-gray-600">Upload, manage, and download various SACCO application forms</p>
                </div>
            </div>
            <button wire:click="openUploadModal" 
                    class="px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Upload New Form</span>
            </button>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="mb-8 bg-gray-50 rounded-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Forms</label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="searchTerm" 
                           type="text" 
                           placeholder="Search by title or description..."
                           class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                <select wire:model.live="selectedCategory" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    <option value="">All Categories</option>
                    @foreach($formCategories as $key => $category)
                        <option value="{{ $key }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Results Count -->
            <div class="flex items-end">
                <div class="text-sm text-gray-600">
                    Showing {{ count($forms) }} of {{ $totalForms }} forms
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Grid -->
    @if(count($forms) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($forms as $form)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                    <!-- Form Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $form['title'] }}</h3>
                                <span class="inline-block px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                    {{ $formCategories[$form['category']] ?? $form['category'] }}
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if(str_contains($form['file_type'], 'pdf'))
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @elseif(str_contains($form['file_type'], 'word'))
                                    <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 18h12V6l-4-4H4v16zm2-2V4h6v2h4v12H6z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        
                        @if($form['description'])
                            <p class="text-sm text-gray-600 mb-3">{{ $form['description'] }}</p>
                        @endif

                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ number_format($form['file_size'] / 1024, 1) }} KB</span>
                            <span>{{ \Carbon\Carbon::parse($form['uploaded_at'])->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="p-6">
                        <div class="flex items-center space-x-3">
                            <button wire:click="downloadForm('{{ $form['id'] }}')" 
                                    class="flex-1 px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Download</span>
                            </button>
                            
                            <button wire:click="openDeleteModal('{{ $form['id'] }}')" 
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No forms found</h3>
            <p class="text-gray-600 mb-4">
                @if($searchTerm || $selectedCategory)
                    Try adjusting your search criteria or upload a new form
                @else
                    Get started by uploading your first SACCO form
                @endif
            </p>
            @if(!$searchTerm && !$selectedCategory)
                <button wire:click="openUploadModal" 
                        class="px-6 py-3 bg-blue-900 text-white font-medium rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all duration-200">
                    Upload First Form
                </button>
            @endif
        </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Upload New Form</h2>
                    <button wire:click="closeUploadModal" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg bg-blue-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <form wire:submit.prevent="uploadForm">
                        <div class="space-y-6">
                            <!-- Form Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Form Title *</label>
                                <input wire:model="formTitle" 
                                       type="text" 
                                       placeholder="Enter form title..."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                @error('formTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Form Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Form Category *</label>
                                <select wire:model="formCategory" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                    <option value="">Select a category</option>
                                    @foreach($formCategories as $key => $category)
                                        <option value="{{ $key }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('formCategory') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Form Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                <textarea wire:model="formDescription" 
                                          rows="3" 
                                          placeholder="Enter form description..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"></textarea>
                                @error('formDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Form File *</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition-colors">
                                    <input wire:model="uploadedFile" 
                                           type="file" 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                           class="hidden" 
                                           id="file-upload">
                                    <label for="file-upload" class="cursor-pointer">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="text-gray-600 mb-2">
                                            <span class="font-medium text-purple-600 hover:text-purple-500">Click to upload</span>
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
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                            <button type="button" 
                                    wire:click="closeUploadModal"
                                    class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all duration-200">
                                Upload Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $selectedForm)
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
                            <h3 class="text-lg font-semibold text-gray-900">Delete Form</h3>
                            <p class="text-sm text-gray-600">This action cannot be undone</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700 mb-6">
                        Are you sure you want to delete "<strong>{{ $selectedForm['title'] }}</strong>"? 
                        This will permanently remove the form file and all associated data.
                    </p>

                    <div class="flex items-center justify-end space-x-3">
                        <button wire:click="closeDeleteModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteForm" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200">
                            Delete Form
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
