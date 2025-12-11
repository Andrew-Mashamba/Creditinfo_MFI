<div class="bg-gradient-to-br from-slate-50 to-blue-50 p-2">
    <!-- Compact Header with Stats -->
    <div class="mb-3">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="p-1.5 bg-blue-900 rounded">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-gray-900">Leadership & Management</h1>
                        <p class="text-xs text-gray-600">Manage leaders, committees, meetings & documents</p>
                    </div>
                </div>
                
                <!-- Quick Stats Grid -->
                <div class="grid grid-cols-4 gap-2">
                    <div class="bg-blue-50 rounded px-3 py-1.5 border border-blue-100">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-semibold text-blue-900">{{ \App\Models\LeaderShipModel::count() ?? 0 }}</p>
                                <p class="text-xs text-gray-600">Leaders</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded px-3 py-1.5 border border-green-100">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-green-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-semibold text-green-900">{{ \App\Models\Committee::count() ?? 0 }}</p>
                                <p class="text-xs text-gray-600">Committees</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-yellow-50 rounded px-3 py-1.5 border border-yellow-100">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-yellow-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-semibold text-yellow-900">{{ \App\Models\Meeting::where('meeting_date', '>=', now())->count() ?? 0 }}</p>
                                <p class="text-xs text-gray-600">Meetings</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded px-3 py-1.5 border border-purple-100">
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-purple-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div>
                                <p class="text-xs font-semibold text-purple-900">{{ \App\Models\MeetingAttendance::where('status', 'present')->count() ?? 0 }}</p>
                                <p class="text-xs text-gray-600">Attended</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <!-- Compact Sidebar -->
        <div class="w-64 bg-white rounded-lg shadow-sm border border-gray-100">
            <!-- Search -->
            <div class="p-3 border-b border-gray-100">
                <div class="relative">
                    <svg class="absolute left-2 top-2 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.debounce.300ms="sidebarSearch" 
                           placeholder="Search..." 
                           class="w-full pl-7 pr-2 py-1.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent bg-gray-50 hover:bg-white"/>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="p-2">
                <h3 class="text-xs font-semibold text-gray-500 uppercase px-2 mb-2">Navigation</h3>
                @php
                    $sections = [
                        ['id' => 'leadership', 'label' => 'Leadership', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'blue'],
                        ['id' => 'committees', 'label' => 'Committees', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'green'],
                        ['id' => 'members', 'label' => 'Members', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'purple'],
                        ['id' => 'meetings', 'label' => 'Meetings', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'yellow'],
                        ['id' => 'attendance', 'label' => 'Attendance', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'color' => 'indigo'],
                        ['id' => 'documents', 'label' => 'Documents', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color' => 'red'],
                        ['id' => 'stipend_report', 'label' => 'Stipends', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'pink'],
                    ];
                    $activeSection = $activeSection ?? 'leadership';
                @endphp
                
                <nav class="space-y-1">
                    @foreach($sections as $section)
                        <button wire:click="$set('activeSection', '{{ $section['id'] }}')" 
                                class="w-full flex items-center px-2 py-1.5 rounded text-xs font-medium transition-all duration-200
                                @if($activeSection === $section['id']) 
                                    bg-blue-900 text-white shadow-sm
                                @else 
                                    hover:bg-gray-100 text-gray-700
                                @endif">
                            <svg class="w-3 h-3 mr-2 @if($activeSection === $section['id']) text-white @else text-{{ $section['color'] }}-600 @endif" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/>
                            </svg>
                            {{ $section['label'] }}
                            @if($section['id'] === 'meetings')
                                <span class="ml-auto bg-yellow-100 text-yellow-800 text-xs px-1.5 py-0.5 rounded-full">
                                    {{ \App\Models\Meeting::where('meeting_date', '>=', now())->count() }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>
            
            <!-- Quick Actions -->
            <div class="p-2 border-t border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-500 uppercase px-2 mb-2">Quick Actions</h3>
                <div class="space-y-1">
                    <button wire:click="openNewLeaderModal" 
                            class="w-full flex items-center px-2 py-1 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors">
                        <svg class="w-3 h-3 mr-1.5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Add Leader
                    </button>
                    <button wire:click="openNewCommitteeModal" 
                            class="w-full flex items-center px-2 py-1 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors">
                        <svg class="w-3 h-3 mr-1.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Committee
                    </button>
                    <button wire:click="openScheduleMeetingModal" 
                            class="w-full flex items-center px-2 py-1 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors">
                        <svg class="w-3 h-3 mr-1.5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Schedule Meeting
                    </button>
                    <button wire:click="exportData" 
                            class="w-full flex items-center px-2 py-1 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded transition-colors">
                        <svg class="w-3 h-3 mr-1.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                <!-- Content Header -->
                <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 flex items-center">
                                @php
                                    $titles = [
                                        'leadership' => ['title' => 'Leadership Management', 'desc' => 'View and manage institution leaders'],
                                        'committees' => ['title' => 'Committee Management', 'desc' => 'Create and manage committees'],
                                        'members' => ['title' => 'Committee Members', 'desc' => 'Assign members to committees'],
                                        'meetings' => ['title' => 'Meeting Schedule', 'desc' => 'Schedule and track meetings'],
                                        'attendance' => ['title' => 'Attendance Tracking', 'desc' => 'Mark and review attendance'],
                                        'documents' => ['title' => 'Document Repository', 'desc' => 'Upload and manage documents'],
                                        'stipend_report' => ['title' => 'Stipend Reports', 'desc' => 'Track stipend payments'],
                                    ];
                                    $current = $titles[$activeSection] ?? $titles['leadership'];
                                @endphp
                                <span class="mr-2">{{ $current['title'] }}</span>
                                @if($activeSection === 'leadership')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-900">
                                        {{ \App\Models\LeaderShipModel::count() }} Total
                                    </span>
                                @elseif($activeSection === 'committees')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-900">
                                        {{ \App\Models\Committee::where('status', true)->count() }} Active
                                    </span>
                                @endif
                            </h2>
                            <p class="text-xs text-gray-600 mt-0.5">{{ $current['desc'] }}</p>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <input type="text" 
                                       wire:model.debounce.300ms="searchTerm" 
                                       placeholder="Search {{ strtolower($current['title']) }}..." 
                                       class="pl-7 pr-2 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-transparent"/>
                                <svg class="absolute left-2 top-1.5 h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            
                            @if($activeSection === 'leadership')
                                <button wire:click="openNewLeaderModal" 
                                        class="inline-flex items-center px-2 py-1 bg-blue-900 text-white text-xs font-medium rounded hover:bg-blue-800 focus:outline-none focus:ring-1 focus:ring-blue-900">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Leader
                                </button>
                            @elseif($activeSection === 'committees')
                                <button wire:click="openNewCommitteeModal" 
                                        class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    New Committee
                                </button>
                            @elseif($activeSection === 'meetings')
                                <button wire:click="openScheduleMeetingModal" 
                                        class="inline-flex items-center px-2 py-1 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 focus:outline-none focus:ring-1 focus:ring-yellow-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Schedule Meeting
                                </button>
                            @endif
                            
                            <button wire:click="refreshData" 
                                    class="p-1 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded focus:outline-none">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Dynamic Content Area -->
                <div class="p-4">
                    @if($activeSection === 'leadership')
                        <livewire:profile-setting.leader-ship-table />
                    @elseif($activeSection === 'committees')
                        <livewire:profile-setting.committee-manager />
                    @elseif($activeSection === 'members')
                        @if(isset($selectedCommitteeId))
                            <livewire:profile-setting.committee-members-manager :committee_id="$selectedCommitteeId" />
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-xs text-gray-500">Please select a committee first</p>
                                <button wire:click="$set('activeSection', 'committees')" 
                                        class="mt-2 text-xs text-blue-900 hover:text-blue-700 font-medium">
                                    Go to Committees →
                                </button>
                            </div>
                        @endif
                    @elseif($activeSection === 'meetings')
                        @if(isset($selectedCommitteeId))
                            <livewire:profile-setting.meeting-manager :committee_id="$selectedCommitteeId" />
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-gray-500">Please select a committee to view meetings</p>
                                <button wire:click="$set('activeSection', 'committees')" 
                                        class="mt-2 text-xs text-blue-900 hover:text-blue-700 font-medium">
                                    Go to Committees →
                                </button>
                            </div>
                        @endif
                    @elseif($activeSection === 'attendance')
                        @if(isset($selectedMeetingId))
                            <livewire:profile-setting.meeting-attendance-manager :meeting_id="$selectedMeetingId" />
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <p class="text-xs text-gray-500">Please select a meeting to mark attendance</p>
                                <button wire:click="$set('activeSection', 'meetings')" 
                                        class="mt-2 text-xs text-blue-900 hover:text-blue-700 font-medium">
                                    Go to Meetings →
                                </button>
                            </div>
                        @endif
                    @elseif($activeSection === 'documents')
                        @if(isset($selectedMeetingId))
                            <livewire:profile-setting.meeting-documents-manager :meeting_id="$selectedMeetingId" />
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs text-gray-500">Please select a meeting to manage documents</p>
                                <button wire:click="$set('activeSection', 'meetings')" 
                                        class="mt-2 text-xs text-blue-900 hover:text-blue-700 font-medium">
                                    Go to Meetings →
                                </button>
                            </div>
                        @endif
                    @elseif($activeSection === 'stipend_report')
                        <livewire:profile-setting.stipend-report-manager />
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-gray-400">Select a section from the sidebar to begin</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-xs flex items-center space-x-2 z-50">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg text-xs flex items-center space-x-2 z-50">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>