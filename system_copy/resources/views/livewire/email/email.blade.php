<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Email System</h1>
                        <p class="text-gray-600 mt-1">Manage your email communications</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Unread</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $unreadCount }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Emails</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $totalEmails }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Storage Used</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($storageUsed / 1024 / 1024, 1) }} MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Compose Button -->
                <div class="p-6 border-b border-gray-100">
                    <button 
                        wire:click="openComposeModal"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-xl transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md mb-3"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Compose
                    </button>
                    
                    <!-- Switch to Outlook Interface -->
                    <button 
                        wire:click="switchToOutlookInterface"
                        class="w-full bg-blue-900 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md text-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        Switch to Outlook View (No Popups)
                    </button>
                </div>

                <!-- Search Section -->
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.debounce.300ms="search" 
                            placeholder="Search emails..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search emails"
                        />
                    </div>
                    
                    <!-- Advanced Search Toggle -->
                    <button 
                        wire:click="$toggle('showAdvancedSearch')"
                        class="mt-2 text-sm text-red-600 hover:text-red-800 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        Advanced Search
                    </button>
                    
                    <!-- Advanced Search Options -->
                    @if($showAdvancedSearch)
                        <div class="mt-4 space-y-3 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                                <input 
                                    type="text" 
                                    wire:model.debounce.300ms="searchFrom"
                                    placeholder="Sender email or name"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                                <input 
                                    type="text" 
                                    wire:model.debounce.300ms="searchTo"
                                    placeholder="Recipient email"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                />
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                                    <input 
                                        type="date" 
                                        wire:model="searchDateFrom"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                                    <input 
                                        type="date" 
                                        wire:model="searchDateTo"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="searchHasAttachment"
                                    id="has-attachment"
                                    class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                />
                                <label for="has-attachment" class="ml-2 text-sm text-gray-700">
                                    Has attachments
                                </label>
                            </div>
                            
                            <button 
                                wire:click="resetSearchFilters"
                                class="w-full py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                            >
                                Clear Filters
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Folders</h3>
                    
                    @php
                        $email_sections = [
                            [
                                'id' => 1, 
                                'label' => 'Inbox', 
                                'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
                                'description' => 'Received messages',
                                'folder' => 'inbox'
                            ],
                            [
                                'id' => 2, 
                                'label' => 'Sent', 
                                'icon' => 'M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76',
                                'description' => 'Sent messages',
                                'folder' => 'sent'
                            ],
                            [
                                'id' => 3, 
                                'label' => 'Drafts', 
                                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                'description' => 'Unsent messages',
                                'folder' => 'drafts'
                            ],
                            [
                                'id' => 4, 
                                'label' => 'Spam', 
                                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                                'description' => 'Spam messages',
                                'folder' => 'spam'
                            ],
                            [
                                'id' => 5, 
                                'label' => 'Trash', 
                                'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                                'description' => 'Deleted messages',
                                'folder' => 'trash'
                            ],
                            [
                                'id' => 6, 
                                'label' => 'Snoozed', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Snoozed messages',
                                'folder' => 'snoozed'
                            ],
                            [
                                'id' => 7, 
                                'label' => 'Scheduled', 
                                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                'description' => 'Scheduled messages',
                                'folder' => 'scheduled'
                            ],
                        ];
                    @endphp

                    <nav class="space-y-2">
                        @foreach ($email_sections as $section)
                            @php
                                $count = 0;
                                if ($section['id'] == 1) {
                                    $count = $unreadCount;
                                }
                                $isActive = $this->selectedMenuItem == $section['id'];
                            @endphp

                            <button
                                wire:click="selectedMenu({{ $section['id'] }})"
                                class="relative w-full group transition-all duration-200"
                                aria-label="{{ $section['label'] }}"
                            >
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                    @if ($isActive) 
                                        bg-red-50 text-red-700 shadow-sm border border-red-200
                                    @else 
                                        bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                    @endif">
                                    
                                    <!-- Loading State -->
                                    <div wire:loading wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Icon -->
                                    <div wire:loading.remove wire:target="selectedMenu({{ $section['id'] }})" class="mr-3">
                                        <svg class="w-5 h-5 @if ($isActive) text-red-600 @else text-gray-500 group-hover:text-gray-700 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                        </svg>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                        <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                    </div>

                                    <!-- Notification Badge -->
                                    @if ($count > 0)
                                        <div class="ml-2">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px] h-5">
                                                {{ $count > 99 ? '99+' : $count }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </nav>
                    
                    <!-- Search Folders Section -->
                    @if(count($searchFolders) > 0)
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-4 px-2">Search Folders</h3>
                        <nav class="space-y-2">
                            @foreach($searchFolders as $searchFolder)
                                @php
                                    $isActive = $this->currentSearchFolderId == $searchFolder->id;
                                @endphp
                                <button
                                    wire:click="selectSearchFolder({{ $searchFolder->id }})"
                                    class="relative w-full group transition-all duration-200"
                                    aria-label="{{ $searchFolder->name }}"
                                >
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200
                                        @if ($isActive) 
                                            bg-purple-50 text-purple-700 shadow-sm border border-purple-200
                                        @else 
                                            bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 
                                        @endif">
                                        
                                        <!-- Icon -->
                                        <div class="mr-3">
                                            <div class="w-5 h-5 rounded-full" style="background-color: {{ $searchFolder->color }}"></div>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ $searchFolder->name }}</div>
                                            @if($searchFolder->description)
                                                <div class="text-xs opacity-75">{{ $searchFolder->description }}</div>
                                            @endif
                                        </div>

                                        <!-- Count Badge -->
                                        @if($searchFolder->cached_count > 0)
                                            <div class="ml-2">
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-gray-600 bg-gray-200 rounded-full min-w-[20px] h-5">
                                                    {{ $searchFolder->cached_count }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Delete Button -->
                                        <button
                                            wire:click.stop="deleteSearchFolder({{ $searchFolder->id }})"
                                            class="ml-2 p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Delete search folder"
                                        >
                                            <svg class="w-4 h-4 text-gray-400 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </button>
                            @endforeach
                        </nav>
                    @endif
                    
                    <!-- Add Search Folder Button -->
                    <button
                        wire:click="openSearchFolderModal"
                        class="mt-4 w-full p-3 text-sm text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors flex items-center justify-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Search Folder
                    </button>
                </div>

                <!-- Storage Info -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Storage</h3>
                    <div class="px-2">
                        <div class="mb-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Used</span>
                                <span class="font-medium text-gray-900">{{ number_format($storageUsed / 1024 / 1024, 1) }} MB of {{ number_format($storageLimit / 1024 / 1024, 0) }} MB</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-red-600 h-2.5 rounded-full" style="width: {{ min(($storageUsed / $storageLimit) * 100, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Main Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Content Header -->
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @if($currentSearchFolderId)
                                        @php
                                            $currentFolder = collect($searchFolders)->firstWhere('id', $currentSearchFolderId);
                                        @endphp
                                        {{ $currentFolder->name ?? 'Search Folder' }}
                                    @else
                                        @switch($this->selectedMenuItem)
                                            @case(1) Inbox @break
                                            @case(2) Sent @break
                                            @case(3) Drafts @break
                                            @case(4) Spam @break
                                            @case(5) Trash @break
                                            @case(6) Snoozed @break
                                            @case(7) Scheduled @break
                                            @default Inbox
                                        @endswitch
                                    @endif
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    @switch($this->selectedMenuItem)
                                        @case(1) All your received emails @break
                                        @case(2) Emails you have sent @break
                                        @case(3) Unsent email drafts @break
                                        @case(4) Spam and junk emails @break
                                        @case(5) Deleted emails @break
                                        @default All your received emails
                                    @endswitch
                                </p>
                            </div>
                            
                            <!-- Breadcrumb -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                            </svg>
                                            Email
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                @switch($this->selectedMenuItem)
                                                    @case(1) Inbox @break
                                                    @case(2) Sent @break
                                                    @case(3) Drafts @break
                                                    @case(4) Spam @break
                                                    @case(5) Trash @break
                                                    @default Inbox
                                                @endswitch
                                            </span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="p-8">
                        <!-- Dynamic Content -->
                        <div wire:loading.remove wire:target="selectedMenu" class="min-h-[400px]">
                            @if($currentEmailId)
                                <!-- Email Detail View -->
                                <livewire:email.email-detail :emailId="$currentEmailId" :key="'email-detail-'.$currentEmailId" />
                            @else
                                <!-- Email List View -->
                                
                                <!-- View Toggle and Focused Inbox Controls -->
                                @if(in_array($selectedMenuItem, [1, 2]))
                                    <div class="mb-4 flex justify-between items-center">
                                        <!-- Focused Inbox Toggle (for Inbox only) -->
                                        @if($selectedMenuItem == 1)
                                            <div class="flex items-center space-x-4">
                                                <button 
                                                    wire:click="toggleFocusedInbox"
                                                    class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                >
                                                    @if($focusedInboxEnabled)
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg>
                                                        Focused Inbox On
                                                    @else
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                        </svg>
                                                        Focused Inbox Off
                                                    @endif
                                                </button>
                                                
                                                @if($focusedInboxEnabled)
                                                    <button 
                                                        wire:click="processAllForFocusedInbox"
                                                        class="text-sm text-blue-600 hover:text-blue-800"
                                                        title="Process all emails for focused inbox"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div></div>
                                        @endif
                                        
                                        <!-- Conversation View Toggle -->
                                        <button 
                                            wire:click="toggleConversationView"
                                            class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            @if($conversationView)
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                </svg>
                                                List View
                                            @else
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                                                </svg>
                                                Conversation View
                                            @endif
                                        </button>
                                    </div>
                                @endif
                                
                                <!-- Focused Inbox Tabs -->
                                @if($selectedMenuItem == 1 && $focusedInboxEnabled)
                                    <div class="mb-6">
                                        <div class="border-b border-gray-200">
                                            <nav class="-mb-px flex space-x-8">
                                                <button 
                                                    wire:click="switchFocusedTab('focused')"
                                                    class="@if($focusedTab === 'focused') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center"
                                                >
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    Focused
                                                    @if(isset($focusedStats['focused_unread']) && $focusedStats['focused_unread'] > 0)
                                                        <span class="ml-2 bg-blue-100 text-blue-600 text-xs px-2 py-0.5 rounded-full">
                                                            {{ $focusedStats['focused_unread'] }}
                                                        </span>
                                                    @endif
                                                </button>
                                                
                                                <button 
                                                    wire:click="switchFocusedTab('other')"
                                                    class="@if($focusedTab === 'other') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm flex items-center"
                                                >
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                    </svg>
                                                    Other
                                                    @if(isset($focusedStats['other_unread']) && $focusedStats['other_unread'] > 0)
                                                        <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
                                                            {{ $focusedStats['other_unread'] }}
                                                        </span>
                                                    @endif
                                                </button>
                                            </nav>
                                        </div>
                                        
                                        <!-- Focused Inbox Stats -->
                                        @if(count($focusedStats) > 0)
                                            <div class="mt-2 text-sm text-gray-600">
                                                {{ $focusedStats['focus_percentage'] }}% of your emails are marked as important
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($emails->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($emails as $email)
                                            @if($conversationView && isset($email['id']) && str_starts_with($email['id'], 'conv_'))
                                                <!-- Conversation View -->
                                                <div 
                                                    wire:click="openConversation('{{ $email['id'] }}')"
                                                    class="cursor-pointer bg-white hover:bg-gray-50 border border-gray-200 rounded-lg p-4 transition-all duration-200 @if($email['is_unread']) bg-blue-50 border-blue-200 @endif"
                                                >
                                                    <div class="flex items-start justify-between">
                                                        <div class="flex-1">
                                                            <div class="flex items-center justify-between mb-1">
                                                                <div class="flex items-center">
                                                                    <h4 class="font-medium text-gray-900 @if($email['is_unread']) font-bold @endif">
                                                                        {{ $email['latest_sender'] }}
                                                                    </h4>
                                                                    @if($email['message_count'] > 1)
                                                                        <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full">
                                                                            {{ $email['message_count'] }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <span class="text-sm text-gray-500">
                                                                    {{ \Carbon\Carbon::parse($email['latest_date'])->format('M d, Y H:i') }}
                                                                </span>
                                                            </div>
                                                            <h5 class="text-sm font-medium text-gray-800 mb-1 @if($email['is_unread']) font-bold @endif">
                                                                {{ $email['subject'] }}
                                                            </h5>
                                                            <p class="text-sm text-gray-600">
                                                                {{ $email['preview'] }}
                                                            </p>
                                                        </div>
                                                        
                                                        <!-- Quick Actions -->
                                                        <div class="ml-4 flex items-center space-x-2">
                                                            @if($email['is_unread'])
                                                                <div class="w-2 h-2 bg-blue-900 rounded-full"></div>
                                                            @endif
                                                            
                                                            @if($email['has_attachments'])
                                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                                </svg>
                                                            @endif
                                                            
                                                            <button 
                                                                wire:click.stop="deleteConversation('{{ $email['id'] }}')"
                                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                                                title="Delete conversation"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Regular Email View -->
                                                <div 
                                                    wire:click="$set('currentEmailId', {{ $email->id }})"
                                                    class="cursor-pointer bg-white hover:bg-gray-50 border border-gray-200 rounded-lg p-4 transition-all duration-200 @if(!$email->is_read && $folder == 'inbox') bg-blue-50 border-blue-200 @endif @if($email->is_pinned ?? false) ring-2 ring-yellow-400 @endif @if($email->is_focused ?? false) border-l-4 border-l-blue-500 @endif"
                                                >
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <h4 class="font-medium text-gray-900 @if(!$email->is_read && $folder == 'inbox') font-bold @endif">
                                                                @if($folder == 'sent' || $folder == 'drafts')
                                                                    To: {{ $email->recipient_email }}
                                                                @else
                                                                    {{ $email->sender_name ?? $email->sender_email ?? 'Unknown Sender' }}
                                                                @endif
                                                            </h4>
                                                            <span class="text-sm text-gray-500">
                                                                @if($selectedMenuItem == 6 && isset($email->snooze_until))
                                                                    <span class="flex items-center text-purple-600">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                        {{ \Carbon\Carbon::parse($email->snooze_until)->format('M j, g:i A') }}
                                                                    </span>
                                                                @elseif($selectedMenuItem == 7 && isset($email->scheduled_at))
                                                                    <span class="flex items-center text-purple-600">
                                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                        </svg>
                                                                        {{ \Carbon\Carbon::parse($email->scheduled_at)->format('M j, g:i A') }}
                                                                    </span>
                                                                @else
                                                                    {{ \Carbon\Carbon::parse($email->created_at)->format('M d, Y H:i') }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <h5 class="text-sm font-medium text-gray-800 mb-1 @if(!$email->is_read && $folder == 'inbox') font-bold @endif">
                                                            {{ Str::limit($email->subject, 80) }}
                                                        </h5>
                                                        <p class="text-sm text-gray-600">
                                                            {{ Str::limit(strip_tags($email->body), 120) }}
                                                        </p>
                                                    </div>
                                                    
                                                    <!-- Quick Actions -->
                                                    <div class="ml-4 flex items-center space-x-2">
                                                        @if($folder == 'inbox' && !$email->is_read)
                                                            <div class="w-2 h-2 bg-blue-900 rounded-full"></div>
                                                        @endif
                                                        
                                                        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <!-- Pin Button -->
                                                            <button 
                                                                wire:click.stop="togglePin({{ $email->id }})"
                                                                class="p-1 transition-colors @if($email->is_pinned ?? false) text-yellow-500 hover:text-yellow-600 @else text-gray-400 hover:text-yellow-500 @endif"
                                                                title="@if($email->is_pinned ?? false) Unpin @else Pin @endif"
                                                            >
                                                                <svg class="w-4 h-4" fill="@if($email->is_pinned ?? false) currentColor @else none @endif" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                                </svg>
                                                            </button>
                                                            
                                                            <!-- Flag Button -->
                                                            <button 
                                                                wire:click.stop="toggleFlag({{ $email->id }})"
                                                                class="p-1 transition-colors @if($email->is_flagged ?? false) text-red-500 hover:text-red-600 @else text-gray-400 hover:text-red-500 @endif"
                                                                title="@if($email->is_flagged ?? false) Unflag @else Flag @endif"
                                                            >
                                                                <svg class="w-4 h-4" fill="@if($email->is_flagged ?? false) currentColor @else none @endif" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                                                                </svg>
                                                            </button>
                                                            
                                                            <!-- Reminder Button -->
                                                            <button 
                                                                wire:click.stop="$emit('createReminderForEmail', {{ $email->id }})"
                                                                class="p-1 text-gray-400 hover:text-purple-500 transition-colors"
                                                                title="Set reminder"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                            </button>
                                                            
                                                            <!-- Focus Toggle (Inbox only) -->
                                                            @if($folder == 'inbox' && $focusedInboxEnabled)
                                                                <button 
                                                                    wire:click.stop="toggleEmailFocus({{ $email->id }})"
                                                                    class="p-1 transition-colors @if($email->is_focused ?? false) text-blue-500 hover:text-blue-600 @else text-gray-400 hover:text-blue-500 @endif"
                                                                    title="@if($email->is_focused ?? false) Mark as not important @else Mark as important @endif"
                                                                >
                                                                    <svg class="w-4 h-4" fill="@if($email->is_focused ?? false) currentColor @else none @endif" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                            
                                                            @if($folder != 'trash')
                                                                <button 
                                                                    wire:click.stop="deleteEmail({{ $email->id }})"
                                                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                                                    title="Delete"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                            
                                                            @if($folder == 'inbox')
                                                                @if($email->is_read)
                                                                    <button 
                                                                        wire:click.stop="markAsUnread({{ $email->id }})"
                                                                        class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
                                                                        title="Mark as unread"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                                                        </svg>
                                                                    </button>
                                                                @else
                                                                    <button 
                                                                        wire:click.stop="markAsRead({{ $email->id }})"
                                                                        class="p-1 text-gray-400 hover:text-green-600 transition-colors"
                                                                        title="Mark as read"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path>
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                                
                                                                <button 
                                                                    wire:click.stop="moveToSpam({{ $email->id }})"
                                                                    class="p-1 text-gray-400 hover:text-yellow-600 transition-colors"
                                                                    title="Mark as spam"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                                    </svg>
                                                                </button>
                                                                
                                                                @if($selectedMenuItem != 7)
                                                                    <button 
                                                                        wire:click.stop="openSnoozeModal({{ $email->id }})"
                                                                        class="p-1 text-gray-400 hover:text-purple-600 transition-colors"
                                                                        title="Snooze"
                                                                    >
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            @endif
                                                            
                                                            @if($selectedMenuItem == 7)
                                                                <!-- Cancel scheduled email -->
                                                                <button 
                                                                    wire:click.stop="cancelScheduledEmail({{ $email->id }})"
                                                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                                                    title="Cancel scheduled send"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-6">
                                        {{ $emails->links() }}
                                    </div>
                                @else
                                    <!-- Empty State -->
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No emails found</h3>
                                        <p class="text-gray-600">
                                            @if($this->search)
                                                No emails match your search criteria
                                            @else
                                                This folder is empty
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Loading State -->
                        <div wire:loading wire:target="selectedMenu" class="min-h-[400px] flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 animate-spin text-red-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-600">Loading emails...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compose Modal -->
    @if($showComposeModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">New Message</h3>
                        <button 
                            wire:click="closeComposeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="sendEmail">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                <input 
                                    type="email" 
                                    wire:model="to"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="recipient@example.com"
                                    required
                                />
                                @error('to') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cc</label>
                                    <input 
                                        type="email" 
                                        wire:model="cc"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                        placeholder="cc@example.com"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bcc</label>
                                    <input 
                                        type="email" 
                                        wire:model="bcc"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                        placeholder="bcc@example.com"
                                    />
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                                    <button 
                                        type="button"
                                        @click="$dispatch('show-templates-modal')"
                                        class="text-sm text-red-600 hover:text-red-700 flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Use Template
                                    </button>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="subject"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Email subject"
                                    required
                                />
                                @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea 
                                    wire:model.debounce.500ms="body"
                                    rows="10"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Type your message here..."
                                    required
                                ></textarea>
                                @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                
                                <!-- Smart Compose Suggestions - AI service removed -->
                            </div>
                            
                            <!-- Signature Section -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Signature</label>
                                    <a 
                                        href="/email/signatures" 
                                        target="_blank"
                                        class="text-sm text-red-600 hover:text-red-700"
                                    >
                                        Manage Signatures
                                    </a>
                                </div>
                                
                                <select 
                                    wire:model="selectedSignatureId"
                                    wire:change="$set('selectedSignatureId', $event.target.value)"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                >
                                    <option value="">No signature</option>
                                    @foreach($signatures as $signature)
                                        <option value="{{ $signature->id }}" @if($signature->is_default) selected @endif>
                                            {{ $signature->name }}
                                            @if($signature->is_default) (Default) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Email Tracking Section -->
                            <div class="border-t pt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            wire:model="enableTracking"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Enable email tracking</span>
                                    </label>
                                    
                                    @if($enableTracking)
                                        <div class="flex items-center space-x-4 text-sm">
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="trackOpens"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-1"
                                                />
                                                <span class="text-gray-600">Track opens</span>
                                            </label>
                                            
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="trackClicks"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-1"
                                                />
                                                <span class="text-gray-600">Track clicks</span>
                                            </label>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($enableTracking)
                                    <p class="text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Tracking adds a small pixel and modifies links to monitor email engagement
                                    </p>
                                @endif
                            </div>
                            
                            <!-- Read/Delivery Receipts Section -->
                            <div class="border-t pt-4">
                                <div class="space-y-3">
                                    <label class="flex items-center cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            wire:model="requestReadReceipt"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Request read receipt</span>
                                    </label>
                                    
                                    <label class="flex items-center cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            wire:model="requestDeliveryReceipt"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Request delivery receipt</span>
                                    </label>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-2">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Recipients will be notified that you requested receipts
                                </p>
                            </div>
                            
                            <!-- Attachments Section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
                                
                                <!-- File Upload Area -->
                                <div class="mb-3">
                                    <label class="flex items-center justify-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md appearance-none cursor-pointer hover:border-gray-400 focus:outline-none">
                                        <span class="flex items-center space-x-2">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <span class="font-medium text-gray-600">
                                                Drop files here or 
                                                <span class="text-blue-600 underline">browse</span>
                                            </span>
                                        </span>
                                        <input 
                                            type="file" 
                                            wire:model="attachments" 
                                            multiple 
                                            class="hidden"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.zip"
                                        />
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">Maximum file size: 25MB. Total: 50MB</p>
                                </div>
                                
                                <!-- Attachment Errors -->
                                @if(count($attachmentErrors) > 0)
                                    <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                        @foreach($attachmentErrors as $error)
                                            <p class="text-sm text-red-600">{{ $error }}</p>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Uploaded Files List -->
                                @if(count($uploadedAttachments) > 0)
                                    <div class="space-y-2 mb-3">
                                        @foreach($uploadedAttachments as $index => $attachment)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div class="flex items-center space-x-3">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-700">{{ $attachment['name'] }}</p>
                                                        <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 2) }} KB</p>
                                                    </div>
                                                </div>
                                                <button 
                                                    type="button"
                                                    wire:click="removeAttachment({{ $index }})"
                                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Upload Progress -->
                                <div wire:loading wire:target="attachments" class="mt-2">
                                    <div class="flex items-center space-x-2">
                                        <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm text-gray-600">Uploading files...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Text Formatting Toolbar -->
                            <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                                <button type="button" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors" title="Bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors" title="Italic">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4M8 20h4m-2-16l-2 16"></path>
                                    </svg>
                                </button>
                                <button type="button" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors" title="Underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M7 4v8a5 5 0 0010 0V4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <button 
                                type="button"
                                wire:click="saveDraft"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                            >
                                Save as Draft
                            </button>
                            
                            <div class="flex space-x-3">
                                <button 
                                    type="button"
                                    wire:click="closeComposeModal"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="button"
                                    wire:click="openScheduleModal"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Schedule
                                </button>
                                <button 
                                    type="submit"
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    Send Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Snooze Modal -->
    @if($showSnoozeModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Snooze Email</h3>
                        <button 
                            wire:click="closeSnoozeModal"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 mb-4">Choose when to see this email again:</p>
                        
                        <!-- Predefined Options -->
                        @foreach($snoozeOptions as $option)
                            <button
                                wire:click="snoozeEmail('{{ $option['value'] }}')"
                                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-700">{{ $option['label'] }}</span>
                                    <span class="text-sm text-gray-500">{{ $option['time'] }}</span>
                                </div>
                            </button>
                        @endforeach
                        
                        <!-- Custom Date/Time -->
                        <div class="border-t pt-3 mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Or pick a custom time:</label>
                            <div class="flex space-x-2">
                                <input
                                    type="date"
                                    wire:model="customSnoozeDate"
                                    min="{{ date('Y-m-d') }}"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                                <input
                                    type="time"
                                    wire:model="customSnoozeTime"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>
                            <button
                                wire:click="snoozeEmail"
                                class="mt-2 w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                                @if(!$customSnoozeDate || !$customSnoozeTime) disabled @endif
                            >
                                Snooze Until Custom Time
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Schedule Modal -->
    @if($showScheduleModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Schedule Email</h3>
                        <button 
                            wire:click="closeScheduleModal"
                            class="text-gray-400 hover:text-gray-500"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <p class="text-sm text-gray-600 mb-4">Choose when to send this email:</p>
                        
                        <!-- Predefined Options -->
                        @foreach($scheduleOptions as $option)
                            <button
                                wire:click="scheduleEmail('{{ $option['value'] }}')"
                                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-700">{{ $option['label'] }}</span>
                                    <span class="text-sm text-gray-500">{{ $option['time'] }}</span>
                                </div>
                            </button>
                        @endforeach
                        
                        <!-- Custom Date/Time -->
                        <div class="border-t pt-3 mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Or pick a custom time:</label>
                            <div class="flex space-x-2">
                                <input
                                    type="date"
                                    wire:model="customScheduleDate"
                                    min="{{ date('Y-m-d') }}"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                                <input
                                    type="time"
                                    wire:model="customScheduleTime"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>
                            <button
                                wire:click="scheduleEmail"
                                class="mt-2 w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                                @if(!$customScheduleDate || !$customScheduleTime) disabled @endif
                            >
                                Schedule for Custom Time
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Undo Send Notification -->
    @if($showUndoNotification)
        <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
            <div class="bg-gray-900 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center space-x-4">
                <span class="text-sm">Email will be sent in <span class="font-bold text-lg" wire:poll.1s="updateUndoCountdown({{ $undoSecondsRemaining - 1 }})">{{ $undoSecondsRemaining }}</span> seconds</span>
                <button 
                    wire:click="undoSend"
                    class="px-4 py-2 bg-white text-gray-900 rounded-md hover:bg-gray-100 transition-colors font-medium"
                >
                    Undo
                </button>
                <button 
                    wire:click="dismissUndo"
                    class="text-gray-400 hover:text-white"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Email Templates Modal -->
    <div 
        x-data="{ showTemplatesModal: false }"
        x-on:show-templates-modal.window="showTemplatesModal = true"
        x-on:template-selected.window="showTemplatesModal = false"
    >
        <div 
            x-show="showTemplatesModal" 
            x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            @click.away="showTemplatesModal = false"
        >
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-xl bg-white" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Email Templates</h3>
                    <button 
                        @click="showTemplatesModal = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                @livewire('email.email-templates')
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 z-50">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg">
                {{ session('message') }}
            </div>
        </div>
    @endif
    
    <!-- Search Folder Modal -->
    @if($showSearchFolderModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create Search Folder</h3>
                        <button 
                            wire:click="closeSearchFolderModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="createSearchFolder">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Folder Name</label>
                                <input 
                                    type="text" 
                                    wire:model="searchFolderName"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="e.g., Important Unread"
                                    required
                                />
                                @error('searchFolderName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                <input 
                                    type="text" 
                                    wire:model="searchFolderDescription"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Brief description of this search folder"
                                />
                            </div>
                            
                            <!-- Search Criteria -->
                            <div class="border-t pt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Search Criteria</h4>
                                
                                <div class="space-y-3">
                                    <!-- Basic Filters -->
                                    <div class="grid grid-cols-3 gap-3">
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="searchFolderCriteria.is_unread"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Unread only</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="searchFolderCriteria.is_flagged"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Flagged</span>
                                        </label>
                                        
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="searchFolderCriteria.has_attachments"
                                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2"
                                            />
                                            <span class="text-sm text-gray-700">Has attachments</span>
                                        </label>
                                    </div>
                                    
                                    <!-- Date Range -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                        <select 
                                            wire:model="searchFolderCriteria.date_range"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        >
                                            <option value="">Any time</option>
                                            <option value="today">Today</option>
                                            <option value="yesterday">Yesterday</option>
                                            <option value="3_days">Last 3 days</option>
                                            <option value="7_days">Last 7 days</option>
                                            <option value="30_days">Last 30 days</option>
                                            <option value="this_month">This month</option>
                                            <option value="last_month">Last month</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Text Search -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Contains Text</label>
                                        <input 
                                            type="text" 
                                            wire:model="searchFolderCriteria.search_text"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="Search in subject and body"
                                        />
                                    </div>
                                    
                                    <!-- From Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">From Email</label>
                                        <input 
                                            type="email" 
                                            wire:model="searchFolderCriteria.from_email"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                            placeholder="sender@example.com"
                                        />
                                    </div>
                                    
                                    <!-- Folder Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Include folders</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach(['inbox' => 'Inbox', 'sent' => 'Sent', 'drafts' => 'Drafts'] as $folder => $label)
                                                <label class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:model="searchFolderCriteria.folders"
                                                        value="{{ $folder }}"
                                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-1"
                                                    />
                                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="closeSearchFolderModal"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Create Search Folder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
    // Auto-hide success messages after 3 seconds
    document.addEventListener('livewire:load', function () {
        Livewire.on('emailSent', () => {
            setTimeout(() => {
                @this.call('$refresh');
            }, 3000);
        });
    });
</script>
@endpush