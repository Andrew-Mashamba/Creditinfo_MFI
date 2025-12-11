<div x-data="{ 
    showUploadModal: false, 
    dragActive: false,
    showQuickActions: false,
    showAttachments: @entangle('showAttachments'),
    uploadProgress: 0,
    selectedFiles: []
}" 
class="overflow-hidden chat-container" style="height: 90vh; min-height: 600px;">
    
    <!-- File Upload Modal -->
    <div x-show="showUploadModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showUploadModal = false"></div>
            
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="inline-block w-full max-w-2xl px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:p-6">
                
                <!-- Upload Area -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">Upload Files</h3>
                    
                    <!-- Drag & Drop Zone -->
                    <div @dragover.prevent="dragActive = true"
                         @dragleave.prevent="dragActive = false"
                         @drop.prevent="dragActive = false; $wire.handleFileDrop($event)"
                         :class="{'border-blue-500 bg-blue-50': dragActive}"
                         class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200">
                        
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        
                        <p class="text-sm text-gray-600 mb-2">Drag and drop files here, or</p>
                        <label for="file-upload" class="cursor-pointer">
                            <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-900 hover:bg-blue-800 transition-colors">
                                Browse Files
                            </span>
                            <input id="file-upload" 
                                   wire:model="uploadedFiles" 
                                   type="file" 
                                   class="sr-only" 
                                   multiple
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.png,.jpg,.jpeg,.gif">
                        </label>
                        <p class="text-xs text-gray-500 mt-2">Supported: PDF, Word, Excel, CSV, Text, Images (Max 10MB each)</p>
                    </div>
                    
                    <!-- File Preview -->
                    @if($uploadedFiles)
                    <div class="mt-4 space-y-2">
                        <h4 class="text-sm font-medium text-gray-700">Selected Files:</h4>
                        @foreach($uploadedFiles as $file)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $file->getClientOriginalName() }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($file->getSize() / 1024, 2) }} KB)</span>
                            </div>
                            <button wire:click="removeFile({{ $loop->index }})" 
                                    class="text-red-500 hover:text-red-700 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Upload Progress -->
                    <div x-show="uploadProgress > 0 && uploadProgress < 100" class="mt-4">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                            <span>Uploading...</span>
                            <span x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-900 h-2 rounded-full transition-all duration-300" 
                                 :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button @click="showUploadModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="processUploadedFiles" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-900 rounded-md hover:bg-blue-800 transition-colors">
                        Upload & Analyze
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex overflow-hidden bg-white rounded-2xl shadow-lg border border-gray-100 chat-main" style="height: 85vh; min-height: 550px;">
        <!-- Enhanced Sidebar -->
        <div class="w-64 bg-white shadow-xl border-r border-gray-200 flex flex-col sidebar">
            <!-- Header -->
            <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-900 to-blue-800">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 backdrop-blur rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Zona AIx</h1>
                        <p class="text-xs text-gray-500">SACCOS Intelligence</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="p-3 border-b border-gray-100">
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="newConversation" 
                            class="flex items-center justify-center p-2 text-xs font-medium text-blue-900 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Chat
                    </button>
                    <button @click="showUploadModal = true" 
                            class="flex items-center justify-center p-2 text-xs font-medium text-green-900 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload
                    </button>
                </div>
            </div>

            <!-- Active Files/Context -->
            @if(count($attachedFiles) > 0)
            <div class="p-3 border-b border-gray-100 bg-blue-50/50">
                <h3 class="text-xs font-semibold text-blue-900 uppercase mb-2">Active Context Files</h3>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                    @foreach($attachedFiles as $file)
                    <div class="flex items-center justify-between p-1.5 bg-white rounded text-xs">
                        <div class="flex items-center space-x-1 flex-1 min-w-0">
                            <svg class="w-3 h-3 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                            </svg>
                            <span class="truncate text-gray-700">{{ $file['name'] }}</span>
                        </div>
                        <button wire:click="removeAttachment('{{ $file['id'] }}')" 
                                class="text-red-500 hover:text-red-700 ml-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Conversation History -->
            <div class="flex-1 overflow-y-auto p-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Recent Chats</h3>
                <div class="space-y-1">
                    @forelse($conversationHistory as $conversation)
                    <div wire:click="loadConversation('{{ $conversation['id'] }}')" 
                         class="group cursor-pointer p-2.5 rounded-lg hover:bg-gray-50 transition-all duration-200 border border-transparent hover:border-gray-200">
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $conversation['title'] }}</p>
                                <p class="text-xs text-gray-500">{{ $conversation['time'] }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="mt-2 text-xs text-gray-500">No conversations yet</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Settings & Info -->
            <div class="p-3 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs text-gray-600">AI Online</span>
                    </div>
                    <button wire:click="toggleSettings" class="p-1 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col bg-gradient-to-b from-gray-50 to-white">
            <!-- Chat Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-lg font-semibold text-gray-900">AI Assistant</h2>
                        @if(count($attachedFiles) > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ count($attachedFiles) }} file(s) attached
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="clearChat" 
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        <button wire:click="exportChat" 
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6" id="chat-messages">
                @if(empty($messages))
                    <!-- Welcome Message -->
                    <div class="max-w-3xl mx-auto text-center py-12">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-900 to-blue-700 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Welcome to Zona AI Assistant</h3>
                        <p class="text-gray-600 mb-8">Your intelligent SACCOS management companion</p>
                        
                        <!-- Quick Start Suggestions -->
                        <div class="grid grid-cols-2 gap-3 max-w-2xl mx-auto">
                            <button wire:click="sendQuickMessage('Show me account summary')" 
                                    class="p-3 text-left bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition-colors">
                                        <svg class="w-4 h-4 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Account Summary</p>
                                        <p class="text-xs text-gray-500">View your account overview</p>
                                    </div>
                                </div>
                            </button>
                            
                            <button wire:click="sendQuickMessage('Generate monthly report')" 
                                    class="p-3 text-left bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition-colors">
                                        <svg class="w-4 h-4 text-green-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Generate Report</p>
                                        <p class="text-xs text-gray-500">Create monthly reports</p>
                                    </div>
                                </div>
                            </button>
                            
                            <button wire:click="sendQuickMessage('Analyze loan portfolio')" 
                                    class="p-3 text-left bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-purple-200 transition-colors">
                                        <svg class="w-4 h-4 text-purple-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Loan Analysis</p>
                                        <p class="text-xs text-gray-500">Portfolio insights</p>
                                    </div>
                                </div>
                            </button>
                            
                            <button @click="showUploadModal = true" 
                                    class="p-3 text-left bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-200 transition-colors">
                                        <svg class="w-4 h-4 text-yellow-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Upload Document</p>
                                        <p class="text-xs text-gray-500">Analyze files with AI</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Chat Messages -->
                    @foreach($messages as $message)
                        <div class="flex items-start space-x-3 {{ $message['sender'] === 'user' ? 'justify-end' : '' }}">
                            @if($message['sender'] === 'ai')
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-900 to-blue-700 rounded-xl flex items-center justify-center shadow-md">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex-1 max-w-3xl {{ $message['sender'] === 'user' ? 'order-first' : '' }}">
                                <div class="rounded-2xl p-4 shadow-sm {{ $message['sender'] === 'user' ? 'bg-blue-900 text-white' : 'bg-white border border-gray-200' }}">
                                    @if($message['sender'] === 'user' && !empty($message['attachments']))
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        @foreach($message['attachments'] as $attachment)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-gray-500">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                            </svg>
                                            {{ $attachment['name'] }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    <div class="{{ $message['sender'] === 'ai' ? 'prose prose-sm max-w-none text-gray-700' : 'text-white' }}">
                                        @if($message['sender'] === 'ai' && !$message['isError'])
                                            {{-- SECURITY: AI responses are now escaped to prevent XSS attacks --}}
                                            {{-- If formatted content is needed, implement a proper HTML sanitizer --}}
                                            <div class="whitespace-pre-wrap">{!! nl2br(e($message['content'])) !!}</div>
                                        @else
                                            <p class="whitespace-pre-wrap">{{ $message['content'] }}</p>
                                        @endif
                                    </div>
                                    
                                    @if($message['sender'] === 'ai')
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                        <div class="flex items-center space-x-1">
                                            <button wire:click="copyMessage('{{ $message['id'] }}')" 
                                                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="regenerateResponse('{{ $message['id'] }}')" 
                                                    class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $message['timestamp']->diffForHumans() }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($message['sender'] === 'user')
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-br from-gray-700 to-gray-600 rounded-xl flex items-center justify-center shadow-md">
                                        <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif

                <!-- Loading State -->
                @if($isLoading)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-900 to-blue-700 rounded-xl flex items-center justify-center shadow-md animate-pulse">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 max-w-3xl">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-blue-900 rounded-full animate-bounce"></div>
                                        <div class="w-2 h-2 bg-blue-900 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                        <div class="w-2 h-2 bg-blue-900 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">AI is thinking...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Enhanced Input Area -->
            <div class="bg-white border-t border-gray-200 p-4">
                <form wire:submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                    <!-- Attached Files Display -->
                    @if(count($attachedFiles) > 0)
                    <div class="mb-3 flex flex-wrap gap-2">
                        @foreach($attachedFiles as $file)
                        <div class="inline-flex items-center px-3 py-1 rounded-lg bg-blue-50 border border-blue-200">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                            </svg>
                            <span class="text-sm text-blue-900">{{ $file['name'] }}</span>
                            <button type="button" 
                                    wire:click="removeAttachment('{{ $file['id'] }}')" 
                                    class="ml-2 text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <div class="relative flex items-end space-x-3">
                        <!-- Input Actions -->
                        <div class="flex items-center space-x-1">
                            <button type="button" 
                                    @click="showUploadModal = true"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all"
                                    title="Upload file">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </button>
                            <button type="button" 
                                    wire:click="toggleVoiceInput"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all"
                                    title="Voice input">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="flex-1 relative">
                            <textarea 
                                wire:model="message" 
                                rows="1" 
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-gray-900 placeholder-gray-500 transition-all duration-200"
                                placeholder="Type your message or drag & drop files here..."
                                wire:keydown.enter.prevent="sendMessage"
                                {{ $isLoading ? 'disabled' : '' }}
                                x-data="{ resize() { this.style.height = '40px'; this.style.height = this.scrollHeight + 'px'; } }"
                                x-init="resize()"
                                @input="resize()"
                                style="min-height: 40px; max-height: 120px;"
                            ></textarea>
                            
                            <!-- Character count -->
                            <div class="absolute right-3 bottom-3 text-xs text-gray-400">
                                <span x-text="$wire.message.length"></span>/2000
                            </div>
                        </div>
                        
                        <!-- Send Button -->
                        <button 
                            type="submit" 
                            class="p-3 bg-blue-900 hover:bg-blue-800 disabled:bg-gray-400 text-white rounded-xl transition-all duration-200 shadow-md hover:shadow-lg disabled:shadow-none"
                            {{ $isLoading ? 'disabled' : '' }}
                        >
                            @if($isLoading)
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            @endif
                        </button>
                    </div>
                </form>
                
                <!-- Quick Actions Bar -->
                <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span>Press Enter to send</span>
                        <span>•</span>
                        <span>Drag & drop files to upload</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($isRecording)
                        <span class="flex items-center text-red-500">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse mr-1"></div>
                            Recording...
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Auto-scroll to bottom on new messages
    Livewire.on('messageAdded', () => {
        setTimeout(() => {
            const messagesContainer = document.getElementById('chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }, 100);
    });
    
    // Handle file drag and drop
    document.addEventListener('DOMContentLoaded', function() {
        const chatArea = document.querySelector('.chat-container');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            chatArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
</script>
@endpush