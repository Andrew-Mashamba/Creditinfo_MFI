<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">User Management System</h1>
                        <p class="text-gray-600 mt-1">Hierarchical organization structure: Departments → Roles → Sub-Roles → Permissions → Users</p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalUsers ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Active Roles</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalRoles ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Departments</p>
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalDepartments ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            <!-- Enhanced Sidebar -->
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
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
                            placeholder="Search users, roles, or departments..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white"
                            aria-label="Search users"
                        />
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Navigation</h3>
                    
                    @php
                        $user_sections = [
                            // Step 1: Organization Structure
                            [
                                'id' => 'departments', 
                                'label' => '1. Departments', 
                                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                                'description' => 'Setup organizational departments',
                                'count' => $totalDepartments ?? 0,
                                'color' => 'red',
                                'step' => 1
                            ],
                            // Step 2: Roles & Sub-Roles
                            [
                                'id' => 'roles', 
                                'label' => '2. Roles & Sub-Roles', 
                                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                                'description' => 'Define roles within departments',
                                'count' => $totalRoles ?? 0,
                                'color' => 'green',
                                'step' => 2
                            ],
                            // Step 3: Permissions
                            [
                                'id' => 'permissions', 
                                'label' => '3. Permissions', 
                                'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
                                'description' => 'Configure role permissions',
                                'count' => null,
                                'color' => 'purple',
                                'step' => 3
                            ],
                            // Step 4: Users
                            [
                                'id' => 'users', 
                                'label' => '4. Users', 
                                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                                'description' => 'Assign users to roles',
                                'count' => $totalUsers ?? 0,
                                'color' => 'indigo',
                                'step' => 4
                            ],
                            // Additional Tools
                            [
                                'id' => 'organizational-structure', 
                                'label' => 'View Hierarchy', 
                                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                                'description' => 'Visualize organization',
                                'count' => null,
                                'color' => 'yellow',
                                'step' => null
                            ],
                            [
                                'id' => 'audit-logs', 
                                'label' => 'Activity Logs', 
                                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                                'description' => 'Track user activities',
                                'count' => null,
                                'color' => 'gray',
                                'step' => null
                            ],
                        ];
                    @endphp

                    <!-- Setup Steps -->
                    <div class="mb-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Setup Workflow</h3>
                        @php
                            $setupSteps = array_filter($user_sections, fn($s) => $s['step'] !== null);
                            $additionalTools = array_filter($user_sections, fn($s) => $s['step'] === null);
                        @endphp
                        
                        <!-- Progress Steps -->
                        <nav class="space-y-2">
                            @foreach ($setupSteps as $section)
                                @php
                                    $isActive = $this->section === $section['id'];
                                    $colorClasses = [
                                        'indigo' => $isActive ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
                                        'green' => $isActive ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100',
                                        'red' => $isActive ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100',
                                        'yellow' => $isActive ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100',
                                        'gray' => $isActive ? 'bg-gray-700 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100',
                                        'purple' => $isActive ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100',
                                        'pink' => $isActive ? 'bg-pink-600 text-white' : 'bg-pink-50 text-pink-700 hover:bg-pink-100',
                                    ];
                                    $iconColorClasses = [
                                        'indigo' => $isActive ? 'text-white' : 'text-indigo-600',
                                        'green' => $isActive ? 'text-white' : 'text-green-600',
                                        'red' => $isActive ? 'text-white' : 'text-red-600',
                                        'yellow' => $isActive ? 'text-white' : 'text-yellow-600',
                                        'gray' => $isActive ? 'text-white' : 'text-gray-600',
                                        'purple' => $isActive ? 'text-white' : 'text-purple-600',
                                        'pink' => $isActive ? 'text-white' : 'text-pink-600',
                                    ];
                                @endphp

                                @if($permissions['canView'] ?? false)
                                <button
                                    wire:click="setSection('{{ $section['id'] }}')"
                                    class="relative w-full group transition-all duration-200"
                                    aria-label="{{ $section['label'] }}"
                                >
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200 shadow-sm
                                        @if ($isActive) 
                                            {{ $colorClasses[$section['color']] }} shadow-lg scale-105
                                        @else 
                                            {{ $colorClasses[$section['color']] }} hover:shadow-md
                                        @endif">
                                        
                                        <!-- Step Number -->
                                        @if($section['step'])
                                            <div class="mr-3 flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                                                    @if($isActive) 
                                                        bg-white bg-opacity-20 text-white
                                                    @else 
                                                        bg-white {{ $iconColorClasses[$section['color']] }}
                                                    @endif">
                                                    {{ $section['step'] }}
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Icon -->
                                        <div class="mr-3">
                                            <svg class="w-5 h-5 {{ $iconColorClasses[$section['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                            </svg>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ str_replace($section['step'] . '. ', '', $section['label']) }}</div>
                                            <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                        </div>

                                        <!-- Count Badge -->
                                        @if ($section['count'] !== null)
                                            <div class="ml-2">
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full min-w-[20px] h-5
                                                    @if ($isActive)
                                                        bg-white bg-opacity-20 text-white
                                                    @else
                                                        bg-white bg-opacity-80 {{ $iconColorClasses[$section['color']] }}
                                                    @endif">
                                                    {{ $section['count'] > 99 ? '99+' : $section['count'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                                @else
                                <div class="relative w-full group transition-all duration-200 opacity-50 cursor-not-allowed" title="No permission to access this section">
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200 shadow-sm
                                        @if ($isActive) 
                                            {{ $colorClasses[$section['color']] }} shadow-lg scale-105
                                        @else 
                                            {{ $colorClasses[$section['color']] }} hover:shadow-md
                                        @endif">
                                        
                                        <!-- Step Number -->
                                        @if($section['step'])
                                            <div class="mr-3 flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                                                    @if($isActive) 
                                                        bg-white bg-opacity-20 text-white
                                                    @else 
                                                        bg-white {{ $iconColorClasses[$section['color']] }}
                                                    @endif">
                                                    {{ $section['step'] }}
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Icon -->
                                        <div class="mr-3">
                                            <svg class="w-5 h-5 {{ $iconColorClasses[$section['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                            </svg>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ str_replace($section['step'] . '. ', '', $section['label']) }}</div>
                                            <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                        </div>

                                        <!-- Count Badge -->
                                        @if ($section['count'] !== null)
                                            <div class="ml-2">
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none rounded-full min-w-[20px] h-5
                                                    @if ($isActive)
                                                        bg-white bg-opacity-20 text-white
                                                    @else
                                                        bg-white bg-opacity-80 {{ $iconColorClasses[$section['color']] }}
                                                    @endif">
                                                    {{ $section['count'] > 99 ? '99+' : $section['count'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                    
                    <!-- Additional Tools -->
                    <div class="border-t pt-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Tools</h3>
                        <nav class="space-y-2">
                            @foreach ($additionalTools as $section)
                                @php
                                    $isActive = $this->section === $section['id'];
                                    $colorClasses = [
                                        'indigo' => $isActive ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
                                        'green' => $isActive ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100',
                                        'red' => $isActive ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100',
                                        'yellow' => $isActive ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100',
                                        'gray' => $isActive ? 'bg-gray-700 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100',
                                        'purple' => $isActive ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100',
                                        'pink' => $isActive ? 'bg-pink-600 text-white' : 'bg-pink-50 text-pink-700 hover:bg-pink-100',
                                    ];
                                    $iconColorClasses = [
                                        'indigo' => $isActive ? 'text-white' : 'text-indigo-600',
                                        'green' => $isActive ? 'text-white' : 'text-green-600',
                                        'red' => $isActive ? 'text-white' : 'text-red-600',
                                        'yellow' => $isActive ? 'text-white' : 'text-yellow-600',
                                        'gray' => $isActive ? 'text-white' : 'text-gray-600',
                                        'purple' => $isActive ? 'text-white' : 'text-purple-600',
                                        'pink' => $isActive ? 'text-white' : 'text-pink-600',
                                    ];
                                @endphp

                                @if($permissions['canView'] ?? false)
                                <button
                                    wire:click="setSection('{{ $section['id'] }}')"
                                    class="relative w-full group transition-all duration-200"
                                    aria-label="{{ $section['label'] }}"
                                >
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200 shadow-sm
                                        @if ($isActive) 
                                            {{ $colorClasses[$section['color']] }} shadow-lg scale-105
                                        @else 
                                            {{ $colorClasses[$section['color']] }} hover:shadow-md
                                        @endif">
                                        
                                        <!-- Icon -->
                                        <div class="mr-3">
                                            <svg class="w-5 h-5 {{ $iconColorClasses[$section['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                            </svg>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                            <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                        </div>
                                    </div>
                                </button>
                                @else
                                <div class="relative w-full group transition-all duration-200 opacity-50 cursor-not-allowed" title="No permission to access this section">
                                    <div class="flex items-center p-3 rounded-xl transition-all duration-200 shadow-sm
                                        @if ($isActive) 
                                            {{ $colorClasses[$section['color']] }} shadow-lg scale-105
                                        @else 
                                            {{ $colorClasses[$section['color']] }} hover:shadow-md
                                        @endif">
                                        
                                        <!-- Icon -->
                                        <div class="mr-3">
                                            <svg class="w-5 h-5 {{ $iconColorClasses[$section['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"></path>
                                            </svg>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 text-left">
                                            <div class="font-medium text-sm">{{ $section['label'] }}</div>
                                            <div class="text-xs opacity-75">{{ $section['description'] }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button wire:click="$set('section', 'users')" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add New User
                        </button>
                        <button wire:click="$set('section', 'roles')" class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Reports
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                @if($permissions['canView'] ?? false)
                    @if($this->section === 'users')
                        @if($permissions['canManageUsers'] ?? $permissions['canView'] ?? false)
                            <livewire:users.simple-users />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to manage users.</div>
                        @endif
                    @elseif($this->section === 'roles')
                        @if($permissions['canManageRoles'] ?? $permissions['canView'] ?? false)
                            <livewire:users.hierarchical-roles />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to manage roles.</div>
                        @endif
                    @elseif($this->section === 'departments')
                        @if($permissions['canManageDepartments'] ?? $permissions['canView'] ?? false)
                            <livewire:users.hierarchical-departments />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to manage departments.</div>
                        @endif
                    @elseif($this->section === 'permissions')
                        @if($permissions['canManagePermissions'] ?? $permissions['canView'] ?? false)
                            <livewire:users.hierarchical-permissions />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to manage permissions.</div>
                        @endif
                    @elseif($this->section === 'organizational-structure')
                        @if($permissions['canView'] ?? false)
                            <livewire:users.organizational-structure />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to view organizational structure.</div>
                        @endif
                {{--@elseif($this->section === 'settings')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <livewire:users.settings />
                        <livewire:users.user-settings />
                        <livewire:users.institution-settings />
                        <livewire:users.password-policy />
                    </div>
                
                @elseif($this->section === 'audit-logs')
                    <livewire:users.audit-logs />--}}
                    @elseif($this->section === 'user-groups')
                        @if($permissions['canView'] ?? false)
                            <livewire:users.user-groups />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to view user groups.</div>
                        @endif
                    @elseif($this->section === 'loan-committee')
                        @if($permissions['canView'] ?? false)
                            <livewire:users.loan-committee />
                        @else
                            <div class="text-center py-8 text-gray-500">You don't have permission to view loan committee.</div>
                        @endif
                    @else
                    <!-- Dashboard Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Users Card -->
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 border border-indigo-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-indigo-900">Users Overview</h3>
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-indigo-900 mb-2">{{ number_format($totalUsers ?? 0) }}</div>
                            <p class="text-sm text-indigo-700">Total registered users</p>
                        </div>
                        
                        <!-- Roles Card -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-green-900">Roles & Permissions</h3>
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-green-900 mb-2">{{ number_format($totalRoles ?? 0) }}</div>
                            <p class="text-sm text-green-700">Active roles defined</p>
                        </div>
                        
                        <!-- Departments Card -->
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 border border-red-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-red-900">Departments</h3>
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-red-900 mb-2">{{ number_format($totalDepartments ?? 0) }}</div>
                            <p class="text-sm text-red-700">Organizational departments</p>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                <div class="p-2 bg-indigo-100 rounded-lg mr-4">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">User Management Dashboard</p>
                                    <p class="text-sm text-gray-500">Welcome to the user management system</p>
                                </div>
                                <span class="text-xs text-gray-400">Just now</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 bg-indigo-100 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Add New User</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Create a new user account with appropriate roles and permissions.</p>
                            <button wire:click="$set('section', 'users')" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                Get Started
                            </button>
                        </div>

                        <div class="bg-white rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 bg-green-100 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Manage Roles</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Define and manage user roles with specific permissions and access levels.</p>
                            <button wire:click="$set('section', 'roles')" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                                Manage Roles
                            </button>
                        </div>

                        <div class="bg-white rounded-xl p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 bg-red-100 rounded-lg mr-4">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Departments</h4>
                            </div>
                            <p class="text-gray-600 mb-4">Organize users into departments for better management and reporting.</p>
                            <button wire:click="$set('section', 'departments')" class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">
                                View Departments
                            </button>
                        </div>
                    </div>
                    @endif
                @else
                    <div class="bg-white rounded-xl p-8 shadow-md">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Access Restricted</h3>
                            <p class="text-gray-600">You don't have permission to access the user management system.</p>
                            <p class="text-sm text-gray-500 mt-2">Please contact your administrator if you need access.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Google Material Icons CDN -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> 