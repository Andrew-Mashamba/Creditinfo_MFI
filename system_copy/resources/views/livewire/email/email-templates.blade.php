<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Email Templates</h2>
        <p class="text-gray-600 mt-1">Create and manage reusable email templates</p>
    </div>

    <!-- Actions Bar -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <!-- Category Filter -->
            <select 
                wire:model="selectedCategory"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
                <option value="all">All Categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <!-- Search -->
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.debounce.300ms="searchTerm"
                    placeholder="Search templates..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 w-64"
                />
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button 
                wire:click="createDefaultTemplates"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                title="Create starter templates"
            >
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Import Defaults
            </button>
            
            <button 
                wire:click="showCreate"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Template
            </button>
        </div>
    </div>

    <!-- Templates Grid -->
    @if(count($templates) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $template)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="p-5">
                        <!-- Template Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 flex items-center">
                                    {{ $template->name }}
                                    @if($template->is_shared)
                                        <svg class="w-4 h-4 text-blue-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Shared template">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    @endif
                                </h3>
                                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                    {{ $categories[$template->category] ?? 'General' }}
                                </span>
                            </div>
                        </div>

                        <!-- Template Description -->
                        @if($template->description)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ $template->description }}
                            </p>
                        @endif

                        <!-- Template Preview -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700">Subject:</p>
                            <p class="text-sm text-gray-600 truncate">{{ $template->subject }}</p>
                        </div>

                        <!-- Usage Stats -->
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <span>Used {{ $template->usage_count }} times</span>
                            @if($template->last_used_at)
                                <span>Last: {{ \Carbon\Carbon::parse($template->last_used_at)->diffForHumans() }}</span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button 
                                    wire:click="useTemplate({{ $template->id }})"
                                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                                >
                                    Use
                                </button>
                                <span class="text-gray-300">|</span>
                                <button 
                                    wire:click="previewTemplate({{ $template->id }})"
                                    class="text-gray-600 hover:text-gray-700 text-sm"
                                >
                                    Preview
                                </button>
                            </div>
                            
                            <div class="flex items-center space-x-1">
                                <button 
                                    wire:click="cloneTemplate({{ $template->id }})"
                                    class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                    title="Clone"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                    </svg>
                                </button>
                                
                                @if($template->user_id == auth()->id())
                                    <button 
                                        wire:click="editTemplate({{ $template->id }})"
                                        class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    
                                    <button 
                                        wire:click="deleteTemplate({{ $template->id }})"
                                        onclick="return confirm('Are you sure you want to delete this template?')"
                                        class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                        title="Delete"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 mb-4">No templates found</p>
            <button 
                wire:click="showCreate"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
                Create Your First Template
            </button>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                {{ $showEditModal ? 'Edit Template' : 'Create New Template' }}
                            </h3>

                            <form wire:submit.prevent="{{ $showEditModal ? 'updateTemplate' : 'createTemplate' }}">
                                <div class="space-y-4">
                                    <!-- Template Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                                        <input 
                                            type="text" 
                                            wire:model="templateName"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="e.g., Welcome Email, Meeting Request"
                                            required
                                        />
                                        @error('templateName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Category and Sharing -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                            <select 
                                                wire:model="templateCategory"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            >
                                                @foreach($categories as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="flex items-end">
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="templateShared"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700">Share with team</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                        <input 
                                            type="text" 
                                            wire:model="templateDescription"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Brief description of when to use this template"
                                        />
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject Line</label>
                                        <input 
                                            type="text" 
                                            wire:model="templateSubject"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Use {{variables}} for dynamic content"
                                            required
                                        />
                                        @error('templateSubject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Body -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Body</label>
                                        <textarea 
                                            wire:model="templateBody"
                                            rows="10"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Write your email template here. Use {{variables}} for placeholders."
                                            required
                                        ></textarea>
                                        @error('templateBody') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Variable Help -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <p class="text-sm text-blue-800">
                                            <strong>Tip:</strong> Use double curly braces for variables, e.g., {{recipient_name}}, {{date}}, {{company}}. 
                                            These will be automatically detected and can be filled in when using the template.
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-6 flex justify-end space-x-3">
                                    <button 
                                        type="button"
                                        wire:click="$set('{{ $showEditModal ? 'showEditModal' : 'showCreateModal' }}', false)"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button 
                                        type="submit"
                                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                    >
                                        {{ $showEditModal ? 'Update Template' : 'Create Template' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Preview Modal -->
    @if($showPreviewModal && $previewTemplate)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Preview: {{ $previewTemplate->name }}
                                </h3>
                                <button 
                                    wire:click="$set('showPreviewModal', false)"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Variable Inputs -->
                            @if(count($previewVariables) > 0)
                                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-700 mb-3">Template Variables</h4>
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach($previewVariables as $var => $value)
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">{{ ucwords(str_replace('_', ' ', $var)) }}</label>
                                                <input 
                                                    type="text" 
                                                    wire:model.lazy="previewVariables.{{ $var }}"
                                                    wire:change="updatePreview"
                                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Email Preview -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <!-- Subject -->
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-medium text-gray-700">Subject:</p>
                                    <p class="text-gray-900">{{ $previewSubject }}</p>
                                </div>
                                
                                <!-- Body -->
                                <div class="p-4 bg-white max-h-96 overflow-y-auto">
                                    <div class="prose max-w-none">
                                        {!! nl2br(e($previewBody)) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 flex justify-end space-x-3">
                                <button 
                                    wire:click="$set('showPreviewModal', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Close
                                </button>
                                <button 
                                    wire:click="useTemplate({{ $previewTemplate->id }})"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    Use This Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>