<div class="flex flex-col h-full bg-white {{ $isMinimized ? 'h-16' : '' }} {{ $isFullscreen ? 'fixed inset-0 z-50' : '' }}">
    <!-- Compose Header -->
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <h3 class="text-lg font-medium text-gray-900">
                @if($isReply)
                    Reply
                @elseif($isReplyAll)
                    Reply All
                @elseif($isForward)
                    Forward
                @else
                    New Message
                @endif
            </h3>
            
            <!-- Priority Indicator -->
            @if($priority !== 'normal')
                <span class="px-2 py-1 text-xs rounded-full {{ $priority === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($priority) }}
                </span>
            @endif
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Minimize/Maximize -->
            <button 
                wire:click="toggleMinimize"
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md"
                title="{{ $isMinimized ? 'Maximize' : 'Minimize' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isMinimized)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    @endif
                </svg>
            </button>
            
            <!-- Fullscreen -->
            <button 
                wire:click="toggleFullscreen"
                class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md"
                title="{{ $isFullscreen ? 'Exit Fullscreen' : 'Fullscreen' }}"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($isFullscreen)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    @endif
                </svg>
            </button>
            
            <!-- Close -->
            <button 
                wire:click="discardEmail"
                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md"
                title="Close"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    
    @if(!$isMinimized)
        <!-- Compose Form -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="p-4 space-y-3">
                <!-- To Field -->
                <div class="flex items-center">
                    <label class="w-12 text-sm font-medium text-gray-700">To:</label>
                    <input 
                        type="email" 
                        wire:model.debounce.300ms="to"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter recipient email"
                        autocomplete="email"
                    >
                    <div class="ml-2 flex space-x-1">
                        <button 
                            wire:click="toggleCc"
                            class="px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded"
                        >
                            Cc
                        </button>
                        <button 
                            wire:click="toggleBcc"
                            class="px-2 py-1 text-xs text-red-600 hover:text-red-800 hover:bg-red-50 rounded"
                        >
                            Bcc
                        </button>
                    </div>
                </div>
                
                @error('to')
                    <div class="text-red-500 text-sm ml-12">{{ $message }}</div>
                @enderror
                
                <!-- CC Field -->
                @if($showCc)
                    <div class="flex items-center">
                        <label class="w-12 text-sm font-medium text-gray-700">Cc:</label>
                        <input 
                            type="email" 
                            wire:model.debounce.300ms="cc"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Enter CC recipients"
                            autocomplete="email"
                        >
                    </div>
                    @error('cc')
                        <div class="text-red-500 text-sm ml-12">{{ $message }}</div>
                    @enderror
                @endif
                
                <!-- BCC Field -->
                @if($showBcc)
                    <div class="flex items-center">
                        <label class="w-12 text-sm font-medium text-gray-700">Bcc:</label>
                        <input 
                            type="email" 
                            wire:model.debounce.300ms="bcc"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Enter BCC recipients"
                            autocomplete="email"
                        >
                    </div>
                    @error('bcc')
                        <div class="text-red-500 text-sm ml-12">{{ $message }}</div>
                    @enderror
                @endif
                
                <!-- Subject Field -->
                <div class="flex items-center">
                    <label class="w-12 text-sm font-medium text-gray-700">Subject:</label>
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="subject"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter subject"
                        autocomplete="off"
                    >
                </div>
                @error('subject')
                    <div class="text-red-500 text-sm ml-12">{{ $message }}</div>
                @enderror
                
                <!-- Advanced Options Toggle -->
                <div class="flex items-center justify-between">
                    <button 
                        wire:click="toggleAdvancedOptions"
                        class="text-sm text-red-600 hover:text-red-800 flex items-center space-x-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Advanced Options</span>
                    </button>
                    
                    <!-- Schedule Toggle -->
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="isScheduled"
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Schedule send</span>
                    </label>
                </div>
                
                <!-- Advanced Options Panel -->
                @if($showAdvancedOptions)
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <!-- Priority -->
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Priority:</label>
                            <select wire:model="priority" class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        
                        <!-- Receipt Options -->
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="requestReadReceipt"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Request read receipt</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="requestDeliveryReceipt"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Request delivery receipt</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="enableTracking"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Enable tracking</span>
                            </label>
                        </div>
                    </div>
                @endif
                
                <!-- Schedule Options -->
                @if($isScheduled)
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input 
                                    type="date" 
                                    wire:model="scheduledDate"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    min="{{ date('Y-m-d') }}"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                <input 
                                    type="time" 
                                    wire:model="scheduledTime"
                                    class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                >
                            </div>
                        </div>
                        @error('scheduledDate')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                        @error('scheduledTime')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
            
            <!-- Message Body -->
            <div class="flex-1 px-4 pb-4">
                <div class="relative h-full">
                    <textarea 
                        wire:model.debounce.500ms="body"
                        class="w-full h-full p-3 border border-gray-300 rounded-md resize-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Type your message here..."
                    ></textarea>
                    @error('body')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Signature Section -->
            @if(!empty($signatures))
                <div class="px-4 pb-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700">Signature:</span>
                            @if($selectedSignature)
                                <span class="text-sm text-gray-600">{{ Str::limit($selectedSignature, 50) }}</span>
                                <button 
                                    wire:click="removeSignature"
                                    class="text-red-500 hover:text-red-700 text-sm"
                                >
                                    Remove
                                </button>
                            @else
                                <span class="text-sm text-gray-400">None</span>
                            @endif
                        </div>
                        
                        <button 
                            wire:click="$set('showSignatureSelector', true)"
                            class="text-sm text-red-600 hover:text-red-800"
                        >
                            Select Signature
                        </button>
                    </div>
                </div>
            @endif
            
            <!-- Attachments Section -->
            <div class="px-4 pb-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Attachments:</span>
                        <span class="text-sm text-gray-600">{{ count($attachments) }} file(s)</span>
                    </div>
                    
                    <label class="cursor-pointer">
                        <input 
                            type="file" 
                            wire:model="attachments" 
                            multiple 
                            class="hidden"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar"
                        >
                        <span class="text-sm text-red-600 hover:text-red-800">Add Files</span>
                    </label>
                </div>
                
                <!-- Attachment List -->
                @if(count($attachments) > 0)
                    <div class="mt-2 space-y-1">
                        @foreach($attachments as $index => $attachment)
                            <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700">{{ $attachment->getClientOriginalName() }}</span>
                                    <span class="text-xs text-gray-500">({{ number_format($attachment->getSize() / 1024, 1) }} KB)</span>
                                </div>
                                <button 
                                    wire:click="removeAttachment({{ $index }})"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <!-- Attachment Errors -->
                @if(count($attachmentErrors) > 0)
                    <div class="mt-2 space-y-1">
                        @foreach($attachmentErrors as $error)
                            <div class="text-red-500 text-sm">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="saveDraft"
                        class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                        Save Draft
                    </button>
                    
                    <button 
                        wire:click="discardEmail"
                        class="px-4 py-2 text-sm text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    >
                        Discard
                    </button>
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($isValidating)
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Sending...</span>
                        </div>
                    @else
                        <button 
                            wire:click="sendEmail"
                            class="px-6 py-2 text-sm text-white bg-red-600 rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        >
                            @if($isScheduled)
                                Schedule Send
                            @else
                                Send
                            @endif
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Signature Selector Modal -->
@if($showSignatureSelector)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Signature</h3>
                <button 
                    wire:click="$set('showSignatureSelector', false)"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($signatures as $signature)
                    <button 
                        wire:click="selectSignature({{ $signature['id'] }})"
                        class="w-full text-left p-3 border border-gray-200 rounded hover:bg-gray-50"
                    >
                        <div class="font-medium text-gray-900">{{ $signature['name'] }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ Str::limit($signature['content'], 100) }}</div>
                    </button>
                @endforeach
            </div>
            
            <div class="mt-4 flex justify-end">
                <button 
                    wire:click="$set('showSignatureSelector', false)"
                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endif
