<div>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Email Signatures</h2>
        <p class="text-gray-600 mt-1">Create and manage your email signatures</p>
    </div>

    <!-- Actions Bar -->
    <div class="mb-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            {{ count($signatures) }} signature{{ count($signatures) !== 1 ? 's' : '' }}
        </div>
        
        <div class="flex items-center space-x-2">
            @if(count($signatures) === 0)
                <button 
                    wire:click="createDefaultSignatures"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Import Defaults
                </button>
            @endif
            
            <button 
                wire:click="showCreate"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Signature
            </button>
        </div>
    </div>

    <!-- Signatures List -->
    @if(count($signatures) > 0)
        <div class="space-y-4">
            @foreach($signatures as $signature)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-5">
                        <!-- Signature Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900 flex items-center">
                                    {{ $signature->name }}
                                    @if($signature->is_default)
                                        <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">
                                            Default
                                        </span>
                                    @endif
                                </h3>
                                <div class="text-sm text-gray-600 mt-1 space-x-3">
                                    @if($signature->include_in_replies)
                                        <span>✓ Replies</span>
                                    @endif
                                    @if($signature->include_in_forwards)
                                        <span>✓ Forwards</span>
                                    @endif
                                    <span>Used {{ $signature->usage_count }} times</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @if(!$signature->is_default)
                                    <button 
                                        wire:click="setDefault({{ $signature->id }})"
                                        class="text-sm text-gray-600 hover:text-gray-700"
                                        title="Set as default"
                                    >
                                        Make Default
                                    </button>
                                @endif
                                
                                <button 
                                    wire:click="previewSignature({{ $signature->id }})"
                                    class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                    title="Preview"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                
                                <button 
                                    wire:click="editSignature({{ $signature->id }})"
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                    title="Edit"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                <button 
                                    wire:click="deleteSignature({{ $signature->id }})"
                                    onclick="return confirm('Are you sure you want to delete this signature?')"
                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                    title="Delete"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Signature Preview -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 max-h-32 overflow-hidden">
                            <div class="text-sm text-gray-700">
                                {{-- SECURITY: Using HTMLPurifier for safe HTML rendering --}}
                                {!! clean($signature->content, 'email') !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <p class="text-gray-600 mb-4">No signatures yet</p>
            <div class="space-x-2">
                <button 
                    wire:click="createDefaultSignatures"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    Import Default Signatures
                </button>
                <button 
                    wire:click="showCreate"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    Create Your First Signature
                </button>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                {{ $showEditModal ? 'Edit Signature' : 'Create New Signature' }}
                            </h3>

                            <form wire:submit.prevent="{{ $showEditModal ? 'updateSignature' : 'createSignature' }}">
                                <div class="space-y-4">
                                    <!-- Signature Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Signature Name</label>
                                        <input 
                                            type="text" 
                                            wire:model="signatureName"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="e.g., Work Signature, Personal Signature"
                                            required
                                        />
                                        @error('signatureName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Options -->
                                    <div class="grid grid-cols-3 gap-4">
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="isDefault"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Set as default</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="includeInReplies"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Include in replies</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="includeInForwards"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Include in forwards</span>
                                        </label>
                                    </div>

                                    <!-- Editor Mode Toggle -->
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            type="button"
                                            wire:click="switchEditorMode('visual')"
                                            class="px-3 py-1 text-sm @if($editorMode === 'visual') bg-red-600 text-white @else bg-gray-200 text-gray-700 @endif rounded-lg"
                                        >
                                            Visual Editor
                                        </button>
                                        <button 
                                            type="button"
                                            wire:click="switchEditorMode('html')"
                                            class="px-3 py-1 text-sm @if($editorMode === 'html') bg-red-600 text-white @else bg-gray-200 text-gray-700 @endif rounded-lg"
                                        >
                                            HTML Editor
                                        </button>
                                    </div>

                                    <!-- Signature Content -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Signature Content</label>
                                        
                                        <!-- Variable Buttons -->
                                        <div class="mb-2 flex flex-wrap gap-2">
                                            <span class="text-xs text-gray-600">Insert variable:</span>
                                            @foreach(['name', 'title', 'company', 'email', 'phone'] as $var)
                                                <button 
                                                    type="button"
                                                    wire:click="insertVariable('{{ $var }}')"
                                                    class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                                >
                                                    @{{ $var }}
                                                </button>
                                            @endforeach
                                        </div>
                                        
                                        @if($editorMode === 'visual')
                                            <div 
                                                x-data="{ content: @entangle('signatureContent') }"
                                                x-init="
                                                    const editor = document.getElementById('signature-editor');
                                                    editor.addEventListener('input', () => {
                                                        content = editor.innerHTML;
                                                    });
                                                    $watch('content', value => {
                                                        if (editor.innerHTML !== value) {
                                                            editor.innerHTML = value;
                                                        }
                                                    });
                                                "
                                            >
                                                <div 
                                                    id="signature-editor"
                                                    contenteditable="true"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 min-h-[200px] focus:outline-none focus:ring-2 focus:ring-red-500"
                                                    x-html="content"
                                                ></div>
                                            </div>
                                        @else
                                            <textarea 
                                                wire:model="signatureContent"
                                                rows="10"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 font-mono text-sm"
                                                placeholder="Enter HTML content for your signature"
                                                required
                                            ></textarea>
                                        @endif
                                        @error('signatureContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Preview -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
                                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                            <div class="prose max-w-none">
                                                {{-- SECURITY: Using HTMLPurifier for safe HTML rendering --}}
                                                {!! clean($signatureContent, 'email') !!}
                                            </div>
                                        </div>
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
                                        {{ $showEditModal ? 'Update Signature' : 'Create Signature' }}
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
    @if($showPreviewModal && $previewSignature)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Preview: {{ $previewSignature->name }}
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

                            <!-- Variable Values -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-3">Variable Values</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach($previewVariables as $var => $value)
                                        <div>
                                            <label class="block text-sm text-gray-600 mb-1">{{ ucwords(str_replace('_', ' ', $var)) }}</label>
                                            <input 
                                                type="text" 
                                                wire:model="previewVariables.{{ $var }}"
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Signature Preview -->
                            <div class="border border-gray-200 rounded-lg p-6 bg-white">
                                <p class="mb-4">Best regards,</p>
                                <div class="border-t-2 border-gray-200 pt-4">
                                    {{-- SECURITY: Using HTMLPurifier for safe HTML rendering --}}
                                    {!! clean($this->getPreviewContent(), 'email') !!}
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
                                    wire:click="useSignature({{ $previewSignature->id }})"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    Use This Signature
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>