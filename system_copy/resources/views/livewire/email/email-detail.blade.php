<div>
    @if($email)
        <!-- Email Header Actions -->
        <div class="mb-6 flex items-center justify-between">
            <button 
                wire:click="backToList"
                class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to list
            </button>

            <div class="flex items-center space-x-2">
                <button 
                    wire:click="reply"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Reply
                </button>
                
                <button 
                    wire:click="replyAll"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Reply All
                </button>
                
                <button 
                    wire:click="forward"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    Forward
                </button>

                @if($email['folder'] != 'spam')
                    <button 
                        wire:click="markAsSpam"
                        class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors"
                        title="Mark as spam"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </button>
                @endif

                <button 
                    wire:click="deleteEmail"
                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                    title="Delete"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Email Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Email Header -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ $email['subject'] }}</h2>
                
                <div class="flex items-start space-x-4">
                    <!-- Avatar -->
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 font-semibold text-lg">
                            {{ strtoupper(substr($email['sender_name'] ?? $email['sender_email'] ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                    
                    <!-- Email Meta -->
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">
                                    {{ $email['sender_name'] ?? $email['sender_email'] ?? 'Unknown Sender' }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    From: {{ $email['sender_email'] ?? 'Unknown' }}
                                </p>
                                @if($email['recipient_email'])
                                    <p class="text-sm text-gray-600">
                                        To: {{ $email['recipient_email'] }}
                                    </p>
                                @endif
                                @if($email['cc'])
                                    <p class="text-sm text-gray-600">
                                        Cc: {{ $email['cc'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($email['created_at'])->format('M d, Y') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($email['created_at'])->format('h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Receipt Status -->
            @if($receiptStatus && (($receiptStatus['request_read_receipt'] ?? false) || ($receiptStatus['request_delivery_receipt'] ?? false)))
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center space-x-6 text-sm">
                        @if($receiptStatus['request_read_receipt'] ?? false)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 @if($receiptStatus['read_receipt_sent'] ?? false) text-green-500 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                </svg>
                                <span class="@if($receiptStatus['read_receipt_sent'] ?? false) text-green-700 @else text-gray-600 @endif">
                                    Read receipt @if($receiptStatus['read_receipt_sent'] ?? false) sent @else requested @endif
                                </span>
                            </div>
                        @endif
                        
                        @if($receiptStatus['request_delivery_receipt'] ?? false)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 @if($receiptStatus['delivery_receipt_sent'] ?? false) text-green-500 @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="@if($receiptStatus['delivery_receipt_sent'] ?? false) text-green-700 @else text-gray-600 @endif">
                                    Delivery receipt @if($receiptStatus['delivery_receipt_sent'] ?? false) sent @else requested @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Tracking Data -->
            @if($trackingData && ($email['enable_tracking'] ?? false))
                <div class="px-6 py-3 bg-blue-50 border-b border-blue-200">
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-6">
                            @if($email['track_opens'] ?? false)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="text-blue-700">
                                        Opened {{ $trackingData['opens_count'] ?? 0 }} time(s)
                                    </span>
                                </div>
                            @endif
                            
                            @if($email['track_clicks'] ?? false)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                    <span class="text-blue-700">
                                        {{ $trackingData['total_clicks'] ?? 0 }} link click(s)
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        @if($trackingData['last_opened_at'] ?? null)
                            <span class="text-xs text-blue-600">
                                Last opened: {{ \Carbon\Carbon::parse($trackingData['last_opened_at'])->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Email Body -->
            <div class="p-6">
                <div class="prose max-w-none">
                    {!! nl2br(e($email['decrypted_body'] ?? $email['body'])) !!}
                </div>
            </div>
            
            <!-- Attachments -->
            @if($attachments->count() > 0)
                <div class="border-t border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">
                        Attachments ({{ $attachments->count() }})
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3 min-w-0">
                                    @php
                                        $extension = pathinfo($attachment->original_filename, PATHINFO_EXTENSION);
                                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isPdf = strtolower($extension) === 'pdf';
                                    @endphp
                                    
                                    @if($isImage)
                                        <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($isPdf)
                                        <svg class="w-8 h-8 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @endif
                                    
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate">
                                            {{ $attachment->original_filename }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ number_format($attachment->size / 1024, 2) }} KB
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-1 ml-2">
                                    @if($isImage || $isPdf)
                                        <button 
                                            wire:click="previewAttachment({{ $attachment->id }})"
                                            class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                            title="Preview"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <button 
                                        wire:click="downloadAttachment({{ $attachment->id }})"
                                        class="p-1 text-gray-400 hover:text-green-600 transition-colors"
                                        title="Download"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Reply Form -->
            @if($showReplyForm)
                <div class="border-t border-gray-200 bg-gray-50 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        @if($replyMode == 'reply')
                            Reply
                        @elseif($replyMode == 'reply-all')
                            Reply All
                        @else
                            Forward
                        @endif
                    </h3>

                    <form wire:submit.prevent="sendReply">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                <input 
                                    type="email" 
                                    wire:model="replyTo"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="recipient@example.com"
                                    required
                                />
                                @error('replyTo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($replyMode == 'reply-all' || $replyMode == 'forward')
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Cc</label>
                                        <input 
                                            type="email" 
                                            wire:model="replyCc"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="cc@example.com"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bcc</label>
                                        <input 
                                            type="email" 
                                            wire:model="replyBcc"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="bcc@example.com"
                                        />
                                    </div>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input 
                                    type="text" 
                                    wire:model="replySubject"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    required
                                />
                                @error('replySubject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea 
                                    wire:model="replyBody"
                                    rows="8"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    required
                                ></textarea>
                                @error('replyBody') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="cancelReply"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @else
        <!-- Loading State -->
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <svg class="w-12 h-12 animate-spin text-red-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Loading email...</p>
            </div>
        </div>
    @endif
    
    <!-- Read Receipt Dialog -->
    @if($showReceiptDialog)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Read Receipt Requested
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                The sender has requested a read receipt for this email. Would you like to send a notification that you have read this message?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button 
                        wire:click="sendReadReceipt"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm"
                    >
                        Send Receipt
                    </button>
                    <button 
                        wire:click="declineReadReceipt"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                    >
                        Decline
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>