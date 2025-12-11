@if($email)
<div class="flex flex-col h-full">
    <!-- Email Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-white">
        <!-- Action Buttons -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <!-- Reply Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button 
                        @click="open = !open"
                        class="flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-900 rounded-md hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Reply
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <button wire:click="reply" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                Reply
                            </button>
                            <button wire:click="replyAll" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                Reply All
                            </button>
                            <button wire:click="forward" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                                Forward
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Button -->
                <button 
                    wire:click="deleteEmail"
                    class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md"
                    title="Delete"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Flag Button -->
                <button 
                    wire:click="toggleFlag"
                    class="p-2 rounded-md transition-colors @if($email['is_flagged'] ?? false) text-red-500 hover:text-red-600 @else text-gray-500 hover:text-red-500 @endif"
                    title="@if($email['is_flagged'] ?? false) Unflag @else Flag @endif"
                >
                    <svg class="w-5 h-5" fill="@if($email['is_flagged'] ?? false) currentColor @else none @endif" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                    </svg>
                </button>
                
                <!-- Pin Button -->
                <button 
                    wire:click="togglePin"
                    class="p-2 rounded-md transition-colors @if($email['is_pinned'] ?? false) text-yellow-500 hover:text-yellow-600 @else text-gray-500 hover:text-yellow-500 @endif"
                    title="@if($email['is_pinned'] ?? false) Unpin @else Pin @endif"
                >
                    <svg class="w-5 h-5" fill="@if($email['is_pinned'] ?? false) currentColor @else none @endif" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </button>
                
                <!-- More Actions -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button 
                        @click="open = !open"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <button wire:click="moveToFolder('junk')" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Mark as Junk</button>
                            <button wire:click="moveToFolder('archive')" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Archive</button>
                            <button wire:click="createRule" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">Create Rule</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Email Subject -->
        <h1 class="text-xl font-semibold text-gray-900 mb-4">{{ $email['subject'] }}</h1>
        
        <!-- Sender Info -->
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">
                        {{ substr($email['sender_name'] ?? $email['sender_email'] ?? 'U', 0, 1) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $email['sender_name'] ?? $email['sender_email'] ?? 'Unknown Sender' }}
                    </p>
                    <p class="text-sm text-gray-600">{{ $email['sender_email'] }}</p>
                    <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                        <span>To: {{ $email['recipient_email'] }}</span>
                        @if($email['cc'])
                            <span>CC: {{ $email['cc'] }}</span>
                        @endif
                        @if($email['bcc'] && $email['sender_id'] == Auth::id())
                            <span>BCC: {{ $email['bcc'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($email['created_at'])->format('M j, Y \a\t g:i A') }}
            </div>
        </div>
    </div>
    
    <!-- Email Body -->
    <div class="flex-1 overflow-y-auto p-6">
        <div class="prose max-w-none">
            {!! nl2br(e($email['body'])) !!}
        </div>
        
        <!-- Attachments -->
        @if($email['has_attachments'])
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Attachments</h4>
                <div class="space-y-2">
                    @php
                        $attachments = json_decode($email['attachments'] ?? '[]', true);
                    @endphp
                    @foreach($attachments as $attachment)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $attachment['name'] ?? 'Unknown file' }}</p>
                                <p class="text-xs text-gray-500">{{ $attachment['size'] ?? 0 }} bytes</p>
                            </div>
                            <button 
                                wire:click="downloadAttachment({{ $attachment['id'] ?? 0 }})"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                Download
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Conversation Thread -->
        @if(count($conversationEmails) > 0)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <button 
                    wire:click="$toggle('showThreads')"
                    class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 mb-4"
                >
                    <svg class="w-4 h-4 mr-2 transform @if($showThreads) rotate-90 @endif transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    {{ count($conversationEmails) }} earlier message{{ count($conversationEmails) > 1 ? 's' : '' }}
                </button>
                
                @if($showThreads)
                    <div class="space-y-4">
                        @foreach($conversationEmails as $thread)
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-600">
                                                {{ substr($thread->sender_name ?? $thread->sender_email ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $thread->sender_name ?? $thread->sender_email }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($thread->created_at)->format('M j, g:i A') }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-700">
                                    {!! nl2br(e(Str::limit($thread->body, 200))) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@else
    <!-- No Email Selected -->
    <div class="flex items-center justify-center h-full">
        <div class="text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Select an email</h3>
            <p class="text-gray-500">Choose an email from the list to view its contents</p>
        </div>
    </div>
@endif