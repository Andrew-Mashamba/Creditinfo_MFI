<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Modern Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="rounded-xl bg-red-600 p-3 shadow-lg">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Clients Management</h1>
                        <p class="mt-1 text-gray-600">Manage, track, and analyze your clients</p>
                    </div>
                </div>
                <!-- Optionally, add quick stats or actions here later -->
            </div>
        </div>


        <div class="overflow-x-auto clients-scroll pb-8">
            <div class="flex gap-6">
                <!-- Enhanced Sidebar -->
                <div class="w-80 shrink-0 rounded-2xl border border-gray-100 bg-white shadow-lg">
                    <!-- Search Section -->
                    <div class="border-b border-gray-100 p-6">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" placeholder="Search clients, applications..."
                                class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-3 text-sm placeholder-gray-500 transition-all duration-200 hover:bg-white focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500"
                                aria-label="Search clients" />
                        </div>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="p-4">
                        <h3 class="mb-4 px-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Navigation</h3>

                        @php
                            $client_sections = [
                                [
                                    'id' => 'dashboard',
                                    'label' => 'Dashboard Overview',
                                    'icon' =>
                                        'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                                    'description' => 'Analytics and insights',
                                ],
                                [
                                    'id' => 'all-members',
                                    'label' => 'All Clients',
                                    'icon' =>
                                        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                                    'description' => 'View all clients',
                                ],
                                [
                                    'id' => 'active-members',
                                    'label' => 'Active Clients',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Active clients only',
                                ],
                                [
                                    'id' => 'pending-applications',
                                    'label' => 'Pending Applications',
                                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                    'description' => 'Awaiting approval',
                                ],
                                [
                                    'id' => 'new-member',
                                    'label' => 'New Client',
                                    'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                    'description' => 'Register new client',
                                ],       
                                [
                                    'id' => 'special-edits',
                                    'label' => 'Special Edits',
                                    'icon' => 'M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L7.5 19.213 3 21l1.787-4.5L16.862 3.487z',
                                    'description' => 'Edit clients special fields',
                                ],
                                
                                [
                                    'id' => 'bulk-import',
                                    'label' => 'Bulk Import',
                                    'icon' => 'M3 3a1 1 0 011-1h3a1 1 0 011 1v4h2V3a1 1 0 011-1h3a1 1 0 011 1v4h2V3a1 1 0 011-1h3a1 1 0 011 1v18a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm9 12.586l3.293-3.293a1 1 0 10-1.414-1.414L12 13.586l-1.879-1.88a1 1 0 00-1.414 1.415L12 16.414z',
                                    'description' => 'Import clients from a file',
                                ],
                                [
                                    'id' => 'member-groups',
                                    'label' => 'Client Groups',
                                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                                    'description' => 'Manage client groups',
                                ],
                                [
                                    'id' => 'member-exit',
                                    'label' => 'Client Exit',
                                    'icon' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                                    'description' => 'Process client exits',
                                ],
                            ];
                        @endphp

                        <nav class="space-y-2">
                            @foreach ($client_sections as $section)
                                {{-- Check permissions for each section --}}
                                @php
                                    $showSection = true;
                                    if ($section['id'] == 'new-member' && !($permissions['canCreate'] ?? false)) {
                                        $showSection = false;
                                    }
                                    if ($section['id'] == 'special-edits' && !($permissions['canEdit'] ?? false)) {
                                        $showSection = false;
                                    }
                                    if ($section['id'] == 'member-exit' && !($permissions['canDelete'] ?? false)) {
                                        $showSection = false;
                                    }
                                    if (in_array($section['id'], ['members-list', 'pending-applications']) && !($permissions['canView'] ?? false)) {
                                        $showSection = false;
                                    }
                                @endphp
                                @if($showSection)
                                @php
                                    $count = 0;
                                    if ($section['id'] == 'pending-applications') {
                                        $count = App\Models\ClientsModel::where('status', 'NEW CLIENT')->count();
                                    }
                                    $isActive = $activeTab === $section['id'];
                                @endphp

                                <button wire:click="$set('activeTab', '{{ $section['id'] }}')"
                                    class="group relative w-full transition-all duration-200"
                                    aria-label="{{ $section['label'] }}">
                                    <div
                                        class="@if ($isActive) bg-red-600 text-white shadow-lg 
                                        @else 
                                            bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 @endif flex items-center rounded-xl p-3 transition-all duration-200">

                                        <!-- Loading State -->
                                        <div wire:loading wire:target="$set('activeTab', '{{ $section['id'] }}')"
                                            class="mr-3">
                                            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>

                                        <!-- Icon -->
                                        <div wire:loading.remove wire:target="$set('activeTab', '{{ $section['id'] }}')"
                                            class="mr-3">
                                            <svg class="@if ($isActive) text-white @else text-gray-500 group-hover:text-gray-700 @endif h-5 w-5"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ $section['icon'] }}"></path>
                                            </svg>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="text-sm font-medium">{{ $section['label'] }}</div>
                                            <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                        </div>

                                        <!-- Notification Badge -->
                                        @if ($count > 0)
                                            <div class="ml-2">
                                                <span
                                                    class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-2 py-1 text-xs font-bold leading-none text-white">
                                                    {{ $count > 99 ? '99+' : $count }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                                @endif
                            @endforeach
                        </nav>
                    </div>

                </div>

                <!-- Enhanced Main Content Area -->
                <div class="flex-1">
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-lg">
                        <!-- Content Header -->
                        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">
                                        @switch($activeTab)
                                            @case('dashboard')
                                                Dashboard Overview
                                            @break

                                            @case('all-members')
                                                All Clients
                                            @break

                                            @case('active-members')
                                                Active Clients
                                            @break

                                            @case('pending-applications')
                                                Pending Applications
                                            @break

                                            @case('new-member')
                                                New Client Registration
                                            @break

                                            @case('special-edits')
                                                Special Edits
                                            @break

                                            @case('bulk-import')
                                                Bulk Import
                                            @break

                                            @case('member-groups')
                                                Client Groups
                                            @break

                                            @case('member-exit')
                                                Client Exit
                                            @break

                                            @default
                                                Dashboard Overview
                                        @endswitch
                                    </h2>
                                    <p class="mt-1 text-gray-600">
                                        @switch($activeTab)
                                            @case('dashboard')
                                                Monitor client trends and performance
                                            @break

                                            @case('all-members')
                                                View and manage all client records
                                            @break

                                            @case('active-members')
                                                View active clients only
                                            @break

                                            @case('pending-applications')
                                                Review and approve pending applications
                                            @break

                                            @case('new-member')
                                                Create and register new clients
                                            @break

                                            @case('special-edits')
                                                Edit clients special fields
                                            @break

                                            @case('bulk-import')    
                                                Import clients from a file
                                            @break

                                            @case('member-groups')
                                                View and manage client groups
                                            @break

                                            @case('member-exit')
                                                Process client exits and account closures
                                            @break

                                            @default
                                                Monitor client trends and performance
                                        @endswitch
                                    </p>
                                </div>

                                <!-- Breadcrumb -->
                                <nav class="flex" aria-label="Breadcrumb">
                                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                        <li class="inline-flex items-center">
                                            <a href="#"
                                                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                                    </path>
                                                </svg>
                                                Clients
                                            </a>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                                                    @switch($activeTab)
                                                        @case('dashboard')
                                                            Dashboard
                                                        @break

                                                        @case('all-members')
                                                            All Clients
                                                        @break

                                                        @case('active-members')
                                                            Active
                                                        @break

                                                        @case('pending-applications')
                                                            Pending
                                                        @break

                                                        @case('new-member')
                                                            New Client
                                                        @break

                                                        @case('special-edits')
                                                            Special Edits
                                                        @break

                                                        @case('bulk-import')
                                                            Bulk Import
                                                        @break

                                                        @case('member-groups')
                                                            Client Groups
                                                        @break

                                                        @case('member-exit')
                                                            Client Exit
                                                        @break

                                                        @default
                                                            Dashboard
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
                            <div wire:loading.remove wire:target="$set('activeTab')" class="min-h-[400px]">
                                @if ($activeTab === 'dashboard')
                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                            <!-- Total Clients Card -->
                                            <div
                                                class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-red-100 p-6 shadow">
                                                <div class="mb-2 flex items-center justify-between">
                                                    <h3 class="text-lg font-semibold text-red-900">Total Clients</h3>
                                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-red-900">
                                                    {{ App\Models\ClientsModel::count() }}</div>
                                            </div>

                                            <!-- Active Clients Card -->
                                            <div
                                                class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-green-100 p-6 shadow">
                                                <div class="mb-2 flex items-center justify-between">
                                                    <h3 class="text-lg font-semibold text-green-900">Active Clients</h3>
                                                    <svg class="h-8 w-8 text-green-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-green-900">
                                                    {{ App\Models\ClientsModel::where('status', 'ACTIVE')->count() }}</div>
                                            </div>

                                            <!-- Pending Applications Card -->
                                            <div
                                                class="rounded-xl border border-yellow-200 bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 shadow">
                                                <div class="mb-2 flex items-center justify-between">
                                                    <h3 class="text-lg font-semibold text-yellow-900">Pending Applications
                                                    </h3>
                                                    <svg class="h-8 w-8 text-yellow-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="text-3xl font-bold text-yellow-900">
                                                    {{ App\Models\ClientsModel::where('status', 'NEW CLIENT')->count() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- All Clients Content -->
                                @if ($activeTab === 'all-members')
                                    <div class="space-y-6">  
                                        <div class="overflow-x-auto">
                                            <livewire:clients.all-members />
                                        </div>
                                    </div>
                                @endif

                                <!-- Active Clients Content -->
                                @if ($activeTab === 'active-members')
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <livewire:clients.active-members />
                                        </div>
                                    </div>
                                @endif

                                <!-- Pending Applications Content -->
                                @if ($activeTab === 'pending-applications')
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <livewire:clients.pending-applications />
                                        </div>
                                    </div>
                                @endif


                                @if ($activeTab === 'new-member')
                                <div class="space-y-6">                                    
                                        <h2 class="text-lg font-semibold text-gray-900">Complete all steps to register a new client</h2>
                                        <div class="p-6">
                                            <!-- Progress Steps -->
                                            <div class="mb-8">
                                                <div class="flex items-center justify-between">
                                                    @foreach (['Personal Info', 'Contact Details', 'Financial Info', 'Documents (Optional)', 'Review & Submit'] as $index => $step)
                                                        <div class="flex flex-col items-center">
                                                            <div class="relative">
                                                                <div
                                                                    class="{{ $currentStep > $index ? 'bg-green-500 text-white' : ($currentStep === $index + 1 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400') }} flex h-8 w-8 items-center justify-center rounded-full transition-colors duration-200">
                                                                    {{ $index + 1 }}
                                                                </div>
                                                                @if ($currentStep === $index + 1)
                                                                    <div
                                                                        class="absolute -bottom-1 -left-1 -right-1 -top-1 animate-pulse rounded-full border-2 border-red-200">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <span
                                                                class="{{ $currentStep > $index ? 'text-green-600' : ($currentStep === $index + 1 ? 'text-gray-800' : 'text-gray-400') }} mt-2 text-xs font-medium">{{ $step }}</span>
                                                        </div>
                                                        @if ($index < 4)
                                                            <div
                                                                class="{{ $currentStep > $index + 1 ? 'bg-green-500' : 'bg-gray-200' }} mx-2 h-px flex-1">
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Form Content -->
                                            <form wire:submit.prevent="save" class="space-y-6">
                                                <!-- Step 1: Personal Information -->
                                                @if ($currentStep === 1)
                                                    <div class="space-y-6">
                                                        <div class="flex justify-center">
                                                            <div class="group relative">
                                                                <div class="h-24 w-24 overflow-hidden rounded-full border-4 border-white shadow-md">
                                                                    @if ($photo)
                                                                        <img class="h-full w-full object-cover" src="{{ $photo->temporaryUrl() }}"
                                                                            alt="Profile photo">
                                                                    @else
                                                                        <img class="h-full w-full object-cover"
                                                                            src="{{ $profile_photo_path ?? asset('images/avatar.png') }}"
                                                                            alt="Default avatar">
                                                                    @endif
                                                                </div>
                                                                <label
                                                                    class="absolute bottom-0 right-0 cursor-pointer rounded-full bg-red-500 p-1.5 text-white shadow-md transition-all hover:bg-red-600 group-hover:scale-110">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                    <input type="file" class="hidden" wire:model="photo" accept="image/*">
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Membership
                                                                    Type <span class="text-red-500">*</span></label>
                                                                <select type="text" wire:model="membership_type"
                                                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    <option value="">Select Type</option>
                                                                    <option value="Individual">Individual</option>
                                                                    <option value="Group">Group</option>
                                                                    <option value="Business">Business</option>
                                                                </select>
                                                                @error('membership_type')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Branch
                                                                    <span class="text-red-500">*</span></label>
                                                                <select type="text" wire:model.defer="branch"
                                                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    <option value="">Select Branch</option>
                                                                    @foreach (App\Models\BranchesModel::all() as $branch)
                                                                        <option value="{{ $branch->id }}">
                                                                            {{ $branch->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('branch')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Member Group</label>
                                                                <select type="text" wire:model.defer="member_group_id"
                                                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    <option value="">Select Member Group</option>
                                                                    @foreach (App\Models\MemberGroup::where('status', 'active')->orderBy('group_name')->get() as $group)
                                                                        <option value="{{ $group->id }}">
                                                                            {{ $group->group_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('member_group_id')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            @if ($membership_type === 'Individual')
                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">First
                                                                        Name <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="first_name"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('first_name')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Middle
                                                                        Name</label>
                                                                    <input type="text" wire:model.defer="middle_name"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('middle_name')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Last
                                                                        Name <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="last_name"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('last_name')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Gender
                                                                        <span class="text-red-500">*</span></label>
                                                                    <select type="text" wire:model.defer="gender"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                        <option value="">Select Gender</option>
                                                                        <option value="male">Male</option>
                                                                        <option value="female">Female</option>
                                                                    </select>
                                                                    @error('gender')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Date
                                                                        of Birth <span class="text-red-500">*</span></label>
                                                                    <input type="date" wire:model.defer="date_of_birth"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('date_of_birth')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">ID Type
                                                                        <span class="text-red-500">*</span></label>
                                                                    <select wire:model="id_type" 
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                        <option value="">Select ID Type</option>
                                                                        <option value="nida">NIDA</option>
                                                                        <option value="driving_license">Driving License</option>
                                                                    </select>
                                                                    @error('id_type')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                @if($id_type == 'nida')
                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">NIDA Number
                                                                        <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="nida_number" 
                                                                        placeholder="19990101-12345-12345-12"
                                                                        maxlength="24"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('nida_number')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>
                                                                @elseif($id_type == 'driving_license')
                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Driving License Number
                                                                        <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="driving_license_number" 
                                                                        placeholder="Enter driving license number"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('driving_license_number')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>
                                                                @endif

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Marital
                                                                        Status <span class="text-red-500">*</span></label>
                                                                    <select type="text" wire:model.defer="marital_status"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                        <option value="">Select Status</option>
                                                                        <option value="single">Single</option>
                                                                        <option value="married">Married</option>
                                                                        <option value="divorced">Divorced</option>
                                                                        <option value="widowed">Widowed</option>
                                                                    </select>
                                                                    @error('marital_status')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>
                                                            @endif

                                                            @if ($membership_type === 'Business' || $membership_type === 'Group')
                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Business/Group
                                                                        Name <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="business_name"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('business_name')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Incorporation
                                                                        Number <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="incorporation_number"
                                                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('incorporation_number')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Step 2: Contact Details -->
                                                @if ($currentStep === 2)
                                                    <div class="space-y-6">
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Phone
                                                                    Number <span class="text-red-500">*</span></label>
                                                                <input type="tel" wire:model.defer="phone_number" placeholder="0754244888"
                                                                    class="@error('phone_number') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('phone_number')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                                                                <input type="email" wire:model.defer="email"
                                                                    class="@error('email') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('email')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Address
                                                                    <span class="text-red-500">*</span></label>
                                                                <input type="text" wire:model.defer="address"
                                                                    class="@error('address') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('address')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Nationality
                                                                    <span class="text-red-500">*</span></label>
                                                                <input type="text" wire:model.defer="nationality"
                                                                    class="@error('nationality') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('nationality')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Citizenship
                                                                    <span class="text-red-500">*</span></label>
                                                                <input type="text" wire:model.defer="citizenship"
                                                                    class="@error('citizenship') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('citizenship')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            @if ($membership_type === 'Individual')
                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Next
                                                                        of Kin Name <span class="text-red-500">*</span></label>
                                                                    <input type="text" wire:model.defer="next_of_kin_name"
                                                                        class="@error('next_of_kin_name') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('next_of_kin_name')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-sm font-medium text-gray-700">Next
                                                                        of Kin Phone <span class="text-red-500">*</span></label>
                                                                    <input type="tel" wire:model.defer="next_of_kin_phone"
                                                                        class="@error('next_of_kin_phone') border-red-500 @enderror w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                    @error('next_of_kin_phone')
                                                                        <p class="mt-1 text-xs text-red-600">
                                                                            {{ $message }}</p>
                                                                    @enderror
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Step 3: Financial Information -->
                                                @if ($currentStep === 3)
                                                    <div class="space-y-6">
                                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Estimated
                                                                    Income
                                                                     <span class="text-red-500">*</span></label>
                                                                <input placeholder="TZS" type="number" wire:model.defer="income_available"
                                                                    class="block w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('income_available')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">Income
                                                                    Source <span class="text-red-500">*</span></label>
                                                                <input type="text" wire:model.defer="income_source"
                                                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('income_source')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>

                                                   

                                                            <div>
                                                                <label class="mb-1 block text-sm font-medium text-gray-700">NBC Bank
                                                                    Account Number<span class="text-red-500">(If exists)</span></label>
                                                                <input placeholder="Enter Account Number" type="number" wire:model.defer="nbc_account_number"
                                                                    class="block w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                                                @error('nbc_account_number')
                                                                    <p class="mt-1 text-xs text-red-600">
                                                                        {{ $message }}</p>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Step 4: Documents and Guarantor Information (Optional) -->
                                                @if ($currentStep === 4)
                                                    <div class="space-y-6">
                                                        <!-- Optional Step Notice -->
                                                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                                            <div class="flex">
                                                                <div class="flex-shrink-0">
                                                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <h3 class="text-sm font-medium text-red-800">Optional Step</h3>
                                                                    <p class="mt-1 text-sm text-red-700">
                                                                        Documents and guarantor information are optional. You can skip this step if not available now and add them later.
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Guarantor Information Section -->
                                                        <div class="mb-4">
                                                            <h3 class="mb-4 text-lg font-semibold">Guarantor
                                                                Information <span class="text-sm font-normal text-gray-500">(Optional)</span></h3>
                                                            <p class="mb-4 text-sm text-gray-600">You may provide
                                                                details of an existing member who will guarantee this
                                                                application.</p>

                                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                                                <div>
                                                                    <label for="guarantor_member_number"
                                                                        class="block text-sm font-medium text-gray-700">Guarantor
                                                                        Membership Number</label>
                                                                    <div class="relative mt-1">
                                                                        <input type="text" wire:model.defer="guarantor_member_number"
                                                                            wire:keyup.debounce.500ms="verifyMembership($event.target.value)"
                                                                            id="guarantor_member_number"
                                                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                                            placeholder="Enter guarantor's membership number">

                                                                        <!-- Verification Status -->
                                                                        @if ($guarantorVerificationMessage)
                                                                            <div class="mt-2">
                                                                                <div
                                                                                    class="{{ $guarantorVerificationStatus === 'success' ? 'text-green-600' : 'text-red-600' }} text-sm">
                                                                                    {{ $guarantorVerificationMessage }}
                                                                                </div>
                                                                                @if ($guarantorVerification)
                                                                                    <div class="mt-1 text-sm text-gray-600">
                                                                                        <p>Member Name:
                                                                                            {{ $guarantorVerification['name'] }}
                                                                                            -
                                                                                            {{ $guarantor_member_number }}
                                                                                        </p>
                                                                                        <p>Membership Type:
                                                                                            {{ $guarantorVerification['membership_type'] }}
                                                                                        </p>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @error('guarantor_member_number')
                                                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                                                    @enderror
                                                                </div>

                                                                <div>
                                                                    <label for="guarantor_relationship"
                                                                        class="block text-sm font-medium text-gray-700">Relationship
                                                                        with Guarantor</label>
                                                                    <input type="text" wire:model.defer="guarantor_relationship"
                                                                        id="guarantor_relationship"
                                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                                        placeholder="e.g., Family, Friend, Colleague"
                                                                        {{ !$guarantorVerification ? 'disabled' : '' }}>
                                                                    @error('guarantor_relationship')
                                                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="mt-4">
                                                                <p class="text-sm text-gray-600">
                                                                    Note: The guarantor must be an active member of the
                                                                    SACCO. The system will verify the membership number
                                                                    automatically.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- Documents Section -->
                                                        <div>
                                                            <label class="mb-1 block text-sm font-medium text-gray-700">Documents
                                                                <span class="text-sm font-normal text-gray-500">(Optional)</span></label>
                                                            <div class="space-y-4">
                                                                <!-- File Upload List -->
                                                                <div class="space-y-2">
                                                                    @foreach ($additionalDocuments as $index => $document)
                                                                        <div class="flex items-center space-x-2">
                                                                            <div class="flex-1">
                                                                                <input type="text"
                                                                                    wire:model.defer="additionalDocuments.{{ $index }}.description"
                                                                                    placeholder="Document description (e.g., Application Letter, ID Copy, etc.)"
                                                                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                                                                @error('additionalDocuments.' . $index . '.description')
                                                                                    <p class="mt-1 text-xs text-red-600">
                                                                                        {{ $message }}</p>
                                                                                @enderror
                                                                            </div>
                                                                            <div class="flex-1">
                                                                                <input type="file"
                                                                                    wire:model.defer="additionalDocuments.{{ $index }}.file"
                                                                                    class="w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-700 hover:file:bg-red-100">
                                                                                @error('additionalDocuments.' . $index . '.file')
                                                                                    <p class="mt-1 text-xs text-red-600">
                                                                                        {{ $message }}</p>
                                                                                @enderror
                                                                            </div>
                                                                            <button type="button" wire:click="removeDocument({{ $index }})"
                                                                                class="text-red-500 hover:text-red-700">
                                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    @endforeach
                                                                </div>

                                                                <!-- Add Document Button -->
                                                                <button type="button" wire:click="addDocument"
                                                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 4v16m8-8H4" />
                                                                    </svg>
                                                                    Add Document
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Step 5: Control Numbers -->
                                                @if ($currentStep === 5)
                                                    <div class="space-y-6">
                                                        <div class="rounded-lg bg-white shadow" id="controlNumbersReceipt">
                                                            <div class="p-6">
                                                                <div class="mb-4 flex items-center justify-between">
                                                                    <div class="flex-1 text-center">
                                                                        <h3 class="text-lg font-semibold">Control
                                                                            Numbers Receipt</h3>
                                                                        <p class="text-sm text-gray-600">Generated on:
                                                                            {{ now()->format('Y-m-d H:i:s') }}</p>
                                                                    </div>
                                                                    <button type="button" onclick="printReceipt()"
                                                                        class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                                                                        <svg class="h-6 w-6" fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>

                                                                <div class="mb-4">
                                                                    <p class="font-semibold">Member Information:</p>
                                                                    <p>Name:
                                                                        {{ $membership_type === 'Individual' ? trim("$first_name $middle_name $last_name") : $business_name }}
                                                                    </p>
                                                                    <p>Phone: {{ $phone_number }}</p>
                                                                    @if ($email)
                                                                        <p>Email: {{ $email }}</p>
                                                                    @endif
                                                                </div>

                                                                <div class="my-4 border-b border-t py-4">
                                                                    <table class="min-w-full">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="text-left">Service</th>
                                                                                <th class="text-left">Control Number
                                                                                </th>
                                                                                <th class="text-right">Amount</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach ($generatedControlNumbers as $control)
                                                                                <tr>
                                                                                    <td>{{ $control['service_code'] }}
                                                                                    </td>
                                                                                    <td class="font-mono">
                                                                                        {{ $control['control_number'] }}
                                                                                    </td>
                                                                                    <td class="text-right">
                                                                                        {{ number_format($control['amount'], 2) }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <div class="mt-4 text-sm text-gray-600">
                                                                    <p>Please keep these control numbers safe for future
                                                                        reference.</p>
                                                                    <p>You can use these numbers to make payments at any
                                                                        of our branches.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Navigation Buttons -->
                                                <div class="mt-8 flex justify-between border-t border-gray-100 pt-6">
                                                    @if ($currentStep > 1)
                                                        <button type="button" wire:click="previousStep"
                                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M15 19l-7-7 7-7" />
                                                            </svg>
                                                            Back to
                                                            {{ $currentStep === 2 ? 'Personal Info' : ($currentStep === 3 ? 'Contact Details' : ($currentStep === 4 ? 'Financial Info' : 'Documents')) }}
                                                        </button>
                                                    @else
                                                        <div></div>
                                                    @endif

                                                    @if ($currentStep === 4)
                                                        <!-- Special buttons for optional Step 4 -->
                                                        <div class="flex space-x-3">
                                                            <button type="button" wire:click="nextStep"
                                                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                                Skip to Review
                                                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M13 5l7 7-7 7M5 12h14" />
                                                                </svg>
                                                            </button>
                                                            <button type="button" wire:click="nextStep"
                                                                class="inline-flex items-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                                Continue to Review
                                                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M9 5l7 7-7 7" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    @elseif ($currentStep < 5)
                                                        <button type="button" wire:click="nextStep"
                                                            class="inline-flex items-center rounded-lg border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                            Continue to
                                                            {{ $currentStep === 1 ? 'Contact Details' : ($currentStep === 2 ? 'Financial Info' : ($currentStep === 3 ? 'Documents (Optional)' : 'Review')) }}
                                                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </button>
                                                    @else
                                                        @if($permissions['canCreate'] ?? false)
                                                        <button type="button" wire:click="save"
                                                            class="inline-flex items-center rounded-lg border border-transparent bg-green-600 px-6 py-3 text-base font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Complete Registration
                                                        </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                   
                                </div>
                                @endif

                                @if ($activeTab === 'special-edits')
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <livewire:clients.special-edits />
                                        </div>
                                    </div>
                                @endif

                                @if ($activeTab === 'bulk-import')
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <livewire:clients.bulk-import />
                                        </div>
                                    </div>
                                @endif

                                @if ($activeTab === 'member-groups')
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <livewire:clients.member-groups />
                                        </div>
                                    </div>
                                @endif

                                @if ($activeTab === 'member-exit')
                                    <div class="space-y-6">
                                        <!-- Flash Messages -->
                                        @if (session()->has('success'))
                                        <div class="rounded-lg border-l-4 border-green-500 bg-green-50 p-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                                            <div class="flex items-start">
                                                <svg class="mr-3 h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-green-800">Success</h4>
                                                    <p class="mt-1 text-sm text-green-700">{{ session('success') }}</p>
                                                </div>
                                                <button @click="show = false" class="ml-4 text-green-600 hover:text-green-800">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @if (session()->has('error'))
                                        <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4" x-data="{ show: true }" x-show="show">
                                            <div class="flex items-start">
                                                <svg class="mr-3 h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-red-800">Error</h4>
                                                    <p class="mt-1 text-sm text-red-700">{{ session('error') }}</p>
                                                </div>
                                                <button @click="show = false" class="ml-4 text-red-600 hover:text-red-800">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @if (session()->has('warning'))
                                        <div class="rounded-lg border-l-4 border-yellow-500 bg-yellow-50 p-4" x-data="{ show: true }" x-show="show">
                                            <div class="flex items-start">
                                                <svg class="mr-3 h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-yellow-800">Warning</h4>
                                                    <p class="mt-1 text-sm text-yellow-700">{{ session('warning') }}</p>
                                                </div>
                                                <button @click="show = false" class="ml-4 text-yellow-600 hover:text-yellow-800">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @if (session()->has('info'))
                                        <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                                            <div class="flex items-start">
                                                <svg class="mr-3 h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-red-800">Information</h4>
                                                    <p class="mt-1 text-sm text-red-700">{{ session('info') }}</p>
                                                </div>
                                                <button @click="show = false" class="ml-4 text-red-600 hover:text-red-800">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Client Exit Search Section -->
                                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Search Member for Exit Processing</h3>
                                            
                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700">Member Number</label>
                                                    <input type="text" 
                                                        wire:model="exitMemberNumber"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                                                        placeholder="Enter member number">
                                                </div>
                                                
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700">Phone Number</label>
                                                    <input type="text" 
                                                        wire:model="exitPhoneNumber"
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                                                        placeholder="Enter phone number">
                                                </div>
                                                
                                                <div class="flex items-end">
                                                    <button wire:click="searchMemberForExit"
                                                        class="rounded-lg bg-red-600 px-6 py-2 text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                        Search Member
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Member Details and Exit Processing -->
                                        @if($exitMemberDetails)
                                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Member Information</h3>
                                            
                                            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                                                <div>
                                                    <p class="text-sm text-gray-600">Member Name</p>
                                                    <p class="font-medium">{{ $exitMemberDetails->first_name }} {{ $exitMemberDetails->last_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Member Number</p>
                                                    <p class="font-medium">{{ $exitMemberDetails->client_number }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Phone Number</p>
                                                    <p class="font-medium">{{ $exitMemberDetails->phone_number }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-600">Current Status</p>
                                                    <p class="font-medium">{{ $exitMemberDetails->status }}</p>
                                                </div>
                                            </div>

                                            <!-- Comprehensive Exit Calculation -->
                                            <div class="mb-6">
                                                <h4 class="mb-3 font-semibold text-gray-900">Exit Calculation Summary</h4>
                                                
                                                <!-- Final Settlement Amount -->
                                                <div class="mb-4">
                                                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                                                        <div class="text-center">
                                                            <div class="text-sm text-purple-700 mb-2">Final Settlement Amount</div>
                                                            <div class="text-3xl font-bold text-purple-900">
                                                                TZS {{ number_format($exitMemberDetails->exit_final_settlement ?? 0, 2) }}
                                                            </div>
                                                            <div class="text-xs text-purple-600 mt-1">
                                                                @if(($exitMemberDetails->exit_final_settlement ?? 0) > 0)
                                                                    Member will receive this amount
                                                                @elseif(($exitMemberDetails->exit_final_settlement ?? 0) < 0)
                                                                    Member owes this amount
                                                                @else
                                                                    No settlement amount
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Credits and Debits Breakdown -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <!-- Credits Section -->
                                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                                        <h5 class="text-lg font-semibold text-green-800 mb-3">Credits (+)</h5>
                                                        
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-green-700">Shares Balance</span>
                                                                <span class="text-sm font-semibold text-green-900">TZS {{ number_format($exitMemberDetails->exit_shares_balance ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-green-700">Savings Balance</span>
                                                                <span class="text-sm font-semibold text-green-900">TZS {{ number_format($exitMemberDetails->exit_savings_balance ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-green-700">Deposits Balance</span>
                                                                <span class="text-sm font-semibold text-green-900">TZS {{ number_format($exitMemberDetails->exit_deposits_balance ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-green-700">Dividends</span>
                                                                <span class="text-sm font-semibold text-green-900">TZS {{ number_format($exitMemberDetails->exit_dividends ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-green-700">Interest on Savings</span>
                                                                <span class="text-sm font-semibold text-green-900">TZS {{ number_format($exitMemberDetails->exit_interest_on_savings ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="border-t border-green-300 pt-2 mt-2">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-sm font-semibold text-green-800">Total Credits</span>
                                                                    <span class="text-lg font-bold text-green-900">TZS {{ number_format($exitMemberDetails->exit_total_credits ?? 0, 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Debits Section -->
                                                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                                        <h5 class="text-lg font-semibold text-red-800 mb-3">Debits (-)</h5>
                                                        
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-red-700">Loan Balance</span>
                                                                <span class="text-sm font-semibold text-red-900">TZS {{ number_format($exitMemberDetails->exit_loan_balance ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm text-red-700">Unpaid Bills</span>
                                                                <span class="text-sm font-semibold text-red-900">TZS {{ number_format($exitMemberDetails->exit_unpaid_bills ?? 0, 2) }}</span>
                                                            </div>
                                                            <div class="border-t border-red-300 pt-2 mt-2">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-sm font-semibold text-red-800">Total Debits</span>
                                                                    <span class="text-lg font-bold text-red-900">TZS {{ number_format($exitMemberDetails->exit_total_debits ?? 0, 2) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Summary Statistics -->
                                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                    <h5 class="text-lg font-semibold text-gray-800 mb-3">Summary</h5>
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                                        <div>
                                                            <div class="text-2xl font-bold text-red-600">{{ $exitMemberDetails->accounts_count ?? 0 }}</div>
                                                            <div class="text-xs text-gray-600">Active Accounts</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-2xl font-bold text-orange-600">{{ $exitMemberDetails->loans_count ?? 0 }}</div>
                                                            <div class="text-xs text-gray-600">Active Loans</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-2xl font-bold text-red-600">{{ $exitMemberDetails->unpaid_bills_count ?? 0 }}</div>
                                                            <div class="text-xs text-gray-600">Unpaid Bills</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-2xl font-bold text-purple-600">{{ number_format($exitMemberDetails->exit_final_settlement ?? 0, 0) }}</div>
                                                            <div class="text-xs text-gray-600">Settlement</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Exit Reason -->
                                            <div class="mb-6">
                                                <label class="mb-2 block text-sm font-medium text-gray-700">Exit Reason</label>
                                                <select wire:model="exitReason"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                                                    <option value="">Select reason for exit</option>
                                                    <option value="voluntary">Voluntary Exit</option>
                                                    <option value="death">Death</option>
                                                    <option value="relocation">Relocation</option>
                                                    <option value="expulsion">Expulsion</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>

                                            <!-- Exit Notes -->
                                            <div class="mb-6">
                                                <label class="mb-2 block text-sm font-medium text-gray-700">Exit Notes</label>
                                                <textarea wire:model="exitNotes"
                                                    rows="3"
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                                                    placeholder="Enter any additional notes about the member exit"></textarea>
                                            </div>

                                            <!-- Exit Eligibility Warning -->
                                            @if($exitMemberDetails->exit_final_settlement != 0)
                                            <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                                                <div class="flex items-start">
                                                    <svg class="mr-3 h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-red-800">Cannot Process Exit</h4>
                                                        <p class="mt-1 text-sm text-red-700">
                                                            Settlement amount must be <strong>TZS 0.00</strong> to process member exit. 
                                                            Current settlement: <strong>TZS {{ number_format($exitMemberDetails->exit_final_settlement, 2) }}</strong>
                                                        </p>
                                                        <p class="mt-2 text-xs text-red-600">
                                                            Please settle all outstanding amounts before proceeding with the exit.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- Action Buttons -->
                                            @if($permissions['canDelete'] ?? false)
                                            <div class="flex justify-end space-x-3">
                                                <button wire:click="cancelMemberExit"
                                                    class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                                    Cancel
                                                </button>
                                                
                                                @if($exitMemberDetails->exit_final_settlement != 0)
                                                <!-- Settlement amount is not zero -->
                                                <button disabled
                                                    class="cursor-not-allowed rounded-lg bg-gray-400 px-6 py-2 text-white opacity-50">
                                                    Cannot Process Exit (Settlement Must Be Zero)
                                                </button>
                                                @elseif($exitMemberDetails->exit_loan_balance > 0 || $exitMemberDetails->exit_unpaid_bills > 0)
                                                <!-- Outstanding obligations exist -->
                                                <button disabled
                                                    class="cursor-not-allowed rounded-lg bg-gray-400 px-6 py-2 text-white opacity-50">
                                                    Cannot Process Exit (Outstanding Obligations)
                                                </button>
                                                @else
                                                <!-- All conditions met - can process exit -->
                                                <button wire:click="confirmExitRequest"
                                                    onclick="console.log('🔘 Submit Exit for Approval button clicked!', 'Member ID:', '{{ $exitMemberDetails->id ?? 'N/A' }}', 'Exit Reason:', '{{ $exitReason ?? 'N/A' }}')"
                                                    class="rounded-lg bg-red-600 px-6 py-2 text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                    Submit Exit for Approval
                                                </button>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        <!-- Exit Confirmation Modal -->
                                        @if($showExitConfirmModal)
                                        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                                <!-- Background overlay -->
                                                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" wire:click="closeExitConfirmModal"></div>

                                                <!-- Center modal -->
                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                                <!-- Modal panel -->
                                                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                                                Confirm Client Exit Request
                                                            </h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500">
                                                                    You are about to submit a member exit request for approval. This request will be reviewed by authorized personnel before processing.
                                                                </p>
                                                                
                                                                @if($exitMemberDetails)
                                                                <div class="mt-4 rounded-lg bg-gray-50 p-4 border border-gray-200">
                                                                    <dl class="space-y-2 text-sm">
                                                                        <div class="flex justify-between">
                                                                            <dt class="font-medium text-gray-700">Member Name:</dt>
                                                                            <dd class="text-gray-900">{{ $exitMemberDetails->first_name }} {{ $exitMemberDetails->last_name }}</dd>
                                                                        </div>
                                                                        <div class="flex justify-between">
                                                                            <dt class="font-medium text-gray-700">Member Number:</dt>
                                                                            <dd class="text-gray-900">{{ $exitMemberDetails->client_number }}</dd>
                                                                        </div>
                                                                        <div class="flex justify-between">
                                                                            <dt class="font-medium text-gray-700">Exit Reason:</dt>
                                                                            <dd class="text-gray-900 capitalize">{{ str_replace('_', ' ', $exitReason) }}</dd>
                                                                        </div>
                                                                        <div class="flex justify-between">
                                                                            <dt class="font-medium text-gray-700">Settlement Amount:</dt>
                                                                            <dd class="text-gray-900 font-semibold">TZS {{ number_format($exitMemberDetails->exit_final_settlement ?? 0, 2) }}</dd>
                                                                        </div>
                                                                    </dl>
                                                                </div>
                                                                @endif

                                                                <p class="mt-4 text-sm text-gray-600">
                                                                    <strong>Note:</strong> This action will send the exit request for approval. The member's status will not change until the request is approved.
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                                        <button type="button" wire:click="submitExitForApproval"
                                                            onclick="console.log('✅ Submit for Approval (MODAL) button clicked!', 'Member:', '{{ $exitMemberDetails->client_number ?? 'N/A' }}', 'Timestamp:', new Date().toISOString())"
                                                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                                            Submit for Approval
                                                        </button>
                                                        <button type="button" wire:click="closeExitConfirmModal"
                                                            onclick="console.log('❌ Cancel (MODAL) button clicked!')"
                                                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Exit History Table -->
                                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Recent Client Exits</h3>
                                            
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Member Name</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Member Number</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Exit Date</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Exit Reason</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Final Settlement</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 bg-white">
                                                        @forelse($exitHistory ?? [] as $exit)
                                                        <tr>
                                                            <td class="whitespace-nowrap px-6 py-4">{{ is_array($exit) ? $exit['member_name'] : $exit->member_name }}</td>
                                                            <td class="whitespace-nowrap px-6 py-4">{{ is_array($exit) ? $exit['client_number'] : $exit->client_number }}</td>
                                                            <td class="whitespace-nowrap px-6 py-4">{{ is_array($exit) ? $exit['exit_date'] : $exit->exit_date }}</td>
                                                            <td class="whitespace-nowrap px-6 py-4">{{ is_array($exit) ? $exit['exit_reason'] : $exit->exit_reason }}</td>
                                                            <td class="whitespace-nowrap px-6 py-4">TZS {{ number_format(is_array($exit) ? $exit['settlement_amount'] : $exit->settlement_amount, 2) }}</td>
                                                            <td class="whitespace-nowrap px-6 py-4">
                                                                <button wire:click="viewExitDetails({{ is_array($exit) ? $exit['id'] : $exit->id }})"
                                                                    class="text-red-600 hover:text-red-900">View</button>
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent client exits</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>


            {{-- Settings Content --}}
            {{--@if ($activeTab === 'settings')
                <div class="space-y-6">
                    <div class="rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h2 class="mb-4 text-lg font-medium text-gray-900">Settings</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Member Categories</h3>
                                        <p class="text-sm text-gray-500">Manage member categories and their
                                            requirements</p>
                                    </div>
                                    <button
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Manage
                                        Categories</button>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">Notification Settings</h3>
                                            <p class="text-sm text-gray-500">Configure email and SMS notifications</p>
                                        </div>
                                        <button
                                            class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Configure</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif--}}
        </div>
        </div>
    </div>

        <!-- Custom scrollbar styles -->
        <style nonce="{{ csp_nonce() }}">
        /* Custom red vertical scrollbar for branches main container */
        .clients-scroll::-webkit-scrollbar {
            width: 2px;
        }
        .clients-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .clients-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .clients-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }

        /* Custom red vertical scrollbar for branches content area */
        .clients-content-scroll::-webkit-scrollbar {
            width: 2px;
        }
        .clients-content-scroll::-webkit-scrollbar-thumb {
            background: #e3342f;
            border-radius: 2px;
        }
        .clients-content-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        /* For Firefox */
        .clients-content-scroll {
            scrollbar-width: thin;
            scrollbar-color: #e3342f transparent;
        }
    </style>
</div>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('livewire:load', function() {
            Livewire.on('scrollToTop', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        function printReceipt(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const receiptContent = document.getElementById('controlNumbersReceipt').innerHTML;

            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
            <html>
                <head>
    <meta charset="UTF-8">
                    <title>Control Numbers Receipt</title>
                    <style nonce="{{ csp_nonce() }}">
                        body { font-family: Arial, sans-serif; }
                        .bg-white { background-color: white; }
                        .rounded-lg { border-radius: 0.5rem; }
                        .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
                        .p-6 { padding: 1.5rem; }
                        .mb-4 { margin-bottom: 1rem; }
                        .text-center { text-align: center; }
                        .text-lg { font-size: 1.125rem; }
                        .font-semibold { font-weight: 600; }
                        .text-sm { font-size: 0.875rem; }
                        .text-gray-600 { color: #4B5563; }
                        .border-t { border-top-width: 1px; }
                        .border-b { border-bottom-width: 1px; }
                        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                        .my-4 { margin-top: 1rem; margin-bottom: 1rem; }
                        .min-w-full { min-width: 100%; }
                        .text-left { text-align: left; }
                        .text-right { text-align: right; }
                        .font-mono { font-family: monospace; }
                        .mt-4 { margin-top: 1rem; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 0.5rem; }
                        th { border-bottom: 2px solid #E5E7EB; }
                        td { border-bottom: 1px solid #E5E7EB; }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
            </html>
        `);

            printWindow.document.close();
            printWindow.focus();

            // Wait for images to load before printing
            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
@endpush

<!-- Flash Messages -->
@if (session()->has('success'))
    <div class="animate-fade-in-down fixed right-4 top-4 z-50">
        <div class="rounded border-l-4 border-green-500 bg-green-100 p-4 text-green-700 shadow-lg">
            <div class="flex items-center">
                <svg class="mr-2 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session()->has('error'))
    <div class="animate-fade-in-down fixed right-4 top-4 z-50">
        <div class="rounded border-l-4 border-red-500 bg-red-100 p-4 text-red-700 shadow-lg">
            <div class="flex items-center">
                <svg class="mr-2 h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<style nonce="{{ csp_nonce() }}">
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out;
    }

    @keyframes fadeInDown {
        0% {
            opacity: 0;
            transform: translateY(-20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
