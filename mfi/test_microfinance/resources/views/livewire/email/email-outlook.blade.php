<div class="h-screen flex flex-col bg-white">
    <!-- Top Navigation Bar -->
    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 bg-white">
        <div class="flex items-center space-x-4">
            <!-- App Logo and Title -->
            <div class="flex items-center space-x-2">
                <div class="p-2 bg-red-600 rounded">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">Mail</span>
            </div>
            
            <!-- Compose Button -->
            <button 
                wire:click="$set('showComposePane', true)"
                class="flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
                onclick="console.log('Compose button clicked, showComposePane:', @json($showComposePane))"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New message
            </button>
            
            <!-- Debug Info -->
            <div class="text-xs text-gray-500 mt-2">
                showComposePane: {{ $showComposePane ? 'true' : 'false' }}
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="flex-1 max-w-xl mx-8">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live="search"
                    placeholder="Search mail and people"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-transparent"
                >
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Account Info -->
        <div class="flex items-center space-x-3">
            <!-- Switch to Modal View -->
            <a href="/email" class="text-xs text-red-600 hover:text-red-800 underline">Switch to Modal View</a>
            
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-red-600">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <span class="text-sm text-gray-700">{{ Auth::user()->name }}</span>
        </div>
    </div>
    
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-4 mt-2" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    
    <!-- Main Content Area -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Sidebar -->
        <div class="w-64 bg-gray-50 border-r border-gray-200 flex flex-col">
            <!-- Folder Navigation -->
            <div class="p-3">
                <nav class="space-y-1">
                    @foreach([
                        ['id' => 1, 'name' => 'Inbox', 'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4', 'count' => $unreadCount],
                        ['id' => 2, 'name' => 'Sent Items', 'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8', 'count' => null],
                        ['id' => 3, 'name' => 'Drafts', 'icon' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z', 'count' => null],
                        ['id' => 4, 'name' => 'Deleted Items', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', 'count' => null],
                        ['id' => 5, 'name' => 'Junk Email', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'count' => null],
                        ['id' => 6, 'name' => 'Snoozed', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'count' => null],
                        ['id' => 7, 'name' => 'Scheduled', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'count' => null]
                    ] as $folder)
                        @php $isActive = $selectedMenuItem == $folder['id'] @endphp
                        <button 
                            wire:click="selectFolder({{ $folder['id'] }})"
                            class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-md transition-colors @if($isActive) bg-red-100 text-red-700 @else text-gray-700 hover:bg-gray-100 @endif"
                        >
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-3 @if($isActive) text-red-600 @else text-gray-500 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $folder['icon'] }}"></path>
                                </svg>
                                {{ $folder['name'] }}
                            </div>
                            @if($folder['count'] > 0)
                                <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">{{ $folder['count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>
            
            <!-- Focused Inbox Toggle -->
            @if($selectedMenuItem == 1)
                <div class="px-3 py-2 border-t border-gray-200">
                    <button 
                        wire:click="toggleFocusedInbox"
                        class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors @if($focusedInboxEnabled) bg-purple-100 text-purple-700 @else text-gray-700 hover:bg-gray-100 @endif"
                    >
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Focused Inbox
                    </button>
                </div>
            @endif
        </div>
        
        <!-- Center Pane - Email List -->
        <div class="flex-1 flex flex-col bg-white">
            <!-- Email List Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        @switch($selectedMenuItem)
                            @case(1) Inbox @break
                            @case(2) Sent Items @break
                            @case(3) Drafts @break
                            @case(4) Deleted Items @break
                            @case(5) Junk Email @break
                            @case(6) Snoozed @break
                            @case(7) Scheduled @break
                            @default Inbox
                        @endswitch
                    </h2>
                    
                    <!-- Focused Inbox Tabs -->
                    @if($selectedMenuItem == 1 && $focusedInboxEnabled)
                        <div class="flex border-b border-gray-200">
                            <button 
                                wire:click="switchFocusedTab('focused')"
                                class="px-4 py-2 text-sm font-medium border-b-2 @if($focusedTab === 'focused') border-red-500 text-red-600 @else border-transparent text-gray-500 hover:text-gray-700 @endif"
                            >
                                Focused
                                @if(isset($focusedStats['focused_unread']) && $focusedStats['focused_unread'] > 0)
                                    <span class="ml-2 bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">{{ $focusedStats['focused_unread'] }}</span>
                                @endif
                            </button>
                            <button 
                                wire:click="switchFocusedTab('other')"
                                class="px-4 py-2 text-sm font-medium border-b-2 @if($focusedTab === 'other') border-red-500 text-red-600 @else border-transparent text-gray-500 hover:text-gray-700 @endif"
                            >
                                Other
                                @if(isset($focusedStats['other_unread']) && $focusedStats['other_unread'] > 0)
                                    <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $focusedStats['other_unread'] }}</span>
                                @endif
                            </button>
                        </div>
                    @endif
                </div>
                
                <!-- View Options -->
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="toggleConversationView"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md"
                        title="Toggle conversation view"
                    >
                        @if($conversationView)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        @endif
                    </button>
                </div>
            </div>
            
            <!-- Email List -->
            <div class="flex-1 overflow-y-auto">
                @if($emails->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($emails as $email)
                            @php
                                $isSelected = $currentEmailId == $email->id;
                                $isUnread = !$email->is_read && in_array($selectedMenuItem, [1, 5]);
                            @endphp
                            <div 
                                wire:click="selectEmail({{ $email->id }})"
                                class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer border-l-4 @if($isSelected) bg-red-50 border-l-red-500 @elseif($isUnread) bg-red-25 border-l-red-300 @else border-l-transparent @endif"
                            >
                                <!-- Sender/Recipient Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium text-gray-900 truncate @if($isUnread) font-bold @endif">
                                            @if(in_array($selectedMenuItem, [2, 3]))
                                                To: {{ $email->recipient_email }}
                                            @else
                                                {{ $email->sender_name ?? $email->sender_email ?? 'Unknown Sender' }}
                                            @endif
                                        </p>
                                        <div class="flex items-center space-x-2">
                                            @if($isUnread)
                                                <div class="w-2 h-2 bg-red-600 rounded-full"></div>
                                            @endif
                                            <p class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($email->created_at)->format('M j, g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-800 truncate @if($isUnread) font-semibold @endif mb-1">
                                        {{ $email->subject }}
                                    </p>
                                    <p class="text-sm text-gray-600 truncate">
                                        {{ Str::limit(strip_tags($email->body), 100) }}
                                    </p>
                                </div>
                                
                                <!-- Email Indicators -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if($email->has_attachments ?? false)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                    @endif
                                    @if($email->is_flagged ?? false)
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                                        </svg>
                                    @endif
                                    @if($email->is_pinned ?? false)
                                        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No emails found</h3>
                        <p class="text-gray-500">
                            @if($search)
                                No emails match your search criteria
                            @else
                                This folder is empty
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Right Pane - Reading Pane -->
        @if($currentEmailId && !$showComposePane)
            <div class="w-1/2 border-l border-gray-200 bg-white flex flex-col">
                <livewire:email.email-reading-pane :emailId="$currentEmailId" :key="'reading-pane-'.$currentEmailId" />
            </div>
        @endif
        
        <!-- Compose Pane -->
        <div class="w-1/2 border-l border-gray-200 bg-white flex flex-col @if(!$showComposePane) hidden @endif">
            <livewire:email.compose-pane :key="'compose-pane-'.Auth::id()" />
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Keyboard shortcuts (Outlook-style)
    document.addEventListener('keydown', function(e) {
        // Ctrl+N for new message
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            @this.set('showComposePane', true);
        }
        
        // Delete key for delete email
        if (e.key === 'Delete' && @this.currentEmailId) {
            e.preventDefault();
            @this.call('deleteEmail', @this.currentEmailId);
        }
        
        // F3 for search
        if (e.key === 'F3') {
            e.preventDefault();
            document.querySelector('input[placeholder="Search mail and people"]').focus();
        }
    });
</script>
@endpush