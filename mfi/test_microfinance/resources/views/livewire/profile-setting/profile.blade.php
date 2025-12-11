<div class="min-h-screen bg-gradient-to-br from-slate-50 to-red-50">
    <div class="p-6">
        {{-- Header Section --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-xl shadow-lg">
                        {{-- Institution Icon --}}
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Institution Settings Manager</h1>
                        <p class="text-gray-600 mt-1">Configure and manage all institutional settings and configurations</p>
                    </div>
                </div>
                {{-- Quick Stats --}}
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Configured Settings</p>
                                <p class="text-lg font-semibold text-gray-900">22</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Pending Actions</p>
                                <p class="text-lg font-semibold text-gray-900">3</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total Sections</p>
                                <p class="text-lg font-semibold text-gray-900">15</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!($permissions['canView'] ?? false) && !($permissions['canManage'] ?? false))
        {{-- No Access Message for users with no permissions --}}
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
            <p class="text-gray-500">You don't have permission to access the profile settings module.</p>
        </div>
        @else
        <div class="flex gap-6">
            {{-- Sidebar --}}
            <div class="w-80 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                {{-- Search --}}
                <div class="p-6 border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="Search settings sections..." class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 bg-gray-50 hover:bg-white focus:bg-white" />
                    </div>
                </div>
                {{-- Navigation --}}
                <div class="p-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-2">Settings Sections</h3>
                    <nav class="space-y-2">
                        @php
                        $menuItems = [
                            ['id' => 1, 'label' => 'Settings', 'permission' => 'view'],
                            ['id' => 2, 'label' => 'Leadership', 'permission' => 'view'],
                            ['id' => 3, 'label' => 'End of Day', 'permission' => 'view'],
                            ['id' => 5, 'label' => 'End of Year', 'permission' => 'view'],
                            ['id' => 6, 'label' => 'Statistics', 'permission' => 'view'],
                            ['id' => 7, 'label' => 'Key Financial Ratios', 'permission' => 'view'],
                            ['id' => 8, 'label' => 'Financial Position', 'permission' => 'view'],
                            ['id' => 9, 'label' => 'Capital Summary', 'permission' => 'view'],
                            ['id' => 10, 'label' => 'Shares Ownership', 'permission' => 'view'],
                            ['id' => 11, 'label' => 'Loan Provision Setting', 'permission' => 'view'],
                            ['id' => 12, 'label' => 'Accounts Setup', 'permission' => 'manage'],
                            ['id' => 13, 'label' => 'Bills Manager', 'permission' => 'manage'],
                            ['id' => 14, 'label' => 'Institution Accounts', 'permission' => 'manage'],
                            ['id' => 15, 'label' => 'Approvals Manager', 'permission' => 'manage'],
                            ['id' => 16, 'label' => 'Data Migration', 'permission' => 'manage'],
                            ['id' => 17, 'label' => 'Domain Management', 'permission' => 'manage'],
                            ['id' => 18, 'label' => 'Audit Trail', 'permission' => 'view'],
                            ['id' => 19, 'label' => 'Scheduled Tasks', 'permission' => 'manage'],
                        ];
                        @endphp
                        @foreach ($menuItems as $menuItem)
                            @php
                                $permissionKey = 'can' . ucfirst($menuItem['permission']);
                            @endphp
                            @if($permissions[$permissionKey] ?? false)
                            <button wire:click="menu_sub_button({{ $menuItem['id'] }})" class="relative w-full group transition-all duration-200">
                                <div class="flex items-center p-3 rounded-xl transition-all duration-200 @if ($this->teller_tab == $menuItem['id']) bg-red-600 text-white shadow-lg @else bg-gray-50 hover:bg-gray-100 text-gray-700 hover:text-gray-900 @endif">
                                    <div class="mr-3">
                                        {{-- You can add icons here for each menu item if desired --}}
                                    </div>
                                    <div class="flex-1 text-left">
                                        <div class="font-medium text-sm">{{ $menuItem['label'] }}</div>
                                    </div>
                                </div>
                            </button>
                            @endif
                        @endforeach
                    </nav>
                </div>
                {{-- Quick Actions --}}
                @if($permissions['canManage'] ?? false)
                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Setting
                        </button>
                        <button class="w-full flex items-center p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Export Settings
                        </button>
                    </div>
                </div>
                @endif
            </div>
            {{-- Main Content --}}
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    {{-- Content Header --}}
                    <div class="px-8 py-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    @php
                                        $sectionTitles = [
                                            1 => 'Organization Settings',
                                            2 => 'Leadership',
                                            3 => 'End of Day',
                                            5 => 'End of Year',
                                            6 => 'Statistics',
                                            7 => 'Key Financial Ratios',
                                            8 => 'Financial Position',
                                            9 => 'Capital Summary',
                                            10 => 'Shares Ownership',
                                            11 => 'Loan Provision Setting',
                                            12 => 'Accounts Setup',
                                            13 => 'Bills Manager',
                                            14 => 'Institution Accounts',
                                            15 => 'Approvals Manager',
                                            16 => 'Data Migration',
                                            17 => 'Domain Management',
                                            18 => 'Audit Trail',
                                            19 => 'Scheduled Tasks',
                                        ];
                                    @endphp
                                    {{ $sectionTitles[$this->teller_tab] ?? 'Organization Settings' }}
                                </h2>
                                <p class="text-gray-600 mt-1">
                                    {{-- You can add dynamic section descriptions here if desired --}}
                                </p>
                            </div>
                        </div>
                    </div>
                    {{-- Main Content --}}
                    <div class="p-8">
                        @if($this->teller_tab==1 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.organization-setting/>
                        @elseif($this->teller_tab==2 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.leader-ship />
                        @elseif($this->teller_tab==3 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.end-of-day />
                        @elseif($this->teller_tab==5 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.divident />
                        @elseif($this->teller_tab==6 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.statistics />
                        @elseif($this->teller_tab==7 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.ratios />
                        @elseif($this->teller_tab==8 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.activities />
                        @elseif($this->teller_tab==9 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.capital-summary />
                        @elseif($this->teller_tab==10 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.share-ownership />
                        @elseif($this->teller_tab==11 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.loan-provision />
                        @elseif($this->teller_tab==12 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.accounts-setup />
                        @elseif($this->teller_tab==13 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.bills-manager />
                        @elseif($this->teller_tab==14 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.institution-accounts />
                        @elseif($this->teller_tab==15 && ($permissions['canManage'] ?? false))
                            <livewire:approvals.process-code-manager />
                        @elseif($this->teller_tab==16 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.data-migration />
                        @elseif($this->teller_tab==17 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.domain-management />
                        @elseif($this->teller_tab==18 && ($permissions['canView'] ?? false))
                            <livewire:profile-setting.audit-trail />
                        @elseif($this->teller_tab==19 && ($permissions['canManage'] ?? false))
                            <livewire:profile-setting.scheduled-tasks />
                        @else
                            {{-- No Access Message --}}
                            <div class="text-center py-12">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Access</h3>
                                <p class="text-gray-500">You don't have permission to access this settings section.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
